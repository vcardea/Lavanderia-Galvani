<?php
require_once SRC_PATH . '/config/database.php';

// 1. Configurazione Date
$today = new DateTime();
$today->setTime(0, 0, 0);

$dayOfWeek = $today->format('w');
$delta = ($dayOfWeek == 0) ? 6 : $dayOfWeek - 1;
$monday = clone $today;
$monday->modify("-$delta days");

$selectedDateStr = $_GET['date'] ?? $today->format('Y-m-d');
try {
    $selectedDate = new DateTime($selectedDateStr);
    $selectedDate->setTime(0, 0, 0);
} catch (Exception $e) {
    $selectedDate = $today;
}

// 2. Recupero Macchine e Ritardi
$database = new Database();
$db = $database->getConnection();
$stmtMacchine = $db->query("SELECT * FROM macchine");
$macchine = $stmtMacchine->fetchAll();

require SRC_PATH . '/templates/header.php';
?>

<div class="flex flex-wrap justify-center gap-2 p-4 bg-black/20 mb-6 rounded-xl">
    <?php
    $tempDate = clone $monday;
    $nowReal = new DateTime();

    for ($i = 0; $i < 7; $i++):
        $isActive = ($tempDate->format('Y-m-d') === $selectedDate->format('Y-m-d'));
        $isPastDay = ($tempDate < $today); // Controlla se passato, ma non salta il ciclo

        // Larghezza flessibile ma con minimo, così si adattano
        $baseClasses = "flex-grow-0 w-[calc(25%-0.5rem)] sm:w-20 py-3 rounded-lg text-center border transition-all duration-200";

        if ($isActive) {
            $colorClasses = "bg-accent text-white shadow-lg shadow-blue-900/20 font-bold border-accent scale-105 ring-2 ring-blue-500/30";
        } elseif ($isPastDay) {
            // Stile per giorni passati: visibili ma spenti e non cliccabili
            $colorClasses = "bg-zinc-900/50 text-zinc-700 border-transparent cursor-not-allowed opacity-60";
        } else {
            $colorClasses = "bg-card text-gray-400 border-zinc-800 hover:bg-zinc-800 hover:text-gray-200 hover:border-zinc-600";
        }
    ?>
        <a href="<?= $isPastDay ? '#' : '?date=' . $tempDate->format('Y-m-d') ?>" class="<?= $baseClasses . ' ' . $colorClasses ?>">
            <span class="block text-[10px] uppercase tracking-wider opacity-70"><?= __('day_' . $tempDate->format('w')) ?></span>
            <span class="block text-xl font-bold leading-none mt-1"><?= $tempDate->format('d') ?></span>
        </a>
    <?php
        $tempDate->modify('+1 day');
    endfor;
    ?>
</div>

<h3 class="px-4 text-lg font-bold text-white mb-4 flex items-center gap-2">
    <span>📅</span>
    <?= __('dash_title') ?> <span class="text-accent"><?= $selectedDate->format('d/m') ?></span>
</h3>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 px-4 pb-24">
    <?php foreach ($macchine as $macchina): ?>
        <div class="bg-card rounded-xl border border-zinc-800 shadow-lg overflow-hidden flex flex-col">

            <?php
            $isManutenzione = ($macchina['stato'] === 'manutenzione');
            $ritardo = (int)($macchina['ritardo'] ?? 0);
            $hasDelay = $ritardo > 0;
            $headerClass = $isManutenzione ? "bg-orange-900/20 border-orange-900/50" : "bg-zinc-800/50 border-zinc-700/50";
            ?>

            <div id="machine-header-<?= $macchina['idmacchina'] ?>" class="<?= $headerClass ?> p-4 border-b h-28 flex flex-col justify-center items-center relative transition-colors duration-300">

                <?php if ($isManutenzione): ?>
                    <div class="absolute top-2 right-2 bg-orange-600 text-[10px] font-bold px-2 py-0.5 rounded text-black shadow-lg shadow-orange-900/40">
                        <?= strtoupper(__('status_maintenance')) ?>
                    </div>
                <?php endif; ?>

                <span class="text-sm font-bold text-gray-200 uppercase tracking-widest mb-2">
                    <?= htmlspecialchars(__($macchina['nome'])) ?>
                </span>

                <?php if (!isset($_GET['date']) || $_GET['date'] === (new DateTime())->format('Y-m-d')): ?>
                <button onclick="openDelayModal(<?= $macchina['idmacchina'] ?>, '<?= htmlspecialchars(__($macchina['nome'])) ?>', <?= $ritardo ?>)"
                    class="machine-delay-btn flex items-center gap-1.5 px-3 py-1.5 rounded-full text-xs font-bold border transition-all hover:scale-105 active:scale-95 <?= $hasDelay ? 'bg-yellow-500/10 text-yellow-400 border-yellow-500/30 animate-pulse' : 'bg-zinc-900/80 text-zinc-500 border-zinc-700 hover:text-gray-300 hover:border-zinc-500' ?>">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="delay-val">
                        <?= $hasDelay ? "+{$ritardo} " . __('lbl_delay_min') : __('lbl_delay') ?>
                    </span>
                </button>
                <?php endif; ?>
            </div>

            <div class="divide-y divide-zinc-800/50">
                <?php
                $hours = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 0];

                foreach ($hours as $h):
                    $timeLabel = sprintf("%02d:00", $h);
                    $slotDate = clone $selectedDate;
                    if ($h == 0) $slotDate->modify('+1 day');
                    $slotDate->setTime($h, 0, 0);
                    $slotFullDate = $slotDate->format('Y-m-d');
                    $slotId = "slot_{$macchina['idmacchina']}_{$slotFullDate}_{$h}";

                    $isSlotPast = ($slotDate < $nowReal);
                    $slotBase = "slot relative h-14 flex items-center justify-center transition-colors duration-200 cursor-pointer border-l-4 border-transparent";

                    if ($isSlotPast) {
                        $slotClass = 'past opacity-40 cursor-default bg-zinc-900/30';
                        $statusText = $isManutenzione ? 'X' : __('status_free');
                        $onclick = "";
                    } elseif ($isManutenzione) {
                        $slotClass = 'past opacity-40 cursor-default bg-zinc-900/30';
                        $statusText = 'MANUTENZIONE';
                        $onclick = "";
                    } else {
                        $slotClass = 'free hover:bg-zinc-800';
                        $statusText = __('status_free');
                        $onclick = "prenotaSlot(this)";
                    }
                ?>
                    <div class="<?= $slotBase ?> <?= $slotClass ?>"
                        id="<?= $slotId ?>"
                        data-machine="<?= $macchina['idmacchina'] ?>"
                        data-date="<?= $slotFullDate ?>"
                        data-hour="<?= $h ?>"
                        <?php if ($onclick): ?> onclick="<?= $onclick ?>" <?php endif; ?>>

                        <span class="absolute left-4 text-xs font-mono text-gray-600 time-label pointer-events-none">
                            <?= $timeLabel ?>
                        </span>
                        <span class="status-text text-sm font-medium pointer-events-none">
                            <?= $statusText ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div id="bookingModal" class="modal-overlay">
    <div class="bg-card w-[90%] max-w-sm rounded-xl p-6 shadow-2xl border border-zinc-700 transform transition-all scale-100">
        <div class="text-xl font-bold text-white mb-2" id="modalTitle"></div>
        <div class="text-gray-400 mb-6 text-sm leading-relaxed" id="modalBody"></div>
        <div class="flex gap-3" id="modalActions"></div>
    </div>
</div>

<div id="delayModal" class="modal-overlay">
    <div class="bg-card w-[90%] max-w-sm rounded-xl p-6 shadow-2xl border border-zinc-700 transform transition-all scale-100">
        <div class="text-xl font-bold text-white mb-2 flex items-center gap-2">
            <span>⏱️</span> <?= __('modal_delay_title') ?>
        </div>
        <p class="text-gray-400 mb-6 text-sm leading-relaxed">
            <?= __('modal_delay_desc') ?> <br>
            <span class="text-accent font-bold" id="delayMachineName"></span>
        </p>

        <input type="hidden" id="delayMachineId">

        <div class="flex items-center justify-center gap-4 mb-6">
            <button type="button" onclick="adjustDelay(-5)" class="p-3 bg-zinc-800 rounded-lg hover:bg-zinc-700 text-white font-mono transition-colors active:scale-95">-5</button>
            <div class="relative w-24">
                <input type="number" id="delayInput" value="0" min="0" max="120" class="w-full bg-black border border-zinc-600 rounded-lg py-2 text-center text-2xl font-bold text-white focus:border-accent focus:ring-1 focus:ring-accent outline-none">
                <span class="absolute right-2 bottom-3 text-[10px] text-gray-500">min</span>
            </div>
            <button type="button" onclick="adjustDelay(+5)" class="p-3 bg-zinc-800 rounded-lg hover:bg-zinc-700 text-white font-mono transition-colors active:scale-95">+5</button>
        </div>

        <div class="flex gap-3">
            <button onclick="closeDelayModal()" class="flex-1 px-4 py-3 rounded bg-zinc-700 text-white font-medium hover:bg-zinc-600 transition-colors">
                <?= __('btn_cancel') ?>
            </button>
            <button onclick="saveDelay()" class="flex-1 px-4 py-3 rounded font-bold text-white bg-yellow-600 hover:bg-yellow-500 shadow-lg shadow-yellow-900/20 transition-colors">
                <?= __('btn_update_delay') ?>
            </button>
        </div>
    </div>
</div>

<?php require SRC_PATH . '/templates/footer.php'; ?>