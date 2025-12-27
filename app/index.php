<?php

/**
 * FRONT CONTROLLER (index.php)
 *
 * Questo è il punto di ingresso unico dell'applicazione.
 *
 * Funzionalità Principali:
 * 1. Inizializzazione Ambiente (Costanti, Timezone, Sessioni).
 * 2. Gestione Lingua (i18n).
 * 3. Routing: Smista le richieste URL verso il file corretto (Pagina o API).
 *
 * Vantaggi:
 * - Centralizza la logica di avvio.
 * - Gestisce i permessi (es. redirect al login) in un unico punto tramite router.
 * - Permette URL puliti (es. /dashboard invece di /src/pages/dashboard.php).
 *
 * @package    App\Core
 * @author     DevTeam
 * @version    1.0
 */

// --------------------------------------------------------------------------
// 1. CONFIGURAZIONE AMBIENTE
// --------------------------------------------------------------------------

// Imposta il fuso orario su Roma per garantire coerenza negli orari delle prenotazioni
date_default_timezone_set('Europe/Rome');

// Definisce i percorsi assoluti per includere i file in modo sicuro
// SRC_PATH: Cartella sorgente (backend, logica)
define('SRC_PATH', __DIR__ . '/src');
// BASE_PATH: Cartella radice del progetto
define('BASE_PATH', __DIR__);

// Inclusione libreria di utilità (funzioni comuni come is_logged())
require_once SRC_PATH . '/utils.php';

// --------------------------------------------------------------------------
// 2. AVVIO SESSIONE E LINGUA
// --------------------------------------------------------------------------

// Avvia o riprende la sessione PHP (necessario per login, messaggi flash, lingua)
session_start();

// Carica la classe per la gestione delle traduzioni e inizializza la lingua corrente
require_once SRC_PATH . '/Lang.php';
Lang::init();

// --------------------------------------------------------------------------
// 3. CALCOLO BASE URL (Routing Dinamico)
// --------------------------------------------------------------------------
// Questo blocco permette all'app di funzionare sia nella root (es. sito.com)
// sia in una sottocartella (es. localhost/lavanderia) senza modificare codice.

$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
// Normalizza i separatori di directory (Windows usa \, Linux /)
$baseUrl = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;

// Definisce la costante globale BASE_URL usata nei link HTML
define('BASE_URL', $baseUrl);

// --------------------------------------------------------------------------
// 4. GESTIONE DELLA RICHIESTA (ROUTING)
// --------------------------------------------------------------------------

// Ottiene il percorso richiesto dall'URL (es. /lavanderia/dashboard)
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Rimuove la parte della sottocartella (BASE_URL) per ottenere la "rotta pura"
// Es. Se siamo in /lavanderia/dashboard, $path diventa solo 'dashboard'
if ($baseUrl && strpos($request, $baseUrl) === 0) {
    $request = substr($request, strlen($baseUrl));
}

// Rimuove slash iniziali/finali per pulizia (es. '/dashboard/' -> 'dashboard')
$path = trim($request, '/');

// --------------------------------------------------------------------------
// 5. SMISTAMENTO ROTTE (Switch)
// --------------------------------------------------------------------------

switch ($path) {
    // --- HOME PAGE / DASHBOARD ---
    case '':
    case '/':
    case 'home':
    case 'lavanderia-galvani': // Gestione legacy per vecchi link o sottocartelle specifiche
        // Protezione: Solo utenti loggati
        Utils::not_logged_yet();
        require SRC_PATH . '/pages/dashboard.php';
        break;

    // --- AUTENTICAZIONE ---
    case 'login':
        // Se già loggato, manda alla dashboard
        Utils::is_already_logged();
        require SRC_PATH . '/auth/login.php';
        break;

    case 'register':
        Utils::is_already_logged();
        require SRC_PATH . '/auth/register.php';
        break;

    case 'logout':
        // Solo chi è loggato può fare logout
        Utils::not_logged_yet();
        require SRC_PATH . '/auth/logout.php';
        break;

    // --- PAGINE UTENTE ---
    case 'dashboard':
        Utils::not_logged_yet();
        require SRC_PATH . '/pages/dashboard.php';
        break;

    case 'admin':
        // Middleware: Solo amministratori
        Utils::admin_only();
        require SRC_PATH . '/pages/admin.php';
        break;

    case 'privacy':
        // Pagina pubblica
        require SRC_PATH . '/pages/privacy.php';
        break;

    // --- API ENDPOINTS (Chiamate AJAX dal frontend) ---

    case 'api/read':
        // Legge lo stato della griglia (JSON)
        require SRC_PATH . '/api/read.php';
        break;

    case 'api/prenota':
        // Conferma una prenotazione (JSON)
        require SRC_PATH . '/api/prenota.php';
        break;

    case 'api/cancella':
        // Cancella una prenotazione (JSON)
        require SRC_PATH . '/api/cancella.php';
        break;

    case 'api/lock':
        // Blocca temporaneamente uno slot (JSON)
        require SRC_PATH . '/api/lock.php';
        break;

    case 'api/unlock':
        // Sblocca uno slot (JSON)
        require SRC_PATH . '/api/unlock.php';
        break;

    case 'api/delay':
        // Segnala ritardo macchina (JSON)
        require SRC_PATH . '/api/delay.php';
        break;

    // --- PAGINE DI ERRORE ---
    case 'error':
        // Visualizza la pagina di errore generica
        require SRC_PATH . '/pages/error.php';
        break;

    // --- DEFAULT (404 NOT FOUND) ---
    default:
        // Se nessuna rotta corrisponde, forza il codice 404
        // e carica la vista di errore.
        $_GET['code'] = 404;
        require SRC_PATH . '/pages/error.php';
        break;
}
