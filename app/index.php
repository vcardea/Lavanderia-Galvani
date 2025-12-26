<?php

// 1. IMPOSTA FUSO ORARIO ITALIA
date_default_timezone_set('Europe/Rome');
define('SRC_PATH', __DIR__ . '/src');
define('BASE_PATH', __DIR__);

// Utilità
require_once SRC_PATH . '/utils.php';

// 2. Sessione
session_start();

// 3. Carica e Inizializza Lingua
require_once SRC_PATH . '/Lang.php';
Lang::init();

// 4. Calcolo automatico della Base URL
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
$baseUrl = ($scriptDir === '/' || $scriptDir === '\\') ? '' : $scriptDir;
define('BASE_URL', $baseUrl);

// 5. Pulizia della richiesta per il Routing
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Rimuove la BASE_URL dalla richiesta per capire la "rotta reale"
if ($baseUrl && strpos($request, $baseUrl) === 0) {
    $request = substr($request, strlen($baseUrl));
}

$path = trim($request, '/');

// --- ROUTING ---
switch ($path) {
    case '':
    case '/':
    case 'home':
    case 'lavanderia-galvani': // Gestione sottocartella se serve
        Utils::not_logged_yet();
        require SRC_PATH . '/pages/dashboard.php';
        break;

    case 'login':
        Utils::is_already_logged();
        require SRC_PATH . '/auth/login.php';
        break;

    case 'register':
        Utils::is_already_logged();
        require SRC_PATH . '/auth/register.php';
        break;

    case 'logout':
        Utils::not_logged_yet();
        require SRC_PATH . '/auth/logout.php';
        break;

    case 'dashboard':
        Utils::not_logged_yet();
        require SRC_PATH . '/pages/dashboard.php';
        break;

    case 'admin':
        Utils::admin_only();
        require SRC_PATH . '/pages/admin.php';
        break;

    case 'privacy':
        require SRC_PATH . '/pages/privacy.php';
        break;

    // --- API ROUTES ---
    case 'api/read':
        require SRC_PATH . '/api/read.php';
        break;

    case 'api/prenota':
        require SRC_PATH . '/api/prenota.php';
        break;

    case 'api/cancella':
        require SRC_PATH . '/api/cancella.php';
        break;

    case 'api/lock':
        require SRC_PATH . '/api/lock.php';
        break;

    case 'api/unlock':
        require SRC_PATH . '/api/unlock.php';
        break;

    case 'api/delay':
        require SRC_PATH . '/api/delay.php';
        break;

    // CRON JOBS
    case 'cron/reset':
        require SRC_PATH . '/cron/reset.php';
        break;

    default:
        http_response_code(404);
        echo htmlspecialchars("404 - Pagina non trovata (Path richiesto: $path)");
        break;
}
