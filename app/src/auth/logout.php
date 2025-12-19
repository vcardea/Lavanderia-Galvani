<?php
// src/auth/logout.php

// 1. Svuota l'array della sessione
$_SESSION = [];

// 2. Se si desidera uccidere la sessione completamente, cancelliamo anche il cookie di sessione.
// Questo cancellerà il cookie nel browser.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
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

// 3. Distrugge la sessione sul server
session_destroy();

// 4. Reindirizza alla pagina di login usando la costante BASE_URL definita in index.php
header("Location: " . BASE_URL . "/login");
exit;
