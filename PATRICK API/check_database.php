<?php
try {
    // Conectar ao MySQL sem selecionar banco de dados
    $pdo = new PDO(
        "mysql:host=localhost",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Criar banco de dados se não existir
    $pdo->exec("CREATE DATABASE IF NOT EXISTS curadoria_memes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Banco de dados verificado/criado com sucesso!\n";

    // Selecionar o banco de dados
    $pdo->exec("USE curadoria_memes");

    // Criar tabela de memes
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS memes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            titulo VARCHAR(255) NOT NULL,
            imagem_url TEXT NOT NULL,
            legenda TEXT,
            autor VARCHAR(100) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )
    ");
    echo "Tabela 'memes' verificada/criada com sucesso!\n";

    // Criar tabela de tags
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS tags (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(50) NOT NULL UNIQUE
        )
    ");
    echo "Tabela 'tags' verificada/criada com sucesso!\n";

    // Criar tabela de relacionamento meme_tag
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS meme_tag (
            meme_id INT,
            tag_id INT,
            PRIMARY KEY (meme_id, tag_id),
            FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
            FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
        )
    ");
    echo "Tabela 'meme_tag' verificada/criada com sucesso!\n";

    // Criar tabela de votos
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS votos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            meme_id INT,
            tipo ENUM('like', 'dislike') NOT NULL,
            ip VARCHAR(45) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
            UNIQUE KEY unique_voto (meme_id, ip)
        )
    ");
    echo "Tabela 'votos' verificada/criada com sucesso!\n";

    echo "\nTodas as tabelas foram verificadas/criadas com sucesso!\n";

} catch (PDOException $e) {
    echo "Erro: " . $e->getMessage() . "\n";
}
?> 