# PHP MVC Framework

Um framework PHP MVC simples e limpo para live-coding, com sistema de commands e jobs.

## Estrutura do Projeto

```
php-mvc-livecode/
├── app/
│   ├── Commands/         # Commands CLI
│   ├── Controllers/      # Controllers MVC
│   ├── Core/            # Classes principais do framework
│   ├── Jobs/            # Jobs para background processing
│   ├── Models/          # Models MVC
│   └── Views/           # Views MVC
├── config/              # Arquivos de configuração
├── public/              # Arquivos públicos (index.php, .htaccess)
├── storage/             # Arquivos de armazenamento
│   ├── cache/           # Cache
│   └── logs/            # Logs
├── cli                  # Executável para commands CLI
└── composer.json
```

## Instalação

1. Clone ou baixe o projeto
2. Execute `composer install`
3. Configure o banco de dados em `config/app.php`
4. Torne o arquivo CLI executável: `chmod +x cli`

## Banco de Dados

Crie uma tabela de exemplo para usuários:

```sql
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

## Uso do Framework

### Web (API REST)

Execute o servidor local:
```bash
php -S localhost:8000 -t public
```

Endpoints disponíveis:
- `GET /` - Informações da API
- `GET /users` - Listar usuários
- `GET /users/{id}` - Mostrar usuário específico
- `POST /users` - Criar usuário
- `PUT /users/{id}` - Atualizar usuário
- `DELETE /users/{id}` - Deletar usuário

### Commands CLI

Listar commands disponíveis:
```bash
php cli help
```

Commands disponíveis:
- `make:controller NomeController` - Criar um novo controller
- `make:model NomeModel` - Criar um novo model
- `make:job NomeJob` - Criar um novo job
- `queue:work` - Executar worker para processar jobs

Exemplos:
```bash
# Criar controller
php cli make:controller Product

# Criar model
php cli make:model Product

# Criar job
php cli make:job ProcessPayment

# Executar worker de jobs
php cli queue:work

# Executar worker para fila específica
php cli queue:work --queue=emails

# Executar worker com limite de jobs
php cli queue:work --max-jobs=10
```

### Jobs (Background Processing)

Os jobs são executados em background através do sistema de filas.

Exemplo de uso em um controller:
```php
use App\Jobs\SendWelcomeEmailJob;
use App\Core\JobQueue;

$jobQueue = new JobQueue();
$job = new SendWelcomeEmailJob(['user_id' => $userId]);
$jobQueue->dispatch($job);
```

Para processar os jobs:
```bash
php cli queue:work
```

## Exemplos Práticos

### Criando um novo recurso

1. **Criar Model:**
```bash
php cli make:model Product
# Table name: products
# Fillable fields: name,price,description
```

2. **Criar Controller:**
```bash
php cli make:controller Product
```

3. **Adicionar rotas em `public/index.php`:**
```php
$router->get('/products', 'ProductController@index');
$router->post('/products', 'ProductController@create');
```

### Criando um Job

1. **Criar o Job:**
```bash
php cli make:job ProcessOrder
# Queue name: orders
# Delay: 0
```

2. **Usar o Job:**
```php
$jobQueue = new JobQueue();
$job = new ProcessOrderJob(['order_id' => $orderId]);
$jobQueue->dispatch($job);
```

3. **Processar Jobs:**
```bash
php cli queue:work --queue=orders
```

## Recursos do Framework

### Core Features
- ✅ Padrão MVC
- ✅ Roteamento simples
- ✅ ORM básico com Active Record
- ✅ Sistema de Commands CLI
- ✅ Sistema de Jobs/Queue
- ✅ Logging automático
- ✅ Tratamento de erros
- ✅ Singleton Application

### Database
- ✅ PDO com prepared statements
- ✅ Query builder básico
- ✅ Migrations (via SQL manual)
- ✅ Model base com CRUD

### Jobs & Queue
- ✅ Interface Job
- ✅ AbstractJob base class
- ✅ Sistema de filas com banco de dados
- ✅ Worker para processar jobs
- ✅ Logging de jobs
- ✅ Tratamento de erros em jobs

### Commands
- ✅ Base Command class
- ✅ CommandRunner
- ✅ Auto-discovery de commands
- ✅ Helpers para input/output
- ✅ Generators (make:controller, make:model, etc)

## Estrutura de Arquivos Gerados

### Controller
```php
<?php
namespace App\Controllers;
use App\Core\Controller;

class ExampleController extends Controller
{
    public function index() { /* ... */ }
    public function show($id) { /* ... */ }
    public function create() { /* ... */ }
    public function update($id) { /* ... */ }
    public function delete($id) { /* ... */ }
}
```

### Model
```php
<?php
namespace App\Models;
use App\Core\Model;

class Example extends Model
{
    protected $table = 'examples';
    protected $fillable = ['name', 'description'];
}
```

### Job
```php
<?php
namespace App\Jobs;
use App\Core\AbstractJob;

class ExampleJob extends AbstractJob
{
    protected $queue = 'default';
    
    public function handle()
    {
        // Job logic here
    }
}
```

## Live Coding

Este framework é ideal para:
- ✅ Demonstrações de padrões MVC
- ✅ Explicação de conceitos de arquitetura
- ✅ Implementação de APIs REST
- ✅ Sistema de background jobs
- ✅ Commands CLI customizados
- ✅ Workshops e tutoriais

## Licença

MIT License
