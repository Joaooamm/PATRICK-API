<?php
require_once 'dao/BaseDAO.php';
require_once 'models/Voto.php';

class VotoDAO extends BaseDAO {

    public function votar($memeId, $tipo, $ipAddress) {
        // Primeiro, verificar se já existe um voto deste IP para este meme
        $votoExistente = $this->buscarVotoPorIpEMeme($memeId, $ipAddress);

        if ($votoExistente) {
            // Se o voto é do mesmo tipo, remover (toggle)
            if ($votoExistente->getTipo() === $tipo) {
                return $this->removerVoto($memeId, $ipAddress);
            } else {
                // Se o voto é diferente, atualizar
                return $this->atualizarVoto($memeId, $tipo, $ipAddress);
            }
        } else {
            // Criar novo voto
            return $this->criarVoto($memeId, $tipo, $ipAddress);
        }
    }

    private function criarVoto($memeId, $tipo, $ipAddress) {
        $sql = "INSERT INTO votos (meme_id, tipo, ip_address) VALUES (?, ?, ?)";
        $params = [$memeId, $tipo, $ipAddress];

        $this->execute($sql, $params);
        return $this->lastInsertId();
    }

    private function atualizarVoto($memeId, $tipo, $ipAddress) {
        $sql = "UPDATE votos SET tipo = ? WHERE meme_id = ? AND ip_address = ?";
        $params = [$tipo, $memeId, $ipAddress];

        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount() > 0;
    }

    private function removerVoto($memeId, $ipAddress) {
        $sql = "DELETE FROM votos WHERE meme_id = ? AND ip_address = ?";
        $params = [$memeId, $ipAddress];

        $stmt = $this->execute($sql, $params);
        return $stmt->rowCount() > 0;
    }

    public function buscarVotoPorIpEMeme($memeId, $ipAddress) {
        $sql = "SELECT * FROM votos WHERE meme_id = ? AND ip_address = ?";
        $result = $this->fetch($sql, [$memeId, $ipAddress]);

        return $result ? new Voto($result) : null;
    }

    public function contarVotosPorMeme($memeId) {
        $sql = "
            SELECT
                SUM(CASE WHEN tipo = 'like' THEN 1 ELSE 0 END) as likes,
                SUM(CASE WHEN tipo = 'dislike' THEN 1 ELSE 0 END) as dislikes
            FROM votos
            WHERE meme_id = ?
        ";

        $result = $this->fetch($sql, [$memeId]);

        return [
            'likes' => (int)($result['likes'] ?? 0),
            'dislikes' => (int)($result['dislikes'] ?? 0)
        ];
    }

    public function listarVotosPorMeme($memeId) {
        $sql = "SELECT * FROM votos WHERE meme_id = ? ORDER BY criado_em DESC";
        $results = $this->fetchAll($sql, [$memeId]);

        $votos = [];
        foreach ($results as $result) {
            $votos[] = new Voto($result);
        }

        return $votos;
    }

    public function deletarVotosPorMeme($memeId) {
        $sql = "DELETE FROM votos WHERE meme_id = ?";
        $stmt = $this->execute($sql, [$memeId]);
        return $stmt->rowCount();
    }

    public function obterEstatisticasGerais() {
        $sql = "
            SELECT
                COUNT(*) as total_votos,
                SUM(CASE WHEN tipo = 'like' THEN 1 ELSE 0 END) as total_likes,
                SUM(CASE WHEN tipo = 'dislike' THEN 1 ELSE 0 END) as total_dislikes,
                COUNT(DISTINCT meme_id) as memes_votados
            FROM votos
        ";

        return $this->fetch($sql);
    }

    public function obterTopMemesPorLikes($limite = 10) {
        $sql = "
            SELECT
                m.id, m.titulo, m.autor,
                COUNT(v.id) as total_likes
            FROM memes m
            INNER JOIN votos v ON m.id = v.meme_id
            WHERE v.tipo = 'like'
            GROUP BY m.id
            ORDER BY total_likes DESC
            LIMIT ?
        ";

        return $this->fetchAll($sql, [$limite]);
    }
}
?>
