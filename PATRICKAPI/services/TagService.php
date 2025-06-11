<?php
require_once 'dao/TagDAO.php';
require_once 'models/Tag.php';

class TagService {
    private $tagDAO;

    public function __construct() {
        $this->tagDAO = new TagDAO();
    }

    public function criarTag($nome) {
        try {
            if (empty($nome)) {
                throw new Exception('Nome da tag é obrigatório');
            }

            $tag = new Tag();
            $tag->setNome($nome);

            // Validar tag
            $erros = $tag->validar();
            if (!empty($erros)) {
                throw new Exception(implode(', ', $erros));
            }

            // Verificar se já existe
            $tagExistente = $this->tagDAO->buscarPorNome($tag->getNome());
            if ($tagExistente) {
                throw new Exception('Tag já existe');
            }

            $id = $this->tagDAO->criar($tag);
            $tag->setId($id);

            return $tag;

        } catch (Exception $e) {
            error_log("Erro ao criar tag: " . $e->getMessage());
            throw new Exception("Erro ao criar tag: " . $e->getMessage());
        }
    }

    public function buscarPorId($id) {
        if (empty($id) || !is_numeric($id)) {
            throw new Exception('ID inválido');
        }

        $tag = $this->tagDAO->buscarPorId($id);
        if (!$tag) {
            throw new Exception('Tag não encontrada');
        }

        return $tag;
    }

    public function buscarPorNome($nome) {
        if (empty($nome)) {
            throw new Exception('Nome da tag é obrigatório');
        }

        return $this->tagDAO->buscarPorNome($nome);
    }

    public function listarTodas() {
        return $this->tagDAO->listarTodas();
    }

    public function atualizarTag($id, $novoNome) {
        try {
            $tag = $this->buscarPorId($id);

            if (empty($novoNome)) {
                throw new Exception('Nome da tag é obrigatório');
            }

            // Verificar se o novo nome já existe em outra tag
            $tagExistente = $this->tagDAO->buscarPorNome($novoNome);
            if ($tagExistente && $tagExistente->getId() != $id) {
                throw new Exception('Já existe uma tag com este nome');
            }

            $tag->setNome($novoNome);

            // Validar
            $erros = $tag->validar();
            if (!empty($erros)) {
                throw new Exception(implode(', ', $erros));
            }

            $sucesso = $this->tagDAO->atualizar($tag);
            if (!$sucesso) {
                throw new Exception('Erro ao atualizar tag');
            }

            return $tag;

        } catch (Exception $e) {
            error_log("Erro ao atualizar tag: " . $e->getMessage());
            throw new Exception("Erro ao atualizar tag: " . $e->getMessage());
        }
    }

    public function deletarTag($id) {
        try {
            $tag = $this->buscarPorId($id);

            // Verificar se a tag está sendo usada
            $memes = $this->tagDAO->buscarPorMeme($id);
            if (!empty($memes)) {
                throw new Exception('Não é possível deletar tag que está sendo usada em memes');
            }

            $sucesso = $this->tagDAO->deletar($id);
            if (!$sucesso) {
                throw new Exception('Erro ao deletar tag');
            }

            return true;

        } catch (Exception $e) {
            error_log("Erro ao deletar tag: " . $e->getMessage());
            throw new Exception("Erro ao deletar tag: " . $e->getMessage());
        }
    }

    public function buscarOuCriarTag($nome) {
        try {
            $tag = $this->tagDAO->buscarPorNome($nome);

            if (!$tag) {
                $tag = $this->criarTag($nome);
            }

            return $tag;

        } catch (Exception $e) {
            error_log("Erro ao buscar/criar tag: " . $e->getMessage());
            throw new Exception("Erro ao processar tag: " . $e->getMessage());
        }
    }

    public function processarTagsString($tagsString) {
        if (empty($tagsString)) {
            return [];
        }

        $tagNames = array_map('trim', explode(',', $tagsString));
        $tags = [];

        foreach ($tagNames as $tagName) {
            if (!empty($tagName)) {
                try {
                    $tag = $this->buscarOuCriarTag($tagName);
                    $tags[] = $tag;
                } catch (Exception $e) {
                    // Log do erro mas continua processando outras tags
                    error_log("Erro ao processar tag '{$tagName}': " . $e->getMessage());
                }
            }
        }

        return $tags;
    }

    public function obterTagsPopulares($limite = 10) {
        $tags = $this->tagDAO->listarTodas();

        // Ordenar por popularidade (total de memes)
        usort($tags, function($a, $b) {
            $aTotalMemes = $a->totalMemes ?? 0;
            $bTotalMemes = $b->totalMemes ?? 0;
            return $bTotalMemes - $aTotalMemes;
        });

        return array_slice($tags, 0, $limite);
    }

    public function buscarTagsPorPalavraChave($palavraChave) {
        if (empty($palavraChave)) {
            return [];
        }

        $todasTags = $this->tagDAO->listarTodas();
        $tagsEncontradas = [];

        foreach ($todasTags as $tag) {
            if (stripos($tag->getNome(), $palavraChave) !== false) {
                $tagsEncontradas[] = $tag;
            }
        }

        return $tagsEncontradas;
    }

    public function validarNomeTag($nome) {
        $erros = [];

        if (empty($nome)) {
            $erros[] = 'Nome da tag é obrigatório';
        } elseif (strlen($nome) > 50) {
            $erros[] = 'Nome da tag deve ter no máximo 50 caracteres';
        } elseif (!preg_match('/^[a-zA-Z0-9\s\-_]+$/', $nome)) {
            $erros[] = 'Nome da tag contém caracteres inválidos';
        }

        return $erros;
    }
}
?>
