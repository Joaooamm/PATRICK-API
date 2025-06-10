<?php
require_once 'services/MemeService.php';
require_once 'services/VotoService.php';
require_once 'services/TagService.php';

class MemeController {
    private $memeService;
    private $votoService;
    private $tagService;

    public function __construct() {
        // Garantir que todas as respostas da API sejam JSON
        header('Content-Type: application/json; charset=utf-8');
        
        $this->memeService = new MemeService();
        $this->votoService = new VotoService();
        $this->tagService = new TagService();
    }

    public function index() {
        try {
            $filtros = $this->obterFiltros();
            $memes = $this->memeService->listarMemes($filtros);

            $this->jsonResponse([
                'sucesso' => true,
                'memes' => array_map(function($meme) {
                    return $meme->toArray();
                }, $memes)
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ], 500);
        }
    }

    public function buscar($id) {
        try {
            $meme = $this->memeService->buscarPorId($id);
            $contadores = $this->votoService->obterContadoresVoto($id);

            $resultado = $meme->toArray();
            $resultado['likes'] = $contadores['likes'];
            $resultado['dislikes'] = $contadores['dislikes'];
            $resultado['voto_usuario'] = $contadores['voto_usuario'];

            $this->jsonResponse([
                'sucesso' => true,
                'meme' => $resultado
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ], 404);
        }
    }

    public function criar() {
        try {
            error_log("Iniciando criação de meme");
            $dados = $this->obterDadosRequisicao();
            error_log("Dados recebidos: " . print_r($dados, true));
            
            $arquivo = $_FILES['imagem'] ?? null;
            if ($arquivo) {
                error_log("Arquivo recebido: " . print_r($arquivo, true));
            }

            // Validar campos obrigatórios
            if (empty($dados['titulo'])) {
                throw new Exception('Título é obrigatório');
            }
            if (empty($dados['autor'])) {
                throw new Exception('Autor é obrigatório');
            }
            if (empty($dados['imagem_url']) && empty($arquivo)) {
                throw new Exception('É necessário fornecer uma imagem (upload ou URL)');
            }

            $meme = $this->memeService->criarMeme($dados, $arquivo);

            $this->jsonResponse([
                'sucesso' => true,
                'mensagem' => 'Meme criado com sucesso',
                'meme' => $meme->toArray()
            ], 201);
        } catch (Exception $e) {
            error_log("Erro ao criar meme: " . $e->getMessage());
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ], 400);
        }
    }

    public function atualizar($id) {
        try {
            $dados = $this->obterDadosRequisicao();
            $arquivo = $_FILES['imagem'] ?? null;

            $meme = $this->memeService->atualizarMeme($id, $dados, $arquivo);

            $this->jsonResponse([
                'sucesso' => true,
                'mensagem' => 'Meme atualizado com sucesso',
                'meme' => $meme->toArray()
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ], 400);
        }
    }

    public function deletar($id) {
        try {
            $this->memeService->deletarMeme($id);

            $this->jsonResponse([
                'sucesso' => true,
                'mensagem' => 'Meme deletado com sucesso'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ], 400);
        }
    }

    public function votar($id) {
        try {
            $dados = $this->obterDadosRequisicao();

            if (empty($dados['tipo'])) {
                throw new Exception('Tipo de voto é obrigatório');
            }

            $resultado = $this->votoService->votar($id, $dados['tipo']);

            $this->jsonResponse($resultado);
        } catch (Exception $e) {
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ], 400);
        }
    }

    public function obterVotos($id) {
        try {
            $contadores = $this->votoService->obterContadoresVoto($id);

            $this->jsonResponse([
                'sucesso' => true,
                'votos' => $contadores
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ], 400);
        }
    }

    public function listarTags() {
        try {
            $tags = $this->tagService->listarTodas();

            $this->jsonResponse([
                'sucesso' => true,
                'tags' => array_map(function($tag) {
                    return $tag->toArray();
                }, $tags)
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ], 500);
        }
    }

    public function criarTag() {
        try {
            $dados = $this->obterDadosRequisicao();

            if (empty($dados['nome'])) {
                throw new Exception('Nome da tag é obrigatório');
            }

            $tag = $this->tagService->criarTag($dados['nome']);

            $this->jsonResponse([
                'sucesso' => true,
                'mensagem' => 'Tag criada com sucesso',
                'tag' => $tag->toArray()
            ], 201);
        } catch (Exception $e) {
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ], 400);
        }
    }

    public function deletarTag($id) {
        try {
            $this->tagService->deletarTag($id);

            $this->jsonResponse([
                'sucesso' => true,
                'mensagem' => 'Tag deletada com sucesso'
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ], 400);
        }
    }

    public function memeAleatorio() {
        try {
            $meme = $this->memeService->buscarMemeAleatorio();
            if (!$meme) {
                throw new Exception('Nenhum meme encontrado');
            }

            $contadores = $this->votoService->obterContadoresVoto($meme->getId());
            $resultado = $meme->toArray();
            $resultado['likes'] = $contadores['likes'];
            $resultado['dislikes'] = $contadores['dislikes'];
            $resultado['voto_usuario'] = $contadores['voto_usuario'];

            $this->jsonResponse([
                'sucesso' => true,
                'meme' => $resultado
            ]);
        } catch (Exception $e) {
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => $e->getMessage()
            ], 404);
        }
    }

    private function obterDadosRequisicao() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        error_log("Content-Type: " . $contentType);
        error_log("REQUEST_METHOD: " . $_SERVER['REQUEST_METHOD']);
        error_log("REQUEST_URI: " . $_SERVER['REQUEST_URI']);

        // Se for multipart/form-data, retorna $_POST
        if (strpos($contentType, 'multipart/form-data') !== false) {
            error_log("Processando multipart/form-data");
            error_log("POST data: " . print_r($_POST, true));
            error_log("FILES data: " . print_r($_FILES, true));
            return $_POST;
        }

        // Se for application/json, processa o JSON
        if (strpos($contentType, 'application/json') !== false) {
            error_log("Processando application/json");
            $json = file_get_contents('php://input');
            error_log("JSON input: " . $json);
            $dados = json_decode($json, true) ?? [];
            
            // Se tags vierem como array, converte para string
            if (isset($dados['tags']) && is_array($dados['tags'])) {
                $dados['tags'] = implode(', ', $dados['tags']);
            }
            
            return $dados;
        }

        // Para outros tipos, retorna $_POST
        error_log("Processando dados padrão");
        error_log("POST data: " . print_r($_POST, true));
        return $_POST;
    }

    private function obterFiltros() {
        return [
            'sort' => $_GET['sort'] ?? 'recent',
            'tag_id' => $_GET['tag_id'] ?? null,
            'page' => $_GET['page'] ?? 1,
            'limit' => $_GET['limit'] ?? 20
        ];
    }

    private function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function roteamento() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($uri, '/'));

        // Remove 'PATRICKAPI' e 'api' do path se existirem
        if ($segments[0] === 'PATRICKAPI') {
            array_shift($segments);
        }
        if ($segments[0] === 'api') {
            array_shift($segments);
        }

        try {
            switch ($method) {
                case 'GET':
                    if (empty($segments[0])) {
                        $this->index();
                    } elseif ($segments[0] === 'meme') {
                        $this->memeAleatorio();
                    } elseif ($segments[0] === 'memes') {
                        if (empty($segments[1])) {
                            $this->index();
                        } elseif (is_numeric($segments[1])) {
                            if (!empty($segments[2]) && $segments[2] === 'votos') {
                                $this->obterVotos($segments[1]);
                            } else {
                                $this->buscar($segments[1]);
                            }
                        }
                    } elseif ($segments[0] === 'tags') {
                        $this->listarTags();
                    }
                    break;

                case 'POST':
                    if ($segments[0] === 'memes') {
                        if (empty($segments[1])) {
                            $this->criar();
                        } elseif (is_numeric($segments[1]) && $segments[2] === 'votar') {
                            $this->votar($segments[1]);
                        }
                    } elseif ($segments[0] === 'tags') {
                        $this->criarTag();
                    }
                    break;

                case 'PUT':
                    if ($segments[0] === 'memes' && is_numeric($segments[1])) {
                        $this->atualizar($segments[1]);
                    }
                    break;

                case 'DELETE':
                    if ($segments[0] === 'memes' && is_numeric($segments[1])) {
                        $this->deletar($segments[1]);
                    } elseif ($segments[0] === 'tags' && is_numeric($segments[1])) {
                        $this->deletarTag($segments[1]);
                    }
                    break;

                default:
                    $this->jsonResponse([
                        'sucesso' => false,
                        'erro' => 'Método não permitido'
                    ], 405);
            }
        } catch (Exception $e) {
            $this->jsonResponse([
                'sucesso' => false,
                'erro' => 'Erro interno do servidor: ' . $e->getMessage()
            ], 500);
        }
    }
}
?>
