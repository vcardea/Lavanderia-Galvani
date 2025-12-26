<?php
// cron/reset_ritardi.php

// 1. CONFIGURAZIONE
require_once SRC_PATH . '/config/database.php';

// Chiave segreta (cambiala con una password difficile!)
$secret_token = '{REDACTED}';

// 2. SICUREZZA: Controllo Token
// Se l'URL non contiene ?token=... o è sbagliato, blocca tutto.
if (!isset($_GET['token']) || $_GET['token'] !== $secret_token) {
    // Rispondi con un errore 403 (Forbidden) fake per non destare sospetti
    header('HTTP/1.0 403 Forbidden');
    die("Access Denied.");
}

// 3. ESECUZIONE
try {
    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("UPDATE macchine SET ritardo = 0");
    $stmt->execute();

    // Output minimale per il log del cron esterno
    echo "OK: Ritardi resettati.";
} catch (Exception $e) {
    // In caso di errore, stampa qualcosa che puoi leggere nei log del servizio esterno
    echo "ERROR: " . $e->getMessage();
}
