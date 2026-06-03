<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */

// Root URL redirects to Login by default
$routes->get('/', 'AuthController::login');

// Registration Flow Routes
$routes->get('register', 'RegisterController::index');
$routes->post('register', 'RegisterController::store');
$routes->get('register/verify-notice', 'RegisterController::verifyNotice');
$routes->get('register/verify', 'RegisterController::verify');

// Authentication Routes
$routes->get('login', 'AuthController::login');
$routes->post('login', 'AuthController::attempt');
$routes->get('logout', 'AuthController::logout');

// Secure Workspace Routes (Filtered by AuthFilter)
$routes->group('', ['filter' => 'auth'], function(RouteCollection $routes) {
    $routes->get('dashboard', 'DashboardController::index');

    // Clinic Settings (Owner Only)
    $routes->get('profile', 'ProfileController::index', ['filter' => 'role:owner']);
    $routes->post('profile', 'ProfileController::update', ['filter' => 'role:owner']);

    // Employee Management (Owner Only)
    $routes->group('employees', ['filter' => 'role:owner'], function(RouteCollection $routes) {
        $routes->get('/', 'EmployeeController::index');
        $routes->get('create', 'EmployeeController::create');
        $routes->post('create', 'EmployeeController::store');
        $routes->get('edit/(:num)', 'EmployeeController::edit/$1');
        $routes->post('edit/(:num)', 'EmployeeController::update/$1');
        $routes->get('toggle/(:num)', 'EmployeeController::toggleStatus/$1');
    });

    // Service Management (Listing available to all roles, editing restricted to Owner)
    $routes->get('services', 'ServiceController::index');
    $routes->group('services', ['filter' => 'role:owner'], function(RouteCollection $routes) {
        $routes->get('create', 'ServiceController::create');
        $routes->post('create', 'ServiceController::store');
        $routes->get('edit/(:num)', 'ServiceController::edit/$1');
        $routes->post('edit/(:num)', 'ServiceController::update/$1');
        $routes->post('delete/(:num)', 'ServiceController::delete/$1');
    });

    // Customer Management
    $routes->group('customers', function(RouteCollection $routes) {
        $routes->get('/', 'CustomerController::index');
        $routes->get('create', 'CustomerController::create');
        $routes->post('create', 'CustomerController::store');
        $routes->get('show/(:num)', 'CustomerController::show/$1');
        $routes->get('edit/(:num)', 'CustomerController::edit/$1');
        $routes->post('edit/(:num)', 'CustomerController::update/$1');
        $routes->post('delete/(:num)', 'CustomerController::delete/$1');
    });

    // Pet Management
    $routes->group('pets', function(RouteCollection $routes) {
        $routes->get('/', 'PetController::index');
        $routes->get('create/(:num)', 'PetController::create/$1'); // customerId parameter
        $routes->post('create', 'PetController::store');
        $routes->get('show/(:num)', 'PetController::show/$1');
        $routes->get('edit/(:num)', 'PetController::edit/$1');
        $routes->post('edit/(:num)', 'PetController::update/$1');
        $routes->post('delete/(:num)', 'PetController::delete/$1');
    });

    // Visit Management
    $routes->group('visits', function(RouteCollection $routes) {
        $routes->get('/', 'VisitController::index');
        $routes->get('create', 'VisitController::create');
        $routes->post('create', 'VisitController::store');
        $routes->get('examine/(:num)', 'VisitController::examine/$1');
        $routes->post('examine/(:num)', 'VisitController::saveExamination/$1');
        $routes->get('cancel/(:num)', 'VisitController::cancel/$1');
    });

    // Medical Records
    $routes->get('records', 'MedicalRecordController::index');
    $routes->get('records/show/(:num)', 'MedicalRecordController::show/$1');

    // Invoices & Payments
    $routes->group('invoices', function(RouteCollection $routes) {
        $routes->get('/', 'InvoiceController::index');
        $routes->get('show/(:num)', 'InvoiceController::show/$1');
        $routes->get('download/(:num)', 'InvoiceController::download/$1');
        $routes->post('pay/(:num)', 'InvoiceController::pay/$1');
    });

    // Analytics & Reporting
    $routes->get('reports', 'ReportsController::index');
});

