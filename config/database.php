<?php
class Database {
    private $host = 'localhost';
    private $database = 'curadoria_memes';
    private $username = 'root';
    private $password = '';
    private $connection;

    public function __construct() {
        $this->connect();
    }

    private function connect() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            die("Erro na conexão com o banco de dados: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->connection;
    }

    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    public function commit() {
        return $this->connection->commit();
    }

    public function rollback() {
        return $this->connection->rollBack();
    }
}

// Função para criar as tabelas se não existirem
function createTables() {
    $db = new Database();
    $pdo = $db->getConnection();

    try {
        // Criar tabela memes
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS memes (
                id INT AUTO_INCREMENT PRIMARY KEY,
                titulo VARCHAR(100) NOT NULL,
                imagem_url TEXT NOT NULL,
                legenda TEXT,
                autor VARCHAR(100) NOT NULL,
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Criar tabela tags
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS tags (
                id INT AUTO_INCREMENT PRIMARY KEY,
                nome VARCHAR(50) UNIQUE NOT NULL
            )
        ");

        // Criar tabela meme_tag
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS meme_tag (
                meme_id INT,
                tag_id INT,
                PRIMARY KEY (meme_id, tag_id),
                FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
                FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
            )
        ");

        // Criar tabela votos
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS votos (
                id INT AUTO_INCREMENT PRIMARY KEY,
                meme_id INT,
                tipo ENUM('like', 'dislike') NOT NULL,
                ip_address VARCHAR(45),
                criado_em DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (meme_id) REFERENCES memes(id) ON DELETE CASCADE,
                UNIQUE KEY unique_vote (meme_id, ip_address)
            )
        ");

        return true;
    } catch (PDOException $e) {
        error_log("Erro ao criar tabelas: " . $e->getMessage());
        return false;
    }
}

// Criar tabelas automaticamente
createTables();
?>
