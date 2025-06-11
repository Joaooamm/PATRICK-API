<?php
require_once 'config/database.php';
require_once 'controllers/MemeController.php';

// Configurar headers para CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratar requisições OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

$memeController = new MemeController();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curadoria de Memes</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="header">
            <h1><i class="fas fa-laugh"></i> Curadoria de Memes</h1>
            <p>Descubra, compartilhe e vote nos melhores memes!</p>
        </header>

        <nav class="nav-tabs">
            <button class="tab-btn active" onclick="showTab('memes')">
                <i class="fas fa-images"></i> Memes
            </button>
            <button class="tab-btn" onclick="showTab('upload')">
                <i class="fas fa-upload"></i> Upload
            </button>
            <button class="tab-btn" onclick="showTab('tags')">
                <i class="fas fa-tags"></i> Tags
            </button>
        </nav>

        <div id="memes" class="tab-content active">
            <div class="filters">
                <select id="tagFilter" onchange="filterMemes()">
                    <option value="">Todas as Tags</option>
                </select>
                <select id="sortFilter" onchange="filterMemes()">
                    <option value="recent">Mais Recentes</option>
                    <option value="likes">Mais Curtidos</option>
                </select>
            </div>
            <div id="memesList" class="memes-grid">
                <!-- Memes serão carregados aqui -->
            </div>
        </div>

        <div id="upload" class="tab-content">
            <div class="upload-form">
                <h2>Adicionar Novo Meme</h2>
                <form id="memeForm" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="titulo">Título:</label>
                        <input type="text" id="titulo" name="titulo" required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="imagem">Imagem:</label>
                        <input type="file" id="imagem" name="imagem" accept="image/*" required>
                        <div class="file-preview" id="imagePreview"></div>
                    </div>

                    <div class="form-group">
                        <label for="legenda">Legenda:</label>
                        <textarea id="legenda" name="legenda" rows="3" maxlength="500"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="autor">Autor:</label>
                        <input type="text" id="autor" name="autor" required maxlength="100">
                    </div>

                    <div class="form-group">
                        <label for="tags">Tags (separadas por vírgula):</label>
                        <input type="text" id="tags" name="tags" placeholder="ex: engraçado, trabalho, gatos">
                    </div>

                    <button type="submit" class="btn-primary">
                        <i class="fas fa-upload"></i> Enviar Meme
                    </button>
                </form>
            </div>
        </div>

        <div id="tags" class="tab-content">
            <div class="tags-section">
                <h2>Gerenciar Tags</h2>
                <div class="tag-form">
                    <input type="text" id="newTag" placeholder="Nova tag" maxlength="50">
                    <button onclick="addTag()" class="btn-secondary">
                        <i class="fas fa-plus"></i> Adicionar
                    </button>
                </div>
                <div id="tagsList" class="tags-list">
                    <!-- Tags serão carregadas aqui -->
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/app.js"></script>
</body>
</html>
