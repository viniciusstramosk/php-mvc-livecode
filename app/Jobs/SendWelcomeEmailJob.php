<?php

namespace App\Jobs;

use App\Core\AbstractJob;
use App\Models\User;

class SendWelcomeEmailJob extends AbstractJob
{
    protected $queue = 'emails';
    protected $delay = 0;
    
    public function handle()
    {
        $userId = $this->data['user_id'] ?? null;
        
        if (!$userId) {
            $this->error("User ID is required");
            return;
        }
        
        $this->info("Sending welcome email for user ID: {$userId}");
        
        $userModel = new User();
        $user = $userModel->find($userId);
        
        if (!$user) {
            $this->error("User not found: {$userId}");
            return;
        }
        
        // Simulate email sending
        $this->info("Preparing welcome email for: {$user['email']}");
        
        // Here you would integrate with your email service
        // For now, we'll just simulate the process
        sleep(2); // Simulate email sending time
        
        $emailContent = "Welcome {$user['name']}! Thanks for joining our platform.";
        
        // Log the "email" instead of actually sending it
        $this->info("Email sent to {$user['email']}: {$emailContent}");
        
        // You could also store email logs in database
        // $this->logEmailSent($user['email'], $emailContent);
        
        $this->info("Welcome email job completed for user: {$user['name']}");
    }
    
    private function logEmailSent($email, $content)
    {
        // Example of logging email to database
        $db = $this->app->getDatabase();
        $db->insert('email_logs', [
            'email' => $email,
            'content' => $content,
            'sent_at' => date('Y-m-d H:i:s')
        ]);
    }
}
