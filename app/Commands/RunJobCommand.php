<?php

namespace App\Commands;

use App\Core\Command;

class RunJobCommand extends Command
{
    protected $signature = 'job:run';
    protected $description = 'Run any job class directly (for testing purposes)';
    
    public function handle($args = [])
    {
        $this->info("Generic Job Runner");
        $this->output("=================");
        
        // Parse job class from arguments
        $jobClass = null;
        $jobData = [];
        
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--class=')) {
                $jobClass = substr($arg, 8);
            } elseif (str_starts_with($arg, '--data=')) {
                $jsonData = substr($arg, 7);
                $jobData = json_decode($jsonData, true) ?: [];
            }
        }
        
        // Ask for job class if not provided
        if (!$jobClass) {
            $this->info("Available jobs:");
            $this->output("- ProcessReportJob");
            $this->output("- SendWelcomeEmailJob");
            $this->output("");
            
            $jobClass = $this->ask("Job class name (without 'Job' suffix)");
            if (!str_ends_with($jobClass, 'Job')) {
                $jobClass .= 'Job';
            }
        }
        
        // Build full class name
        $fullClass = "App\\Jobs\\{$jobClass}";
        
        // Check if class exists
        if (!class_exists($fullClass)) {
            $this->error("Job class {$fullClass} not found!");
            $this->info("Available jobs in app/Jobs/:");
            
            $jobFiles = glob(__DIR__ . '/../Jobs/*.php');
            foreach ($jobFiles as $file) {
                $className = basename($file, '.php');
                $this->output("- {$className}");
            }
            return;
        }
        
        // Ask for job data if not provided
        if (empty($jobData)) {
            $this->info("Job data (JSON format, or press Enter for empty):");
            $input = $this->ask("Data");
            if (!empty($input)) {
                $jobData = json_decode($input, true);
                if ($jobData === null) {
                    $this->warning("Invalid JSON, using empty data");
                    $jobData = [];
                }
            }
        }
        
        try {
            // Create job instance
            $job = new $fullClass($jobData);
            
            $this->output("");
            $this->success("Job {$jobClass} created successfully!");
            $this->info("Queue: {$job->getQueue()}");
            $this->info("Delay: {$job->getDelay()} seconds");
            $this->info("Data: " . json_encode($jobData));
            
            $this->output("");
            
            if ($this->confirm("Execute this job now?")) {
                $this->output("");
                $this->info("Executing job...");
                $this->output(str_repeat("-", 60));
                
                $startTime = microtime(true);
                $job->handle();
                $endTime = microtime(true);
                
                $duration = round($endTime - $startTime, 2);
                
                $this->output(str_repeat("-", 60));
                $this->success("Job executed successfully in {$duration} seconds!");
            }
            
        } catch (\Exception $e) {
            $this->error("Error creating/executing job: " . $e->getMessage());
        }
    }
}
