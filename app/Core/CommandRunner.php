<?php

namespace App\Core;

class CommandRunner
{
    private $commands = [];
    
    public function __construct()
    {
        $this->registerBuiltInCommands();
    }
    
    public function register($command)
    {
        if (!$command instanceof Command) {
            throw new \Exception("Command must extend App\\Core\\Command");
        }
        
        $signature = $command->getSignature();
        $this->commands[$signature] = $command;
    }
    
    public function run($argv)
    {
        if (count($argv) < 2) {
            $this->showHelp();
            return;
        }
        
        $commandName = $argv[1];
        $args = array_slice($argv, 2);
        
        if ($commandName === 'help' || $commandName === '--help') {
            $this->showHelp();
            return;
        }
        
        if (!isset($this->commands[$commandName])) {
            echo "Command '{$commandName}' not found." . PHP_EOL;
            $this->showHelp();
            return;
        }
        
        $command = $this->commands[$commandName];
        $command->handle($args);
    }
    
    private function showHelp()
    {
        echo "Available commands:" . PHP_EOL;
        echo "==================" . PHP_EOL;
        
        foreach ($this->commands as $signature => $command) {
            echo sprintf("  %-20s %s", $signature, $command->getDescription()) . PHP_EOL;
        }
        
        echo PHP_EOL . "Usage: php cli [command] [arguments]" . PHP_EOL;
    }
    
    private function registerBuiltInCommands()
    {
        // Commands serão registrados automaticamente
        $commandFiles = glob(__DIR__ . '/../Commands/*.php');
        
        foreach ($commandFiles as $file) {
            $className = basename($file, '.php');
            $fullClass = "App\\Commands\\{$className}";
            
            if (class_exists($fullClass)) {
                $command = new $fullClass();
                if ($command instanceof Command) {
                    $this->register($command);
                }
            }
        }
    }
}
