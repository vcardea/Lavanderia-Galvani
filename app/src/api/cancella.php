<?php
header('Content-Type: application/json');
require_once SRC_PATH . '/utils.php';
require_once SRC_PATH . '/config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Utils::is_logged()) {
    exit(json_encode(['success' => false, 'message' => __('err_unauthorized')]));
}

$idprenotazione = $_POST['idprenotazione'] ?? 0;

$database = new Database();
$db = $database->getConnection();

try {
    $sql = "DELETE FROM prenotazioni WHERE idprenotazione = ? AND idutente = ?";
    $stmt = $db->prepare($sql);
    $stmt->execute([$idprenotazione, $_SESSION['user_id']]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => __('err_booking_not_found')]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => __('err_db_generic')]);
}
