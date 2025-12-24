<?php

/**
 * API Endpoint: Conferma Prenotazione (api/prenota.php)
 *
 * Scopo:
 * Trasforma una prenotazione temporanea (Lock/In Attesa) in una definitiva (Confermata).
 *
 * Gestione Race Condition (Il Fix):
 * Questo script implementa controlli critici "Just-In-Time" (eseguiti l'istante prima del salvataggio).
 * Senza questi controlli, un utente potrebbe aprire più schede del browser, ottenere il lock su
 * 10 slot diversi e confermarli tutti simultaneamente, aggirando i limiti logici dell'applicazione.
 *
 * @package    App\API
 * @version    1.2 (Fix Race Condition + Full Docs)
 */

header('Content-Type: application/json');

// ==============================================================================
// 1. CARICAMENTO DIPENDENZE E CONFIGURAZIONE
// ==============================================================================
require_once SRC_PATH . '/config/database.php';
require_once SRC_PATH . '/utils.php';

// ==============================================================================
// 2. VALIDAZIONE PRELIMINARE DELLA RICHIESTA
// ==============================================================================

/**
 * Controllo Metodo HTTP
 * Accettiamo solo POST per operazioni che modificano lo stato del database.
 */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit(json_encode(['success' => false, 'message' => __('err_method')]));
}

/**
 * Controllo Autenticazione
 * Verifica che l'utente abbia una sessione attiva.
 */
if (!Utils::is_logged()) {
    exit(json_encode(['success' => false, 'message' => __('err_login_required')]));
}

/**
 * Recupero ID Lock
 * L'ID della prenotazione temporanea passato dal frontend.
 */
$lockId = isset($_POST['lock_id']) ? (int)$_POST['lock_id'] : 0;

if (!$lockId) {
    exit(json_encode(['success' => false, 'message' => __('err_missing_id')]));
}

// ==============================================================================
// 3. RECUPERO DATI E VERIFICA STATO INIZIALE
// ==============================================================================

$database = new Database();
$db = $database->getConnection();

try {
    // Recuperiamo i dettagli della prenotazione "in_attesa"
    $stmtGet = $db->prepare("SELECT data_prenotazione, ora_inizio, stato, idutente 
                             FROM prenotazioni WHERE idprenotazione = :id");
    $stmtGet->execute([':id' => $lockId]);
    $prenotazione = $stmtGet->fetch();

    /**
     * CHECK 1: Proprietà e Validità
     * 1. La prenotazione deve esistere.
     * 2. L'ID utente della sessione deve coincidere con quello della prenotazione (Security Check).
     */
    if (!$prenotazione || $prenotazione['idutente'] != $_SESSION['user_id']) {
        exit(json_encode(['success' => false, 'message' => __('err_booking_expired')]));
    }

    /**
     * CHECK 2: Idempotenza
     * Se la prenotazione è già confermata (es. doppio click involontario),
     * restituiamo successo senza fare nulla per evitare errori.
     */
    if ($prenotazione['stato'] === 'confermata') {
        exit(json_encode(['success' => true]));
    }

    // ==============================================================================
    // 4. VERIFICA VINCOLI TEMPORALI (REGOLE DI BUSINESS)
    // ==============================================================================

    // Parsing delle date per i confronti
    $dataTarget = new DateTime($prenotazione['data_prenotazione']);
    $oraInizio = new DateTime($prenotazione['data_prenotazione'] . ' ' . $prenotazione['ora_inizio']);
    $adesso = new DateTime();

    $settimanaCorrente = $adesso->format('oW'); // Anno + Settimana (es. 202445)
    $settimanaTarget = $dataTarget->format('oW');
    $settimanaProssima = (clone $adesso)->modify('+1 week')->format('oW');
    $ora = (int)$oraInizio->format('H');

    /**
     * CHECK 3: Finestra Temporale
     * Regola: Si prenota solo nella settimana corrente.
     * Eccezione: Lunedì a mezzanotte si apre la settimana successiva.
     */
    $isOpeningTime = ($settimanaTarget === $settimanaProssima) &&
        ($dataTarget->format('N') == 1) && // Lunedì
        ($ora === 0);                      // Ore 00:00

    if ($settimanaTarget !== $settimanaCorrente && !$isOpeningTime) {
        exit(json_encode([
            'success' => false,
            'message' => __('err_future_date') . " ({$dataTarget->format('d/m/Y')})"
        ]));
    }

    /**
     * CHECK 4: Slot nel Passato
     * Non è possibile confermare una prenotazione per un'ora già trascorsa.
     */
    if ($oraInizio < $adesso) {
        exit(json_encode([
            'success' => false,
            'message' => __('err_past_date') . " ({$oraInizio->format('d/m/Y H:i')})"
        ]));
    }

    // ==============================================================================
    // 5. FIX RACE CONDITION (CONTROLLI DI INTEGRITÀ)
    // ==============================================================================
    // Questi controlli vengono eseguiti QUI, un istante prima della scrittura,
    // per impedire l'exploit "Multi-Tab" (aprire più lock e confermarli insieme).

    /**
     * CHECK 5 (FIX): Sovrapposizione Oraria (Overlap Check)
     * Verifica se l'utente ha GIÀ una prenotazione CONFERMATA per la stessa data e ora
     * su una macchina diversa.
     */
    $sqlOverlap = "SELECT idprenotazione FROM prenotazioni 
                   WHERE idutente = :uid 
                   AND data_prenotazione = :data 
                   AND ora_inizio = :ora 
                   AND stato = 'confermata'";

    $stmtOverlap = $db->prepare($sqlOverlap);
    $stmtOverlap->execute([
        ':uid' => $_SESSION['user_id'],
        ':data' => $prenotazione['data_prenotazione'],
        ':ora' => $prenotazione['ora_inizio']
    ]);

    if ($stmtOverlap->rowCount() > 0) {
        // Se troviamo un risultato, l'utente sta provando a duplicarsi. Blocchiamo.
        exit(json_encode([
            'success' => false,
            'message' => "Hai già una prenotazione confermata per quest'ora su un'altra macchina."
        ]));
    }

    /**
     * CHECK 6 (FIX): Limite Giornaliero (Quota Check)
     * Verifica quante prenotazioni CONFERMATE ha già l'utente per quel giorno.
     * Se l'utente ha aperto 10 lock ma il limite è 2, i primi 2 passeranno, 
     * gli altri 8 verranno bloccati qui.
     */
    $MAX_SLOT_GIORNALIERI = 2; // Valore hardcoded (o recuperabile da config)

    $sqlLimit = "SELECT COUNT(*) as num FROM prenotazioni 
                 WHERE idutente = :uid 
                 AND data_prenotazione = :data 
                 AND stato = 'confermata'";

    $stmtLimit = $db->prepare($sqlLimit);
    $stmtLimit->execute([
        ':uid' => $_SESSION['user_id'],
        ':data' => $prenotazione['data_prenotazione']
    ]);
    $count = $stmtLimit->fetch()['num'];

    if ($count >= $MAX_SLOT_GIORNALIERI) {
        // Limite raggiunto: blocchiamo la conferma del lock in eccesso.
        exit(json_encode([
            'success' => false,
            'message' => "Hai raggiunto il limite giornaliero di {$MAX_SLOT_GIORNALIERI} ore."
        ]));
    }

    // ==============================================================================
    // 6. ESECUZIONE AGGIORNAMENTO (COMMIT)
    // ==============================================================================

    /**
     * Update Atomico
     * Aggiorna lo stato SOLO se è ancora 'in_attesa'.
     * Questo è l'ultimo baluardo contro modifiche concorrenti.
     */
    $sql = "UPDATE prenotazioni SET stato = 'confermata' 
            WHERE idprenotazione = :id AND stato = 'in_attesa'";

    $stmtUpd = $db->prepare($sql);
    $stmtUpd->execute([':id' => $lockId]);

    if ($stmtUpd->rowCount() > 0) {
        // Successo: lo slot è ufficialmente prenotato.
        echo json_encode(['success' => true]);
    } else {
        // Fallimento Tecnico:
        // Solitamente accade se il "Lock" è scaduto (timeout) nel frattempo
        // ed è stato ripulito da un processo di background o da un altro utente.
        echo json_encode(['success' => false, 'message' => __('err_tech_confirm')]);
    }
} catch (Exception $e) {
    // ==============================================================================
    // 7. GESTIONE ERRORI
    // ==============================================================================
    // Cattura eccezioni database (es. connessione persa, errori sintassi SQL)
    echo json_encode(['success' => false, 'message' => __('err_db_generic') . ': ' . $e->getMessage()]);
}
