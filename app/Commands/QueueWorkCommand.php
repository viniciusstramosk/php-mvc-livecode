<?php

namespace App\Commands;

use App\Core\Command;
use App\Core\JobQueue;

class QueueWorkCommand extends Command
{
    protected $signature = 'queue:work';
    protected $description = 'Process jobs from the queue';
    
    public function handle($args = [])
    {
        $queue = 'default';
        $maxJobs = 0;
        
        // Parse arguments
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--queue=')) {
                $queue = substr($arg, 8);
            } elseif (str_starts_with($arg, '--max-jobs=')) {
                $maxJobs = (int) substr($arg, 11);
            }
        }
        
        $this->info("Starting queue worker...");
        $this->info("Queue: {$queue}");
        
        if ($maxJobs > 0) {
            $this->info("Max jobs: {$maxJobs}");
        } else {
            $this->info("Max jobs: unlimited (Ctrl+C to stop)");
        }
        
        $jobQueue = new JobQueue();
        $jobQueue->work($queue, $maxJobs);
    }
}
