<?php
require_once 'dao/MemeDAO.php';
require_once 'dao/TagDAO.php';
require_once 'models/Meme.php';

class MemeService {
    private $memeDAO;
    private $tagDAO;

    public function __construct() {
        $this->memeDAO = new MemeDAO();
        $this->tagDAO = new TagDAO();
    }

    public function criarMeme($dados, $arquivo = null) {
        try {
            // Validar dados básicos
            if (empty($dados['titulo']) || empty($dados['autor'])) {
                throw new Exception('Título e autor são obrigatórios');
            }

            // Processar upload da imagem
            $imagemUrl = $this->processarUploadImagem($arquivo);

            // Criar objeto Meme
            $meme = new Meme();
            $meme->setTitulo($dados['titulo']);
            $meme->setImagemUrl($imagemUrl);
            $meme->setLegenda($dados['legenda'] ?? '');
            $meme->setAutor($dados['autor']);

            // Validar meme
            $erros = $meme->validar();
            if (!empty($erros)) {
                throw new Exception(implode(', ', $erros));
            }

            // Iniciar transação
            $this->memeDAO->beginTransaction();

            try {
                // Salvar meme
                $memeId = $this->memeDAO->criar($meme);
                $meme->setId($memeId);

                // Processar tags
                if (!empty($dados['tags'])) {
                    $this->processarTags($memeId, $dados['tags']);
                }

                $this->memeDAO->commit();

                return $this->buscarPorId($memeId);

            } catch (Exception $e) {
                $this->memeDAO->rollback();
                throw $e;
            }

        } catch (Exception $e) {
            error_log("Erro ao criar meme: " . $e->getMessage());
            throw new Exception("Erro ao criar meme: " . $e->getMessage());
        }
    }

    public function buscarPorId($id) {
        if (empty($id) || !is_numeric($id)) {
            throw new Exception('ID inválido');
        }

        $meme = $this->memeDAO->buscarPorId($id);
        if (!$meme) {
            throw new Exception('Meme não encontrado');
        }

        return $meme;
    }

    public function listarMemes($filtros = []) {
        $orderBy = 'criado_em DESC';
        $tagId = null;

        // Processar filtros
        if (!empty($filtros['sort'])) {
            switch ($filtros['sort']) {
                case 'likes':
                    $orderBy = 'likes DESC';
                    break;
                case 'recent':
                    $orderBy = 'criado_em DESC';
                    break;
                case 'oldest':
                    $orderBy = 'criado_em ASC';
                    break;
            }
        }

        if (!empty($filtros['tag_id']) && is_numeric($filtros['tag_id'])) {
            $tagId = $filtros['tag_id'];
        }

        return $this->memeDAO->listarTodos($orderBy, $tagId);
    }

    public function atualizarMeme($id, $dados, $arquivo = null) {
        try {
            $meme = $this->buscarPorId($id);

            // Atualizar dados
            if (isset($dados['titulo'])) {
                $meme->setTitulo($dados['titulo']);
            }
            if (isset($dados['legenda'])) {
                $meme->setLegenda($dados['legenda']);
            }
            if (isset($dados['autor'])) {
                $meme->setAutor($dados['autor']);
            }

            // Processar nova imagem se enviada
            if ($arquivo && $arquivo['error'] === UPLOAD_ERR_OK) {
                $imagemUrl = $this->processarUploadImagem($arquivo);
                $meme->setImagemUrl($imagemUrl);
            }

            // Validar
            $erros = $meme->validar();
            if (!empty($erros)) {
                throw new Exception(implode(', ', $erros));
            }

            // Atualizar no banco
            $sucesso = $this->memeDAO->atualizar($meme);
            if (!$sucesso) {
                throw new Exception('Erro ao atualizar meme');
            }

            // Atualizar tags se fornecidas
            if (isset($dados['tags'])) {
                $this->atualizarTagsMeme($id, $dados['tags']);
            }

            return $this->buscarPorId($id);

        } catch (Exception $e) {
            error_log("Erro ao atualizar meme: " . $e->getMessage());
            throw new Exception("Erro ao atualizar meme: " . $e->getMessage());
        }
    }

    public function deletarMeme($id) {
        try {
            $meme = $this->buscarPorId($id);

            // Deletar arquivo de imagem
            $this->deletarArquivoImagem($meme->getImagemUrl());

            // Deletar do banco (cascade irá remover tags e votos)
            $sucesso = $this->memeDAO->deletar($id);
            if (!$sucesso) {
                throw new Exception('Erro ao deletar meme');
            }

            return true;

        } catch (Exception $e) {
            error_log("Erro ao deletar meme: " . $e->getMessage());
            throw new Exception("Erro ao deletar meme: " . $e->getMessage());
        }
    }

    private function processarUploadImagem($arquivo) {
        if (!$arquivo || $arquivo['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Erro no upload da imagem');
        }

        // Validar tipo de arquivo
        $tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($arquivo['type'], $tiposPermitidos)) {
            throw new Exception('Tipo de arquivo não permitido. Use JPG, PNG ou GIF.');
        }

        // Validar tamanho (5MB máximo)
        $tamanhoMaximo = 5 * 1024 * 1024; // 5MB
        if ($arquivo['size'] > $tamanhoMaximo) {
            throw new Exception('Arquivo muito grande. Máximo 5MB.');
        }

        // Gerar nome único
        $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid('meme_') . '.' . $extensao;

        // Criar diretório se não existir
        $diretorio = 'uploads/memes/';
        if (!file_exists($diretorio)) {
            mkdir($diretorio, 0755, true);
        }

        $caminhoCompleto = $diretorio . $nomeArquivo;

        // Mover arquivo
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoCompleto)) {
            throw new Exception('Erro ao salvar imagem');
        }

        return $caminhoCompleto;
    }

    private function processarTags($memeId, $tagsString) {
        if (empty($tagsString)) {
            return;
        }

        $tagNames = array_map('trim', explode(',', $tagsString));

        foreach ($tagNames as $tagName) {
            if (!empty($tagName)) {
                $tag = $this->tagDAO->buscarOuCriar($tagName);
                $this->memeDAO->adicionarTag($memeId, $tag->getId());
            }
        }
    }

    private function atualizarTagsMeme($memeId, $tagsString) {
        // Remover todas as tags atuais
        $tagsAtuais = $this->memeDAO->buscarTagsPorMeme($memeId);
        foreach ($tagsAtuais as $tag) {
            $this->memeDAO->removerTag($memeId, $tag['id']);
        }

        // Adicionar novas tags
        $this->processarTags($memeId, $tagsString);
    }

    private function deletarArquivoImagem($imagemUrl) {
        if (file_exists($imagemUrl)) {
            unlink($imagemUrl);
        }
    }
}
?>
