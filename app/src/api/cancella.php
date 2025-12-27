<?php

/**
 * API Endpoint: Cancella Prenotazione (api/cancella.php)
 *
 * Scopo:
 * Permette a un utente autenticato di eliminare una propria prenotazione esistente.
 *
 * Flusso Logico:
 * 1. Verifica che la richiesta sia POST e che l'utente sia loggato.
 * 2. Riceve l'ID della prenotazione da cancellare.
 * 3. Esegue una query di DELETE sul database, assicurandosi che la prenotazione appartenga all'utente corrente.
 * 4. Restituisce un feedback JSON di successo o errore.
 *
 * @package    App\API
 * @author     DevTeam
 * @version    1.0
 */

header('Content-Type: application/json');

// Inclusione delle dipendenze necessarie
require_once SRC_PATH . '/utils.php';
require_once SRC_PATH . '/config/database.php';

// --------------------------------------------------------------------------
// 1. CONTROLLI DI SICUREZZA
// --------------------------------------------------------------------------

// Verifica Metodo HTTP (solo POST è permesso per modifiche) e Autenticazione Utente
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !Utils::is_logged()) {
    // Se non autorizzato, termina con errore JSON e codice HTTP appropriato (anche se qui exit esce con 200 OK e JSON di errore)
    exit(json_encode(['success' => false, 'message' => __('err_unauthorized')]));
}

// --------------------------------------------------------------------------
// 2. RECUPERO DATI INPUT
// --------------------------------------------------------------------------

// Recupera l'ID prenotazione dal corpo della richiesta POST, default 0 se mancante
$idprenotazione = $_POST['idprenotazione'] ?? 0;

// --------------------------------------------------------------------------
// 3. ESECUZIONE CANCELLAZIONE
// --------------------------------------------------------------------------

$database = new Database();
$db = $database->getConnection();

try {
    // Prepariamo la query di eliminazione.
    // IMPORTANTE: La clausola "AND idutente = ?" è fondamentale per la sicurezza.
    // Impedisce a un utente malintenzionato di cancellare prenotazioni altrui semplicemente cambiando l'ID.
    $sql = "DELETE FROM prenotazioni WHERE idprenotazione = ? AND idutente = ?";
    $stmt = $db->prepare($sql);

    // Eseguiamo la query passando l'ID prenotazione e l'ID dell'utente loggato (dalla sessione)
    $stmt->execute([$idprenotazione, $_SESSION['user_id']]);

    // Verifichiamo se è stata effettivamente cancellata una riga
    if ($stmt->rowCount() > 0) {
        // Successo: la prenotazione esisteva ed era dell'utente
        echo json_encode(['success' => true]);
    } else {
        // Fallimento logico: prenotazione non trovata o non appartenente all'utente
        echo json_encode(['success' => false, 'message' => __('err_booking_not_found')]);
    }
} catch (Exception $e) {
    // Gestione eccezioni database (es. errori di connessione)
    echo json_encode(['success' => false, 'message' => __('err_db_generic')]);
}
