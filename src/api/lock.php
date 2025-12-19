<?php
header('Content-Type: application/json');
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/utils.php';

// 1. Controllo Metodo e Login
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Metodo errato']));
}

if (!Utils::is_logged()) {
    exit(json_encode(['success' => false, 'message' => 'Login richiesto']));
}

$idmacchina = $_POST['idmacchina'] ?? 0;
$data = $_POST['data'] ?? '';
$ora = $_POST['ora'] ?? '';

// ---------------------------------------------------------
// 2. VALIDAZIONE TEMPORALE (Frontend check)
// ---------------------------------------------------------
try {
    $oggi = new DateTime();
    $dataTarget = new DateTime($data);
    $inizioSlot = new DateTime("$data $ora:00:00");

    // A. Controllo Settimana Corrente
    if ($dataTarget->format('oW') !== $oggi->format('oW')) {
        exit(json_encode(['success' => false, 'message' => 'Puoi prenotare solo per la settimana corrente!']));
    }

    // B. Controllo Slot nel Passato
    if ($inizioSlot < $oggi) {
        exit(json_encode(['success' => false, 'message' => 'Non puoi prenotare nel passato!']));
    }
} catch (Exception $e) {
    exit(json_encode(['success' => false, 'message' => 'Data non valida']));
}

// ---------------------------------------------------------
// 3. RECUPERO CONFIGURAZIONE (Database)
// ---------------------------------------------------------
$database = new Database();
$db = $database->getConnection();

// --- CONTROLLO STATO MACCHINA (Manutenzione) ---
$stmtStatus = $db->prepare("SELECT stato FROM macchine WHERE idmacchina = ?");
$stmtStatus->execute([$idmacchina]);
$statoMacchina = $stmtStatus->fetchColumn();

if ($statoMacchina === 'manutenzione') {
    exit(json_encode(['success' => false, 'message' => 'Questa macchina è in manutenzione al momento.']));
}

// Recuperiamo il limite dal database (Configurazioni Generali)
// Se non esiste nel DB, usiamo 3 come fallback di sicurezza
$stmtConfig = $db->prepare("SELECT valore FROM configurazioni WHERE chiave = 'max_hours_weekly'");
$stmtConfig->execute();
$dbVal = $stmtConfig->fetchColumn();
$MAX_ORE_SETTIMANALI = ($dbVal !== false) ? (int)$dbVal : 3;

// ---------------------------------------------------------
// 4. CONTROLLO LIMITE UTENTE (Anti-Monopolio)
// ---------------------------------------------------------
// Contiamo prenotazioni confermate E quelle 'in_attesa' (i lock attivi)
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
        'message' => "Hai raggiunto il limite di $MAX_ORE_SETTIMANALI ore settimanali!"
    ]));
}

// ---------------------------------------------------------
// 5. TRANSAZIONE DI LOCK (Pessimistic Lock)
// ---------------------------------------------------------

$ora_inizio = sprintf("%02d:00:00", $ora);
$ora_fine = sprintf("%02d:00:00", ($ora == 23 ? 0 : $ora + 1));

try {
    $db->beginTransaction();

    // Controlliamo se lo slot è libero (bloccando la riga per lettura)
    // Ignoriamo i lock 'in_attesa' più vecchi di 2 minuti (zombie)
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
        echo json_encode(['success' => false, 'message' => 'Slot appena occupato da un altro utente!']);
        exit;
    }

    // Creiamo il Lock (Stato: in_attesa)
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

    // Restituiamo l'ID del lock al frontend
    echo json_encode(['success' => true, 'lock_id' => $tempId]);
} catch (Exception $e) {
    if ($db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Errore Tecnico (Lock)']);
}
