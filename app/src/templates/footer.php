</main>

<footer class="mt-auto w-full border-t border-zinc-800 bg-black/60 backdrop-blur-xl pt-12 pb-8">
    <div class="max-w-6xl mx-auto px-6">

        <div class="grid grid-cols-1 md:grid-cols-12 gap-10 mb-12 border-b border-zinc-800/50 pb-10">

            <div class="md:col-span-4 space-y-4 text-center md:text-left">
                <div class="flex items-center justify-center md:justify-start gap-2">
                    <span class="text-2xl">🧺</span>
                    <h4 class="text-xl font-bold text-white tracking-tight">
                        Lavanderia Galvani
                    </h4>
                </div>
                <p class="text-sm text-zinc-500 leading-relaxed max-w-sm mx-auto md:mx-0">
                    <?= __('footer_desc') ?>
                </p>
                <div class="md:hidden flex justify-center pt-2">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-zinc-900 border border-zinc-800">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                        </span>
                        <span class="text-xs font-mono font-medium text-zinc-400">System Online</span>
                    </div>
                </div>
            </div>

            <div class="md:col-span-3 text-center md:text-left">
                <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-5">
                    <?= __('footer_support') ?>
                </h5>
                <ul class="space-y-3 text-sm text-zinc-400">
                    <li>
                        <a href="https://www.er-go.it/esplora-i-benefici/benefici-erogati-da-er.go/alloggio/regolamento_residenze_2022_it.pdf/@@display-file/file/regolamento_residenze_2022_it.pdf" target="_blank" class="hover:text-accent hover:text-white transition-colors flex items-center justify-center md:justify-start gap-2 group">
                            <svg class="w-4 h-4 text-zinc-600 group-hover:text-accent transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <?= __('footer_rules') ?>
                        </a>
                    </li>
                    <li>
                        <a href="<?= BASE_URL ?>/privacy" class="hover:text-accent hover:text-white transition-colors flex items-center justify-center md:justify-start gap-2 group">
                            <svg class="w-4 h-4 text-zinc-600 group-hover:text-accent transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            <?= __('footer_privacy') ?>
                        </a>
                    </li>
                    <li>
                        <a href="mailto:galvani@er-go.it" class="hover:text-accent hover:text-white transition-colors flex items-center justify-center md:justify-start gap-2 group">
                            <svg class="w-4 h-4 text-zinc-600 group-hover:text-accent transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                            <?= __('footer_report') ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="md:col-span-3 text-center md:text-left">
                <h5 class="text-xs font-bold text-white uppercase tracking-wider mb-5">
                    <?= __('footer_source_code') ?>
                </h5>
                <ul class="space-y-3 text-sm text-zinc-400">
                    <li>
                        <a href="https://github.com/vcardea/Lavanderia-Galvani" target="_blank" class="hover:text-white transition-colors flex items-center justify-center md:justify-start gap-2 group">
                            <svg class="w-4 h-4 text-zinc-600 group-hover:text-white transition-colors" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z" />
                            </svg>
                            Github Repo
                        </a>
                    </li>
                </ul>
            </div>

            <div class="hidden md:flex md:col-span-2 flex-col items-end">
                <h5 class="text-xs font-bold text-gray-300 uppercase tracking-widest mb-4">
                    <?= __('footer_status') ?>
                </h5>

                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-zinc-900/50 border border-zinc-800 hover:border-zinc-700 transition-colors cursor-default">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
                    </span>
                    <span class="text-xs font-mono font-medium text-green-500">
                        <?= __('footer_operational') ?>
                    </span>
                </div>
                <p class="text-[10px] text-zinc-600 mt-2 font-mono">
                    SRV: <?= date('H:i') ?>
                </p>
            </div>
        </div>

        <div class="border-zinc-800/50 flex flex-col items-center justify-center gap-3 text-xs">
            <a href="https://github.com/vcardea" target="_blank" class="group flex items-center gap-1.5 bg-zinc-900/30 px-3 py-1.5 rounded-lg border border-transparent hover:border-zinc-800 hover:bg-zinc-900 transition-all">
                <span class="text-zinc-500 group-hover:text-zinc-300"><?= __('footer_coded') ?></span>
                <span class="text-red-500/70 group-hover:text-red-500 animate-pulse text-sm">❤</span>
                <span class="text-zinc-500 group-hover:text-zinc-300"><?= __('footer_by') ?></span>
                <span class="text-zinc-500 group-hover:text-white transition-colors">Vincenzo Cardea</span>
            </a>
        </div>
    </div>
</footer>

<script src="<?= BASE_URL ?>/public/js/app.js?v=<?= time() ?>"></script>
</body>

</html>