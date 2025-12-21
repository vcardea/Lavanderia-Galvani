<?php
header('Content-Type: application/json');

try {
    if (!defined('SRC_PATH') && file_exists(__DIR__ . '/../utils.php')) {
        define('SRC_PATH', __DIR__ . '/..');
    }

    require_once SRC_PATH . '/utils.php';
    require_once SRC_PATH . '/config/database.php';

    if (!Utils::is_logged()) {
        echo json_encode(['success' => false, 'message' => 'Login richiesto']);
        exit;
    }

    $date = $_GET['date'] ?? date('Y-m-d');

    $database = new Database();
    $db = $database->getConnection();

    // QUERY CORRETTA PER L'EDGE CASE DOMENICA -> LUNEDI
    // Seleziona prenotazioni se:
    // 1. La data corrisponde esattamente a oggi
    // 2. OPPURE è domani, ma SOLO se l'ora è 00:00:00 (il famoso slot notturno)
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

    // Recupero Stato Macchine (Ritardi) con gestione errore colonna mancante
    try {
        $stmtM = $db->query("SELECT idmacchina, ritardo FROM macchine");
        $macchine = $stmtM->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $macchine = []; // Se fallisce (es. colonna ritardo non esiste), array vuoto per non rompere il frontend
    }

    echo json_encode([
        'success' => true,
        'prenotazioni' => $prenotazioni,
        'macchine' => $macchine
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Errore Server: ' . $e->getMessage()
    ]);
}
