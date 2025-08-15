<?php

namespace App\Commands;

use App\Core\Command;
use App\Jobs\ProcessReportJob;

class SimulateReportCommand extends Command
{
    protected $signature = 'simulate:report';
    protected $description = 'Simulate running a report generation job (without database)';
    
    public function handle($args = [])
    {
        $this->info("Starting report generation simulation...");
        
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
        
        // Create the job (without dispatching to queue)
        $job = new ProcessReportJob($jobData);
        
        $this->info("Job Configuration:");
        $this->info("- Report type: {$reportType}");
        $this->info("- Queue: {$job->getQueue()}");
        $this->info("- Delay: {$job->getDelay()} seconds");
        
        if ($userId) {
            $this->info("- User ID: {$userId}");
        }
        
        $this->output("");
        $this->success("Job configured successfully!");
        
        // Ask if user wants to execute immediately
        if ($this->confirm("Do you want to execute this job now?")) {
            $this->output("");
            $this->info("Executing job directly...");
            $this->output("=" . str_repeat("=", 50));
            
            // Execute the job directly
            $job->handle();
            
            $this->output("=" . str_repeat("=", 50));
            $this->success("Job simulation completed!");
        } else {
            $this->info("Job not executed. In a real scenario, this would be queued for later processing.");
            $this->info("Command to process queued jobs: php cli queue:work --queue={$job->getQueue()}");
        }
    }
}
