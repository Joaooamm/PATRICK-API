<?php
require_once 'config/database.php';

function inserirMeme($titulo, $imagem_url, $legenda, $autor, $tags) {
    try {
        $pdo = new PDO(
            "mysql:host=localhost;dbname=curadoria_memes;charset=utf8mb4",
            "root",
            "",
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );

        // Inserir o meme
        $stmt = $pdo->prepare("
            INSERT INTO memes (titulo, imagem_url, legenda, autor, created_at) 
            VALUES (?, ?, ?, ?, NOW())
        ");

        $stmt->execute([$titulo, $imagem_url, $legenda, $autor]);
        $memeId = $pdo->lastInsertId();

        // Inserir as tags
        foreach ($tags as $tag) {
            // Verificar se a tag existe
            $stmt = $pdo->prepare("SELECT id FROM tags WHERE nome = ?");
            $stmt->execute([$tag]);
            $tagId = $stmt->fetchColumn();

            // Se a tag não existe, criar
            if (!$tagId) {
                $stmt = $pdo->prepare("INSERT INTO tags (nome) VALUES (?)");
                $stmt->execute([$tag]);
                $tagId = $pdo->lastInsertId();
            }

            // Associar tag ao meme
            $stmt = $pdo->prepare("INSERT INTO meme_tag (meme_id, tag_id) VALUES (?, ?)");
            $stmt->execute([$memeId, $tagId]);
        }

        return "Meme inserido com sucesso! ID: " . $memeId;
    } catch (PDOException $e) {
        return "Erro: " . $e->getMessage();
    }
}

// Exemplo de uso
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = $_POST['titulo'] ?? '';
    $imagem_url = $_POST['imagem_url'] ?? '';
    $legenda = $_POST['legenda'] ?? '';
    $autor = $_POST['autor'] ?? '';
    $tags = explode(',', $_POST['tags'] ?? '');

    $resultado = inserirMeme($titulo, $imagem_url, $legenda, $autor, $tags);
    echo $resultado;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Inserir Meme</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 20px auto; padding: 20px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; }
        input[type="text"], textarea { width: 100%; padding: 8px; margin-bottom: 10px; }
        button { padding: 10px 20px; background: #007bff; color: white; border: none; cursor: pointer; }
        button:hover { background: #0056b3; }
    </style>
</head>
<body>
    <h1>Inserir Novo Meme</h1>
    <form method="POST">
        <div class="form-group">
            <label>Título:</label>
            <input type="text" name="titulo" required>
        </div>
        <div class="form-group">
            <label>URL da Imagem:</label>
            <input type="text" name="imagem_url" required>
        </div>
        <div class="form-group">
            <label>Legenda:</label>
            <textarea name="legenda" rows="3"></textarea>
        </div>
        <div class="form-group">
            <label>Autor:</label>
            <input type="text" name="autor" required>
        </div>
        <div class="form-group">
            <label>Tags (separadas por vírgula):</label>
            <input type="text" name="tags" placeholder="ex: engraçado, programação, memes">
        </div>
        <button type="submit">Inserir Meme</button>
    </form>
</body>
</html> 