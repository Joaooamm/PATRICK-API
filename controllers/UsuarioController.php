<?php

require_once 'services/UsuarioService.php';
require_once 'middleware/JWTMiddleware.php';

class UsuarioController {
    private $usuarioService;

    public function __construct() {
        try {
            $this->usuarioService = new UsuarioService();
        } catch (Exception $e) {
            $this->sendErrorResponse("Erro interno do servidor", 500);
        }
    }

    // Método público - Login (gera JWT)
    public function login() {
        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                $this->sendErrorResponse("Dados inválidos", 400);
                return;
            }

            $usuario = $this->usuarioService->autenticar($data['email'], $data['senha']);

            // Gerar token JWT
            $token = JWTMiddleware::generateToken($usuario->id, $usuario->email);

            $this->sendSuccessResponse([
                'message' => 'Login realizado com sucesso',
                'token' => $token,
                'usuario' => $usuario->toArray()
            ]);

        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 401);
        }
    }

    // Métodos protegidos (requerem JWT)
    public function create() {
        // Validar token JWT
        $userData = JWTMiddleware::validateToken();
        if (!$userData) {
            return; // Middleware já enviou resposta de erro
        }

        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                $this->sendErrorResponse("Dados inválidos", 400);
                return;
            }

            $usuario = $this->usuarioService->criarUsuario($data);

            $this->sendSuccessResponse([
                'message' => 'Usuário criado com sucesso',
                'usuario' => $usuario->toArray()
            ], 201);

        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    public function read() {
        // Validar token JWT
        $userData = JWTMiddleware::validateToken();
        if (!$userData) {
            return; // Middleware já enviou resposta de erro
        }

        try {
            $usuarios = $this->usuarioService->listarUsuarios();

            $usuariosArray = array_map(function($usuario) {
                return $usuario->toArray();
            }, $usuarios);

            $this->sendSuccessResponse([
                'message' => 'Usuários listados com sucesso',
                'usuarios' => $usuariosArray,
                'total' => count($usuariosArray)
            ]);

        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 500);
        }
    }

    public function readOne($id) {
        // Validar token JWT
        $userData = JWTMiddleware::validateToken();
        if (!$userData) {
            return; // Middleware já enviou resposta de erro
        }

        try {
            $usuario = $this->usuarioService->buscarUsuario($id);

            $this->sendSuccessResponse([
                'message' => 'Usuário encontrado',
                'usuario' => $usuario->toArray()
            ]);

        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 404);
        }
    }

    public function update($id) {
        // Validar token JWT
        $userData = JWTMiddleware::validateToken();
        if (!$userData) {
            return; // Middleware já enviou resposta de erro
        }

        try {
            $data = json_decode(file_get_contents("php://input"), true);

            if (!$data) {
                $this->sendErrorResponse("Dados inválidos", 400);
                return;
            }

            $usuario = $this->usuarioService->atualizarUsuario($id, $data);

            $this->sendSuccessResponse([
                'message' => 'Usuário atualizado com sucesso',
                'usuario' => $usuario->toArray()
            ]);

        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    public function delete($id) {
        // Validar token JWT
        $userData = JWTMiddleware::validateToken();
        if (!$userData) {
            return; // Middleware já enviou resposta de erro
        }

        try {
            $this->usuarioService->deletarUsuario($id);

            $this->sendSuccessResponse([
                'message' => 'Usuário deletado com sucesso'
            ]);

        } catch (Exception $e) {
            $this->sendErrorResponse($e->getMessage(), 400);
        }
    }

    // Métodos auxiliares para resposta
    private function sendSuccessResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'data' => $data
        ]);
    }

    private function sendErrorResponse($message, $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }
}
