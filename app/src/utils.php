<?php

/**
 * Classe di utilità che contiene metodi generici per operazioni comuni.
 */
class Utils
{

    /**
     * Controlla se l'utente è loggato.
     *
     * @return bool True se l'utente è loggato, false altrimenti.
     */
    static function is_logged()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Controlla se l'utente loggato è un amministratore.
     *
     * @return bool True se l'utente è un amministratore, false altrimenti.
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
            header("Location: " . BASE_URL . "/pages/dashboard.php");
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
     * Reindirizza al login se l'utente non è un amministratore.
     */
    static function admin_only()
    {
        if (!self::is_admin()) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
    }

    /**
     * Recupera il valore precedente di un campo del modulo in caso di errore di validazione.
     *
     * @param string $field Il nome del campo del modulo.
     * @return string Il valore precedente del campo, o una stringa vuota se non esiste.
     */
    function old($field)
    {
        return isset($_POST[$field]) ? htmlspecialchars($_POST[$field]) : '';
    }
}
