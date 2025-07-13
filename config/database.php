<?php
// config/database.php

class Database {
    private string $host = 'localhost';
    private string $username = 'root';
    private string $password = '';
    private string $database = 'kampus_system';
    private ?mysqli $connection = null;
    
    public function __construct() {
        try {
            $this->connection = new mysqli($this->host, $this->username, $this->password, $this->database);
            
            if ($this->connection->connect_error) {
                throw new Exception("Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset
            $this->connection->set_charset("utf8");
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getConnection(): mysqli {
        if ($this->connection === null) {
            throw new Exception("Database connection not established");
        }
        return $this->connection;
    }
    
    public function close(): void {
        if ($this->connection && !$this->connection->connect_error) {
            $this->connection->close();
            $this->connection = null;
        }
    }
    
    public function __destruct() {
        $this->close();
    }
}
?>