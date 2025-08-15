<?php

namespace App\Core;

abstract class AbstractJob implements Job
{
    protected $data;
    protected $queue = 'default';
    protected $delay = 0;
    protected $app;
    
    public function __construct($data = [])
    {
        $this->data = $data;
        $this->app = Application::getInstance();
    }
    
    abstract public function handle();
    
    public function getName()
    {
        return static::class;
    }
    
    public function getQueue()
    {
        return $this->queue;
    }
    
    public function getDelay()
    {
        return $this->delay;
    }
    
    public function getData()
    {
        return $this->data;
    }
    
    protected function log($message, $level = 'info')
    {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] [" . strtoupper($level) . "] {$message}";
        
        // Always output to console
        echo $formattedMessage . PHP_EOL;
        
        // Try to also log to file
        try {
            $logMessage = "[{$timestamp}] [{$level}] {$this->getName()}: {$message}" . PHP_EOL;
            $logFile = $this->app->getConfig('paths')['logs'] . 'jobs.log';
            file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
        } catch (\Exception $e) {
            // Ignore file logging errors
        }
    }
    
    protected function info($message)
    {
        $this->log($message, 'info');
    }
    
    protected function error($message)
    {
        $this->log($message, 'error');
    }
    
    protected function warning($message)
    {
        $this->log($message, 'warning');
    }
}
