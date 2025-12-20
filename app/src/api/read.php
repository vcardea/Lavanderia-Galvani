<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../utils.php';
require_once __DIR__ . '/../config/database.php';

if (!Utils::is_logged()) {
    exit(json_encode(['success' => false]));
}

$date = $_GET['date'] ?? date('Y-m-d');

try {
    $database = new Database();
    $db = $database->getConnection();

    // 1. Recupero Prenotazioni (Come prima)
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

    // 2. RECUPERO STATO MACCHINE (Ritardo e Stato) - NUOVO PEZZO
    // Questo serve per aggiornare l'header della colonna in tempo reale
    $stmtM = $db->query("SELECT idmacchina, stato, ritardo FROM macchine");
    $macchineStatus = $stmtM->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'prenotazioni' => $prenotazioni,
        'macchine' => $macchineStatus // Inviamo anche i metadati aggiornati
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false]);
}
