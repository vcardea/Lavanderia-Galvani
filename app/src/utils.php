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
    static function old($field)
    {
        return isset($_POST[$field]) ? htmlspecialchars($_POST[$field]) : '';
    }

    /**
     * Preserva la data selezionata nei link.
     */
    static function preserve_date()
    {
        return isset($_GET['date']) ? '&date=' . $_GET['date'] : '';
    }

    /**
     * Esegue un reset giornaliero dei ritardi delle macchine.
     * Usa un file di testo per tracciare l'ultima esecuzione.
     * 
     * @param PDO $db Connessione al database
     */
    public static function checkDailyReset($db)
    {
        // Percorso di un file di testo che useremo come "memoria"
        // Lo salviamo nella cartella src per tenerlo nascosto
        $lockFile = BASE_PATH . '/daily_reset_log.txt';
        $oggi = date('Y-m-d');

        // Se il file non esiste, crealo vuoto
        if (!file_exists($lockFile)) {
            file_put_contents($lockFile, '');
        }

        // Leggiamo l'ultima data registrata
        $ultimaEsecuzione = file_get_contents($lockFile);

        // Se la data nel file è DIVERSA da oggi, dobbiamo resettare!
        if ($ultimaEsecuzione !== $oggi) {
            try {
                // 1. Esegui il reset
                $stmt = $db->prepare("UPDATE macchine SET ritardo = 0");
                $stmt->execute();

                // 2. Aggiorna il file con la data di oggi per non rifarlo fino a domani
                file_put_contents($lockFile, $oggi);

                // Opzionale: Log di debug (puoi rimuoverlo se vuoi)
                // error_log("Reset giornaliero eseguito il: " . $oggi);

            } catch (Exception $e) {
                // Se fallisce, pazienza, riproverà al prossimo caricamento
                error_log("Errore reset giornaliero: " . $e->getMessage());
            }
        }
    }
}
