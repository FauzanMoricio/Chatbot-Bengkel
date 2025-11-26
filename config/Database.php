<?php
date_default_timezone_set('Asia/Jakarta');

class Database {
    private $host = 'localhost';
    private $db_name = 'chatbot-bengkel'; // Sesuaikan dengan nama database Anda
    private $username = 'root'; // Sesuaikan dengan username database Anda
    private $password = ''; // Sesuaikan dengan password database Anda
    private $conn = null;

    public function connect() {
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4", 
                $this->username, 
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw new Exception("Database connection failed");
        }
        return $this->conn;
    }
}
?>