<?php
header('Content-Type: application/json');
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/utils.php';

// 1. Controllo Metodo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => __('err_method')]));
}

// 2. Controllo Login
if (!Utils::is_logged()) {
    exit(json_encode(['success' => false, 'message' => __('err_login_required')]));
}

$lockId = $_POST['lock_id'] ?? 0;

if (!$lockId) {
    exit(json_encode(['success' => false, 'message' => __('err_missing_id')]));
}

$database = new Database();
$db = $database->getConnection();

try {
    $stmtGet = $db->prepare("SELECT data_prenotazione, ora_inizio, stato, idutente 
                             FROM prenotazioni WHERE idprenotazione = :id");
    $stmtGet->execute([':id' => $lockId]);
    $prenotazione = $stmtGet->fetch();

    if (!$prenotazione || $prenotazione['idutente'] != $_SESSION['user_id']) {
        exit(json_encode(['success' => false, 'message' => __('err_booking_expired')]));
    }

    if ($prenotazione['stato'] === 'confermata') {
        exit(json_encode(['success' => true]));
    }

    $dataTarget = new DateTime($prenotazione['data_prenotazione']);
    $oraInizio = new DateTime($prenotazione['data_prenotazione'] . ' ' . $prenotazione['ora_inizio']);
    $adesso = new DateTime();
    $settimanaProssima = (clone $adesso)->modify('+1 week')->format('oW');
    $ora = $oraInizio->format('H');

    // A. Controllo Settimana Corrente
    if (
        $dataTarget->format('oW') !== $adesso->format('oW') &&
        !(
            $dataTarget->format('oW') === $settimanaProssima && // È la settimana prossima
            $dataTarget->format('N') == 1 &&                    // È Lunedì (1)
            (int)$ora === 0                                      // È mezzanotte
        )
    ) {
        exit(json_encode(['success' => false, 'message' => __('err_future_date') . " ({$dataTarget->format('d/m/Y')})"]));
    }

    // B. Controllo Slot nel Passato
    if ($oraInizio < $adesso) {
        exit(json_encode(['success' => false, 'message' => __('err_past_date') . " ({$oraInizio->format('d/m/Y H:i')})"]));
    }

    $sql = "UPDATE prenotazioni SET stato = 'confermata' 
            WHERE idprenotazione = :id AND stato = 'in_attesa'";

    $stmtUpd = $db->prepare($sql);
    $stmtUpd->execute([':id' => $lockId]);

    if ($stmtUpd->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => __('err_tech_confirm')]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => __('err_db_generic') . ': ' . $e->getMessage()]);
}
