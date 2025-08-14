<?php

namespace CNA\Controllers;

use CNA\Utils\View;
use CNA\Utils\Session;
use CNA\Utils\Language;

abstract class BaseController
{
    protected View $view;
    protected Language $lang;

    public function __construct()
    {
        $this->view = new View();
        $this->lang = Language::getInstance();
    }

    protected function requireAuth(): void
    {
        if (!Session::isAuthenticated()) {
            Session::flash('error', $this->lang->translate('auth.access_denied'));
            $this->redirect('/login');
        }
    }

    protected function requireAdmin(): void
    {
        $this->requireAuth();
        
        $user = Session::user();
        if (!$user || $user['role'] !== 'admin') {
            Session::flash('error', $this->lang->translate('auth.access_denied'));
            $this->redirect('/dashboard');
        }
    }

    protected function redirect(string $url): void
    {
        header("Location: {$url}");
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function validateCsrf(): bool
    {
        $token = $_POST['csrf_token'] ?? $_GET['csrf_token'] ?? '';
        return Session::verifyCsrf($token);
    }

    protected function getInput(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    protected function hasInput(string $key): bool
    {
        return isset($_POST[$key]) || isset($_GET[$key]);
    }

    protected function validate(array $rules, array $data): array
    {
        $errors = [];
        
        foreach ($rules as $field => $rule) {
            $value = $data[$field] ?? '';
            
            // Required validation
            if (strpos($rule, 'required') !== false && empty($value)) {
                $errors[$field] = $this->lang->translate('common.required');
                continue;
            }
            
            // Email validation
            if (strpos($rule, 'email') !== false && !empty($value)) {
                if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $errors[$field] = $this->lang->translate('common.invalid_email');
                }
            }
            
            // Phone validation
            if (strpos($rule, 'phone') !== false && !empty($value)) {
                $digits = preg_replace('/\D/', '', $value);
                if (strlen($digits) < 10 || strlen($digits) > 15) {
                    $errors[$field] = $this->lang->translate('common.invalid_phone');
                }
            }
            
            // Min length validation
            if (preg_match('/min:(\d+)/', $rule, $matches)) {
                $minLength = (int) $matches[1];
                if (strlen($value) < $minLength) {
                    $errors[$field] = "Minimum {$minLength} characters required";
                }
            }
            
            // Max length validation
            if (preg_match('/max:(\d+)/', $rule, $matches)) {
                $maxLength = (int) $matches[1];
                if (strlen($value) > $maxLength) {
                    $errors[$field] = "Maximum {$maxLength} characters allowed";
                }
            }
        }
        
        return $errors;
    }

    protected function setOldInput(array $data): void
    {
        Session::put('old_input', $data);
    }

    protected function clearOldInput(): void
    {
        Session::remove('old_input');
    }
}