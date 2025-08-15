<?php

namespace App\Controllers;

use App\Core\Controller;

class ProductController extends Controller
{
    public function index()
    {
        // TODO: Implement index method
        $this->json(['message' => 'Hello from ProductController!']);
    }
    
    public function show($id)
    {
        // TODO: Implement show method
        $this->json(['id' => $id, 'message' => 'Showing resource']);
    }
    
    public function create()
    {
        // TODO: Implement create method
        $data = $this->getInput();
        $this->json(['message' => 'Resource created', 'data' => $data]);
    }
    
    public function update($id)
    {
        // TODO: Implement update method
        $data = $this->getInput();
        $this->json(['id' => $id, 'message' => 'Resource updated', 'data' => $data]);
    }
    
    public function delete($id)
    {
        // TODO: Implement delete method
        $this->json(['id' => $id, 'message' => 'Resource deleted']);
    }
}
