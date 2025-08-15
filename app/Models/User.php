<?php

namespace App\Models;

use App\Core\Model;

class User extends Model
{
    protected $table = 'users';
    protected $fillable = ['name', 'email', 'password'];
    
    public function findByEmail($email)
    {
        return $this->where('email', $email);
    }
    
    public function getActiveUsers()
    {
        return $this->where('status', 'active');
    }
    
    public function createWithHashedPassword($data)
    {
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        return $this->create($data);
    }
}
