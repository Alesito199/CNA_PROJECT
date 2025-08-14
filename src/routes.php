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

// Estimates
$router->get('/estimates', 'CNA\Controllers\EstimateController@index');
$router->get('/estimates/create', 'CNA\Controllers\EstimateController@create');
$router->post('/estimates', 'CNA\Controllers\EstimateController@store');
$router->get('/estimates/{id}', 'CNA\Controllers\EstimateController@show');
$router->get('/estimates/{id}/edit', 'CNA\Controllers\EstimateController@edit');
$router->post('/estimates/{id}', 'CNA\Controllers\EstimateController@update');
$router->delete('/estimates/{id}', 'CNA\Controllers\EstimateController@delete');
$router->post('/estimates/{id}/convert', 'CNA\Controllers\EstimateController@convertToInvoice');
$router->post('/estimates/{id}/status', 'CNA\Controllers\EstimateController@updateStatus');

// Invoices
$router->get('/invoices', 'CNA\Controllers\InvoiceController@index');
$router->get('/invoices/create', 'CNA\Controllers\InvoiceController@create');
$router->post('/invoices', 'CNA\Controllers\InvoiceController@store');
$router->get('/invoices/{id}', 'CNA\Controllers\InvoiceController@show');
$router->get('/invoices/{id}/edit', 'CNA\Controllers\InvoiceController@edit');
$router->post('/invoices/{id}', 'CNA\Controllers\InvoiceController@update');
$router->delete('/invoices/{id}', 'CNA\Controllers\InvoiceController@delete');
$router->post('/invoices/{id}/payment', 'CNA\Controllers\InvoiceController@recordPayment');
$router->post('/invoices/{id}/status', 'CNA\Controllers\InvoiceController@updateStatus');

// Language switching
$router->get('/lang/{lang}', function($lang) {
    $language = CNA\Utils\Language::getInstance();
    if (in_array($lang, $language->getSupportedLanguages())) {
        $language->setLanguage($lang);
    }
    
    $referer = $_SERVER['HTTP_REFERER'] ?? '/dashboard';
    CNA\Utils\Router::redirect($referer);
});