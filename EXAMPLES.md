# Exemplos de Uso do Framework

Este documento contém exemplos práticos de como usar o framework PHP MVC.

## 1. Configuração Inicial

### Configurar Banco de Dados

Edite `config/app.php`:

```php
'database' => [
    'host' => 'localhost',
    'dbname' => 'php_mvc_db',
    'username' => 'root',
    'password' => 'sua_senha',
    'charset' => 'utf8mb4'
],
```

### Executar SQL

Execute o arquivo `database.sql` no seu banco de dados para criar as tabelas.

## 2. Executar o Servidor

```bash
# Servidor web para API
php -S localhost:8000 -t public

# Acessar: http://localhost:8000
```

## 3. Testar API

### Listar usuários
```bash
curl http://localhost:8000/users
```

### Criar usuário
```bash
curl -X POST http://localhost:8000/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Pedro","email":"pedro@example.com"}'
```

### Mostrar usuário específico
```bash
curl http://localhost:8000/users/1
```

## 4. Commands CLI

### Listar commands
```bash
php cli help
```

### Criar controller
```bash
php cli make:controller Order
# Cria: app/Controllers/OrderController.php
```

### Criar model
```bash
php cli make:model Order
# Pergunta: Table name (orders)
# Pergunta: Fillable fields (customer_name,total,status)
# Cria: app/Models/Order.php
```

### Criar job
```bash
php cli make:job ProcessPayment
# Pergunta: Queue name (payments)  
# Pergunta: Delay in seconds (0)
# Cria: app/Jobs/ProcessPaymentJob.php
```

## 5. Sistema de Jobs

### Exemplo de Job
```php
<?php
// app/Jobs/ProcessPaymentJob.php
namespace App\Jobs;

use App\Core\AbstractJob;

class ProcessPaymentJob extends AbstractJob
{
    protected $queue = 'payments';
    
    public function handle()
    {
        $orderId = $this->data['order_id'];
        $amount = $this->data['amount'];
        
        $this->info("Processing payment for order: {$orderId}");
        
        // Simular processamento
        sleep(3);
        
        // Sua lógica de pagamento aqui
        // $paymentGateway->charge($amount);
        
        $this->info("Payment processed successfully!");
    }
}
```

### Disparar Job em Controller
```php
<?php
// app/Controllers/OrderController.php
namespace App\Controllers;

use App\Core\Controller;
use App\Jobs\ProcessPaymentJob;
use App\Core\JobQueue;

class OrderController extends Controller
{
    public function processPayment()
    {
        $data = $this->getInput();
        
        // Criar job
        $job = new ProcessPaymentJob([
            'order_id' => $data['order_id'],
            'amount' => $data['amount']
        ]);
        
        // Disparar job
        $jobQueue = new JobQueue();
        $jobId = $jobQueue->dispatch($job);
        
        $this->json([
            'message' => 'Payment job dispatched',
            'job_id' => $jobId
        ]);
    }
}
```

### Executar Worker
```bash
# Worker padrão
php cli queue:work

# Worker para fila específica  
php cli queue:work --queue=payments

# Worker com limite de jobs
php cli queue:work --max-jobs=5
```

## 6. Estrutura de Rotas Completa

Adicione em `public/index.php`:

```php
<?php
// Routes básicas
$router->get('/', function() {
    echo json_encode(['message' => 'API funcionando!']);
});

// Users CRUD
$router->get('/users', 'UserController@index');
$router->get('/users/{id}', 'UserController@show');  
$router->post('/users', 'UserController@create');
$router->put('/users/{id}', 'UserController@update');
$router->delete('/users/{id}', 'UserController@delete');

// Products CRUD
$router->get('/products', 'ProductController@index');
$router->post('/products', 'ProductController@create');

// Orders
$router->post('/orders/payment', 'OrderController@processPayment');

// Health check
$router->get('/health', function() {
    echo json_encode(['status' => 'ok', 'timestamp' => time()]);
});
```

## 7. Exemplo Completo: Sistema de Pedidos

### 1. Criar Model
```bash
php cli make:model Order
# Table: orders
# Fillable: customer_name,total,status
```

### 2. Criar Controller
```bash
php cli make:controller Order
```

### 3. Criar Job para processar pedido
```bash
php cli make:job ProcessOrder
# Queue: orders
# Delay: 0
```

### 4. Implementar Job
```php
<?php
// app/Jobs/ProcessOrderJob.php
namespace App\Jobs;

use App\Core\AbstractJob;
use App\Models\Order;

class ProcessOrderJob extends AbstractJob
{
    protected $queue = 'orders';
    
    public function handle()
    {
        $orderId = $this->data['order_id'];
        $this->info("Processing order: {$orderId}");
        
        $orderModel = new Order();
        $order = $orderModel->find($orderId);
        
        if (!$order) {
            $this->error("Order not found: {$orderId}");
            return;
        }
        
        // Simular processamento
        sleep(2);
        
        // Atualizar status
        $orderModel->update($orderId, ['status' => 'processed']);
        
        $this->info("Order processed: {$orderId}");
    }
}
```

### 5. Usar no Controller
```php
<?php
// app/Controllers/OrderController.php (adicionar método)
public function create()
{
    $data = $this->getInput();
    
    // Criar pedido
    $orderModel = new \App\Models\Order();
    $orderId = $orderModel->create($data);
    
    // Disparar job de processamento
    $jobQueue = new \App\Core\JobQueue();
    $job = new \App\Jobs\ProcessOrderJob(['order_id' => $orderId]);
    $jobQueue->dispatch($job);
    
    $this->json([
        'message' => 'Order created and queued for processing',
        'order_id' => $orderId
    ], 201);
}
```

### 6. Adicionar rotas
```php
// public/index.php
$router->post('/orders', 'OrderController@create');
$router->get('/orders/{id}', 'OrderController@show');
```

### 7. Testar
```bash
# Criar pedido
curl -X POST http://localhost:8000/orders \
  -H "Content-Type: application/json" \
  -d '{"customer_name":"Ana","total":99.99,"status":"pending"}'

# Executar worker para processar
php cli queue:work --queue=orders --max-jobs=1
```

## 8. Logs e Debugging

### Logs de Jobs
Os jobs automaticamente fazem log em `storage/logs/jobs.log`:

```bash
# Ver logs
tail -f storage/logs/jobs.log
```

### Debug de Erros
Configure `debug => true` em `config/app.php` para ver erros detalhados.

## 9. Estrutura de Arquivos Finais

Após usar os commands, sua estrutura ficará:

```
php-mvc-livecode/
├── app/
│   ├── Controllers/
│   │   ├── OrderController.php
│   │   ├── ProductController.php
│   │   └── UserController.php
│   ├── Models/
│   │   ├── Order.php
│   │   ├── Product.php  
│   │   └── User.php
│   └── Jobs/
│       ├── ProcessOrderJob.php
│       ├── ProcessPaymentJob.php
│       └── SendWelcomeEmailJob.php
└── storage/
    └── logs/
        └── jobs.log
```

## 10. Próximos Passos

Este framework é ideal para:
- ✅ Live coding de APIs REST
- ✅ Demonstração de padrões MVC
- ✅ Sistema de background jobs
- ✅ Commands CLI personalizados
- ✅ Workshops e tutoriais

Para produção, considere adicionar:
- Middleware de autenticação
- Validação de dados
- Cache
- Logs mais robustos
- Testes automatizados
