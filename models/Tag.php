<?php
class Tag {
    private $id;
    private $nome;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->nome = $data['nome'] ?? '';
        }
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getNome() {
        return $this->nome;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setNome($nome) {
        $this->nome = htmlspecialchars(trim(strtolower($nome)));
    }

    // Métodos de validação
    public function validar() {
        $erros = [];

        if (empty($this->nome)) {
            $erros[] = 'Nome da tag é obrigatório';
        } elseif (strlen($this->nome) > 50) {
            $erros[] = 'Nome da tag deve ter no máximo 50 caracteres';
        } elseif (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $this->nome)) {
            $erros[] = 'Nome da tag contém caracteres inválidos';
        }

        return $erros;
    }

    // Converter para array
    public function toArray() {
        return [
            'id' => $this->id,
            'nome' => $this->nome
        ];
    }
}
?>
