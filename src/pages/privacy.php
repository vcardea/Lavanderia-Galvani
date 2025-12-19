<?php
require_once __DIR__ . '/../config/database.php';
require SRC_PATH . '/templates/header.php';
?>

<div class="max-w-3xl mx-auto px-4 py-10">

    <div class="mb-10 text-center">
        <h1 class="text-3xl font-bold text-white mb-2">Privacy & Gestione Dati</h1>
        <div class="flex flex-col md:flex-row justify-center items-center gap-2 text-xs text-zinc-500 uppercase tracking-widest">
            <span>Ultimo agg: <?= date('d/m/Y') ?></span>
            <span class="hidden md:inline">•</span>
            <span>Rif. GDPR (UE 2016/679)</span>
        </div>
    </div>

    <div class="space-y-6">

        <section class="bg-card p-6 rounded-xl border border-zinc-800 shadow-md w-full">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                📂 1. Quali dati raccogliamo
            </h2>
            <p class="text-gray-400 mb-4 text-sm leading-relaxed">
                Raccogliamo solo le informazioni strettamente necessarie (Principio di Minimizzazione) per garantire il funzionamento del servizio.
            </p>
            <ul class="list-disc list-inside text-sm text-gray-400 space-y-2 ml-2 marker:text-accent">
                <li><strong class="text-gray-200">Email Istituzionale:</strong> Per identificarti univocamente come studente autorizzato.</li>
                <li><strong class="text-gray-200">Numero Appartamento:</strong> Per comunicazioni urgenti relative all'uso delle macchine.</li>
                <li><strong class="text-gray-200">Password:</strong> Salvata esclusivamente in formato hash (criptato irreversibilmente).</li>
                <li><strong class="text-gray-200">Log di Prenotazione:</strong> Storico di utilizzo per la gestione dei turni.</li>
            </ul>
        </section>

        <section class="bg-card p-6 rounded-xl border border-zinc-800 shadow-md w-full">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                ⚙️ 2. Finalità del trattamento
            </h2>
            <div class="space-y-4 text-sm text-gray-400">
                <p>I tuoi dati servono esclusivamente a:</p>
                <ul class="list-disc list-inside space-y-1 ml-2 marker:text-accent">
                    <li>Gestire l'accesso sicuro (Login).</li>
                    <li>Organizzare il calendario ed evitare conflitti tra inquilini.</li>
                    <li>Garantire il rispetto delle regole comuni.</li>
                </ul>
                
                <div class="bg-blue-900/10 border border-blue-900/30 p-4 rounded-lg text-blue-300 text-xs leading-relaxed mt-4">
                    <strong>Nota sulla visibilità:</strong> I tuoi dati non vengono ceduti a terzi. 
                    Tuttavia, il tuo <em>Username</em> sarà visibile agli altri studenti sul calendario prenotazioni per permettere l'organizzazione interna (es. scambi turno).
                </div>
            </div>
        </section>

        <section class="bg-card p-6 rounded-xl border border-zinc-800 shadow-md w-full">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                🗑️ 3. Cancellazione e Diritto all'Oblio
            </h2>
            <p class="text-gray-400 mb-6 text-sm leading-relaxed">
                Puoi richiedere la cancellazione del tuo account in qualsiasi momento (Art. 17 GDPR). 
                Quando un account viene eliminato, applichiamo una procedura di <strong>Anonimizzazione</strong>.
            </p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="bg-red-500/5 p-4 rounded-lg border border-red-500/20">
                    <h3 class="font-bold text-red-400 mb-2 text-sm flex items-center gap-2">
                        ❌ Dati Eliminati
                    </h3>
                    <ul class="text-xs text-gray-400 space-y-1 ml-1">
                        <li>• Email e Password</li>
                        <li>• Associazione col numero appartamento</li>
                        <li>• Tutte le prenotazioni future</li>
                    </ul>
                </div>
                
                <div class="bg-green-500/5 p-4 rounded-lg border border-green-500/20">
                    <h3 class="font-bold text-green-400 mb-2 text-sm flex items-center gap-2">
                        ✅ Dati Anonimizzati
                    </h3>
                    <ul class="text-xs text-gray-400 space-y-1 ml-1">
                        <li>• Lo storico delle prenotazioni passate rimane per statistica.</li>
                        <li>• L'autore diventa genericamente <em>"Utente Cancellato"</em>.</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="bg-card p-6 rounded-xl border border-zinc-800 shadow-md w-full">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                🛡️ 4. I tuoi Diritti
            </h2>
            <p class="text-sm text-gray-400 mb-3">
                In conformità al GDPR (Art. 15-22), hai diritto di chiedere al gestore:
            </p>
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-gray-300">
                <li class="flex items-center gap-2 bg-zinc-900/50 p-2 rounded border border-zinc-800/50">
                    <span>📄</span> L'accesso ai tuoi dati
                </li>
                <li class="flex items-center gap-2 bg-zinc-900/50 p-2 rounded border border-zinc-800/50">
                    <span>✏️</span> La rettifica di dati errati
                </li>
                <li class="flex items-center gap-2 bg-zinc-900/50 p-2 rounded border border-zinc-800/50">
                    <span>🚫</span> La limitazione del trattamento
                </li>
                <li class="flex items-center gap-2 bg-zinc-900/50 p-2 rounded border border-zinc-800/50">
                    <span>📦</span> La portabilità dei dati
                </li>
            </ul>
            <p class="text-xs text-gray-500 mt-4">
                Per esercitare i diritti: <a href="mailto:galvani@er-go.it" class="text-accent hover:underline">galvani@er-go.it</a>
            </p>
        </section>

        <section class="bg-card p-6 rounded-xl border border-zinc-800 shadow-md w-full">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                🍪 5. Cookie Policy
            </h2>
            <p class="text-sm text-gray-400 leading-relaxed">
                Utilizziamo esclusivamente <strong>Cookie Tecnici</strong> (Session ID) necessari per mantenere l'accesso attivo mentre navighi. 
                Non effettuiamo tracciamento, profilazione pubblicitaria né usiamo analytics di terze parti invasive.
            </p>
        </section>

    </div>

    <div class="mt-10 text-center border-t border-zinc-800 pt-8">
        <a href="<?= BASE_URL ?>/dashboard" class="text-zinc-500 hover:text-white transition-colors text-sm font-medium inline-flex items-center gap-2 px-4 py-2 hover:bg-zinc-900 rounded-lg">
            &larr; Torna alla Dashboard
        </a>
    </div>

</div>

<?php require SRC_PATH . '/templates/footer.php'; ?>