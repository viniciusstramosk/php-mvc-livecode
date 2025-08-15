-- Criar banco de dados (opcional)
-- CREATE DATABASE php_mvc_db;
-- USE php_mvc_db;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de produtos (exemplo)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    description TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabela de jobs (criada automaticamente pelo sistema, mas aqui está para referência)
CREATE TABLE IF NOT EXISTS jobs (
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
);

-- Tabela de logs de email (exemplo)
CREATE TABLE IF NOT EXISTS email_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    content TEXT,
    sent_at DATETIME NOT NULL
);

-- Dados de exemplo
INSERT INTO users (name, email, password) VALUES 
('João Silva', 'joao@example.com', '$2y$10$example_hash'),
('Maria Santos', 'maria@example.com', '$2y$10$example_hash');

INSERT INTO products (name, price, description) VALUES 
('Produto A', 29.90, 'Descrição do Produto A'),
('Produto B', 45.50, 'Descrição do Produto B'),
('Produto C', 12.99, 'Descrição do Produto C');
