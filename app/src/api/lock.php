<?php

/**
 * API Endpoint: Lock Slot (api/lock.php)
 *
 * Scopo:
 * Tenta di ottenere un "Lock" esclusivo su uno slot temporale.
 * Crea una prenotazione in stato 'in_attesa' che dura pochi minuti.
 *
 * Flusso:
 * 1. Validazioni (Login, Date passate/future).
 * 2. Controllo disponibilità macchina e limiti utente.
 * 3. Transazione Atomica con 'FOR UPDATE' per evitare Race Conditions.
 *
 * @package    App\API
 */

header('Content-Type: application/json');
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/utils.php';

// --------------------------------------------------------------------------
// 1. CONTROLLI PRELIMINARI
// --------------------------------------------------------------------------

// Verifica Metodo e Autenticazione
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => __('err_method')]));
}

if (!Utils::is_logged()) {
    exit(json_encode(['success' => false, 'message' => __('err_login_required')]));
}

$idmacchina = $_POST['idmacchina'] ?? 0;
$data = $_POST['data'] ?? '';
$ora = $_POST['ora'] ?? '';

// --------------------------------------------------------------------------
// 2. VALIDAZIONE TEMPORALE (Business Logic)
// --------------------------------------------------------------------------
try {
    $oggi = new DateTime();
    $dataTarget = new DateTime($data);
    $inizioSlot = new DateTime("$data $ora:00:00");
    $settimanaProssima = (clone $oggi)->modify('+1 week')->format('oW');

    // Regola: Prenotabile solo settimana corrente.
    // Eccezione: Lunedì a mezzanotte si apre la settimana successiva.
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

    // Regola: Non prenotare nel passato
    if ($inizioSlot < $oggi) {
        exit(json_encode(['success' => false, 'message' => __('err_past_date')]));
    }
} catch (Exception $e) {
    exit(json_encode(['success' => false, 'message' => __('err_invalid_date')]));
}

// --------------------------------------------------------------------------
// 3. RECUPERO CONFIGURAZIONE E STATO MACCHINA
// --------------------------------------------------------------------------
$database = new Database();
$db = $database->getConnection();

// Controlla se la macchina è in manutenzione
$stmtStatus = $db->prepare("SELECT stato FROM macchine WHERE idmacchina = ?");
$stmtStatus->execute([$idmacchina]);
$statoMacchina = $stmtStatus->fetchColumn();

if ($statoMacchina === 'manutenzione') {
    exit(json_encode(['success' => false, 'message' => __('err_machine_maintenance')]));
}

// Recupera limite ore settimanali dalla configurazione (default: 3)
$stmtConfig = $db->prepare("SELECT valore FROM configurazioni WHERE chiave = 'max_hours_weekly'");
$stmtConfig->execute();
$dbVal = $stmtConfig->fetchColumn();
$MAX_ORE_SETTIMANALI = ($dbVal !== false) ? (int)$dbVal : 3;

// --------------------------------------------------------------------------
// 4. CONTROLLO LIMITE UTENTE
// --------------------------------------------------------------------------
// Conta quante prenotazioni attive ha l'utente nella settimana target
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

// --------------------------------------------------------------------------
// 5. TRANSAZIONE DI LOCK (Concurrency Control)
// --------------------------------------------------------------------------
$ora_inizio = sprintf("%02d:00:00", $ora);
$ora_fine = sprintf("%02d:00:00", ($ora == 23 ? 0 : $ora + 1));

try {
    // Inizio transazione atomica
    $db->beginTransaction();

    // Query di controllo con LOCK PESSIMISTICO (FOR UPDATE).
    // Blocca le righe lette finché la transazione non finisce.
    // Verifica se esiste già una prenotazione confermata o un lock recente (< 2 min).
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
        // Slot già occupato
        $db->rollBack();
        echo json_encode(['success' => false, 'message' => __('err_slot_occupied')]);
        exit;
    }

    // Inserimento del Lock ('in_attesa')
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

    // Commit della transazione: il lock è ora visibile agli altri
    $db->commit();

    echo json_encode(['success' => true, 'lock_id' => $tempId]);
} catch (Exception $e) {
    // In caso di errore tecnico, rollback per mantenere consistenza DB
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => __('err_tech_lock')]);
}
