<?php

namespace App\Core;

class Controller
{
    protected $app;
    protected $db;
    
    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->db = $this->app->getDatabase();
    }
    
    protected function view($view, $data = [])
    {
        $viewPath = $this->app->getConfig('paths')['views'] . $view . '.php';
        
        if (!file_exists($viewPath)) {
            throw new \Exception("View {$view} not found");
        }
        
        extract($data);
        require $viewPath;
    }
    
    protected function json($data, $statusCode = 200)
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
    }
    
    protected function redirect($url)
    {
        header("Location: {$url}");
        exit;
    }
    
    protected function getInput($key = null, $default = null)
    {
        $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
        
        if ($key) {
            return $input[$key] ?? $default;
        }
        
        return $input;
    }
}
