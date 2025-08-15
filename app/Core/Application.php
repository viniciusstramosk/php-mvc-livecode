<?php

namespace App\Core;

class Application
{
    private static $instance = null;
    private $config;
    private $router;
    private $database;
    
    private function __construct()
    {
        $this->config = require __DIR__ . '/../../config/app.php';
        $this->router = new Router();
        // Database will be initialized lazily when needed
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function run()
    {
        try {
            $this->router->dispatch();
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }
    
    public function getConfig($key = null)
    {
        if ($key) {
            return $this->config[$key] ?? null;
        }
        return $this->config;
    }
    
    public function getDatabase()
    {
        if ($this->database === null) {
            $this->database = new Database($this->config['database']);
        }
        return $this->database;
    }
    
    public function getRouter()
    {
        return $this->router;
    }
    
    private function handleException(\Exception $e)
    {
        if ($this->config['debug']) {
            echo "<h1>Error: " . $e->getMessage() . "</h1>";
            echo "<pre>" . $e->getTraceAsString() . "</pre>";
        } else {
            echo "<h1>Internal Server Error</h1>";
        }
    }
}
