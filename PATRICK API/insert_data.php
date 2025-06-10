<?php
require_once 'config/database.php';

try {
    // Conectar ao banco de dados
    $pdo = new PDO(
        "mysql:host=localhost;dbname=curadoria_memes;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    echo "Conexão com o banco de dados estabelecida com sucesso!\n";

    // Verificar se as tags existem
    $stmt = $pdo->query("SELECT COUNT(*) FROM tags");
    $tagCount = $stmt->fetchColumn();
    
    if ($tagCount == 0) {
        // Inserir tags se não existirem
        $tags = ['programação', 'engraçado', 'stackoverflow'];
        $stmt = $pdo->prepare("INSERT INTO tags (nome) VALUES (?)");
        foreach ($tags as $tag) {
            $stmt->execute([$tag]);
            echo "Tag inserida: " . $tag . "\n";
        }
    }

    // Inserir um meme de exemplo
    $meme = [
        'titulo' => 'Stack Overflow vs Google',
        'imagem_url' => 'https://i.imgur.com/8tMuXaK.jpg',
        'legenda' => 'Quando você procura no Google e o primeiro resultado é do Stack Overflow',
        'autor' => 'CodeMaster',
        'tags' => ['programação', 'engraçado', 'stackoverflow']
    ];

    echo "\nTentando inserir o meme...\n";
    echo "Título: " . $meme['titulo'] . "\n";
    echo "URL da imagem: " . $meme['imagem_url'] . "\n";

    // Primeiro, inserir o meme
    $stmt = $pdo->prepare("
        INSERT INTO memes (titulo, imagem_url, legenda, autor, created_at) 
        VALUES (?, ?, ?, ?, NOW())
    ");

    $stmt->execute([
        $meme['titulo'],
        $meme['imagem_url'],
        $meme['legenda'],
        $meme['autor']
    ]);
    
    $memeId = $pdo->lastInsertId();
    echo "\nMeme inserido com ID: " . $memeId . "\n";
    
    // Depois, inserir as tags
    $stmtTags = $pdo->prepare("
        INSERT INTO meme_tag (meme_id, tag_id)
        SELECT ?, id FROM tags WHERE nome = ?
    ");
    
    foreach ($meme['tags'] as $tag) {
        $stmtTags->execute([$memeId, $tag]);
        echo "Tag associada: " . $tag . "\n";
    }

    echo "\nMeme inserido com sucesso!\n";
    echo "ID do meme: " . $memeId . "\n";
    echo "Título: " . $meme['titulo'] . "\n";
    echo "Tags: " . implode(", ", $meme['tags']) . "\n";
    echo "\nPara ver o meme, acesse: http://localhost/PATRICKAPI/\n";

} catch (PDOException $e) {
    echo "Erro no banco de dados: " . $e->getMessage() . "\n";
    echo "Código do erro: " . $e->getCode() . "\n";
} catch (Exception $e) {
    echo "Erro geral: " . $e->getMessage() . "\n";
}
?> 