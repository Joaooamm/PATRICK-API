<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Configurar headers para CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Tratar requisições OPTIONS
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'config/database.php';
require_once 'controllers/MemeController.php';

$request = $_SERVER['REQUEST_URI'];
error_log("Request URI: " . $request);

// Verificar se é uma requisição API
$isApiRequest = strpos($request, '/api/') !== false;
error_log("Is API Request: " . ($isApiRequest ? 'true' : 'false'));

if ($isApiRequest) {
    error_log("Processando requisição API");
    $memeController = new MemeController();
    $memeController->roteamento();
    exit();
}

// Se não for uma requisição API, continua para renderizar a interface
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Curadoria de Memes - API</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="container">
        <header class="header">
            <div class="header-content">
                <h1><i class="fas fa-laugh"></i> Curadoria de Memes</h1>
                <p>API para gerenciamento e curadoria de memes</p>
                <div class="header-stats">
                    <div class="stat-item">
                        <i class="fas fa-code"></i>
                        <span>REST API</span>
                        <span>Documentação</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-database"></i>
                        <span>MySQL</span>
                        <span>Banco de Dados</span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-shield-alt"></i>
                        <span>Seguro</span>
                        <span>Autenticado</span>
                    </div>
                </div>
            </div>
        </header>

        <nav class="nav-tabs">
            <button class="tab-btn active" onclick="showTab('documentation')">
                <i class="fas fa-book"></i> Documentação
            </button>
            <button class="tab-btn" onclick="showTab('test')">
                <i class="fas fa-vial"></i> Testar API
            </button>
            <button class="tab-btn" onclick="showTab('memes')">
                <i class="fas fa-images"></i> Memes
            </button>
        </nav>

        <div id="documentation" class="tab-content active">
            <div class="api-docs">
                <h2>Documentação da API</h2>
                
                <section class="endpoint">
                    <h3><i class="fas fa-list"></i> Listar Memes</h3>
                    <div class="endpoint-details">
                        <span class="method get">GET</span>
                        <code>/api/memes</code>
                        <p>Retorna a lista de todos os memes.</p>
                        <div class="params">
                            <h4>Parâmetros de Query:</h4>
                            <ul>
                                <li><code>sort</code> - Ordenação (recent, likes, trending)</li>
                                <li><code>tag_id</code> - Filtrar por tag</li>
                                <li><code>page</code> - Número da página</li>
                                <li><code>limit</code> - Itens por página</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="endpoint">
                    <h3><i class="fas fa-plus"></i> Criar Meme</h3>
                    <div class="endpoint-details">
                        <span class="method post">POST</span>
                        <code>/api/memes</code>
                        <p>Cria um novo meme.</p>
                        <div class="params">
                            <h4>Body (multipart/form-data):</h4>
                            <ul>
                                <li><code>titulo</code> - Título do meme</li>
                                <li><code>imagem</code> - Arquivo de imagem</li>
                                <li><code>legenda</code> - Legenda do meme</li>
                                <li><code>autor</code> - Nome do autor</li>
                                <li><code>tags</code> - Tags separadas por vírgula</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="endpoint">
                    <h3><i class="fas fa-thumbs-up"></i> Votar em Meme</h3>
                    <div class="endpoint-details">
                        <span class="method post">POST</span>
                        <code>/api/memes/{id}/votar</code>
                        <p>Registra um voto (like/dislike) em um meme.</p>
                        <div class="params">
                            <h4>Body (JSON):</h4>
                            <ul>
                                <li><code>tipo</code> - "like" ou "dislike"</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <section class="endpoint">
                    <h3><i class="fas fa-tags"></i> Gerenciar Tags</h3>
                    <div class="endpoint-details">
                        <span class="method get">GET</span>
                        <code>/api/tags</code>
                        <p>Lista todas as tags disponíveis.</p>
                    </div>
                </section>
            </div>
        </div>

        <div id="test" class="tab-content">
            <div class="api-tester">
                <h2>Testar API</h2>
                <div class="tester-form">
                    <div class="form-group">
                        <label for="endpoint">Endpoint:</label>
                        <div class="endpoint-input">
                            <span class="method-select">
                                <select id="method">
                                    <option value="GET">GET</option>
                                    <option value="POST">POST</option>
                                    <option value="PUT">PUT</option>
                                    <option value="DELETE">DELETE</option>
                                </select>
                            </span>
                            <input type="text" id="endpoint" placeholder="/api/memes">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="requestBody">Body (JSON):</label>
                        <textarea id="requestBody" rows="5" placeholder='{"key": "value"}'></textarea>
                    </div>

                    <button onclick="testEndpoint()" class="btn-primary">
                        <i class="fas fa-play"></i> Testar
                    </button>

                    <div class="response-section">
                        <h3>Resposta:</h3>
                        <pre id="response"></pre>
                    </div>
                </div>
            </div>
        </div>

        <div id="memes" class="tab-content">
            <div class="filters">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Buscar memes...">
                </div>
                <select id="tagFilter" onchange="filterMemes()">
                    <option value="">Todas as Tags</option>
                </select>
                <select id="sortFilter" onchange="filterMemes()">
                    <option value="recent">Mais Recentes</option>
                    <option value="likes">Mais Curtidos</option>
                    <option value="trending">Em Alta</option>
                </select>
            </div>
            <div id="memesList" class="memes-grid">
                <!-- Memes serão carregados aqui -->
            </div>
            <div class="loading-container" id="loadingMemes" style="display: none;">
                <div class="loading"></div>
                <p>Carregando memes...</p>
            </div>
        </div>
    </div>

    <script>
        // Funções básicas
        function showTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            
            document.getElementById(tabId).classList.add('active');
            event.currentTarget.classList.add('active');
        }

        // Testar API
        async function testEndpoint() {
            const method = document.getElementById('method').value;
            const endpoint = document.getElementById('endpoint').value;
            const body = document.getElementById('requestBody').value;
            const responseElement = document.getElementById('response');

            try {
                const options = {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json'
                    }
                };

                if (body && (method === 'POST' || method === 'PUT')) {
                    options.body = body;
                }

                // Adicionar o prefixo /PATRICKAPI/ se não estiver presente
                const fullEndpoint = endpoint.startsWith('/PATRICKAPI/') ? endpoint : `/PATRICKAPI${endpoint}`;
                const response = await fetch(fullEndpoint, options);
                const data = await response.json();

                responseElement.innerHTML = JSON.stringify(data, null, 2);
                responseElement.style.color = response.ok ? '#2ecc71' : '#e74c3c';
            } catch (error) {
                responseElement.innerHTML = `Erro: ${error.message}`;
                responseElement.style.color = '#e74c3c';
            }
        }

        // Carregar memes
        async function loadMemes() {
            const loadingElement = document.getElementById('loadingMemes');
            const memesList = document.getElementById('memesList');
            
            loadingElement.style.display = 'block';
            
            try {
                const response = await fetch('/PATRICKAPI/api/memes');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const data = await response.json();
                
                if (data.sucesso) {
                    memesList.innerHTML = data.memes.map(meme => `
                        <div class="meme-card">
                            <img src="${meme.imagem_url}" alt="${meme.titulo}" class="meme-image">
                            <div class="meme-info">
                                <h3 class="meme-title">${meme.titulo}</h3>
                                <p class="meme-author">Por: ${meme.autor}</p>
                                <div class="meme-tags">
                                    ${meme.tags.map(tag => `<span class="tag">${tag.nome}</span>`).join('')}
                                </div>
                                <div class="vote-buttons">
                                    <button class="vote-btn like-btn" onclick="votar(${meme.id}, 'like')">
                                        <i class="fas fa-thumbs-up"></i>
                                        <span class="vote-count">${meme.likes}</span>
                                    </button>
                                    <button class="vote-btn dislike-btn" onclick="votar(${meme.id}, 'dislike')">
                                        <i class="fas fa-thumbs-down"></i>
                                        <span class="vote-count">${meme.dislikes}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('');
                } else {
                    console.error('Erro na resposta da API:', data.erro);
                }
            } catch (error) {
                console.error('Erro ao carregar memes:', error);
                memesList.innerHTML = '<div class="error-message">Erro ao carregar memes. Por favor, tente novamente.</div>';
            } finally {
                loadingElement.style.display = 'none';
            }
        }

        // Votar em meme
        async function votar(memeId, tipo) {
            try {
                const response = await fetch(`/PATRICKAPI/api/memes/${memeId}/votar`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ tipo })
                });
                
                const data = await response.json();
                if (data.sucesso) {
                    loadMemes(); // Recarregar memes para atualizar contadores
                }
            } catch (error) {
                console.error('Erro ao votar:', error);
            }
        }

        // Carregar memes ao iniciar
        document.addEventListener('DOMContentLoaded', loadMemes);
    </script>
</body>
</html>
