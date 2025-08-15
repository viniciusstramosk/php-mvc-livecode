<?php

namespace App\Core;

class Router
{
    private $routes = [];
    
    public function get($uri, $controller)
    {
        $this->addRoute('GET', $uri, $controller);
    }
    
    public function post($uri, $controller)
    {
        $this->addRoute('POST', $uri, $controller);
    }
    
    public function put($uri, $controller)
    {
        $this->addRoute('PUT', $uri, $controller);
    }
    
    public function delete($uri, $controller)
    {
        $this->addRoute('DELETE', $uri, $controller);
    }
    
    private function addRoute($method, $uri, $controller)
    {
        $this->routes[$method][$uri] = $controller;
    }
    
    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = $this->getUri();
        
        if (isset($this->routes[$method][$uri])) {
            $controller = $this->routes[$method][$uri];
            $this->callController($controller);
        } else {
            $this->notFound();
        }
    }
    
    private function getUri()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $uri = parse_url($uri, PHP_URL_PATH);
        return $uri === '/' ? '/' : rtrim($uri, '/');
    }
    
    private function callController($controller)
    {
        if (is_string($controller)) {
            // Format: "ControllerName@method"
            $parts = explode('@', $controller);
            $controllerClass = 'App\\Controllers\\' . $parts[0];
            $method = $parts[1] ?? 'index';
            
            if (class_exists($controllerClass)) {
                $instance = new $controllerClass();
                if (method_exists($instance, $method)) {
                    $instance->$method();
                } else {
                    throw new \Exception("Method {$method} not found in {$controllerClass}");
                }
            } else {
                throw new \Exception("Controller {$controllerClass} not found");
            }
        } elseif (is_callable($controller)) {
            $controller();
        }
    }
    
    private function notFound()
    {
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
    }
}
