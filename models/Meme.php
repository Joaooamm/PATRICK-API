<?php
class Meme {
    private $id;
    private $titulo;
    private $imagemUrl;
    private $legenda;
    private $autor;
    private $criadoEm;
    private $tags;
    private $likes;
    private $dislikes;

    public function __construct($data = []) {
        if (!empty($data)) {
            $this->id = $data['id'] ?? null;
            $this->titulo = $data['titulo'] ?? '';
            $this->imagemUrl = $data['imagem_url'] ?? '';
            $this->legenda = $data['legenda'] ?? '';
            $this->autor = $data['autor'] ?? '';
            $this->criadoEm = $data['criado_em'] ?? null;
            $this->tags = $data['tags'] ?? [];
            $this->likes = $data['likes'] ?? 0;
            $this->dislikes = $data['dislikes'] ?? 0;
        }
    }

    // Getters
    public function getId() {
        return $this->id;
    }

    public function getTitulo() {
        return $this->titulo;
    }

    public function getImagemUrl() {
        return $this->imagemUrl;
    }

    public function getLegenda() {
        return $this->legenda;
    }

    public function getAutor() {
        return $this->autor;
    }

    public function getCriadoEm() {
        return $this->criadoEm;
    }

    public function getTags() {
        return $this->tags;
    }

    public function getLikes() {
        return $this->likes;
    }

    public function getDislikes() {
        return $this->dislikes;
    }

    // Setters
    public function setId($id) {
        $this->id = $id;
    }

    public function setTitulo($titulo) {
        $this->titulo = htmlspecialchars(trim($titulo));
    }

    public function setImagemUrl($imagemUrl) {
        $this->imagemUrl = $imagemUrl;
    }

    public function setLegenda($legenda) {
        $this->legenda = htmlspecialchars(trim($legenda));
    }

    public function setAutor($autor) {
        $this->autor = htmlspecialchars(trim($autor));
    }

    public function setCriadoEm($criadoEm) {
        $this->criadoEm = $criadoEm;
    }

    public function setTags($tags) {
        $this->tags = $tags;
    }

    public function setLikes($likes) {
        $this->likes = (int)$likes;
    }

    public function setDislikes($dislikes) {
        $this->dislikes = (int)$dislikes;
    }

    // Métodos de validação
    public function validar() {
        $erros = [];

        if (empty($this->titulo)) {
            $erros[] = 'Título é obrigatório';
        } elseif (strlen($this->titulo) > 100) {
            $erros[] = 'Título deve ter no máximo 100 caracteres';
        }

        if (empty($this->imagemUrl)) {
            $erros[] = 'Imagem é obrigatória';
        }

        if (empty($this->autor)) {
            $erros[] = 'Autor é obrigatório';
        } elseif (strlen($this->autor) > 100) {
            $erros[] = 'Nome do autor deve ter no máximo 100 caracteres';
        }

        if (!empty($this->legenda) && strlen($this->legenda) > 500) {
            $erros[] = 'Legenda deve ter no máximo 500 caracteres';
        }

        return $erros;
    }

    // Converter para array
    public function toArray() {
        return [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'imagem_url' => $this->imagemUrl,
            'legenda' => $this->legenda,
            'autor' => $this->autor,
            'criado_em' => $this->criadoEm,
            'tags' => $this->tags,
            'likes' => $this->likes,
            'dislikes' => $this->dislikes
        ];
    }
}
?>
