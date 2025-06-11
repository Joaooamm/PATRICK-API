-- Criar banco de dados
CREATE DATABASE IF NOT EXISTS api_trabalho;
USE api_trabalho;

-- Criar tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Inserir usuário inicial para testes
INSERT INTO usuarios (nome, email, senha) VALUES
('Admin', 'admin@teste.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'); -- senha: password

-- Índices para melhor performance
CREATE INDEX idx_email ON usuarios(email);
CREATE INDEX idx_created_at ON usuarios(created_at);
