<?php

namespace App\Core;

abstract class Command
{
    protected $app;
    protected $signature;
    protected $description;
    
    public function __construct()
    {
        $this->app = Application::getInstance();
    }
    
    abstract public function handle($args = []);
    
    public function getSignature()
    {
        return $this->signature;
    }
    
    public function getDescription()
    {
        return $this->description;
    }
    
    protected function output($message)
    {
        echo $message . PHP_EOL;
    }
    
    protected function info($message)
    {
        $this->output("[INFO] " . $message);
    }
    
    protected function error($message)
    {
        $this->output("[ERROR] " . $message);
    }
    
    protected function success($message)
    {
        $this->output("[SUCCESS] " . $message);
    }
    
    protected function ask($question)
    {
        echo $question . ": ";
        return trim(fgets(STDIN));
    }
    
    protected function confirm($question)
    {
        $response = $this->ask($question . " (y/n)");
        return strtolower($response) === 'y' || strtolower($response) === 'yes';
    }
}
