<?php

/**
 * API Endpoint: Sblocca Slot (api/unlock.php)
 *
 * Scopo:
 * Libera uno slot precedentemente bloccato ("in_attesa") dall'utente,
 * rendendolo nuovamente disponibile per gli altri.
 * Usato quando l'utente chiude il modale di conferma senza prenotare.
 *
 * @package    App\API
 */

header('Content-Type: application/json');
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/utils.php';

// Controllo Metodo POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => __('err_method')]));
}

// Controllo Login
if (!Utils::is_logged()) {
    exit(json_encode(['success' => false, 'message' => __('err_login_required')]));
}

$lockId = $_POST['lock_id'] ?? 0;

$database = new Database();
$db = $database->getConnection();

// Cancelliamo la prenotazione temporanea.
// CRITICO: Controlliamo che appartenga all'utente (idutente = ?) E che sia ancora in stato 'in_attesa'.
// Non vogliamo cancellare accidentalmente una prenotazione 'confermata'.
$sql = "DELETE FROM prenotazioni WHERE idprenotazione = ? AND idutente = ? AND stato = 'in_attesa'";
$stmt = $db->prepare($sql);
$stmt->execute([$lockId, $_SESSION['user_id']]);

// Restituiamo sempre successo, anche se non abbiamo cancellato nulla (idempotenza)
echo json_encode(['success' => true]);
