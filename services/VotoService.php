<?php
require_once 'dao/VotoDAO.php';
require_once 'dao/MemeDAO.php';

class VotoService {
    private $votoDAO;
    private $memeDAO;

    public function __construct() {
        $this->votoDAO = new VotoDAO();
        $this->memeDAO = new MemeDAO();
    }

    public function votar($memeId, $tipo) {
        try {
            // Validar dados
            if (empty($memeId) || !is_numeric($memeId)) {
                throw new Exception('ID do meme inválido');
            }

            if (!in_array($tipo, ['like', 'dislike'])) {
                throw new Exception('Tipo de voto inválido');
            }

            // Verificar se o meme existe
            $meme = $this->memeDAO->buscarPorId($memeId);
            if (!$meme) {
                throw new Exception('Meme não encontrado');
            }

            // Obter IP do usuário
            $ipAddress = $this->obterIpUsuario();

            // Processar voto
            $resultado = $this->votoDAO->votar($memeId, $tipo, $ipAddress);

            // Retornar contadores atualizados
            $contadores = $this->votoDAO->contarVotosPorMeme($memeId);

            return [
                'sucesso' => true,
                'acao' => $resultado ? 'votado' : 'removido',
                'likes' => $contadores['likes'],
                'dislikes' => $contadores['dislikes'],
                'voto_atual' => $this->obterVotoUsuario($memeId, $ipAddress)
            ];

        } catch (Exception $e) {
            error_log("Erro ao votar: " . $e->getMessage());
            throw new Exception("Erro ao processar voto: " . $e->getMessage());
        }
    }

    public function obterVotoUsuario($memeId, $ipAddress = null) {
        if ($ipAddress === null) {
            $ipAddress = $this->obterIpUsuario();
        }

        $voto = $this->votoDAO->buscarVotoPorIpEMeme($memeId, $ipAddress);
        return $voto ? $voto->getTipo() : null;
    }

    public function obterContadoresVoto($memeId) {
        if (empty($memeId) || !is_numeric($memeId)) {
            throw new Exception('ID do meme inválido');
        }

        $contadores = $this->votoDAO->contarVotosPorMeme($memeId);
        $votoUsuario = $this->obterVotoUsuario($memeId);

        return [
            'likes' => $contadores['likes'],
            'dislikes' => $contadores['dislikes'],
            'voto_usuario' => $votoUsuario
        ];
    }

    public function listarVotosPorMeme($memeId) {
        if (empty($memeId) || !is_numeric($memeId)) {
            throw new Exception('ID do meme inválido');
        }

        return $this->votoDAO->listarVotosPorMeme($memeId);
    }

    public function obterEstatisticasGerais() {
        $stats = $this->votoDAO->obterEstatisticasGerais();
        $topMemes = $this->votoDAO->obterTopMemesPorLikes(5);

        return [
            'total_votos' => $stats['total_votos'],
            'total_likes' => $stats['total_likes'],
            'total_dislikes' => $stats['total_dislikes'],
            'memes_votados' => $stats['memes_votados'],
            'top_memes' => $topMemes
        ];
    }

    public function removerTodosVotos($memeId) {
        try {
            if (empty($memeId) || !is_numeric($memeId)) {
                throw new Exception('ID do meme inválido');
            }

            $quantidade = $this->votoDAO->deletarVotosPorMeme($memeId);

            return [
                'sucesso' => true,
                'votos_removidos' => $quantidade
            ];

        } catch (Exception $e) {
            error_log("Erro ao remover votos: " . $e->getMessage());
            throw new Exception("Erro ao remover votos: " . $e->getMessage());
        }
    }

    private function obterIpUsuario() {
        // Tentar obter o IP real mesmo com proxy/load balancer
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',     // Cloudflare
            'HTTP_CLIENT_IP',            // Proxy
            'HTTP_X_FORWARDED_FOR',      // Load balancer/proxy
            'HTTP_X_FORWARDED',          // Proxy
            'HTTP_X_CLUSTER_CLIENT_IP',  // Cluster
            'HTTP_FORWARDED_FOR',        // Proxy
            'HTTP_FORWARDED',            // Proxy
            'REMOTE_ADDR'                // IP padrão
        ];

        foreach ($ipKeys as $key) {
            if (array_key_exists($key, $_SERVER) && !empty($_SERVER[$key])) {
                $ips = explode(',', $_SERVER[$key]);
                $ip = trim($ips[0]);

                // Validar se é um IP válido
                if (filter_var($ip, FILTER_VALIDATE_IP,
                    FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback para IP local em desenvolvimento
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    public function validarLimiteVotos($ipAddress = null) {
        if ($ipAddress === null) {
            $ipAddress = $this->obterIpUsuario();
        }

        // Implementar limite de votos por IP por período (opcional)
        // Por exemplo: máximo 100 votos por hora
        $sql = "
            SELECT COUNT(*) as total_votos
            FROM votos
            WHERE ip_address = ?
            AND criado_em > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ";

        // Esta implementação seria feita no VotoDAO se necessário
        return true; // Por enquanto, sempre permite
    }
}
?>
