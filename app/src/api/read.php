<?php

/**
 * API Endpoint: Leggi Prenotazioni (api/read.php)
 *
 * Scopo:
 * Restituisce i dati necessari per popolare la griglia della dashboard:
 * 1. Elenco prenotazioni attive per il giorno selezionato (e prime ore del successivo).
 * 2. Stato attuale delle macchine (es. ritardi accumulati).
 *
 * @package    App\API
 */

header('Content-Type: application/json');

try {
    // Definizione fallback di SRC_PATH se lo script viene chiamato direttamente e non via router
    if (!defined('SRC_PATH') && file_exists(__DIR__ . '/../utils.php')) {
        define('SRC_PATH', __DIR__ . '/..');
    }

    require_once SRC_PATH . '/utils.php';
    require_once SRC_PATH . '/config/database.php';

    // Accesso solo a utenti loggati
    if (!Utils::is_logged()) {
        echo json_encode(['success' => false, 'message' => 'Login richiesto']);
        exit;
    }

    // Data richiesta (default: oggi)
    $date = $_GET['date'] ?? date('Y-m-d');

    $database = new Database();
    $db = $database->getConnection();

    // --------------------------------------------------------------------------
    // QUERY PRENOTAZIONI
    // --------------------------------------------------------------------------
    // Questa query gestisce un "Edge Case" specifico:
    // Seleziona le prenotazioni del giorno corrente, MA ANCHE lo slot di mezzanotte
    // del giorno successivo (00:00:00), che logicamente appartiene alla "notte" del giorno selezionato.
    $sql = "
        SELECT p.idprenotazione, p.idmacchina, p.data_prenotazione, p.ora_inizio, p.stato, p.idutente, u.username
        FROM prenotazioni p
        JOIN utenti u ON p.idutente = u.idutente
        WHERE 
            (
                (p.data_prenotazione = :today) 
                OR 
                (p.data_prenotazione = DATE_ADD(:today, INTERVAL 1 DAY) AND p.ora_inizio = '00:00:00')
            )
            AND
            (
                p.stato = 'confermata'
                OR 
                (p.stato = 'in_attesa' AND p.created_at > NOW() - INTERVAL 2 MINUTE)
            )
    ";

    $stmt = $db->prepare($sql);
    $stmt->execute([':today' => $date]);
    $rows = $stmt->fetchAll();

    // Formattazione dati per il frontend (aggiunta flag 'is_mine')
    $prenotazioni = [];
    foreach ($rows as $row) {
        $prenotazioni[] = [
            'idprenotazione' => $row['idprenotazione'],
            'idmacchina' => $row['idmacchina'],
            'data_prenotazione' => $row['data_prenotazione'],
            'ora_inizio' => $row['ora_inizio'],
            'stato' => $row['stato'],
            'username' => $row['username'],
            'is_mine' => ($row['idutente'] == $_SESSION['user_id'])
        ];
    }

    // --------------------------------------------------------------------------
    // QUERY STATO MACCHINE (RITARDI)
    // --------------------------------------------------------------------------
    try {
        $stmtM = $db->query("SELECT idmacchina, ritardo FROM macchine");
        $macchine = $stmtM->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        // Se la colonna 'ritardo' non esiste ancora nel DB, evitiamo crash ritornando array vuoto
        $macchine = [];
    }

    echo json_encode([
        'success' => true,
        'prenotazioni' => $prenotazioni,
        'macchine' => $macchine
    ]);
} catch (Throwable $e) {
    // Gestione errori fatali
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Errore Server: ' . $e->getMessage()
    ]);
}
