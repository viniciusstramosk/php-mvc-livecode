# Guia Completo: Criando Commands Personalizados para Jobs

Este guia explica como criar commands CLI personalizados para executar jobs no nosso framework PHP MVC.

## 📝 **Como Funciona o Sistema**

### 1. **Auto-Discovery de Commands**
O framework automaticamente descobre todos os commands na pasta `app/Commands/`. Qualquer classe que extends `Command` é registrada automaticamente.

### 2. **Estrutura Base de um Command**
```php
<?php
namespace App\Commands;

use App\Core\Command;

class MeuCommand extends Command
{
    protected $signature = 'meu:command';
    protected $description = 'Descrição do command';
    
    public function handle($args = [])
    {
        // Lógica do command aqui
    }
}
```

## 🚀 **Passo a Passo: Criar Command para Executar Job**

### **Passo 1: Criar o Job**
```bash
php cli make:job MinhaTaskPersonalizada
# Escolha queue: processos
# Escolha delay: 0
```

### **Passo 2: Personalizar o Job**
```php
<?php
// app/Jobs/MinhaTaskPersonalizadaJob.php
namespace App\Jobs;

use App\Core\AbstractJob;

class MinhaTaskPersonalizadaJob extends AbstractJob
{
    protected $queue = 'processos';
    protected $delay = 0;
    
    public function handle()
    {
        $nome = $this->data['nome'] ?? 'Usuário';
        $tipo = $this->data['tipo'] ?? 'geral';
        
        $this->info("Iniciando processamento para: {$nome}");
        $this->info("Tipo de processo: {$tipo}");
        
        // Simular trabalho
        sleep(2);
        
        $this->info("Processamento concluído!");
    }
}
```

### **Passo 3: Criar Command Personalizado**
```php
<?php
// app/Commands/ExecutarTaskCommand.php
namespace App\Commands;

use App\Core\Command;
use App\Jobs\MinhaTaskPersonalizadaJob;

class ExecutarTaskCommand extends Command
{
    protected $signature = 'executar:task';
    protected $description = 'Executa minha task personalizada';
    
    public function handle($args = [])
    {
        $this->info("Executando Task Personalizada...");
        
        // Coletar dados via argumentos
        $nome = null;
        $tipo = 'geral';
        
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--nome=')) {
                $nome = substr($arg, 7);
            } elseif (str_starts_with($arg, '--tipo=')) {
                $tipo = substr($arg, 7);
            }
        }
        
        // Coletar dados interativamente se não fornecidos
        if (!$nome) {
            $nome = $this->ask("Nome da pessoa");
        }
        
        if ($tipo === 'geral') {
            $input = $this->ask("Tipo de processo (vendas/usuarios/geral)");
            if (!empty($input)) {
                $tipo = $input;
            }
        }
        
        // Criar e executar o job
        $job = new MinhaTaskPersonalizadaJob([
            'nome' => $nome,
            'tipo' => $tipo
        ]);
        
        $this->success("Configuração do Job:");
        $this->info("- Nome: {$nome}");
        $this->info("- Tipo: {$tipo}");
        $this->info("- Queue: {$job->getQueue()}");
        
        // Perguntar se quer executar agora
        if ($this->confirm("Executar agora?")) {
            $this->output("");
            $job->handle();
            $this->success("Job executado com sucesso!");
        }
    }
}
```

### **Passo 4: Usar o Command**
```bash
# Listar commands disponíveis
php cli help

# Executar interativamente
php cli executar:task

# Executar com argumentos
php cli executar:task --nome=João --tipo=vendas
```

## 📋 **Exemplos Práticos**

### **Example 1: Command para Backup**
```php
<?php
// app/Commands/BackupCommand.php
namespace App\Commands;

use App\Core\Command;

class BackupCommand extends Command
{
    protected $signature = 'backup:run';
    protected $description = 'Execute backup operations';
    
    public function handle($args = [])
    {
        $tipo = 'completo';
        
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--type=')) {
                $tipo = substr($arg, 7);
            }
        }
        
        $this->info("Iniciando backup tipo: {$tipo}");
        
        if ($tipo === 'db') {
            $this->backupDatabase();
        } elseif ($tipo === 'files') {
            $this->backupFiles();
        } else {
            $this->backupDatabase();
            $this->backupFiles();
        }
        
        $this->success("Backup concluído!");
    }
    
    private function backupDatabase()
    {
        $this->info("Fazendo backup do banco...");
        sleep(3);
        $this->info("Banco salvo em: backup_" . date('Y-m-d_H-i-s') . ".sql");
    }
    
    private function backupFiles()
    {
        $this->info("Fazendo backup dos arquivos...");
        sleep(2);
        $this->info("Arquivos salvos em: files_" . date('Y-m-d_H-i-s') . ".zip");
    }
}
```

**Uso:**
```bash
php cli backup:run --type=db
php cli backup:run --type=files
php cli backup:run  # backup completo
```

### **Example 2: Command para Envio de Emails em Lote**
```php
<?php
// app/Commands/SendBulkEmailCommand.php
namespace App\Commands;

use App\Core\Command;
use App\Jobs\SendWelcomeEmailJob;
use App\Core\JobQueue;

class SendBulkEmailCommand extends Command
{
    protected $signature = 'email:bulk';
    protected $description = 'Send bulk emails to users';
    
    public function handle($args = [])
    {
        $userIds = [];
        
        // Parse user IDs from arguments
        foreach ($args as $arg) {
            if (str_starts_with($arg, '--users=')) {
                $ids = substr($arg, 8);
                $userIds = explode(',', $ids);
                $userIds = array_map('intval', $userIds);
            }
        }
        
        if (empty($userIds)) {
            $input = $this->ask("User IDs (comma-separated)");
            $userIds = array_map('intval', explode(',', $input));
        }
        
        $this->info("Enviando emails para " . count($userIds) . " usuários...");
        
        $jobQueue = new JobQueue();
        $jobIds = [];
        
        foreach ($userIds as $userId) {
            $job = new SendWelcomeEmailJob(['user_id' => $userId]);
            $jobId = $jobQueue->dispatch($job);
            $jobIds[] = $jobId;
            $this->info("Job {$jobId} criado para usuário {$userId}");
        }
        
        $this->success("Todos os emails foram enfileirados!");
        $this->info("Para processar: php cli queue:work --queue=emails");
        
        if ($this->confirm("Processar emails agora?")) {
            $jobQueue->work('emails', count($userIds));
        }
    }
}
```

**Uso:**
```bash
php cli email:bulk --users=1,2,3,4,5
```

## 🛠 **Commands Avançados Disponíveis**

### 1. **`php cli job:run`** - Executor Genérico
```bash
# Executar qualquer job interativamente
php cli job:run

# Executar job específico com dados
php cli job:run --class=ProcessReportJob --data='{"type":"sales"}'
```

### 2. **`php cli simulate:report`** - Simulador de Relatório
```bash
# Executar com argumentos
php cli simulate:report --type=sales --user=123

# Executar interativamente
php cli simulate:report
```

### 3. **`php cli run:report`** - Executor de Relatório com Queue
```bash
php cli run:report --type=users --user=456
```

## 🔄 **Fluxo Completo de Trabalho**

### **1. Desenvolvimento**
```bash
# Criar job
php cli make:job ProcessarPedido

# Criar command personalizado
# (criar arquivo manualmente em app/Commands/)

# Testar command
php cli meu:command
```

### **2. Execução**
```bash
# Listar commands
php cli help

# Executar command específico
php cli processar:pedido --pedido=123

# Processar jobs na fila
php cli queue:work --queue=pedidos
```

### **3. Monitoramento**
```bash
# Ver logs de jobs
cat storage/logs/jobs.log

# Processar jobs com limite
php cli queue:work --max-jobs=10
```

## 📚 **Métodos Úteis da Classe Command**

### **Input/Output**
```php
$this->info("Mensagem informativa");
$this->success("Mensagem de sucesso");
$this->error("Mensagem de erro");
$this->warning("Mensagem de aviso");
$this->output("Texto simples");
```

### **Interação com Usuário**
```php
$nome = $this->ask("Qual seu nome?");
$confirmacao = $this->confirm("Continuar?"); // true/false
```

### **Parsing de Argumentos**
```php
foreach ($args as $arg) {
    if (str_starts_with($arg, '--option=')) {
        $value = substr($arg, 9);
    }
}
```

## 🎯 **Dicas de Melhores Práticas**

### 1. **Nomenclatura Clara**
- Commands: `acao:objeto` (ex: `backup:run`, `email:send`)
- Jobs: `VerbSubstantivoJob` (ex: `ProcessReportJob`)

### 2. **Tratamento de Erros**
```php
try {
    $job->handle();
    $this->success("Sucesso!");
} catch (\Exception $e) {
    $this->error("Erro: " . $e->getMessage());
}
```

### 3. **Documentação no Command**
```php
protected $description = 'Descrição clara do que o command faz';
```

### 4. **Flexibilidade**
- Aceite argumentos via CLI
- Ofereça modo interativo
- Permita execução imediata ou via queue

## 🎬 **Para Live-Code**

Este sistema é perfeito para demonstrar:
- ✅ Command Pattern
- ✅ Interação CLI
- ✅ Job Queue System
- ✅ Error Handling
- ✅ User Input Validation
- ✅ Separation of Concerns

**Comando para demonstrar:**
```bash
php cli job:run
# Mostra descoberta automática, interação, execução
```
