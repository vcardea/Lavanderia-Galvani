<?php
require_once __DIR__ . '/../config/database.php';
require SRC_PATH . '/templates/header.php';
?>

<div class="max-w-3xl mx-auto px-4 py-10">

    <div class="mb-10 text-center">
        <h1 class="text-3xl font-bold text-white mb-2"><?= __('priv_title') ?></h1>
        <div class="flex flex-col md:flex-row justify-center items-center gap-2 text-xs text-zinc-500 uppercase tracking-widest">
            <span><?= __('priv_last_update') ?> <?= date('d/m/Y') ?></span>
            <span class="hidden md:inline">•</span>
            <span><?= __('priv_ref_gdpr') ?></span>
        </div>
    </div>

    <div class="space-y-6">

        <section class="bg-card p-6 rounded-xl border border-zinc-800 shadow-md w-full">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                📂 <?= __('priv_s1_title') ?>
            </h2>
            <p class="text-gray-400 mb-4 text-sm leading-relaxed">
                <?= __('priv_s1_desc') ?>
            </p>
            <ul class="list-disc list-inside text-sm text-gray-400 space-y-2 ml-2 marker:text-accent">
                <li><strong class="text-gray-200"><?= __('priv_data_email') ?>:</strong> <?= __('priv_data_email_desc') ?></li>
                <li><strong class="text-gray-200"><?= __('priv_data_apt') ?>:</strong> <?= __('priv_data_apt_desc') ?></li>
                <li><strong class="text-gray-200"><?= __('priv_data_pass') ?>:</strong> <?= __('priv_data_pass_desc') ?></li>
                <li><strong class="text-gray-200"><?= __('priv_data_log') ?>:</strong> <?= __('priv_data_log_desc') ?></li>
            </ul>
        </section>

        <section class="bg-card p-6 rounded-xl border border-zinc-800 shadow-md w-full">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                ⚙️ <?= __('priv_s2_title') ?>
            </h2>
            <div class="space-y-4 text-sm text-gray-400">
                <p><?= __('priv_s2_intro') ?></p>
                <ul class="list-disc list-inside space-y-1 ml-2 marker:text-accent">
                    <li><?= __('priv_use_1') ?></li>
                    <li><?= __('priv_use_2') ?></li>
                    <li><?= __('priv_use_3') ?></li>
                </ul>

                <div class="bg-blue-900/10 border border-blue-900/30 p-4 rounded-lg text-blue-300 text-xs leading-relaxed mt-4">
                    <strong><?= __('priv_note_vis_title') ?></strong> <?= __('priv_note_vis_desc') ?>
                </div>
            </div>
        </section>

        <section class="bg-card p-6 rounded-xl border border-zinc-800 shadow-md w-full">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                🗑️ <?= __('priv_s3_title') ?>
            </h2>
            <p class="text-gray-400 mb-6 text-sm leading-relaxed">
                <?= __('priv_s3_desc') ?>
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-red-500/5 p-4 rounded-lg border border-red-500/20">
                    <h3 class="font-bold text-red-400 mb-2 text-sm flex items-center gap-2">
                        ❌ <?= __('priv_del_title') ?>
                    </h3>
                    <ul class="text-xs text-gray-400 space-y-1 ml-1">
                        <li>• <?= __('priv_del_1') ?></li>
                        <li>• <?= __('priv_del_2') ?></li>
                        <li>• <?= __('priv_del_3') ?></li>
                    </ul>
                </div>

                <div class="bg-green-500/5 p-4 rounded-lg border border-green-500/20">
                    <h3 class="font-bold text-green-400 mb-2 text-sm flex items-center gap-2">
                        ✅ <?= __('priv_anon_title') ?>
                    </h3>
                    <ul class="text-xs text-gray-400 space-y-1 ml-1">
                        <li>• <?= __('priv_anon_1') ?></li>
                        <li>• <?= __('priv_anon_2') ?></li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="bg-card p-6 rounded-xl border border-zinc-800 shadow-md w-full">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                🛡️ <?= __('priv_s4_title') ?>
            </h2>
            <p class="text-sm text-gray-400 mb-3">
                <?= __('priv_s4_desc') ?>
            </p>
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-300">
                <li class="flex items-center gap-2 bg-zinc-900/50 p-2 rounded border border-zinc-800/50">
                    <span>📄</span> <?= __('priv_right_access') ?>
                </li>
                <li class="flex items-center gap-2 bg-zinc-900/50 p-2 rounded border border-zinc-800/50">
                    <span>✏️</span> <?= __('priv_right_rect') ?>
                </li>
                <li class="flex items-center gap-2 bg-zinc-900/50 p-2 rounded border border-zinc-800/50">
                    <span>🚫</span> <?= __('priv_right_limit') ?>
                </li>
                <li class="flex items-center gap-2 bg-zinc-900/50 p-2 rounded border border-zinc-800/50">
                    <span>📦</span> <?= __('priv_right_port') ?>
                </li>
            </ul>
            <p class="text-xs text-gray-500 mt-4">
                <?= __('priv_contact_text') ?> <a href="mailto:galvani@er-go.it" class="text-accent hover:underline">galvani@er-go.it</a>
            </p>
        </section>

        <section class="bg-card p-6 rounded-xl border border-zinc-800 shadow-md w-full">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                🍪 <?= __('priv_s5_title') ?>
            </h2>
            <p class="text-sm text-gray-400 leading-relaxed">
                <?= __('priv_s5_desc') ?>
            </p>
        </section>

    </div>

    <div class="mt-10 text-center border-t border-zinc-800 pt-8">
        <a href="<?= BASE_URL ?>/dashboard" class="text-zinc-500 hover:text-white transition-colors text-sm font-medium inline-flex items-center gap-2 px-4 py-2 hover:bg-zinc-900 rounded-lg">
            &larr; <?= __('btn_back_dashboard') ?>
        </a>
    </div>

</div>

<?php require SRC_PATH . '/templates/footer.php'; ?>