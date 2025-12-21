<?php
header('Content-Type: application/json');
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/utils.php';

// 1. Controllo Metodo e Login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => __('err_method')]));
}

if (!Utils::is_logged()) {
    exit(json_encode(['success' => false, 'message' => __('err_login_required')]));
}

$idmacchina = $_POST['idmacchina'] ?? 0;
$data = $_POST['data'] ?? '';
$ora = $_POST['ora'] ?? '';

// 2. VALIDAZIONE TEMPORALE
try {
    $oggi = new DateTime();
    $dataTarget = new DateTime($data);
    $inizioSlot = new DateTime("$data $ora:00:00");
    $settimanaProssima = (clone $oggi)->modify('+1 week')->format('oW');

    if (
        $dataTarget->format('oW') !== $oggi->format('oW') &&
        !(
            $dataTarget->format('oW') === $settimanaProssima && // È la settimana prossima
            $dataTarget->format('N') == 1 &&                    // È Lunedì (1)
            (int)$ora == 0                                      // È mezzanotte
        )
    ) {
        exit(json_encode(['success' => false, 'message' => __('err_current_week_only')]));
    }

    if ($inizioSlot < $oggi) {
        exit(json_encode(['success' => false, 'message' => __('err_past_date')]));
    }
} catch (Exception $e) {
    exit(json_encode(['success' => false, 'message' => __('err_invalid_date')]));
}

// 3. RECUPERO CONFIGURAZIONE
$database = new Database();
$db = $database->getConnection();

$stmtStatus = $db->prepare("SELECT stato FROM macchine WHERE idmacchina = ?");
$stmtStatus->execute([$idmacchina]);
$statoMacchina = $stmtStatus->fetchColumn();

if ($statoMacchina === 'manutenzione') {
    exit(json_encode(['success' => false, 'message' => __('err_machine_maintenance')]));
}

$stmtConfig = $db->prepare("SELECT valore FROM configurazioni WHERE chiave = 'max_hours_weekly'");
$stmtConfig->execute();
$dbVal = $stmtConfig->fetchColumn();
$MAX_ORE_SETTIMANALI = ($dbVal !== false) ? (int)$dbVal : 3;

// 4. CONTROLLO LIMITE UTENTE
$sqlCount = "SELECT COUNT(*) FROM prenotazioni 
             WHERE idutente = :u 
             AND YEARWEEK(data_prenotazione, 1) = YEARWEEK(:d, 1)
             AND (stato = 'confermata' OR stato = 'in_attesa')";

$stmtCount = $db->prepare($sqlCount);
$stmtCount->execute([':u' => $_SESSION['user_id'], ':d' => $data]);
$prenotazioniGiaFatte = $stmtCount->fetchColumn();

if ($prenotazioniGiaFatte >= $MAX_ORE_SETTIMANALI) {
    exit(json_encode([
        'success' => false,
        'message' => sprintf(__('err_limit_reached'), $MAX_ORE_SETTIMANALI)
    ]));
}

// 5. TRANSAZIONE DI LOCK
$ora_inizio = sprintf("%02d:00:00", $ora);
$ora_fine = sprintf("%02d:00:00", ($ora == 23 ? 0 : $ora + 1));

try {
    $db->beginTransaction();

    $sqlCheck = "SELECT idprenotazione FROM prenotazioni 
                 WHERE idmacchina = :m AND data_prenotazione = :d AND ora_inizio = :o
                 AND (
                    stato = 'confermata' 
                    OR (stato = 'in_attesa' AND created_at > NOW() - INTERVAL 2 MINUTE)
                 )
                 FOR UPDATE";

    $stmt = $db->prepare($sqlCheck);
    $stmt->execute([':m' => $idmacchina, ':d' => $data, ':o' => $ora_inizio]);

    if ($stmt->rowCount() > 0) {
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => __('err_slot_occupied')]);
        exit;
    }

    $sqlIns = "INSERT INTO prenotazioni (idutente, idmacchina, data_prenotazione, ora_inizio, ora_fine, stato)
               VALUES (:u, :m, :d, :os, :oe, 'in_attesa')";
    $stmtIns = $db->prepare($sqlIns);
    $stmtIns->execute([
        ':u' => $_SESSION['user_id'],
        ':m' => $idmacchina,
        ':d' => $data,
        ':os' => $ora_inizio,
        ':oe' => $ora_fine
    ]);

    $tempId = $db->lastInsertId();
    $db->commit();

    echo json_encode(['success' => true, 'lock_id' => $tempId]);
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => __('err_tech_lock')]);
}
