-- Criar o banco de dados
CREATE DATABASE IF NOT EXISTS curadoria_memes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Usar o banco de dados
USE curadoria_memes;

-- Criar tabela memes
CREATE TABLE IF NOT EXISTS memes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titulo VARCHAR(100) NOT NULL,
    imagem_url TEXT NOT NULL,
    legenda TEXT,
    autor VARCHAR(100) NOT NULL,
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Criar tabela tags
CREATE TABLE IF NOT EXISTS tags (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) UNIQUE NOT NULL
);

-- Criar tabela meme_tag
CREATE TABLE IF NOT EXISTS meme_tag (
    meme_id INT,
    tag_id INT,
    PRIMARY KEY (meme_id, tag_id),
    FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
);

-- Criar tabela votos
CREATE TABLE IF NOT EXISTS votos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    meme_id INT,
    tipo ENUM('like', 'dislike') NOT NULL,
    ip_address VARCHAR(45),
    criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
    UNIQUE KEY unique_vote (meme_id, ip_address)
); 