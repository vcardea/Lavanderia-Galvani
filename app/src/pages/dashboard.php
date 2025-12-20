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

// 2. Recupero Macchine
$database = new Database();
$db = $database->getConnection();
$stmtMacchine = $db->query("SELECT * FROM macchine");
$macchine = $stmtMacchine->fetchAll();

require SRC_PATH . '/templates/header.php';
?>

<div class="flex overflow-x-auto gap-2 p-4 bg-black/20 mb-4 no-scrollbar">
    <?php
    $tempDate = clone $monday;
    $nowReal = new DateTime();

    for ($i = 0; $i < 7; $i++):
        $isActive = ($tempDate->format('Y-m-d') === $selectedDate->format('Y-m-d'));
        $isPastDay = ($tempDate < $today);

        $baseClasses = "flex-shrink-0 w-16 py-3 rounded-lg text-center border transition-all duration-200";

        if ($isActive) {
            $colorClasses = "bg-accent text-white shadow-lg shadow-blue-900/20 font-bold border-accent scale-105";
        } elseif ($isPastDay) {
            $colorClasses = "bg-zinc-900 text-zinc-600 border-transparent opacity-40 grayscale pointer-events-none cursor-default";
        } else {
            $colorClasses = "bg-card text-gray-400 border-zinc-800 hover:bg-zinc-800 hover:text-gray-200";
        }

        $finalClass = "$baseClasses $colorClasses";
    ?>
        <a href="<?= $isPastDay ? '#' : '?date=' . $tempDate->format('Y-m-d') ?>" class="<?= $finalClass ?>">
            <span class="block text-[10px] uppercase tracking-wider opacity-70"><?= __('day_' . $tempDate->format('w')) ?></span>
            <span class="block text-xl font-bold leading-none mt-1"><?= $tempDate->format('d') ?></span>
        </a>
    <?php
        $tempDate->modify('+1 day');
    endfor;
    ?>
</div>

<h3 class="px-4 text-lg font-bold text-white mb-3">
    <?= __('dash_title') ?> <?= $selectedDate->format('d/m') ?>
</h3>

<div class="flex gap-3 px-2 pb-20 overflow-x-auto no-scrollbar">
    <?php foreach ($macchine as $macchina): ?>
        <div class="flex-1 min-w-[120px]">

            <?php
            $isManutenzione = ($macchina['stato'] === 'manutenzione');
            $headerClass = $isManutenzione ? "bg-orange-900/40 border-orange-700" : "bg-zinc-800 border-zinc-700";
            ?>
            <div class="<?= $headerClass ?> p-3 rounded-t-lg text-center border-b-2 h-20 flex flex-col justify-center items-center relative overflow-hidden">
                <?php if ($isManutenzione): ?>
                    <div class="absolute top-0 right-0 bg-orange-600 text-[8px] font-bold px-2 py-0.5 text-black">
                        <?= strtoupper(__('status_maintenance')) ?>
                    </div>
                <?php endif; ?>

                <span class="text-xs font-bold text-gray-300 uppercase tracking-wide">
                    <?= htmlspecialchars($macchina['nome']) ?>
                </span>
            </div>

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

                $slotClass = 'free';
                // TRADUZIONE 'Libero'
                $statusText = __('status_free');
                $onclick = "prenotaSlot(this)";

                if ($isSlotPast) {
                    $slotClass = 'past';
                    $statusText = __('status_free');
                    $onclick = "";
                } elseif ($isManutenzione) {
                    $slotClass = 'past';
                    $statusText = 'X';
                    $onclick = "";
                }
            ?>
                <div class="slot <?= $slotClass ?>"
                    id="<?= $slotId ?>"
                    data-machine="<?= $macchina['idmacchina'] ?>"
                    data-date="<?= $slotFullDate ?>"
                    data-hour="<?= $h ?>"
                    <?php if ($onclick): ?> onclick="<?= $onclick ?>" <?php endif; ?>>

                    <span class="absolute top-1 left-2 text-[10px] text-gray-500 font-mono time-label pointer-events-none">
                        <?= $timeLabel ?>
                    </span>
                    <span class="status-text font-medium pointer-events-none">
                        <?= $statusText ?>
                    </span>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
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
            <button type="button" onclick="adjustDelay(-5)" class="p-3 bg-zinc-800 rounded-lg hover:bg-zinc-700 text-white">-5</button>
            <div class="relative w-24">
                <input type="number" id="delayInput" value="0" min="0" max="120" class="w-full bg-black border border-zinc-600 rounded-lg py-2 text-center text-2xl font-bold text-white focus:border-accent focus:ring-1 focus:ring-accent outline-none">
                <span class="absolute right-2 bottom-3 text-[10px] text-gray-500">min</span>
            </div>
            <button type="button" onclick="adjustDelay(+5)" class="p-3 bg-zinc-800 rounded-lg hover:bg-zinc-700 text-white">+5</button>
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

<div id="bookingModal" class="modal-overlay">
    <div class="bg-card w-[90%] max-w-sm rounded-xl p-6 shadow-2xl border border-zinc-700 transform transition-all scale-100">
        <div class="text-xl font-bold text-white mb-2" id="modalTitle"></div>
        <div class="text-gray-400 mb-6 text-sm leading-relaxed" id="modalBody"></div>
        <div class="flex gap-3" id="modalActions"></div>
    </div>
</div>

<?php require SRC_PATH . '/templates/footer.php'; ?>