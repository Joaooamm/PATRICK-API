<?php
require_once 'config/database.php';

abstract class BaseDAO {
    protected $db;
    protected $connection;

    public function __construct() {
        $this->db = new Database();
        $this->connection = $this->db->getConnection();
    }

    protected function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Erro na execução SQL: " . $e->getMessage());
            throw new Exception("Erro na operação do banco de dados");
        }
    }

    protected function fetch($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetch();
    }

    protected function fetchAll($sql, $params = []) {
        $stmt = $this->execute($sql, $params);
        return $stmt->fetchAll();
    }

    protected function lastInsertId() {
        return $this->connection->lastInsertId();
    }

    public function beginTransaction() {
        return $this->db->beginTransaction();
    }

    public function commit() {
        return $this->db->commit();
    }

    public function rollback() {
        return $this->db->rollback();
    }
}
?>
