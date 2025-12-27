<?php

/**
 * Pagina di Logout (auth/logout.php)
 *
 * Scopo:
 * Termina la sessione corrente dell'utente in modo sicuro e pulito.
 *
 * Flusso Logico:
 * 1. Svuota l'array $_SESSION.
 * 2. Invalida e cancella il cookie di sessione nel browser.
 * 3. Distrugge la sessione lato server.
 * 4. Reindirizza l'utente alla pagina di login.
 *
 * @package    App\Auth
 */

// src/auth/logout.php

// --------------------------------------------------------------------------
// 1. PULIZIA VARIABILI DI SESSIONE
// --------------------------------------------------------------------------

// Svuota l'array della sessione in memoria
$_SESSION = [];

// --------------------------------------------------------------------------
// 2. CANCELLAZIONE COOKIE DI SESSIONE
// --------------------------------------------------------------------------

// Se si desidera uccidere la sessione completamente, cancelliamo anche il cookie di sessione.
// Questo rimuoverà fisicamente il riferimento alla sessione nel browser dell'utente.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    // Imposta il cookie di sessione con una data di scadenza nel passato (time() - 42000)
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// --------------------------------------------------------------------------
// 3. DISTRUZIONE SESSIONE SERVER
// --------------------------------------------------------------------------

// Distrugge definitivamente i dati di sessione salvati sul server
session_destroy();

// --------------------------------------------------------------------------
// 4. REINDIRIZZAMENTO
// --------------------------------------------------------------------------

// Reindirizza l'utente sloggato alla pagina di login usando la costante BASE_URL definita nel router (index.php)
header("Location: " . BASE_URL . "/login");
exit;
