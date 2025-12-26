<?php
// 1. Logica PHP (riceve i dati dall'index.php)
$code = isset($_GET['code']) ? (int)$_GET['code'] : 404;

// Mappatura Messaggi
$errors = [
    400 => ['title' => 'err_400_title', 'desc' => 'err_400_desc'],
    401 => ['title' => 'err_401_title', 'desc' => 'err_401_desc'],
    403 => ['title' => 'err_403_title', 'desc' => 'err_403_desc'],
    404 => ['title' => 'err_404_title', 'desc' => 'err_404_desc'],
    500 => ['title' => 'err_500_title', 'desc' => 'err_500_desc'],
    503 => ['title' => 'err_503_title', 'desc' => 'err_503_desc'],
];

// Fallback sicurezza
if (!array_key_exists($code, $errors)) {
    $code = 404;
}

$errorInfo = $errors[$code];

http_response_code($code);

require SRC_PATH . '/templates/header.php';
?>

<div class="flex flex-col items-center justify-center min-h-[70vh] px-4 text-center animate-in fade-in duration-500">

    <div class="bg-zinc-800/50 p-8 rounded-full mb-6 shadow-2xl border border-zinc-700/50 relative group">
        <div class="absolute inset-0 bg-red-500/20 rounded-full blur-xl animate-pulse group-hover:bg-red-500/30 transition-all"></div>

        <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-red-500 relative z-10 drop-shadow-[0_0_15px_rgba(239,68,68,0.5)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
    </div>

    <h1 class="text-9xl font-black text-transparent bg-clip-text bg-gradient-to-br from-zinc-500 via-zinc-200 to-zinc-600 mb-2 select-none drop-shadow-lg tracking-tighter">
        <?= $code ?>
    </h1>

    <h2 class="text-3xl font-bold text-white mb-3 tracking-wide">
        <?= __($errorInfo['title']) ?>
    </h2>

    <p class="text-gray-400 max-w-md mx-auto mb-10 text-lg leading-relaxed">
        <?= __($errorInfo['desc']) ?>
    </p>

    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
        <a href="<?= BASE_URL ?>/dashboard" class="group px-8 py-3 bg-accent hover:bg-blue-600 text-white font-bold rounded-xl shadow-lg shadow-blue-900/20 transition-all transform hover:scale-[1.02] active:scale-[0.98] flex items-center justify-center gap-3">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:-translate-x-1 transition-transform" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            <?= __('err_btn_home') ?>
        </a>

        <?php if ($code !== 403 && $code !== 401): ?>
            <button onclick="history.back()" class="px-8 py-3 bg-zinc-800/80 hover:bg-zinc-700 text-gray-300 font-medium rounded-xl border border-zinc-700 hover:border-zinc-500 transition-all flex items-center justify-center gap-2 backdrop-blur-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                <?= __('err_btn_back') ?>
            </button>
        <?php endif; ?>
    </div>

</div>

<?php require SRC_PATH . '/templates/footer.php'; ?>