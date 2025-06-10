<?php
class Voto {
    private $id;
    private $memeId;
    private $tipo;
    private $ipAddress;
    private $criadoEm;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->memeId = $data['meme_id'] ?? null;
            $this->tipo = $data['tipo'] ?? '';
            $this->ipAddress = $data['ip_address'] ?? '';
            $this->criadoEm = $data['criado_em'] ?? null;
        }
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getMemeId() {
        return $this->memeId;
    }

    public function getTipo() {
        return $this->tipo;
    }

    public function getIpAddress() {
        return $this->ipAddress;
    }

    public function getCriadoEm() {
        return $this->criadoEm;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setMemeId($memeId) {
        $this->memeId = (int)$memeId;
    }

    public function setTipo($tipo) {
        $this->tipo = $tipo;
    }

    public function setIpAddress($ipAddress) {
        $this->ipAddress = $ipAddress;
    }

    public function setCriadoEm($criadoEm) {
        $this->criadoEm = $criadoEm;
    }

    // Métodos de validação
    public function validar() {
        $erros = [];

        if (empty($this->memeId)) {
            $erros[] = 'ID do meme é obrigatório';
        }

        if (!in_array($this->tipo, ['like', 'dislike'])) {
            $erros[] = 'Tipo de voto deve ser "like" ou "dislike"';
        }

        if (empty($this->ipAddress)) {
            $erros[] = 'IP address é obrigatório';
        }

        return $erros;
    }

    // Converter para array
    public function toArray() {
        return [
            'id' => $this->id,
            'meme_id' => $this->memeId,
            'tipo' => $this->tipo,
            'ip_address' => $this->ipAddress,
            'criado_em' => $this->criadoEm
        ];
    }
}
?>
