<?php
require_once 'services/MemeService.php';
require_once 'services/VotoService.php';
require_once 'services/TagService.php';

class MemeController {
    private $memeService;
    private $votoService;
    private $tagService;

    public function __construct() {
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
            $dados = $this->obterDadosRequisicao();
            $arquivo = $_FILES['imagem'] ?? null;


            $meme = $this->memeService->criarMeme($dados, $arquivo);

            $this->jsonResponse([
                'sucesso' => true,
                'mensagem' => 'Meme criado com sucesso',
                'meme' => $meme->toArray()
            ], 201);
        } catch (Exception $e) {
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

    private function obterDadosRequisicao() {
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            var_dump($json);
            if (json_last_error() !== JSON_ERROR_NONE) {
                echo "Erro ao decodificar JSON: " . json_last_error_msg();
            }
            $dados = json_decode($json);
        }
    
        return [];
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
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public function roteamento() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $segments = explode('/', trim($uri, '/'));

        // Remove 'api' do path se existir
        if ($segments[0] === 'api') {
            array_shift($segments);
        }

        try {
            switch ($method) {
                case 'GET':
                    if (empty($segments[0])) {
                        $this->index();
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
                    
                    if ($segments[2] === 'memes') {
                        if (!empty($segments[1])) {
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
                'erro' => 'Erro interno do servidor'
            ], 500);
        }
    }
}
?>
