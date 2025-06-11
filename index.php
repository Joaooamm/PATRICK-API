<?php

// Configurar headers CORS
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Responder OPTIONS request (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Incluir dependências
require_once '../controllers/UsuarioController.php';

// Obter método HTTP e URI
$method = $_SERVER['REQUEST_METHOD'];
$uri = $_SERVER['REQUEST_URI'];

// Remover query string da URI
$uri = parse_url($uri, PHP_URL_PATH);

// Remover prefixo se existir (ajustar conforme necessário)
$basePath = '/public';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Separar URI em partes
$uriParts = explode('/', trim($uri, '/'));

try {
    $controller = new UsuarioController();

    // Roteamento
    switch ($uriParts[0]) {
        case 'login':
            if ($method === 'POST') {
                $controller->login();
            } else {
                sendMethodNotAllowed();
            }
            break;

        case 'usuarios':
            switch ($method) {
                case 'GET':
                    if (isset($uriParts[1]) && is_numeric($uriParts[1])) {
                        // GET /usuarios/{id}
                        $controller->readOne($uriParts[1]);
                    } else {
                        // GET /usuarios
                        $controller->read();
                    }
                    break;

                case 'POST':
                    // POST /usuarios
                    $controller->create();
                    break;

                case 'PUT':
                    if (isset($uriParts[1]) && is_numeric($uriParts[1])) {
                        // PUT /usuarios/{id}
                        $controller->update($uriParts[1]);
                    } else {
                        sendBadRequest('ID é obrigatório para atualização');
                    }
                    break;

                case 'DELETE':
                    if (isset($uriParts[1]) && is_numeric($uriParts[1])) {
                        // DELETE /usuarios/{id}
                        $controller->delete($uriParts[1]);
                    } else {
                        sendBadRequest('ID é obrigatório para exclusão');
                    }
                    break;

                default:
                    sendMethodNotAllowed();
                    break;
            }
            break;

        case '':
            // Rota raiz - documentação da API
            sendApiDocumentation();
            break;

        default:
            sendNotFound();
            break;
    }

} catch (Exception $e) {
    sendInternalServerError($e->getMessage());
}

// Funções auxiliares para respostas de erro
function sendMethodNotAllowed() {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Método não permitido',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function sendNotFound() {
    http_response_code(404);
    echo json_encode([
        'success' => false,
        'message' => 'Endpoint não encontrado',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function sendBadRequest($message) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function sendInternalServerError($message) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erro interno do servidor',
        'error' => $message,
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}

function sendApiDocumentation() {
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'message' => 'API RESTful - Trabalho Parte II',
        'version' => '2.0',
        'endpoints' => [
            'POST /login' => 'Autenticar usuário e gerar JWT',
            'GET /usuarios' => 'Listar todos os usuários (protegido)',
            'GET /usuarios/{id}' => 'Buscar usuário por ID (protegido)',
            'POST /usuarios' => 'Criar novo usuário (protegido)',
            'PUT /usuarios/{id}' => 'Atualizar usuário (protegido)',
            'DELETE /usuarios/{id}' => 'Deletar usuário (protegido)'
        ],
        'authentication' => [
            'type' => 'Bearer Token (JWT)',
            'header' => 'Authorization: Bearer YOUR_TOKEN'
        ],
        'timestamp' => date('Y-m-d H:i:s')
    ]);
}
