<?php

require_once 'config/database.php';
require_once 'models/Usuario.php';

class UsuarioDAO {
    private $conn;
    private $table_name = "usuarios";

    public function __construct() {
        try {
            $database = new Database();
            $this->conn = $database->getConnection();
        } catch (Exception $e) {
            throw new Exception("Erro ao conectar com o banco de dados: " . $e->getMessage());
        }
    }

    public function create(Usuario $usuario) {
        try {
            $query = "INSERT INTO " . $this->table_name . "
                     SET nome=:nome, email=:email, senha=:senha, created_at=NOW()";

            $stmt = $this->conn->prepare($query);

            $senha_hash = password_hash($usuario->senha, PASSWORD_DEFAULT);

            $stmt->bindParam(":nome", $usuario->nome);
            $stmt->bindParam(":email", $usuario->email);
            $stmt->bindParam(":senha", $senha_hash);

            if ($stmt->execute()) {
                $usuario->id = $this->conn->lastInsertId();
                return $usuario;
            }

            throw new Exception("Erro ao criar usuário");

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new Exception("E-mail já cadastrado");
            }
            throw new Exception("Erro no banco de dados: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Erro ao criar usuário: " . $e->getMessage());
        }
    }

    public function read() {
        try {
            $query = "SELECT id, nome, email, created_at, updated_at FROM " . $this->table_name . " ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();

            $usuarios = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuario = new Usuario();
                $usuario->id = $row['id'];
                $usuario->nome = $row['nome'];
                $usuario->email = $row['email'];
                $usuario->created_at = $row['created_at'];
                $usuario->updated_at = $row['updated_at'];
                $usuarios[] = $usuario;
            }

            return $usuarios;

        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar usuários: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Erro inesperado: " . $e->getMessage());
        }
    }

    public function readOne($id) {
        try {
            $query = "SELECT id, nome, email, created_at, updated_at FROM " . $this->table_name . " WHERE id = ? LIMIT 0,1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuario = new Usuario();
                $usuario->id = $row['id'];
                $usuario->nome = $row['nome'];
                $usuario->email = $row['email'];
                $usuario->created_at = $row['created_at'];
                $usuario->updated_at = $row['updated_at'];
                return $usuario;
            }

            return null;

        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar usuário: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Erro inesperado: " . $e->getMessage());
        }
    }

    public function update(Usuario $usuario) {
        try {
            $query = "UPDATE " . $this->table_name . "
                     SET nome=:nome, email=:email, updated_at=NOW()
                     WHERE id=:id";

            $stmt = $this->conn->prepare($query);

            $stmt->bindParam(":nome", $usuario->nome);
            $stmt->bindParam(":email", $usuario->email);
            $stmt->bindParam(":id", $usuario->id);

            if ($stmt->execute()) {
                return $this->readOne($usuario->id);
            }

            throw new Exception("Erro ao atualizar usuário");

        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                throw new Exception("E-mail já cadastrado por outro usuário");
            }
            throw new Exception("Erro no banco de dados: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Erro ao atualizar usuário: " . $e->getMessage());
        }
    }

    public function delete($id) {
        try {
            $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $id);

            if ($stmt->execute()) {
                return $stmt->rowCount() > 0;
            }

            return false;

        } catch (PDOException $e) {
            throw new Exception("Erro ao deletar usuário: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Erro inesperado: " . $e->getMessage());
        }
    }

    public function findByEmail($email) {
        try {
            $query = "SELECT id, nome, email, senha, created_at, updated_at FROM " . $this->table_name . " WHERE email = ? LIMIT 0,1";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(1, $email);
            $stmt->execute();

            if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $usuario = new Usuario();
                $usuario->id = $row['id'];
                $usuario->nome = $row['nome'];
                $usuario->email = $row['email'];
                $usuario->senha = $row['senha'];
                $usuario->created_at = $row['created_at'];
                $usuario->updated_at = $row['updated_at'];
                return $usuario;
            }

            return null;

        } catch (PDOException $e) {
            throw new Exception("Erro ao buscar usuário por email: " . $e->getMessage());
        } catch (Exception $e) {
            throw new Exception("Erro inesperado: " . $e->getMessage());
        }
    }
}
