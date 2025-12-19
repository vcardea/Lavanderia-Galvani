<?php
class Database
{
    private $host = "localhost"; // Cambia se necessario
    private $db_name = "lavanderia"; // Il nome del tuo DB
    private $username = "root"; // Tuo user DB
    private $password = ""; // Tua pass DB
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4"); // Importante per accenti e emoji
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Attiva errori
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            echo "Errore di connessione: " . $exception->getMessage();
            exit;
        }
        return $this->conn;
    }
}
