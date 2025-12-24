<!DOCTYPE html>
<html lang="<?= Lang::current() ?>" class="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Lavanderia Galvani</title>

    <link rel="icon" type="image/svg+xml" href="<?= BASE_URL ?>/public/img/favicon.svg">
    <link rel="apple-touch-icon" href="<?= BASE_URL ?>/public/img/favicon.svg">
    <meta name="theme-color" content="#1e1e1e">

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        const BASE_URL = "<?= BASE_URL ?>";
        window.TRANSLATIONS = <?= json_encode(Lang::getAll()) ?>;

        function t(key) {
            return window.TRANSLATIONS[key] || key;
        }

        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        dark: '#121212',
                        card: '#1e1e1e',
                        accent: '#0d6efd',
                    }
                }
            }
        }
    </script>

    <style type="text/tailwindcss">
        @layer utilities {
            body { @apply bg-dark text-gray-200 font-sans antialiased; }
            
            /* Slot Base e Logiche (Invariato) */
            .slot { @apply h-14 border border-zinc-800 bg-card flex flex-col items-center justify-center text-sm cursor-pointer relative transition-all duration-200 select-none; }
            .slot:hover { @apply bg-zinc-800; }
            .slot:last-child { @apply rounded-b-lg; }
            .slot.free .status-text { @apply text-green-500; }
            .slot.taken { @apply bg-red-900/20 border-red-900/50 cursor-not-allowed; }
            .slot.taken .status-text { @apply text-red-400 font-medium; }
            .slot.mine { @apply bg-cyan-900/20 border-cyan-900/50 border-l-4 border-l-cyan-500; }
            .slot.mine .status-text { @apply text-cyan-400 font-bold; }
            .slot.pending { @apply bg-yellow-900/20 border-yellow-600/50 animate-pulse; }
            .slot.pending .status-text { @apply text-yellow-500; }
            .slot.past { @apply opacity-40 cursor-default border-zinc-900 !important; }
            .slot.past .time-label { @apply text-zinc-600 !important; }

            .no-scrollbar::-webkit-scrollbar { display: none; }
            .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            .modal-overlay { @apply fixed inset-0 bg-black/80 hidden items-center justify-center z-50 opacity-0 transition-opacity duration-300; }
            .modal-overlay.open { @apply flex opacity-100; }
        }
    </style>

    <!-- <link rel="stylesheet" href="/public/css/styles.css"> -->
</head>

<body class="flex flex-col min-h-screen">

    <header class="sticky top-0 z-40 w-full bg-card/95 backdrop-blur-sm border-b border-zinc-800 shadow-md">
        <div class="max-w-4xl mx-auto px-4 py-3">

            <div class="flex justify-between items-center h-12">

                <a href="<?= BASE_URL ?>/" class="text-xl font-bold text-white tracking-tight flex items-center gap-2 hover:text-accent transition-colors shrink-0">
                    <span>🧺</span> <span>Lavanderia</span>
                </a>

                <?php if (Utils::is_logged()): ?>
                    <nav class="hidden sm:flex items-center gap-1 bg-zinc-900/50 p-1 rounded-lg border border-zinc-800">
                        <a href="<?= BASE_URL ?>/dashboard"
                            class="text-sm font-medium px-4 py-2 rounded transition-all hover:bg-zinc-700 <?= (strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false) ? 'text-white bg-zinc-800 shadow-sm' : 'text-gray-400' ?>">
                            <?= __('nav_dashboard') ?>
                        </a>
                        <?php if (Utils::is_admin()): ?>
                            <a href="<?= BASE_URL ?>/admin"
                                class="text-sm font-medium px-4 py-2 rounded transition-all hover:bg-zinc-700 <?= (strpos($_SERVER['REQUEST_URI'], 'admin') !== false) ? 'text-accent bg-zinc-800 shadow-sm' : 'text-gray-400 hover:text-accent' ?>">
                                <?= __('nav_admin') ?>
                            </a>
                        <?php endif; ?>
                    </nav>
                <?php endif; ?>

                <div class="flex items-center gap-4">

                    <div class="flex items-center gap-2 text-xs sm:text-sm font-bold bg-black/30 px-3 py-1.5 rounded-md border border-zinc-700 shadow-inner">
                        <a href="?lang=it" class="transition-colors <?= Lang::current() == 'it' ? 'text-white' : 'text-zinc-500 hover:text-gray-300' ?>">IT</a>
                        <span class="text-zinc-600">|</span>
                        <a href="?lang=en" class="transition-colors <?= Lang::current() == 'en' ? 'text-white' : 'text-zinc-500 hover:text-gray-300' ?>">EN</a>
                    </div>

                    <?php if (isset($_SESSION['user_id'])): ?>

                        <div class="hidden md:flex flex-col items-end leading-none mr-2">
                            <span class="text-sm font-bold text-gray-200"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        </div>

                        <a href="<?= BASE_URL ?>/logout" title="<?= __('nav_logout') ?>"
                            class="text-red-400 hover:text-white hover:bg-red-600/80 p-2.5 rounded-lg transition-all border border-transparent hover:border-red-500/50">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </a>

                        <button id="mobile-menu-btn" class="sm:hidden text-gray-300 hover:text-white p-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7" />
                            </svg>
                        </button>

                    <?php else: ?>

                        <div class="flex items-center gap-3">
                            <a href="<?= BASE_URL ?>/login" class="text-sm font-medium text-zinc-400 hover:text-white transition-colors">
                                Accedi
                            </a>

                            <a href="/register"
                                class="hidden md:inline-block bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 transition">
                                Registrati
                            </a>
                        </div>

                    <?php endif; ?>

                </div>
            </div>

            <?php if (isset($_SESSION['user_id'])): ?>
                <div id="mobile-menu" class="hidden sm:hidden mt-3 pt-3 border-t border-zinc-800 pb-2 space-y-2">
                    <a href="<?= BASE_URL ?>/dashboard"
                        class="block px-3 py-3 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], 'dashboard') !== false) ? 'bg-zinc-800 text-white' : 'text-gray-400 hover:bg-zinc-800 hover:text-white' ?>">
                        <?= __('nav_dashboard') ?>
                    </a>
                    <?php if ($_SESSION['ruolo'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/admin"
                            class="block px-3 py-3 rounded-md text-base font-medium <?= (strpos($_SERVER['REQUEST_URI'], 'admin') !== false) ? 'bg-zinc-800 text-accent' : 'text-gray-400 hover:bg-zinc-800 hover:text-accent' ?>">
                            <?= __('nav_admin') ?>
                        </a>
                    <?php endif; ?>
                    <div class="px-3 py-2 text-sm text-gray-500 border-t border-zinc-800 mt-2">
                        Loggato come: <span class="text-gray-300 font-bold"><?= htmlspecialchars($_SESSION['username']) ?></span>
                    </div>
                </div>
            <?php endif; ?>

        </div>
    </header>

    <script>
        const btn = document.getElementById('mobile-menu-btn');
        const menu = document.getElementById('mobile-menu');

        if (btn && menu) {
            btn.addEventListener('click', () => {
                menu.classList.toggle('hidden');
            });
        }
    </script>

    <main class="w-full max-w-4xl mx-auto flex-1 p-4">