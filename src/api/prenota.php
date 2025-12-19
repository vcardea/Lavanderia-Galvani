<?php
header('Content-Type: application/json');
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/utils.php';

// 1. Controllo Metodo
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => 'Metodo errato']));
}

// 2. Controllo Login (Utils)
if (!Utils::is_logged()) {
    exit(json_encode(['success' => false, 'message' => 'Login richiesto']));
}

$lockId = $_POST['lock_id'] ?? 0;

if (!$lockId) {
    exit(json_encode(['success' => false, 'message' => 'ID prenotazione mancante']));
}

$database = new Database();
$db = $database->getConnection();

try {
    // ------------------------------------------------------------------
    // PASSO EXTRA: Recuperiamo i dati del lock per poter fare i controlli
    // ------------------------------------------------------------------
    $stmtGet = $db->prepare("SELECT data_prenotazione, ora_inizio, stato, idutente 
                             FROM prenotazioni WHERE idprenotazione = :id");
    $stmtGet->execute([':id' => $lockId]);
    $prenotazione = $stmtGet->fetch();

    // Se non esiste o non è tua
    if (!$prenotazione || $prenotazione['idutente'] != $_SESSION['user_id']) {
        exit(json_encode(['success' => false, 'message' => 'Prenotazione non trovata o scaduta.']));
    }

    // Se è già confermata, usciamo subito (o diamo true se vuoi idempotenza)
    if ($prenotazione['stato'] === 'confermata') {
        exit(json_encode(['success' => true]));
    }

    // ------------------------------------------------------------------
    // I TUOI CONTROLLI DI SICUREZZA (Applicati ai dati DB)
    // ------------------------------------------------------------------

    $dataTarget = new DateTime($prenotazione['data_prenotazione']);
    $oraInizio = new DateTime($prenotazione['data_prenotazione'] . ' ' . $prenotazione['ora_inizio']);
    $adesso = new DateTime();

    // A. Controllo Settimana Corrente
    if ($dataTarget->format('oW') !== $adesso->format('oW')) {
        // Se per assurdo il lock è rimasto appeso dalla settimana scorsa
        exit(json_encode(['success' => false, 'message' => "Non puoi prenotare nel futuro ({$dataTarget->format('d/m/Y')})!"]));
    }

    // B. Controllo Slot nel Passato
    if ($oraInizio < $adesso) {
        exit(json_encode(['success' => false, 'message' => "Non puoi prenotare nel passato ({$oraInizio->format('d/m/Y H:i')})!"]));
    }


    // ------------------------------------------------------------------
    // CONFERMA FINALE
    // ------------------------------------------------------------------

    $sql = "UPDATE prenotazioni SET stato = 'confermata' 
            WHERE idprenotazione = :id AND stato = 'in_attesa'";

    $stmtUpd = $db->prepare($sql);
    $stmtUpd->execute([':id' => $lockId]);

    if ($stmtUpd->rowCount() > 0) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Errore tecnico durante la conferma.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Errore DB: ' . $e->getMessage()]);
}
