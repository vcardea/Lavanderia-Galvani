<?php

/**
 * API Endpoint: Aggiorna Ritardo (api/delay.php)
 *
 * Scopo:
 * Permette agli utenti di segnalare un ritardo (in minuti) accumulato da una macchina,
 * informando così gli utenti successivi.
 *
 * @package    App\API
 */

header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils.php';

// Controlli sicurezza standard
if (!Utils::is_logged()) {
    exit(json_encode(['success' => false, 'message' => __('err_login_required')]));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => __('err_method')]));
}

// Input: ID macchina e Minuti di ritardo (assicurandosi che siano >= 0)
$idmacchina = $_POST['idmacchina'] ?? 0;
$minuti = max(0, (int)($_POST['minuti'] ?? 0));

$database = new Database();
$db = $database->getConnection();

try {
    // Aggiornamento diretto tabella macchine
    $stmt = $db->prepare("UPDATE macchine SET ritardo = ? WHERE idmacchina = ?");
    $stmt->execute([$minuti, $idmacchina]);
    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => __('err_db_generic')]);
}
