<?php

namespace CNA\Utils;

use CNA\Config\Config;

class Language
{
    private static ?Language $instance = null;
    private string $currentLanguage;
    private array $translations = [];

    public function __construct()
    {
        $this->currentLanguage = $this->detectLanguage();
        $this->loadTranslations();
    }

    public static function getInstance(): Language
    {
        if (self::$instance === null) {
            self::$instance = new Language();
        }
        return self::$instance;
    }

    private function detectLanguage(): string
    {
        // Priority: Session > Query parameter > Browser > Default
        $supportedLanguages = Config::get('language.supported', ['en']);
        
        // Check session
        if (Session::has('language')) {
            $lang = Session::get('language');
            if (in_array($lang, $supportedLanguages)) {
                return $lang;
            }
        }
        
        // Check query parameter
        if (isset($_GET['lang'])) {
            $lang = $_GET['lang'];
            if (in_array($lang, $supportedLanguages)) {
                Session::put('language', $lang);
                return $lang;
            }
        }
        
        // Check browser language
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            $browserLangs = explode(',', $_SERVER['HTTP_ACCEPT_LANGUAGE']);
            foreach ($browserLangs as $browserLang) {
                $lang = substr(trim($browserLang), 0, 2);
                if (in_array($lang, $supportedLanguages)) {
                    Session::put('language', $lang);
                    return $lang;
                }
            }
        }
        
        return Config::get('language.default', 'en');
    }

    private function loadTranslations(): void
    {
        $langFile = SRC_DIR . '/Languages/' . $this->currentLanguage . '.php';
        if (file_exists($langFile)) {
            $this->translations = require $langFile;
        }
    }

    public function translate(string $key, array $params = []): string
    {
        $translation = $this->getNestedValue($this->translations, $key);
        
        if ($translation === null) {
            return $key; // Return key if translation not found
        }
        
        // Replace parameters
        foreach ($params as $paramKey => $paramValue) {
            $translation = str_replace(':' . $paramKey, $paramValue, $translation);
        }
        
        return $translation;
    }

    private function getNestedValue(array $array, string $key)
    {
        $keys = explode('.', $key);
        $value = $array;
        
        foreach ($keys as $k) {
            if (!is_array($value) || !array_key_exists($k, $value)) {
                return null;
            }
            $value = $value[$k];
        }
        
        return $value;
    }

    public function getCurrentLanguage(): string
    {
        return $this->currentLanguage;
    }

    public function setLanguage(string $language): void
    {
        $supportedLanguages = Config::get('language.supported', ['en']);
        if (in_array($language, $supportedLanguages)) {
            $this->currentLanguage = $language;
            Session::put('language', $language);
            $this->loadTranslations();
        }
    }

    public function getSupportedLanguages(): array
    {
        return Config::get('language.supported', ['en']);
    }

    public static function t(string $key, array $params = []): string
    {
        return self::getInstance()->translate($key, $params);
    }

    public function formatNumber(float $number, int $decimals = 2): string
    {
        $locale_info = [
            'en' => ['decimal_point' => '.', 'thousands_sep' => ','],
            'es' => ['decimal_point' => ',', 'thousands_sep' => '.'],
        ];
        
        $info = $locale_info[$this->currentLanguage] ?? $locale_info['en'];
        return number_format($number, $decimals, $info['decimal_point'], $info['thousands_sep']);
    }

    public function formatCurrency(float $amount): string
    {
        $formatted = $this->formatNumber($amount, 2);
        return $this->currentLanguage === 'es' ? $formatted . ' $' : '$' . $formatted;
    }

    public function formatDate(string $date, string $format = 'medium'): string
    {
        $timestamp = strtotime($date);
        if (!$timestamp) {
            return $date;
        }
        
        $formats = [
            'en' => [
                'short' => 'm/d/Y',
                'medium' => 'M j, Y',
                'long' => 'F j, Y',
                'datetime' => 'm/d/Y g:i A',
            ],
            'es' => [
                'short' => 'd/m/Y',
                'medium' => 'j M Y',
                'long' => 'j \d\e F \d\e Y',
                'datetime' => 'd/m/Y H:i',
            ],
        ];
        
        $formatStr = $formats[$this->currentLanguage][$format] ?? $formats['en'][$format] ?? 'Y-m-d';
        return date($formatStr, $timestamp);
    }
}