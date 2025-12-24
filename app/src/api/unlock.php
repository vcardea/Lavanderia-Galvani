<?php
header('Content-Type: application/json');
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/utils.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => __('err_method')]));
}

if (!Utils::is_logged()) {
    exit(json_encode(['success' => false, 'message' => __('err_login_required')]));
}

$lockId = $_POST['lock_id'] ?? 0;

$database = new Database();
$db = $database->getConnection();

// Cancelliamo solo se è mio e se è ancora in attesa
$sql = "DELETE FROM prenotazioni WHERE idprenotazione = ? AND idutente = ? AND stato = 'in_attesa'";
$stmt = $db->prepare($sql);
$stmt->execute([$lockId, $_SESSION['user_id']]);

echo json_encode(['success' => true]);
