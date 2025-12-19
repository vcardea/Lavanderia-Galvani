<?php
class Lang
{
    private static $translations = [];
    private static $current = 'it';

    // Inizializza la lingua (legge dalla sessione o GET)
    public static function init()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        // 1. Cambio lingua via URL (?lang=en)
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['it', 'en'])) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        // 2. Imposta lingua corrente
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

    // Funzione per tradurre lato PHP
    public static function get($key)
    {
        return self::$translations[$key] ?? $key; // Restituisce la chiave se manca la traduzione
    }

    // Restituisce la lingua corrente (es. 'it')
    public static function current()
    {
        return self::$current;
    }

    // Restituisce tutto l'array (per passarlo a JS)
    public static function getAll()
    {
        return self::$translations;
    }
}

// Funzione helper globale (per scrivere meno codice nelle viste)
function __($key)
{
    return Lang::get($key);
}
