<?php
// src/pages/admin.php
require_once SRC_PATH . '/config/database.php';

$database = new Database();
$db = $database->getConnection();
$msg = '';
$msgType = 'success';

// --- GESTIONE AZIONI POST ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && $_POST['action'] === 'update_config') {
        $newLimit = (int)$_POST['max_hours'];
        if ($newLimit > 0) {
            $stmt = $db->prepare("UPDATE configurazioni SET valore = ? WHERE chiave = 'max_hours_weekly'");
            $stmt->execute([$newLimit]);
            $msg = sprintf(__('msg_config_updated'), $newLimit);
        } else {
            $msg = __('msg_invalid_num');
            $msgType = 'error';
        }
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'toggle_machine') {
        $idMacchina = $_POST['id_macchina'];
        $currentStatus = $_POST['current_status'];
        $newStatus = ($currentStatus === 'attiva') ? 'manutenzione' : 'attiva';
        $stmt = $db->prepare("UPDATE macchine SET stato = ? WHERE idmacchina = ?");
        $stmt->execute([$newStatus, $idMacchina]);
        $statusLabel = ($newStatus === 'attiva') ? __('st_active') : __('st_maint');
        $msg = sprintf(__('msg_machine_updated'), strtoupper($statusLabel));
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'reset_password') {
        $userId = $_POST['user_id'];
        $newPass = bin2hex(random_bytes(4));
        $newHash = password_hash($newPass, PASSWORD_BCRYPT);
        $stmt = $db->prepare("UPDATE utenti SET password_hash = ? WHERE idutente = ?");
        $stmt->execute([$newHash, $userId]);
        $msg = __('msg_pass_reset') . " <strong class='font-mono bg-black px-2 py-1 rounded text-accent'>" . htmlspecialchars($newPass) . "</strong>";
    }
    elseif (isset($_POST['action']) && $_POST['action'] === 'delete_user') {
        $userId = $_POST['user_id'];
        $anonimo = "Utente Cancellato";
        $dummyEmail = "deleted_$userId@anon.imo";
        $dummyPass = "LOCKED";
        $stmt = $db->prepare("UPDATE utenti SET nome = ?, email = ?, username = ?, password_hash = ?, numero_appartamento = '' WHERE idutente = ?");
        $stmt->execute([$anonimo, $dummyEmail, $anonimo, $dummyPass, $userId]);

        // Cancella prenotazioni future
        $stmtDelRes = $db->prepare("DELETE FROM prenotazioni WHERE idutente = ? AND data_prenotazione >= CURRENT_DATE");
        $stmtDelRes->execute([$userId]);
        $msg = __('msg_user_deleted');
    }
}

// --- RECUPERO DATI (Con gestione errori) ---
try {
    // Config
    $stmtConf = $db->prepare("SELECT valore FROM configurazioni WHERE chiave = 'max_hours_weekly'");
    $stmtConf->execute();
    $currentLimit = $stmtConf->fetchColumn() ?: 3;

    // Macchine
    $stmtMacchine = $db->query("SELECT * FROM macchine");
    $macchine = $stmtMacchine->fetchAll();

    // Utenti (Escludendo se stessi)
    $stmtList = $db->prepare("SELECT * FROM utenti WHERE idutente != ? ORDER BY data_registrazione DESC");
    $stmtList->execute([$_SESSION['user_id']]);
    $utenti = $stmtList->fetchAll();
} catch (Exception $e) {
    die("Errore Database: " . $e->getMessage());
}

require SRC_PATH . '/templates/header.php';
?>

<div class="p-4 max-w-6xl mx-auto space-y-8 pb-20">

    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between border-b border-zinc-800 pb-4 gap-2">
        <h2 class="text-2xl font-bold text-white"><?= __('admin_title') ?></h2>
        <span class="text-xs text-gray-500 uppercase tracking-widest bg-zinc-900 px-2 py-1 rounded"><?= __('admin_subtitle') ?></span>
    </div>

    <?php if ($msg): ?>
        <div class="<?= ($msgType === 'success') ? 'bg-green-900/30 border-green-800 text-green-300' : 'bg-red-900/30 border-red-800 text-red-300' ?> border p-4 rounded-lg flex items-center shadow-lg text-sm">
            <span class="text-2xl mr-3"><?= ($msgType === 'success') ? '✅' : '⚠️' ?></span>
            <div><?= $msg ?></div>
        </div>
    <?php endif; ?>

    <section class="bg-card rounded-xl border border-zinc-800 shadow-xl overflow-hidden">
        <div class="p-4 bg-zinc-800/50 border-b border-zinc-700">
            <h3 class="font-bold text-gray-200 flex items-center gap-2">⚙️ <?= __('admin_sect_settings') ?></h3>
        </div>
        <div class="p-6">
            <form method="POST" class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <input type="hidden" name="action" value="update_config">
                <div class="w-full sm:w-auto sm:flex-1 max-w-xs">
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-2"><?= __('lbl_max_hours') ?></label>
                    <input type="number" name="max_hours" value="<?= $currentLimit ?>" min="1" max="20"
                        class="w-full bg-zinc-900 border border-zinc-700 rounded p-3 text-white focus:border-accent focus:ring-1 focus:ring-accent font-bold text-lg">
                </div>
                <button type="submit" class="w-full sm:w-auto bg-accent hover:bg-blue-600 text-white font-bold py-3 px-6 rounded transition-colors shadow-lg shadow-blue-900/20">
                    <?= __('btn_save') ?>
                </button>
            </form>
            <p class="text-xs text-gray-500 mt-3">
                <?= __('note_immediate_effect') ?>
            </p>
        </div>
    </section>

    <section class="bg-card rounded-xl border border-zinc-800 shadow-xl overflow-hidden">
        <div class="p-4 bg-zinc-800/50 border-b border-zinc-700">
            <h3 class="font-bold text-gray-200 flex items-center gap-2">🧺 <?= __('admin_sect_machines') ?></h3>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-400">
                <thead class="bg-zinc-800/30 uppercase text-xs">
                    <tr>
                        <th class="p-4"><?= __('th_name') ?></th>
                        <th class="p-4"><?= __('th_type') ?></th>
                        <th class="p-4"><?= __('th_status') ?></th>
                        <th class="p-4 text-right"><?= __('th_action') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    <?php foreach ($macchine as $m): ?>
                        <tr class="hover:bg-zinc-800/30">
                            <td class="p-4 font-bold text-white"><?= htmlspecialchars(__($m['nome'] ?? '')) ?></td>
                            <td class="p-4 uppercase text-xs tracking-wider"><?= __($m['tipo'] ?? '') ?></td>
                            <td class="p-4">
                                <?php if (($m['stato'] ?? '') === 'attiva'): ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-green-900/30 text-green-400 border border-green-900/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-400 animate-pulse"></span> <?= __('st_active') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-orange-900/30 text-orange-400 border border-orange-900/50">
                                        <span class="w-1.5 h-1.5 rounded-full bg-orange-400"></span> <?= __('st_maint') ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="p-4 text-right">
                                <form method="POST">
                                    <input type="hidden" name="action" value="toggle_machine">
                                    <input type="hidden" name="id_macchina" value="<?= $m['idmacchina'] ?>">
                                    <input type="hidden" name="current_status" value="<?= $m['stato'] ?>">
                                    <button class="px-3 py-1.5 rounded text-xs font-bold border transition-all <?= ($m['stato'] === 'attiva') ? 'text-orange-400 border-orange-900/50 hover:bg-orange-600 hover:text-white' : 'text-green-400 border-green-900/50 hover:bg-green-600 hover:text-white' ?>">
                                        <?= ($m['stato'] === 'attiva') ? __('btn_set_maint') : __('btn_set_active') ?>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="md:hidden p-4 space-y-4">
            <?php foreach ($macchine as $m): ?>
                <div class="bg-zinc-900/50 border border-zinc-700 rounded-lg p-4 flex flex-col gap-3">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="block text-white font-bold text-lg"><?= htmlspecialchars(__($m['nome'] ?? '')) ?></span>
                            <span class="text-xs text-gray-500 uppercase"><?= __($m['tipo'] ?? '') ?></span>
                        </div>
                        <span class="px-2 py-1 rounded-full text-[10px] font-bold border <?= ($m['stato'] === 'attiva') ? 'bg-green-900/30 text-green-400 border-green-900/50' : 'bg-orange-900/30 text-orange-400 border-orange-900/50' ?>">
                            <?= ($m['stato'] === 'attiva') ? __('st_active') : __('st_maint') ?>
                        </span>
                    </div>
                    <form method="POST" class="mt-2">
                        <input type="hidden" name="action" value="toggle_machine">
                        <input type="hidden" name="id_macchina" value="<?= $m['idmacchina'] ?>">
                        <input type="hidden" name="current_status" value="<?= $m['stato'] ?>">
                        <button class="w-full py-2 rounded text-sm font-bold border transition-colors <?= ($m['stato'] === 'attiva') ? 'border-orange-900/50 text-orange-400 hover:bg-orange-900/20' : 'border-green-900/50 text-green-400 hover:bg-green-900/20' ?>">
                            <?= ($m['stato'] === 'attiva') ? __('btn_set_maint') : __('btn_set_active') ?>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="bg-card rounded-xl border border-zinc-800 shadow-xl overflow-hidden">
        <div class="p-4 bg-zinc-800/50 border-b border-zinc-700">
            <h3 class="font-bold text-gray-200 flex items-center gap-2">👥 <?= __('admin_sect_users') ?></h3>
        </div>

        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left text-sm text-gray-400">
                <thead class="bg-zinc-800/30 uppercase text-xs">
                    <tr>
                        <th class="p-4"><?= __('th_username') ?></th>
                        <th class="p-4"><?= __('th_email') ?></th>
                        <th class="p-4"><?= __('th_apt') ?></th>
                        <th class="p-4 text-center"><?= __('th_action') ?></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800">
                    <?php if (empty($utenti)): ?>
                        <tr>
                            <td colspan="4" class="p-6 text-center text-gray-500 italic">
                                <?= __('no_other_users') ?>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($utenti as $u): ?>
                            <?php if (($u['username'] ?? '') === 'Utente Cancellato') continue; ?>
                            <tr class="hover:bg-zinc-800/30">
                                <td class="p-4 font-bold text-accent"><?= htmlspecialchars($u['username'] ?? 'N/D') ?></td>
                                <td class="p-4"><?= htmlspecialchars($u['email'] ?? '') ?></td>
                                <td class="p-4"><?= htmlspecialchars($u['numero_appartamento'] ?? '') ?></td>
                                <td class="p-4">
                                    <div class="flex gap-2 justify-center">
                                        <form id="form_reset_<?= $u['idutente'] ?>" method="POST">
                                            <input type="hidden" name="action" value="reset_password">
                                            <input type="hidden" name="user_id" value="<?= $u['idutente'] ?>">
                                            <button type="button" onclick="confirmAdminAction('form_reset_<?= $u['idutente'] ?>', 'reset', '<?= htmlspecialchars($u['username'] ?? 'User') ?>')" class="text-yellow-500 hover:text-yellow-100 bg-yellow-900/20 hover:bg-yellow-700 p-2 rounded transition-colors" title="<?= __('reset_user_pwd') ?>">🔑</button>
                                        </form>
                                        <form id="form_delete_<?= $u['idutente'] ?>" method="POST">
                                            <input type="hidden" name="action" value="delete_user">
                                            <input type="hidden" name="user_id" value="<?= $u['idutente'] ?>">
                                            <button type="button" onclick="confirmAdminAction('form_delete_<?= $u['idutente'] ?>', 'delete', '<?= htmlspecialchars($u['username'] ?? 'User') ?>')" class="text-red-500 hover:text-red-100 bg-red-900/20 hover:bg-red-700 p-2 rounded transition-colors" title="<?= __('delete_user') ?>">🗑️</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="md:hidden p-4 space-y-4">
            <?php if (empty($utenti)): ?>
                <div class="text-center text-gray-500 italic p-4">
                    <?= __('no_other_users') ?>
                </div>
            <?php else: ?>
                <?php foreach ($utenti as $u): ?>
                    <?php if (($u['username'] ?? '') === 'Utente Cancellato') continue; ?>
                    <div class="bg-zinc-900/50 border border-zinc-700 rounded-lg p-4 relative">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <span class="text-accent font-bold text-lg block"><?= htmlspecialchars($u['username'] ?? 'N/D') ?></span>
                                <span class="text-xs text-zinc-500">Apt: <strong class="text-gray-300"><?= htmlspecialchars($u['numero_appartamento'] ?? '-') ?></strong></span>
                            </div>
                        </div>
                        <div class="text-xs text-gray-400 mb-4 bg-black/20 p-2 rounded break-all">
                            <?= htmlspecialchars($u['email'] ?? 'No Email') ?>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <form id="m_form_reset_<?= $u['idutente'] ?>" method="POST">
                                <input type="hidden" name="action" value="reset_password">
                                <input type="hidden" name="user_id" value="<?= $u['idutente'] ?>">
                                <button type="button" onclick="confirmAdminAction('m_form_reset_<?= $u['idutente'] ?>', 'reset', '<?= htmlspecialchars($u['username'] ?? 'User') ?>')" class="w-full bg-yellow-900/20 text-yellow-500 border border-yellow-900/50 py-2 rounded text-sm font-bold hover:bg-yellow-900/40"><?= __('reset_user_pwd') ?></button>
                            </form>
                            <form id="m_form_delete_<?= $u['idutente'] ?>" method="POST">
                                <input type="hidden" name="action" value="delete_user">
                                <input type="hidden" name="user_id" value="<?= $u['idutente'] ?>">
                                <button type="button" onclick="confirmAdminAction('m_form_delete_<?= $u['idutente'] ?>', 'delete', '<?= htmlspecialchars($u['username'] ?? 'User') ?>')" class="w-full bg-red-900/20 text-red-500 border border-red-900/50 py-2 rounded text-sm font-bold hover:bg-red-900/40"><?= __('delete_user') ?></button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

</div>

<div id="bookingModal" class="modal-overlay">
    <div class="bg-card w-[90%] max-w-sm rounded-xl p-6 shadow-2xl border border-zinc-700 transform transition-all scale-100">
        <div class="text-xl font-bold text-white mb-2" id="modalTitle"></div>
        <div class="text-gray-400 mb-6 text-sm leading-relaxed" id="modalBody"></div>
        <div class="flex gap-3" id="modalActions"></div>
    </div>
</div>

<script>
    const txt_reset_title = "<?= __('modal_reset_title') ?>";
    const txt_reset_body_tpl = "<?= __('modal_reset_body') ?>";
    const txt_btn_reset = "<?= __('btn_reset_confirm') ?>";
    const txt_del_title = "<?= __('modal_delete_title') ?>";
    const txt_del_body_tpl = "<?= __('modal_delete_body') ?>";
    const txt_btn_del = "<?= __('btn_delete_confirm') ?>";
    const txt_cancel = "<?= __('btn_cancel') ?>";

    function confirmAdminAction(formId, actionType, username) {
        let title, body, btnClass, btnText;
        if (actionType === 'reset') {
            title = txt_reset_title;
            body = txt_reset_body_tpl.replace('%s', username);
            btnClass = 'bg-yellow-600 hover:bg-yellow-500 shadow-yellow-900/20';
            btnText = txt_btn_reset;
        } else if (actionType === 'delete') {
            title = txt_del_title;
            body = txt_del_body_tpl.replace('%s', username);
            btnClass = 'bg-red-600 hover:bg-red-500 shadow-red-900/20';
            btnText = txt_btn_del;
        }

        const modal = document.getElementById('bookingModal');
        document.getElementById('modalTitle').innerText = title;
        document.getElementById('modalBody').innerHTML = body;

        const actions = document.getElementById('modalActions');
        actions.innerHTML = '';

        const btnCancel = document.createElement('button');
        btnCancel.className = 'flex-1 px-4 py-2 rounded bg-zinc-700 text-white font-medium hover:bg-zinc-600 transition-colors';
        btnCancel.innerText = txt_cancel;
        btnCancel.onclick = () => modal.classList.remove('open');
        actions.appendChild(btnCancel);

        const btnConfirm = document.createElement('button');
        btnConfirm.className = `flex-1 px-4 py-2 rounded font-bold text-white transition-colors shadow-lg ${btnClass}`;
        btnConfirm.innerText = btnText;
        btnConfirm.onclick = () => document.getElementById(formId).submit();
        actions.appendChild(btnConfirm);

        modal.classList.add('open');
    }

    document.getElementById('bookingModal').addEventListener('click', (e) => {
        if (e.target.classList.contains('modal-overlay')) document.getElementById('bookingModal').classList.remove('open');
    });
</script>

<?php require SRC_PATH . '/templates/footer.php'; ?>