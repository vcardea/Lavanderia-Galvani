<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../utils.php';

// 1. Verifica Login
if (!Utils::is_logged()) {
    exit(json_encode(['success' => false, 'message' => __('err_login_required')]));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => __('err_method')]));
}

$idmacchina = $_POST['idmacchina'] ?? 0;
// Accettiamo anche 0 per resettare il ritardo, ma non negativi
$minuti = max(0, (int)($_POST['minuti'] ?? 0));

$database = new Database();
$db = $database->getConnection();

try {
    // Update Atomico: sovrascrive il valore.
    // In uno scenario più complesso potremmo usare versioning, ma qui "Last Write Wins" è accettabile 
    // perché il polling aggiorna la vista molto spesso.
    $stmt = $db->prepare("UPDATE macchine SET ritardo = ? WHERE idmacchina = ?");
    $stmt->execute([$minuti, $idmacchina]);

    echo json_encode(['success' => true, 'message' => __('delay_saved')]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => __('err_db_generic')]);
}
