<?php
try {
    // Conectar ao MySQL sem especificar o banco de dados
    $pdo = new PDO('mysql:host=localhost', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Criar o banco de dados
    $pdo->exec("CREATE DATABASE IF NOT EXISTS curadoria_memes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Banco de dados criado com sucesso!\n";

    // Selecionar o banco de dados
    $pdo->exec("USE curadoria_memes");

    // Criar tabelas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS memes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(100) NOT NULL,
            imagem_url TEXT NOT NULL,
            legenda TEXT,
            autor VARCHAR(100) NOT NULL,
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Tabela 'memes' criada com sucesso!\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(50) UNIQUE NOT NULL
        )
    ");
    echo "Tabela 'tags' criada com sucesso!\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS meme_tag (
            meme_id INT,
            tag_id INT,
            PRIMARY KEY (meme_id, tag_id),
            FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        )
    ");
    echo "Tabela 'meme_tag' criada com sucesso!\n";

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS votos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            meme_id INT,
            tipo ENUM('like', 'dislike') NOT NULL,
            ip_address VARCHAR(45),
            criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
            UNIQUE KEY unique_vote (meme_id, ip_address)
        )
    ");
    echo "Tabela 'votos' criada com sucesso!\n";

    echo "Todas as tabelas foram criadas com sucesso!\n";

} catch(PDOException $e) {
    die("Erro: " . $e->getMessage() . "\n");
}
?> 