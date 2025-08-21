<?php

namespace App\Core;

class JobQueue
{
    private $db;
    private $app;
    
    public function __construct()
    {
        $this->app = Application::getInstance();
        $this->db = $this->app->getDatabase();
        $this->createJobsTable();
    }
    
    public function dispatch(Job $job)
    {
        $jobData = [
            'job_class' => $job->getName(),
            'queue' => $job->getQueue(),
            'payload' => json_encode($job->getData()),
            'status' => 'pending',
            'available_at' => date('Y-m-d H:i:s', time() + $job->getDelay()),
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->db->insert('jobs', $jobData);
    }
    
    public function work($queue = 'default', $maxJobs = 0)
    {
        $processedJobs = 0;
        
        echo "Starting job worker for queue: {$queue}" . PHP_EOL;
        
        while (true) {
            $job = $this->getNextJob($queue);
            
            if (!$job) {
                if ($maxJobs > 0 && $processedJobs >= $maxJobs) {
                    break;
                }
                
                echo "No jobs available, waiting..." . PHP_EOL;
                sleep(5);
                continue;
            }
            
            $this->processJob($job);
            $processedJobs++;
            
            if ($maxJobs > 0 && $processedJobs >= $maxJobs) {
                break;
            }
        }
        
        echo "Worker finished. Processed {$processedJobs} jobs." . PHP_EOL;
    }
    
    private function getNextJob($queue)
    {
        return $this->db->selectOne(
            "SELECT * FROM jobs 
             WHERE queue = :queue 
             AND status = 'pending' 
             AND available_at <= datetime('now') 
             ORDER BY created_at ASC 
             LIMIT 1",
            ['queue' => $queue]
        );
    }
    
    private function processJob($jobData)
    {
        $jobId = $jobData['id'];
        
        try {
            // Marcar job como processando
            $this->updateJobStatus($jobId, 'processing');
            
            $jobClass = $jobData['job_class'];
            $payload = json_decode($jobData['payload'], true);
            
            if (!class_exists($jobClass)) {
                throw new \Exception("Job class {$jobClass} not found");
            }
            
            $job = new $jobClass($payload);
            
            if (!$job instanceof Job) {
                throw new \Exception("Job must implement Job interface");
            }
            
            echo "Processing job: {$jobClass} (ID: {$jobId})" . PHP_EOL;
            
            $job->handle();
            
            // Marcar como concluído
            $this->updateJobStatus($jobId, 'completed');
            
            echo "Job completed: {$jobClass} (ID: {$jobId})" . PHP_EOL;
            
        } catch (\Exception $e) {
            $this->updateJobStatus($jobId, 'failed', $e->getMessage());
            echo "Job failed: {$jobData['job_class']} (ID: {$jobId}) - Error: " . $e->getMessage() . PHP_EOL;
        }
    }
    
    private function updateJobStatus($jobId, $status, $error = null)
    {
        $data = [
            'status' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ];
        
        if ($error) {
            $data['error_message'] = $error;
        }
        
        $this->db->update('jobs', $data, 'id = :id', ['id' => $jobId]);
    }
    
    private function createJobsTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS jobs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            job_class VARCHAR(255) NOT NULL,
            queue VARCHAR(100) DEFAULT 'default',
            payload TEXT,
            status ENUM('pending', 'processing', 'completed', 'failed') DEFAULT 'pending',
            error_message TEXT NULL,
            available_at DATETIME NOT NULL,
            created_at DATETIME NOT NULL,
            updated_at DATETIME NULL,
            INDEX idx_queue_status (queue, status),
            INDEX idx_available_at (available_at)
        )";
        
        try {
            $this->db->query($sql);
        } catch (\Exception $e) {
            // Table might already exist, ignore error
        }
    }
    
    public function getJobStats($queue = null)
    {
        $where = $queue ? "WHERE queue = :queue" : "";
        $params = $queue ? ['queue' => $queue] : [];
        
        return $this->db->select(
            "SELECT status, COUNT(*) as count 
             FROM jobs {$where} 
             GROUP BY status",
            $params
        );
    }
    
    public function clearCompletedJobs($olderThan = '7 days')
    {
        return $this->db->delete(
            'jobs',
            "status = 'completed' AND updated_at < DATE_SUB(NOW(), INTERVAL {$olderThan})"
        );
    }
}
