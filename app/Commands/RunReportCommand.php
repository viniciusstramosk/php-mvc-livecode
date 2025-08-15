<?php

namespace App\Commands;

use App\Core\Command;
use App\Core\JobQueue;
use App\Jobs\ProcessReportJob;

class RunReportCommand extends Command
{
    protected $signature = 'run:report';
    protected $description = 'Run a report generation job';
    
    public function handle($args = [])
    {
        $this->info("Starting report generation process...");
        
        // Parse arguments
        $reportType = 'general';
        $userId = null;
        
        // Check for report type argument
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--type=')) {
                $reportType = substr($arg, 7);
            } elseif (str_starts_with($arg, '--user=')) {
                $userId = (int) substr($arg, 7);
            }
        }
        
        // Ask for missing information interactively
        if ($reportType === 'general') {
            $input = $this->ask("Report type (sales/users/general)");
            if (!empty($input)) {
                $reportType = $input;
            }
        }
        
        if (!$userId) {
            $input = $this->ask("User ID (optional, press Enter to skip)");
            if (!empty($input)) {
                $userId = (int) $input;
            }
        }
        
        // Prepare job data
        $jobData = [
            'report_type' => $reportType
        ];
        
        if ($userId) {
            $jobData['user_id'] = $userId;
        }
        
        // Create and dispatch the job
        $job = new ProcessReportJob($jobData);
        $jobQueue = new JobQueue();
        
        $this->info("Dispatching job to queue: {$job->getQueue()}");
        $this->info("Report type: {$reportType}");
        
        if ($userId) {
            $this->info("User ID: {$userId}");
        }
        
        $jobId = $jobQueue->dispatch($job);
        
        $this->success("Report job dispatched successfully!");
        $this->info("Job ID: {$jobId}");
        $this->info("Queue: {$job->getQueue()}");
        
        $this->output("");
        $this->info("To process this job, run:");
        $this->output("php cli queue:work --queue={$job->getQueue()}");
        
        // Ask if user wants to process immediately
        if ($this->confirm("Do you want to process this job now?")) {
            $this->info("Processing job immediately...");
            $this->output("");
            
            // Process just this one job
            $jobQueue->work($job->getQueue(), 1);
        }
    }
}
