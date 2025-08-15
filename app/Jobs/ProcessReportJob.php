<?php

namespace App\Jobs;

use App\Core\AbstractJob;

class ProcessReportJob extends AbstractJob
{
    protected $queue = 'teste';
    protected $delay = 0;
    
    public function handle()
    {
        $this->info("Starting ProcessReportJob execution");
        
        $reportType = $this->data['report_type'] ?? 'general';
        $userId = $this->data['user_id'] ?? null;
        
        $this->info("Processing report type: {$reportType}");
        
        if ($userId) {
            $this->info("Processing report for user ID: {$userId}");
        }
        
        // Simulate report processing
        $this->info("Generating report data...");
        sleep(3);
        
        $this->info("Formatting report...");
        sleep(2);
        
        $this->info("Saving report to file...");
        sleep(1);
        
        $reportId = rand(1000, 9999);
        $this->info("Report generated successfully with ID: {$reportId}");
        
        $this->info("ProcessReportJob completed successfully");
    }
}
