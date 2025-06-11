<?php

class Usuario {
    public $id;
    public $nome;
    public $email;
    public $senha;
    public $created_at;
    public $updated_at;

    public function __construct($id = null, $nome = null, $email = null, $senha = null) {
        $this->id = $id;
        $this->nome = $nome;
        $this->email = $email;
        $this->senha = $senha;
    }

    public function toArray() {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
