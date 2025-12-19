<?php
class Database
{
    private $host = "localhost";
    private $db_name = "lavanderia";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO("mysql:host=" . $this->host . ";dbname=" . $this->db_name, $this->username, $this->password);
            $this->conn->exec("set names utf8mb4");
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            // Usa la chiave di traduzione se disponibile, altrimenti fallback
            $msg = function_exists('__') ? __('err_db_conn') : "Connection Error: ";
            echo $msg . $exception->getMessage();
            exit;
        }
        return $this->conn;
    }
}
