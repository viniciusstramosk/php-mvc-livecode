<?php

namespace App\Commands;

use App\Core\Command;

class MakeModelCommand extends Command
{
    protected $signature = 'make:model';
    protected $description = 'Create a new model';
    
    public function handle($args = [])
    {
        if (empty($args)) {
            $this->error("Model name is required");
            $this->output("Usage: php cli make:model ModelName");
            return;
        }
        
        $modelName = $args[0];
        $modelPath = __DIR__ . '/../Models/' . $modelName . '.php';
        
        if (file_exists($modelPath)) {
            $this->error("Model {$modelName} already exists!");
            return;
        }
        
        $tableName = $this->ask("Table name (leave empty for auto-generated)");
        if (empty($tableName)) {
            $tableName = strtolower($modelName) . 's';
        }
        
        $fillableFields = $this->ask("Fillable fields (comma-separated, optional)");
        $fillable = [];
        if (!empty($fillableFields)) {
            $fillable = array_map('trim', explode(',', $fillableFields));
        }
        
        $template = $this->getModelTemplate($modelName, $tableName, $fillable);
        
        if (file_put_contents($modelPath, $template)) {
            $this->success("Model {$modelName} created successfully!");
            $this->info("Location: app/Models/{$modelName}.php");
            $this->info("Table: {$tableName}");
        } else {
            $this->error("Failed to create model {$modelName}");
        }
    }
    
    private function getModelTemplate($modelName, $tableName, $fillable)
    {
        $fillableString = '';
        if (!empty($fillable)) {
            $fillableArray = "'" . implode("', '", $fillable) . "'";
            $fillableString = "\n    protected \$fillable = [{$fillableArray}];";
        }
        
        return "<?php

namespace App\Models;

use App\Core\Model;

class {$modelName} extends Model
{
    protected \$table = '{$tableName}';{$fillableString}
    
    // Add your custom methods here
    
    public static function findByName(\$name)
    {
        \$instance = new static();
        return \$instance->where('name', \$name);
    }
}
";
    }
}
