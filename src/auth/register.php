<?php
require_once SRC_PATH . '/config/database.php';

$error = '';

// Variabili per Data Retention
$email_input = '';
$apt_input = '';
$generated_username = ''; // Per mantenere lo username se c'è un errore

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $database = new Database();
    $db = $database->getConnection();

    // Recupero input
    $email_input = trim($_POST['email'] ?? '');
    $apt_input = trim($_POST['appartamento'] ?? '');
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';
    // Ora recuperiamo lo username generato dal client
    $username_input = trim($_POST['username'] ?? '');

    // 1. Validazione
    if ($apt_input < 1 || $apt_input > 23) {
        $error = "Il numero appartamento deve essere compreso tra 1 e 23.";
    } elseif (!preg_match('/^[a-zA-Z0-9.]+@(studio\.unibo\.it|unibo\.it)$/', $email_input)) {
        $error = "Devi usare una mail istituzionale (@studio.unibo.it o @unibo.it)";
    } elseif (empty($username_input)) {
        $error = "Lo username non è stato generato correttamente.";
    } elseif (!preg_match('/^[a-zA-Z]+[0-9]{1,2}-[0-9]{2}$/', $username_input)) {
        $error = "Lo username non è valido.";
    } elseif (strlen($password) < 8) {
        $error = "La password deve essere di almeno 8 caratteri.";
    } elseif ($password !== $password_confirm) {
        $error = "Le password non coincidono.";
    } else {
        // 2. Verifica duplicati (Email o Username) nel DB
        $stmt = $db->prepare("SELECT idutente FROM utenti WHERE email = ? OR username = ?");
        $stmt->execute([$email_input, $username_input]);

        if ($stmt->rowCount() > 0) {
            $error = "Email già registrata o Username ($username_input) non disponibile. Riprova.";
        } else {
            // 3. Inserimento
            $parts = explode('@', $email_input);
            $localPart = $parts[0];
            $nameParts = explode('.', $localPart);
            $nomeReale = ucfirst($nameParts[0]);

            $hash = password_hash($password, PASSWORD_BCRYPT);

            $sql = "INSERT INTO utenti (email, password_hash, nome, numero_appartamento, username) VALUES (?, ?, ?, ?, ?)";
            $insert = $db->prepare($sql);

            if ($insert->execute([$email_input, $hash, $nomeReale, $apt_input, $username_input])) {
                $_SESSION['user_id'] = $db->lastInsertId();
                $_SESSION['username'] = $username_input;
                $_SESSION['ruolo'] = 'user';
                header("Location: " . BASE_URL . "/dashboard");
                exit;
            } else {
                $error = "Errore generico nel database.";
            }
        }
    }
}

require SRC_PATH . '/templates/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center px-4 py-8">
    <div class="w-full max-w-md bg-card p-8 rounded-xl shadow-2xl border border-zinc-800">
        <h2 class="text-2xl font-bold text-center mb-6 text-white">
            <?= __('register_title') ?>
        </h2>

        <?php if (!empty($error)): ?>
            <div class="bg-red-900/30 border border-red-800 text-red-300 p-3 rounded mb-4 text-sm text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/register" class="space-y-4">

            <div class="grid grid-cols-12 gap-3">
                <div class="col-span-8">
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase">
                        <?= __('email_label') ?>
                    </label>
                    <input type="email" name="email" id="emailInput" placeholder="nome.cognome@studio..." required
                        value="<?= htmlspecialchars($email_input) ?>"
                        oninput="generateUsername()"
                        class="w-full bg-zinc-800 border border-zinc-700 rounded p-3 text-white focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all placeholder-zinc-600 text-sm">
                </div>

                <div class="col-span-4">
                    <label class="block text-xs font-medium text-gray-400 mb-1 uppercase">
                        <?= __('apt_label') ?>
                    </label>
                    <input type="number" name="appartamento" id="aptInput" placeholder="12" required
                        value="<?= htmlspecialchars($apt_input) ?>"
                        min="1" max="23"
                        oninput="generateUsername()"
                        class="w-full bg-zinc-800 border border-zinc-700 rounded p-3 text-white focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all placeholder-zinc-600 text-sm text-center">
                </div>
            </div>

            <div class="relative group">
                <label class="block text-xs font-medium text-accent mb-1 uppercase flex justify-between">
                    Username (Generato)
                    <span class="text-[10px] text-gray-500 lowercase font-normal">sarà il tuo nome utente</span>
                </label>
                <div class="relative">
                    <input type="text" name="username" id="usernameOutput" readonly required
                        value="<?= htmlspecialchars($generated_username) ?>"
                        class="w-full bg-zinc-900/50 border border-zinc-700/50 border-dashed rounded p-3 text-gray-300 font-mono text-sm cursor-not-allowed select-all focus:outline-none">

                    <button type="button" onclick="regenerateRandom()" title="Cambia numero casuale" class="absolute right-2 top-2 p-1.5 text-zinc-500 hover:text-white transition-colors rounded-md hover:bg-zinc-700">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </button>
                </div>
            </div>

            <hr class="border-zinc-800 my-4">

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1 uppercase">
                    <?= __('password_label') ?>
                </label>
                <div class="relative">
                    <input type="password" name="password" id="pass1" required
                        oninput="checkMatch()"
                        class="w-full bg-zinc-800 border border-zinc-700 rounded p-3 text-white focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all pr-10">
                    <button type="button" onclick="togglePass('pass1', 'eye1Closed', 'eye1Open')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-white transition-colors">
                        <svg id="eye1Closed" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                        <svg id="eye1Open" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1 uppercase">
                    Conferma Password
                </label>
                <div class="relative">
                    <input type="password" name="password_confirm" id="pass2" required
                        oninput="checkMatch()"
                        class="w-full bg-zinc-800 border border-zinc-700 rounded p-3 text-white focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all pr-10">
                    <button type="button" onclick="togglePass('pass2', 'eye2Closed', 'eye2Open')" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-white transition-colors">
                        <svg id="eye2Closed" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                        <svg id="eye2Open" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
                <p id="matchError" class="text-xs text-red-400 mt-1 hidden">⚠ Le password non coincidono</p>
                <p id="matchSuccess" class="text-xs text-green-500 mt-1 hidden">✓ Le password coincidono</p>
            </div>

            <button type="submit" id="submitBtn" class="w-full bg-accent hover:bg-blue-600 text-white font-bold py-3 rounded transition-colors shadow-lg shadow-blue-900/20">
                <?= __('btn_register') ?>
            </button>
        </form>

        <p class="text-center text-sm text-gray-500 mt-6">
            <?= __('link_have_account') ?>
            <a href="<?= BASE_URL ?>/login" class="text-accent hover:underline">
                <?= __('link_login_here') ?>
            </a>
        </p>
    </div>
</div>

<script>
    // Variabile globale per mantenere il numero random stabile mentre si digita l'email
    let currentRandom = Math.floor(Math.random() * 90) + 10; // Random tra 10 e 99

    function generateUsername() {
        const email = document.getElementById('emailInput').value;
        const apt = document.getElementById('aptInput').value;
        const output = document.getElementById('usernameOutput');

        // Se mancano dati essenziali, svuota o metti placeholder
        if (!email || !apt) {
            output.value = '...';
            return;
        }

        // Estrai nome utente dalla mail (es. vincenzo da vincenzo.cardea@...)
        let namePart = email.split('@')[0]; // vincenzo.cardea
        namePart = namePart.split('.')[0]; // vincenzo

        // Pulisci caratteri strani per sicurezza
        namePart = namePart.replace(/[^a-zA-Z0-9]/g, '').toLowerCase();

        // Genera stringa finale: nome + apt + "-" + random
        // Es: vincenzo12-45
        output.value = `${namePart}${apt}-${currentRandom}`;
    }

    // Funzione manuale per cambiare solo il numero random se all'utente non piace
    function regenerateRandom() {
        currentRandom = Math.floor(Math.random() * 90) + 10;
        generateUsername(); // Aggiorna la vista
    }

    // --- LE TUE FUNZIONI ESISTENTI PER PASSWORD ---
    function togglePass(inputId, iconClosedId, iconOpenId) {
        const input = document.getElementById(inputId);
        const iconClosed = document.getElementById(iconClosedId);
        const iconOpen = document.getElementById(iconOpenId);

        if (input.type === "password") {
            input.type = "text";
            iconClosed.classList.add('hidden');
            iconOpen.classList.remove('hidden');
        } else {
            input.type = "password";
            iconClosed.classList.remove('hidden');
            iconOpen.classList.add('hidden');
        }
    }

    function checkMatch() {
        const p1 = document.getElementById('pass1').value;
        const p2 = document.getElementById('pass2').value;
        const errorMsg = document.getElementById('matchError');
        const successMsg = document.getElementById('matchSuccess');
        const btn = document.getElementById('submitBtn');
        const p2Input = document.getElementById('pass2');

        if (p2.length === 0) {
            errorMsg.classList.add('hidden');
            successMsg.classList.add('hidden');
            p2Input.classList.remove('border-red-500', 'border-green-500', 'border-zinc-700');
            p2Input.classList.add('border-zinc-700');
            return;
        }

        if (p1 !== p2) {
            errorMsg.classList.remove('hidden');
            successMsg.classList.add('hidden');
            p2Input.classList.remove('border-zinc-700', 'border-green-500');
            p2Input.classList.add('border-red-500');
            btn.classList.add('opacity-50', 'cursor-not-allowed');
        } else {
            errorMsg.classList.add('hidden');
            successMsg.classList.remove('hidden');
            p2Input.classList.remove('border-zinc-700', 'border-red-500');
            p2Input.classList.add('border-green-500');
            btn.classList.remove('opacity-50', 'cursor-not-allowed');
        }
    }

    // Inizializza al caricamento se ci sono dati salvati (Data Retention)
    document.addEventListener('DOMContentLoaded', () => {
        if (document.getElementById('emailInput').value) {
            generateUsername();
        }
    });
</script>

<?php require SRC_PATH . '/templates/footer.php'; ?>