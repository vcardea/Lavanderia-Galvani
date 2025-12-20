<?php

/**
 * Classe di utilità che contiene metodi generici per operazioni comuni.
 */
class Utils
{

    /**
     * Controlla se l'utente è loggato.
     */
    static function is_logged()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Controlla se l'utente loggato è un amministratore.
     */
    static function is_admin()
    {
        return self::is_logged() && ($_SESSION['ruolo'] ?? '') === 'admin';
    }

    /**
     * Reindirizza l'utente alla dashboard se è già loggato.
     */
    static function is_already_logged()
    {
        if (self::is_logged()) {
            // ERRORE ERA QUI: Rimuovi "/pages" e ".php"
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
    }

    /**
     * Reindirizza al login se l'utente non è loggato.
     */
    static function not_logged_yet()
    {
        if (!self::is_logged()) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
    }

    /**
     * Gestione intelligente accesso Admin
     */
    static function admin_only()
    {
        // 1. Se non è loggato, va al login
        if (!self::is_logged()) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        // 2. Se è loggato ma non è admin, va alla dashboard (invece che al login)
        if (!self::is_admin()) {
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
    }

    /**
     * Recupera il valore precedente di un campo del modulo...
     */
    function old($field)
    {
        return isset($_POST[$field]) ? htmlspecialchars($_POST[$field]) : '';
    }
}
