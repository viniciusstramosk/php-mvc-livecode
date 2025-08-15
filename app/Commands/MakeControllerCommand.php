<?php

namespace App\Commands;

use App\Core\Command;

class MakeControllerCommand extends Command
{
    protected $signature = 'make:controller';
    protected $description = 'Create a new controller';
    
    public function handle($args = [])
    {
        if (empty($args)) {
            $this->error("Controller name is required");
            $this->output("Usage: php cli make:controller ControllerName");
            return;
        }
        
        $controllerName = $args[0];
        
        // Ensure it ends with Controller
        if (!str_ends_with($controllerName, 'Controller')) {
            $controllerName .= 'Controller';
        }
        
        $controllerPath = __DIR__ . '/../Controllers/' . $controllerName . '.php';
        
        if (file_exists($controllerPath)) {
            $this->error("Controller {$controllerName} already exists!");
            return;
        }
        
        $template = $this->getControllerTemplate($controllerName);
        
        if (file_put_contents($controllerPath, $template)) {
            $this->success("Controller {$controllerName} created successfully!");
            $this->info("Location: app/Controllers/{$controllerName}.php");
        } else {
            $this->error("Failed to create controller {$controllerName}");
        }
    }
    
    private function getControllerTemplate($controllerName)
    {
        return "<?php

namespace App\Controllers;

use App\Core\Controller;

class {$controllerName} extends Controller
{
    public function index()
    {
        // TODO: Implement index method
        \$this->json(['message' => 'Hello from {$controllerName}!']);
    }
    
    public function show(\$id)
    {
        // TODO: Implement show method
        \$this->json(['id' => \$id, 'message' => 'Showing resource']);
    }
    
    public function create()
    {
        // TODO: Implement create method
        \$data = \$this->getInput();
        \$this->json(['message' => 'Resource created', 'data' => \$data]);
    }
    
    public function update(\$id)
    {
        // TODO: Implement update method
        \$data = \$this->getInput();
        \$this->json(['id' => \$id, 'message' => 'Resource updated', 'data' => \$data]);
    }
    
    public function delete(\$id)
    {
        // TODO: Implement delete method
        \$this->json(['id' => \$id, 'message' => 'Resource deleted']);
    }
}
";
    }
}
