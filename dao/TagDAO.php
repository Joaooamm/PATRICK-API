<?php
require_once 'dao/BaseDAO.php';
require_once 'models/Tag.php';

class TagDAO extends BaseDAO {

    public function criar(Tag $tag) {
        $sql = "INSERT INTO tags (nome) VALUES (?)";
        $params = [$tag->getNome()];

        try {
            $this->execute($sql, $params);
            return $this->lastInsertId();
        } catch (Exception $e) {
            // Se a tag já existe, buscar e retornar o ID
            if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $existing = $this->buscarPorNome($tag->getNome());
                return $existing ? $existing->getId() : null;
            }
            throw $e;
        }
    }

    public function buscarPorId($id) {
        $sql = "SELECT * FROM tags WHERE id = ?";
        $result = $this->fetch($sql, [$id]);

        return $result ? new Tag($result) : null;
    }

    public function buscarPorNome($nome) {
        $sql = "SELECT * FROM tags WHERE nome = ?";
        $result = $this->fetch($sql, [strtolower(trim($nome))]);

        return $result ? new Tag($result) : null;
    }

    public function listarTodas() {
        $sql = "
            SELECT t.*, COUNT(mt.meme_id) as total_memes
            FROM tags t
            LEFT JOIN meme_tag mt ON t.id = mt.tag_id
            GROUP BY t.id
            ORDER BY total_memes DESC, t.nome ASC
        ";

        $results = $this->fetchAll($sql);
        $tags = [];

        foreach ($results as $result) {
            $tag = new Tag($result);
            $tag->totalMemes = $result['total_memes'];
            $tags[] = $tag;
        }

        return $tags;
    }

    public function atualizar(Tag $tag) {
        $sql = "UPDATE tags SET nome = ? WHERE id = ?";
        $params = [$tag->getNome(), $tag->getId()];

        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount() > 0;
    }

    public function deletar($id) {
        $sql = "DELETE FROM tags WHERE id = ?";
        $stmt = $this->execute($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function buscarOuCriar($nome) {
        $tag = $this->buscarPorNome($nome);

        if (!$tag) {
            $novaTag = new Tag();
            $novaTag->setNome($nome);

            $erros = $novaTag->validar();
            if (!empty($erros)) {
                throw new Exception(implode(', ', $erros));
            }

            $id = $this->criar($novaTag);
            $novaTag->setId($id);
            return $novaTag;
        }

        return $tag;
    }

    public function buscarPorMeme($memeId) {
        $sql = "
            SELECT t.*
            FROM tags t
            INNER JOIN meme_tag mt ON t.id = mt.tag_id
            WHERE mt.meme_id = ?
            ORDER BY t.nome ASC
        ";

        $results = $this->fetchAll($sql, [$memeId]);
        $tags = [];

        foreach ($results as $result) {
            $tags[] = new Tag($result);
        }

        return $tags;
    }
}
?>
