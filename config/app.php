<?php

return [
    'app_name' => 'PHP MVC Framework',
    'app_env' => 'development',
    'debug' => true,
    
    'database' => [
        'host' => 'localhost',
        'dbname' => 'php_mvc_db',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8mb4'
    ],

    'paths' => [
        'views' => __DIR__ . '/../app/Views/',
        'logs' => __DIR__ . '/../storage/logs/',
        'cache' => __DIR__ . '/../storage/cache/'
    ]
];
