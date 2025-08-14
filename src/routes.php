<?php

// Application routes
use CNA\Controllers\AuthController;
use CNA\Controllers\DashboardController;
use CNA\Controllers\ClientController;

// Authentication routes
$router->get('/', function() {
    if (CNA\Utils\Session::isAuthenticated()) {
        CNA\Utils\Router::redirect('/dashboard');
    } else {
        CNA\Utils\Router::redirect('/login');
    }
});

$router->get('/login', 'CNA\Controllers\AuthController@showLogin');
$router->post('/login', 'CNA\Controllers\AuthController@login');
$router->get('/register', 'CNA\Controllers\AuthController@showRegister');
$router->post('/register', 'CNA\Controllers\AuthController@register');
$router->post('/logout', 'CNA\Controllers\AuthController@logout');
$router->get('/logout', 'CNA\Controllers\AuthController@logout');

// Dashboard
$router->get('/dashboard', 'CNA\Controllers\DashboardController@index');

// Clients
$router->get('/clients', 'CNA\Controllers\ClientController@index');
$router->get('/clients/create', 'CNA\Controllers\ClientController@create');
$router->post('/clients', 'CNA\Controllers\ClientController@store');
$router->get('/clients/{id}', 'CNA\Controllers\ClientController@show');
$router->get('/clients/{id}/edit', 'CNA\Controllers\ClientController@edit');
$router->post('/clients/{id}', 'CNA\Controllers\ClientController@update');
$router->delete('/clients/{id}', 'CNA\Controllers\ClientController@delete');

// Language switching
$router->get('/lang/{lang}', function($lang) {
    $language = CNA\Utils\Language::getInstance();
    if (in_array($lang, $language->getSupportedLanguages())) {
        $language->setLanguage($lang);
    }
    
    $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
    CNA\Utils\Router::redirect($referer);
});