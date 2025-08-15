<?php

require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Application;

// Initialize application
$app = Application::getInstance();

// Define routes
$router = $app->getRouter();

// API Routes
$router->get('/', function() {
    echo json_encode(['message' => 'PHP MVC Framework API', 'version' => '1.0']);
});

$router->get('/users', 'UserController@index');
$router->get('/users/{id}', 'UserController@show');
$router->post('/users', 'UserController@create');
$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@delete');

// Add more routes here...

// Run the application
$app->run();
