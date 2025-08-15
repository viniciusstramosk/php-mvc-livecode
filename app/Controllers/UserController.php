<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Models\User;
use App\Jobs\SendWelcomeEmailJob;
use App\Core\JobQueue;

class UserController extends Controller
{
    private $userModel;
    
    public function __construct()
    {
        parent::__construct();
        $this->userModel = new User();
    }
    
    public function index()
    {
        try {
            $users = $this->userModel->all();
            $this->json(['users' => $users]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function show($id)
    {
        try {
            $user = $this->userModel->find($id);
            
            if (!$user) {
                $this->json(['error' => 'User not found'], 404);
                return;
            }
            
            $this->json(['user' => $user]);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function create()
    {
        try {
            $data = $this->getInput();
            
            // Validate required fields
            if (empty($data['name']) || empty($data['email'])) {
                $this->json(['error' => 'Name and email are required'], 400);
                return;
            }
            
            $userId = $this->userModel->create($data);
            
            // Dispatch welcome email job
            $jobQueue = new JobQueue();
            $welcomeEmailJob = new SendWelcomeEmailJob(['user_id' => $userId]);
            $jobQueue->dispatch($welcomeEmailJob);
            
            $this->json([
                'message' => 'User created successfully',
                'user_id' => $userId
            ], 201);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function update($id)
    {
        try {
            $user = $this->userModel->find($id);
            
            if (!$user) {
                $this->json(['error' => 'User not found'], 404);
                return;
            }
            
            $data = $this->getInput();
            $this->userModel->update($id, $data);
            
            $this->json(['message' => 'User updated successfully']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
    
    public function delete($id)
    {
        try {
            $user = $this->userModel->find($id);
            
            if (!$user) {
                $this->json(['error' => 'User not found'], 404);
                return;
            }
            
            $this->userModel->delete($id);
            
            $this->json(['message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            $this->json(['error' => $e->getMessage()], 500);
        }
    }
}
