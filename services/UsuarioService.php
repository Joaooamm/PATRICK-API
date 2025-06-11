<?php

require_once 'dao/UsuarioDAO.php';
require_once 'models/Usuario.php';

class UsuarioService {
    private $usuarioDAO;

    public function __construct() {
        try {
            $this->usuarioDAO = new UsuarioDAO();
        } catch (Exception $e) {
            throw new Exception("Erro ao inicializar serviço: " . $e->getMessage());
        }
    }

    public function criarUsuario($dados) {
        try {
            // Validações
            if (empty($dados['nome'])) {
                throw new Exception("Nome é obrigatório");
            }

            if (empty($dados['email'])) {
                throw new Exception("E-mail é obrigatório");
            }

            if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("E-mail inválido");
            }

            if (empty($dados['senha'])) {
                throw new Exception("Senha é obrigatória");
            }

            if (strlen($dados['senha']) < 6) {
                throw new Exception("Senha deve ter pelo menos 6 caracteres");
            }

            // Verificar se email já existe
            $usuarioExistente = $this->usuarioDAO->findByEmail($dados['email']);
            if ($usuarioExistente) {
                throw new Exception("E-mail já cadastrado");
            }

            // Criar usuário
            $usuario = new Usuario();
            $usuario->nome = trim($dados['nome']);
            $usuario->email = trim(strtolower($dados['email']));
            $usuario->senha = $dados['senha'];

            return $this->usuarioDAO->create($usuario);

        } catch (Exception $e) {
            throw new Exception("Erro ao criar usuário: " . $e->getMessage());
        }
    }

    public function listarUsuarios() {
        try {
            return $this->usuarioDAO->read();
        } catch (Exception $e) {
            throw new Exception("Erro ao listar usuários: " . $e->getMessage());
        }
    }

    public function buscarUsuario($id) {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new Exception("ID inválido");
            }

            $usuario = $this->usuarioDAO->readOne($id);
            if (!$usuario) {
                throw new Exception("Usuário não encontrado");
            }

            return $usuario;
        } catch (Exception $e) {
            throw new Exception("Erro ao buscar usuário: " . $e->getMessage());
        }
    }

    public function atualizarUsuario($id, $dados) {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new Exception("ID inválido");
            }

            // Verificar se usuário existe
            $usuario = $this->usuarioDAO->readOne($id);
            if (!$usuario) {
                throw new Exception("Usuário não encontrado");
            }

            // Validações
            if (empty($dados['nome'])) {
                throw new Exception("Nome é obrigatório");
            }

            if (empty($dados['email'])) {
                throw new Exception("E-mail é obrigatório");
            }

            if (!filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception("E-mail inválido");
            }

            // Verificar se email já existe em outro usuário
            $usuarioComEmail = $this->usuarioDAO->findByEmail($dados['email']);
            if ($usuarioComEmail && $usuarioComEmail->id != $id) {
                throw new Exception("E-mail já cadastrado por outro usuário");
            }

            // Atualizar dados
            $usuario->nome = trim($dados['nome']);
            $usuario->email = trim(strtolower($dados['email']));

            return $this->usuarioDAO->update($usuario);

        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar usuário: " . $e->getMessage());
        }
    }

    public function deletarUsuario($id) {
        try {
            if (empty($id) || !is_numeric($id)) {
                throw new Exception("ID inválido");
            }

            // Verificar se usuário existe
            $usuario = $this->usuarioDAO->readOne($id);
            if (!$usuario) {
                throw new Exception("Usuário não encontrado");
            }

            $resultado = $this->usuarioDAO->delete($id);
            if (!$resultado) {
                throw new Exception("Erro ao deletar usuário");
            }

            return true;

        } catch (Exception $e) {
            throw new Exception("Erro ao deletar usuário: " . $e->getMessage());
        }
    }

    public function autenticar($email, $senha) {
        try {
            if (empty($email)) {
                throw new Exception("E-mail é obrigatório");
            }

            if (empty($senha)) {
                throw new Exception("Senha é obrigatória");
            }

            $usuario = $this->usuarioDAO->findByEmail($email);
            if (!$usuario) {
                throw new Exception("Credenciais inválidas");
            }

            if (!password_verify($senha, $usuario->senha)) {
                throw new Exception("Credenciais inválidas");
            }

            return $usuario;

        } catch (Exception $e) {
            throw new Exception("Erro na autenticação: " . $e->getMessage());
        }
    }
}
