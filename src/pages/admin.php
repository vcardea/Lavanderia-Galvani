<?php
require_once SRC_PATH . '/config/database.php';

$database = new Database();
$db = $database->getConnection();
$msg = '';
$msgType = 'success'; // 'success' o 'error'

// --- GESTIONE AZIONI POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // A. AGGIORNA CONFIGURAZIONE (Ore Settimanali)
    if (isset($_POST['action']) && $_POST['action'] === 'update_config') {
        $newLimit = (int)$_POST['max_hours'];
        if ($newLimit > 0) {
            $stmt = $db->prepare("UPDATE configurazioni SET valore = ? WHERE chiave = 'max_hours_weekly'");
            $stmt->execute([$newLimit]);
            $msg = "Limite ore settimanali aggiornato a: <strong>$newLimit</strong>";
        } else {
            $msg = "Inserisci un numero valido.";
            $msgType = 'error';
        }
    }

    // B. CAMBIA STATO MACCHINA (Manutenzione <-> Attiva)
    if (isset($_POST['action']) && $_POST['action'] === 'toggle_machine') {
        $idMacchina = $_POST['id_macchina'];
        $currentStatus = $_POST['current_status'];
        // Toggle logico
        $newStatus = ($currentStatus === 'attiva') ? 'manutenzione' : 'attiva';

        $stmt = $db->prepare("UPDATE macchine SET stato = ? WHERE idmacchina = ?");
        $stmt->execute([$newStatus, $idMacchina]);

        $msg = "Stato macchina aggiornato a: <strong>" . strtoupper($newStatus) . "</strong>";
    }

    // C. RESET PASSWORD UTENTE
    if (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $userId = $_POST['user_id'];
        $newPass = bin2hex(random_bytes(4));
        $newHash = password_hash($newPass, PASSWORD_BCRYPT);

        $stmt = $db->prepare("UPDATE utenti SET password_hash = ? WHERE idutente = ?");
        $stmt->execute([$newHash, $userId]);
        $msg = "Password resettata. Nuova password: <strong class='font-mono bg-black px-2 py-1 rounded text-accent'>" . htmlspecialchars($newPass) . "</strong>";
    }

    // D. ELIMINA UTENTE
    if (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $userId = $_POST['user_id'];
        $anonimo = "Utente Cancellato";
        $dummyEmail = "deleted_$userId@anon.imo";
        $dummyPass = "LOCKED";

        $stmt = $db->prepare("UPDATE utenti SET nome = ?, email = ?, username = ?, password_hash = ?, numero_appartamento = '' WHERE idutente = ?");
        $stmt->execute([$anonimo, $dummyEmail, $anonimo, $dummyPass, $userId]);

        $stmtDelRes = $db->prepare("DELETE FROM prenotazioni WHERE idutente = ? AND data_prenotazione >= CURRENT_DATE");
        $stmtDelRes->execute([$userId]);

        $msg = "Utente anonimizzato e prenotazioni future cancellate.";
    }
}

// --- RECUPERO DATI ---

// 1. Configurazione attuale
$stmtConf = $db->prepare("SELECT valore FROM configurazioni WHERE chiave = 'max_hours_weekly'");
$stmtConf->execute();
$currentLimit = $stmtConf->fetchColumn() ?: 3;

// 2. Lista Macchine
$stmtMacchine = $db->query("SELECT * FROM macchine"); // Prendo tutte, anche in manutenzione
$macchine = $stmtMacchine->fetchAll();

// 3. Lista Utenti
$stmtList = $db->prepare("SELECT * FROM utenti WHERE idutente != ? ORDER BY data_registrazione DESC");
$stmtList->execute([$_SESSION['user_id']]);
$utenti = $stmtList->fetchAll();

// INIZIO VISTA
require SRC_PATH . '/templates/header.php';
?>

<div class="p-4 max-w-6xl mx-auto space-y-8">

    <div class="flex items-center justify-between border-b border-zinc-800 pb-4">
        <h2 class="text-2xl font-bold text-white">Pannello Amministrazione</h2>
        <span class="text-xs text-gray-500 uppercase tracking-widest">Admin Area</span>
    </div>

    <?php if ($msg): ?>
        <div class="<?= ($msgType === 'success') ? 'bg-green-900/30 border-green-800 text-green-300' : 'bg-red-900/30 border-red-800 text-red-300' ?> border p-4 rounded-lg flex items-center shadow-lg">
            <span class="text-2xl mr-3"><?= ($msgType === 'success') ? '✅' : '⚠️' ?></span>
            <div><?= $msg ?></div>
        </div>
    <?php endif; ?>

    <section class="bg-card rounded-xl border border-zinc-800 shadow-xl overflow-hidden">
        <div class="p-4 bg-zinc-800/50 border-b border-zinc-700 flex justify-between items-center">
            <h3 class="font-bold text-gray-200">⚙️ Impostazioni Generali</h3>
        </div>
        <div class="p-6">
            <form method="POST" class="flex items-end gap-4">
                <input type="hidden" name="action" value="update_config">
                <div class="flex-1 max-w-xs">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Max Ore Settimanali per Utente</label>
                    <input type="number" name="max_hours" value="<?= $currentLimit ?>" min="1" max="20"
                        class="w-full bg-zinc-900 border border-zinc-700 rounded p-3 text-white focus:border-accent focus:ring-1 focus:ring-accent font-bold text-lg">
                </div>
                <button type="submit" class="bg-accent hover:bg-blue-600 text-white font-bold py-3 px-6 rounded transition-colors shadow-lg shadow-blue-900/20">
                    Salva Modifiche
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-3">
                *Questa modifica ha effetto immediato su tutte le nuove prenotazioni.
            </p>
        </div>
    </section>

    <section class="bg-card rounded-xl border border-zinc-800 shadow-xl overflow-hidden">
        <div class="p-4 bg-zinc-800/50 border-b border-zinc-700">
            <h3 class="font-bold text-gray-200">🧺 Stato Macchine</h3>
        </div>

        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left text-sm text-gray-400">
                <thead class="bg-zinc-800/30 uppercase text-xs">
                    <tr>
                        <th class="p-4">Nome</th>
                        <th class="p-4">Tipo</th>
                        <th class="p-4">Stato Attuale</th>
                        <th class="p-4 text-right">Azione</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    <?php foreach ($macchine as $m): ?>
                        <tr class="hover:bg-zinc-800/30">
                            <td class="p-4 font-bold text-white"><?= htmlspecialchars($m['nome']) ?></td>
                            <td class="p-4 uppercase text-xs tracking-wider"><?= $m['tipo'] ?></td>
                            <td class="p-4">
                                <?php if ($m['stato'] === 'attiva'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-green-900/30 text-green-400 border border-green-900/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span> Attiva
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-orange-900/30 text-orange-400 border border-orange-900/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span> Manutenzione
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <form method="POST">
                                    <input type="hidden" name="action" value="toggle_machine">
                                    <input type="hidden" name="id_macchina" value="<?= $m['idmacchina'] ?>">
                                    <input type="hidden" name="current_status" value="<?= $m['stato'] ?>">

                                    <?php if ($m['stato'] === 'attiva'): ?>
                                        <button type="submit" class="text-orange-400 hover:text-white border border-orange-900/50 hover:bg-orange-600 px-3 py-1.5 rounded text-xs font-bold transition-all">
                                            Metti in Manutenzione
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="text-green-400 hover:text-white border border-green-900/50 hover:bg-green-600 px-3 py-1.5 rounded text-xs font-bold transition-all">
                                            Riattiva
                                        </button>
                                    <?php endif; ?>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

    <section class="bg-card rounded-xl border border-zinc-800 shadow-xl overflow-hidden">
        <div class="p-4 bg-zinc-800/50 border-b border-zinc-700">
            <h3 class="font-bold text-gray-200">👥 Lista Utenti</h3>
        </div>

        <div class="overflow-x-auto no-scrollbar">
            <table class="w-full text-left text-sm text-gray-400">
                <thead class="bg-zinc-800/30 uppercase text-xs">
                    <tr>
                        <th class="p-4">Username</th>
                        <th class="p-4">Email</th>
                        <th class="p-4">Appartamento</th>
                        <th class="p-4 text-center">Azioni</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    <?php foreach ($utenti as $u): ?>
                        <?php if ($u['username'] === 'Utente Cancellato') continue; ?>
                        <tr class="hover:bg-zinc-800/30">
                            <td class="p-4 font-bold text-accent"><?= htmlspecialchars($u['username']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($u['email']) ?></td>
                            <td class="p-4"><?= htmlspecialchars($u['numero_appartamento']) ?></td>
                            <td class="p-4">
                                <div class="flex gap-2 justify-center">

                                    <form id="form_reset_<?= $u['idutente'] ?>" method="POST">
                                        <input type="hidden" name="action" value="reset_password">
                                        <input type="hidden" name="user_id" value="<?= $u['idutente'] ?>">

                                        <button type="button"
                                            onclick="confirmAdminAction('form_reset_<?= $u['idutente'] ?>', 'reset', '<?= htmlspecialchars($u['username']) ?>')"
                                            class="text-yellow-500 hover:text-yellow-100 bg-yellow-900/20 hover:bg-yellow-700 p-2 rounded transition-colors"
                                            title="Reset Password">
                                            🔑
                                        </button>
                                    </form>

                                    <form id="form_delete_<?= $u['idutente'] ?>" method="POST">
                                        <input type="hidden" name="action" value="delete_user">
                                        <input type="hidden" name="user_id" value="<?= $u['idutente'] ?>">

                                        <button type="button"
                                            onclick="confirmAdminAction('form_delete_<?= $u['idutente'] ?>', 'delete', '<?= htmlspecialchars($u['username']) ?>')"
                                            class="text-red-500 hover:text-red-100 bg-red-900/20 hover:bg-red-700 p-2 rounded transition-colors"
                                            title="Elimina">
                                            🗑️
                                        </button>
                                    </form>

                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </section>

</div>

<div id="bookingModal" class="modal-overlay">
    <div class="bg-card w-[90%] max-w-sm rounded-xl p-6 shadow-2xl border border-zinc-700 transform transition-all scale-100">
        <div class="text-xl font-bold text-white mb-2" id="modalTitle">Titolo</div>
        <div class="text-gray-400 mb-6 text-sm leading-relaxed" id="modalBody">Messaggio...</div>
        <div class="flex gap-3" id="modalActions"></div>
    </div>
</div>

<script>
    function confirmAdminAction(formId, actionType, username) {
        let title, body, btnClass, btnText;

        if (actionType === 'reset') {
            title = 'Reset Password';
            body = `Sei sicuro di voler resettare la password per l'utente <b>${username}</b>?<br>La nuova password verrà mostrata a schermo.`;
            btnClass = 'bg-yellow-600 hover:bg-yellow-500 shadow-yellow-900/20';
            btnText = 'Reset Password';
        } else if (actionType === 'delete') {
            title = 'Elimina Utente';
            body = `ATTENZIONE: Stai per anonimizzare l'utente <b>${username}</b>.<br>Questa azione è irreversibile.`;
            btnClass = 'bg-red-600 hover:bg-red-500 shadow-red-900/20';
            btnText = 'Elimina Definitivamente';
        }

        // Funzione per aprire il modal (versione semplificata per Admin)
        const modal = document.getElementById('bookingModal');
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalBody').innerHTML = body;

        const actions = document.getElementById('modalActions');
        actions.innerHTML = '';

        // Tasto Annulla
        const btnCancel = document.createElement('button');
        btnCancel.className = 'flex-1 px-4 py-2 rounded bg-zinc-700 text-white font-medium hover:bg-zinc-600 transition-colors';
        btnCancel.innerText = 'Annulla';
        btnCancel.onclick = () => modal.classList.remove('open');
        actions.appendChild(btnCancel);

        // Tasto Conferma
        const btnConfirm = document.createElement('button');
        btnConfirm.className = `flex-1 px-4 py-2 rounded font-bold text-white transition-colors shadow-lg ${btnClass}`;
        btnConfirm.innerText = btnText;

        // Al click conferma: invia il form HTML
        btnConfirm.onclick = () => {
            document.getElementById(formId).submit();
        };
        actions.appendChild(btnConfirm);

        modal.classList.add('open');
    }

    // Chiudi modal cliccando fuori
    document.getElementById('bookingModal').addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-overlay')) {
            document.getElementById('bookingModal').classList.remove('open');
        }
    });
</script>

<?php require SRC_PATH . '/templates/footer.php'; ?>