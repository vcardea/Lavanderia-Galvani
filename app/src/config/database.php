<?php

/**
 * Classe Database (config/database.php)
 *
 * Scopo:
 * Gestisce la connessione al database MySQL tramite PDO.
 * Include una logica custom per caricare le variabili d'ambiente da un file .env,
 * supportando ambienti dove le librerie di composer (come phpdotenv) non sono disponibili.
 *
 * @package    App\Config
 */

class Database
{
    private $host;
    private $db_name;
    private $username;
    private $password;
    public $conn;

    /**
     * Costruttore: Inizializza le credenziali.
     * Tenta di leggere da $_ENV (popolato dal metodo loadEnv o dal server).
     * Fallback sui valori di default per sviluppo locale.
     */
    function __construct()
    {
        // 1. Caricamento variabili d'ambiente dal file .env (se esiste)
        $this->loadEnv();

        // 2. Assegnazione credenziali con fallback
        $this->host = $_ENV['DB_HOST'] ?? 'localhost';
        $this->db_name = $_ENV['DB_NAME'] ?? 'lavanderia';
        $this->username = $_ENV['DB_USER'] ?? 'root';
        $this->password = $_ENV['DB_PASS'] ?? '';
    }

    /**
     * Metodo Helper: Carica manualmente il file .env
     * Legge il file riga per riga e popola $_ENV.
     * Utile su hosting condivisi o XAMPP senza Composer.
     */
    private function loadEnv()
    {
        if (isset($_ENV['DB_HOST'])) return; // Già caricate

        // Risale di due livelli da src/config/ per trovare la root
        $path = __DIR__ . '/../../.env';

        if (file_exists($path)) {
            $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                // Ignora commenti (#)
                if (strpos(trim($line), '#') === 0) continue;

                if (strpos($line, '=') !== false) {
                    list($name, $value) = explode('=', $line, 2);
                    $name = trim($name);
                    $value = trim(trim($value), '"\''); // Rimuove spazi e quote

                    if (!array_key_exists($name, $_ENV)) {
                        $_ENV[$name] = $value;
                    }
                }
            }
        }
    }

    /**
     * Stabilisce la connessione PDO.
     * @return PDO|null Oggetto connessione o null in caso di errore.
     */
    public function getConnection()
    {
        $this->conn = null;
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name,
                $this->username,
                $this->password
            );
            // Forza charset UTF-8 per evitare problemi con caratteri speciali
            $this->conn->exec("set names utf8mb4");
            // Attiva eccezioni per errori SQL (fondamentale per try-catch)
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // Imposta fetch mode di default ad array associativo
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $exception) {
            // Gestione errore connessione con supporto multilingua (se disponibile)
            $msg = function_exists('__') ? __('err_db_conn') : "Connection Error: ";
            // Nota: In produzione, evitare di stampare $exception->getMessage() direttamente all'utente
            echo $msg . $exception->getMessage();
            exit;
        }
        return $this->conn;
    }
}
