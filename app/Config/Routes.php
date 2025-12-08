<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

$routes->get('/', 'Home::index');

$routes->group('api/v1', ['namespace' => 'App\Controllers\Api'], function ($routes) {

    // Mobile User APIs (Public)
    // $routes->post('mobile/register', 'UsersController::createMobile');
    // $routes->post('mobile/login', 'UsersController::loginMobile');

    // Token Generated (requires valid credentials)
    $routes->post('token/generate', 'UsersController::generateToken');

    // Token refresh (requires Bearer token)
    $routes->post('token/refresh', 'UsersController::refreshToken');

    // Public: login/logout
    $routes->post('users/login', 'UsersController::login');
    $routes->post('users/logout', 'UsersController::logout');


    // Public: index/show
    $publicControllers = [
        'modules'               => 'ModulesController',
        'module-permissions'    => 'ModulePermissionsController',
        'roles'                 => 'RolesController',
        'users'                 => 'UsersController',
        'schools'                 => 'SchoolsController',
      
    ];

    foreach ($publicControllers as $route => $controller) {
        $routes->resource($route, ['controller' => $controller, 'only' => ['index', 'show']]);
    }

    // Protected (Auth)
    $routes->group('', ['filter' => 'auth'], function ($routes) use ($publicControllers) {

        foreach ($publicControllers as $route => $controller) {
            $routes->post($route, "$controller::create");
            $routes->put("$route/(:num)", "$controller::update/$1");
            $routes->delete("$route/(:num)", "$controller::delete/$1");
        }
    });
});