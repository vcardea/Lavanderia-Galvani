<?php

/**
 * Classe Gestione Lingua (src/Lang.php)
 *
 * Scopo:
 * Gestisce l'internazionalizzazione (i18n) dell'applicazione.
 * - Rileva la lingua preferita dalla sessione o URL.
 * - Carica il file di dizionario appropriato (it.php o en.php).
 * - Fornisce il metodo statico get() e l'helper globale __() per tradurre le stringhe.
 *
 * @package    App\Core
 */

class Lang
{
    private static $translations = [];
    private static $current = 'it'; // Default italiano

    /**
     * Inizializza il sistema di lingua.
     * Deve essere chiamato all'inizio di ogni richiesta (es. in index.php).
     */
    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Cambio lingua via URL (?lang=en)
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['it', 'en'])) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        // 2. Imposta lingua corrente dalla sessione
        self::$current = $_SESSION['lang'] ?? 'it';

        // 3. Carica il file dizionario
        $file = SRC_PATH . '/lang/' . self::$current . '.php';
        if (file_exists($file)) {
            self::$translations = require $file;
        } else {
            // Fallback su italiano se il file non esiste
            self::$translations = require SRC_PATH . '/lang/it.php';
        }
    }

    /**
     * Recupera una stringa tradotta.
     * @param string $key Chiave della traduzione.
     * @return string Testo tradotto o la chiave stessa se non trovata.
     */
    public static function get($key)
    {
        return self::$translations[$key] ?? $key;
    }

    /**
     * Restituisce il codice della lingua corrente (es. 'it', 'en').
     */
    public static function current()
    {
        return self::$current;
    }

    /**
     * Restituisce l'intero array delle traduzioni.
     * Utile per passare le stringhe al frontend JavaScript.
     */
    public static function getAll()
    {
        return self::$translations;
    }
}

// Funzione helper globale per abbreviare Lang::get()
// Esempio d'uso: <?= __('welcome_msg') >
function __($key)
{
    return Lang::get($key);
}
