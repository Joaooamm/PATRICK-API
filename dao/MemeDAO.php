<?php
require_once 'dao/BaseDAO.php';
require_once 'models/Meme.php';

class MemeDAO extends BaseDAO {

    public function criar(Meme $meme) {
        $sql = "INSERT INTO memes (titulo, imagem_url, legenda, autor) VALUES (?, ?, ?, ?)";
        $params = [
            $meme->getTitulo(),
            $meme->getImagemUrl(),
            $meme->getLegenda(),
            $meme->getAutor()
        ];

        $this->execute($sql, $params);
        return $this->lastInsertId();
    }

    public function buscarPorId($id) {
        $sql = "
            SELECT m.*,
                   COALESCE(SUM(CASE WHEN v.tipo = 'like' THEN 1 ELSE 0 END), 0) as likes,
                   COALESCE(SUM(CASE WHEN v.tipo = 'dislike' THEN 1 ELSE 0 END), 0) as dislikes
            FROM memes m
            LEFT JOIN votos v ON m.id = v.meme_id
            WHERE m.id = ?
            GROUP BY m.id
        ";

        $result = $this->fetch($sql, [$id]);

        if ($result) {
            $meme = new Meme($result);
            $meme->setTags($this->buscarTagsPorMeme($id));
            return $meme;
        }

        return null;
    }

    public function listarTodos($orderBy = 'criado_em DESC', $tagId = null) {
        $sql = "
            SELECT m.*,
                   COALESCE(SUM(CASE WHEN v.tipo = 'like' THEN 1 ELSE 0 END), 0) as likes,
                   COALESCE(SUM(CASE WHEN v.tipo = 'dislike' THEN 1 ELSE 0 END), 0) as dislikes
            FROM memes m
            LEFT JOIN votos v ON m.id = v.meme_id
        ";

        $params = [];

        if ($tagId) {
            $sql .= " INNER JOIN meme_tag mt ON m.id = mt.meme_id WHERE mt.tag_id = ?";
            $params[] = $tagId;
        }

        $sql .= " GROUP BY m.id ORDER BY " . $this->sanitizeOrderBy($orderBy);

        $results = $this->fetchAll($sql, $params);
        $memes = [];

        foreach ($results as $result) {
            $meme = new Meme($result);
            $meme->setTags($this->buscarTagsPorMeme($result['id']));
            $memes[] = $meme;
        }

        return $memes;
    }

    public function atualizar(Meme $meme) {
        $sql = "UPDATE memes SET titulo = ?, imagem_url = ?, legenda = ?, autor = ? WHERE id = ?";
        $params = [
            $meme->getTitulo(),
            $meme->getImagemUrl(),
            $meme->getLegenda(),
            $meme->getAutor(),
            $meme->getId()
        ];

        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount() > 0;
    }

    public function deletar($id) {
        $sql = "DELETE FROM memes WHERE id = ?";
        $stmt = $this->execute($sql, [$id]);
        return $stmt->rowCount() > 0;
    }

    public function buscarTagsPorMeme($memeId) {
        $sql = "
            SELECT t.id, t.nome
            FROM tags t
            INNER JOIN meme_tag mt ON t.id = mt.tag_id
            WHERE mt.meme_id = ?
        ";

        return $this->fetchAll($sql, [$memeId]);
    }

    public function adicionarTag($memeId, $tagId) {
        $sql = "INSERT IGNORE INTO meme_tag (meme_id, tag_id) VALUES (?, ?)";
        $this->execute($sql, [$memeId, $tagId]);
    }

    public function removerTag($memeId, $tagId) {
        $sql = "DELETE FROM meme_tag WHERE meme_id = ? AND tag_id = ?";
        $stmt = $this->execute($sql, [$memeId, $tagId]);
        return $stmt->rowCount() > 0;
    }

    private function sanitizeOrderBy($orderBy) {
        $allowed = [
            'criado_em DESC' => 'criado_em DESC',
            'criado_em ASC' => 'criado_em ASC',
            'likes DESC' => 'likes DESC',
            'likes ASC' => 'likes ASC',
            'titulo ASC' => 'titulo ASC',
            'titulo DESC' => 'titulo DESC'
        ];

        return $allowed[$orderBy] ?? 'criado_em DESC';
    }
}
?>
