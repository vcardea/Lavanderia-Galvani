<?php

/**
 * Classe di Utilità (src/utils.php)
 *
 * Scopo:
 * Contiene metodi statici per operazioni comuni (autenticazione, redirect, reset ritardi).
 * Evita la duplicazione del codice nelle varie pagine.
 *
 * @package    App\Core
 */

class Utils
{

    /**
     * Controlla se l'utente è loggato.
     * @return bool True se la sessione contiene user_id.
     */
    static function is_logged()
    {
        return isset($_SESSION['user_id']);
    }

    /**
     * Controlla se l'utente loggato è un amministratore.
     * @return bool
     */
    static function is_admin()
    {
        return self::is_logged() && ($_SESSION['ruolo'] ?? '') === 'admin';
    }

    /**
     * Reindirizza l'utente alla dashboard se è già loggato.
     * Utile nelle pagine di Login/Register per evitare doppi accessi.
     */
    static function is_already_logged()
    {
        if (self::is_logged()) {
            // Nota: Rimuovi riferimenti diretti a .php o cartelle fisiche se usi il router
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
    }

    /**
     * Reindirizza al login se l'utente non è loggato.
     * Protegge le pagine riservate.
     */
    static function not_logged_yet()
    {
        if (!self::is_logged()) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
    }

    /**
     * Middleware per l'accesso esclusivo agli Admin.
     * 1. Se non loggato -> Login.
     * 2. Se loggato ma user -> Dashboard.
     */
    static function admin_only()
    {
        // 1. Se non è loggato, va al login
        if (!self::is_logged()) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }

        // 2. Se è loggato ma non è admin, va alla dashboard
        if (!self::is_admin()) {
            header("Location: " . BASE_URL . "/dashboard");
            exit;
        }
    }

    /**
     * Recupera il valore precedente di un campo del modulo (per ripopolare form in errore).
     * @param string $field Nome del campo input.
     * @return string Valore sanificato.
     */
    static function old($field)
    {
        return isset($_POST[$field]) ? htmlspecialchars($_POST[$field]) : '';
    }

    /**
     * Costruisce la query string per preservare la data selezionata nei link.
     * Utile per cambiare lingua o pagina senza perdere il giorno corrente.
     * @return string Esempio: "&date=2025-01-01" o stringa vuota.
     */
    static function preserve_date()
    {
        return isset($_GET['date']) ? '&date=' . $_GET['date'] : '';
    }

    /**
     * Esegue un reset giornaliero dei ritardi delle macchine.
     *
     * Funzionamento:
     * Usa un file di testo (daily_reset_log.txt) come "semaforo persistente".
     * Se la data nel file è diversa da oggi, esegue l'UPDATE sul DB e aggiorna il file.
     * Questo evita l'uso di Cron Jobs esterni, spesso inaffidabili su hosting gratuiti.
     * * @param PDO $db Connessione al database.
     */
    public static function checkDailyReset($db)
    {
        // Percorso del file di log (nascosto nella cartella src o root privata)
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
                // 1. Esegui il reset su DB
                $stmt = $db->prepare("UPDATE macchine SET ritardo = 0");
                $stmt->execute();

                // 2. Aggiorna il file con la data di oggi
                file_put_contents($lockFile, $oggi);

                // Opzionale: Log di debug
                // error_log("Reset giornaliero eseguito il: " . $oggi);

            } catch (Exception $e) {
                // Se fallisce, logghiamo l'errore ma non blocchiamo l'esecuzione della pagina
                error_log("Errore reset giornaliero: " . $e->getMessage());
            }
        }
    }
}
