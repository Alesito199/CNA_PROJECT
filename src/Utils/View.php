<?php

namespace CNA\Utils;

use CNA\Config\Config;

class View
{
    private array $data = [];
    private string $layout = 'app';
    
    public function __construct()
    {
        // Set default view data
        $this->data = [
            'config' => Config::all(),
            'user' => Session::user(),
            'csrf_token' => Session::csrf(),
            'flash_messages' => $this->getFlashMessages(),
            'current_url' => $_SERVER['REQUEST_URI'] ?? '/',
        ];
    }

    public function render(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        
        $viewFile = $this->findViewFile($view);
        if (!$viewFile) {
            throw new \RuntimeException("View file not found: {$view}");
        }

        // Extract data to variables
        extract($this->data);

        // Start output buffering for content
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        // Render with layout if specified
        if ($this->layout) {
            $layoutFile = $this->findViewFile("layouts/{$this->layout}");
            if ($layoutFile) {
                extract($this->data);
                require $layoutFile;
            } else {
                echo $content;
            }
        } else {
            echo $content;
        }
    }

    public function setLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    public function with(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    private function findViewFile(string $view): ?string
    {
        $viewPath = SRC_DIR . '/Views/' . str_replace('.', '/', $view) . '.php';
        return file_exists($viewPath) ? $viewPath : null;
    }

    private function getFlashMessages(): array
    {
        return [
            'success' => Session::flash('success'),
            'error' => Session::flash('error'),
            'warning' => Session::flash('warning'),
            'info' => Session::flash('info'),
        ];
    }

    public static function make(string $view, array $data = []): self
    {
        $instance = new self();
        return $instance->with($data);
    }

    // Helper methods for views
    public function asset(string $path): string
    {
        $baseUrl = Config::get('app.url', 'http://localhost');
        return rtrim($baseUrl, '/') . '/assets/' . ltrim($path, '/');
    }

    public function url(string $path = ''): string
    {
        return Router::url($path);
    }

    public function e(string $text): string
    {
        return Security::escapeHtml($text);
    }

    public function old(string $key, string $default = ''): string
    {
        return Session::get('old_input')[$key] ?? $default;
    }
}