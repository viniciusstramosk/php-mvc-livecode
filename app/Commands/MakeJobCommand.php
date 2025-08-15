<?php

namespace App\Commands;

use App\Core\Command;

class MakeJobCommand extends Command
{
    protected $signature = 'make:job';
    protected $description = 'Create a new job';
    
    public function handle($args = [])
    {
        if (empty($args)) {
            $this->error("Job name is required");
            $this->output("Usage: php cli make:job JobName");
            return;
        }
        
        $jobName = $args[0];
        
        // Ensure it ends with Job
        if (!str_ends_with($jobName, 'Job')) {
            $jobName .= 'Job';
        }
        
        $jobPath = __DIR__ . '/../Jobs/' . $jobName . '.php';
        
        if (file_exists($jobPath)) {
            $this->error("Job {$jobName} already exists!");
            return;
        }
        
        $queue = $this->ask("Queue name (default: default)");
        if (empty($queue)) {
            $queue = 'default';
        }
        
        $delay = $this->ask("Delay in seconds (default: 0)");
        if (empty($delay)) {
            $delay = 0;
        }
        
        $template = $this->getJobTemplate($jobName, $queue, $delay);
        
        if (file_put_contents($jobPath, $template)) {
            $this->success("Job {$jobName} created successfully!");
            $this->info("Location: app/Jobs/{$jobName}.php");
            $this->info("Queue: {$queue}");
        } else {
            $this->error("Failed to create job {$jobName}");
        }
    }
    
    private function getJobTemplate($jobName, $queue, $delay)
    {
        return "<?php

namespace App\Jobs;

use App\Core\AbstractJob;

class {$jobName} extends AbstractJob
{
    protected \$queue = '{$queue}';
    protected \$delay = {$delay};
    
    public function handle()
    {
        \$this->info(\"Starting {$jobName} execution\");
        
        // TODO: Implement your job logic here
        // You can access job data using \$this->data
        
        // Example:
        // \$userId = \$this->data['user_id'] ?? null;
        // if (\$userId) {
        //     // Process user-related task
        //     \$this->info(\"Processing user ID: {\$userId}\");
        // }
        
        // Simulate some work
        sleep(2);
        
        \$this->info(\"{$jobName} completed successfully\");
    }
}
";
    }
}
