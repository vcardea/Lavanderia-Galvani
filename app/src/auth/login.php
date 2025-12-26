<?php
require_once SRC_PATH . '/config/database.php';

$error = '';
$email_input = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_input = trim($_POST['email']) ?? '';
    $password = trim($_POST['password']) ?? '';

    $database = new Database();
    $db = $database->getConnection();

    $stmt = $db->prepare("SELECT idutente, username, password_hash, ruolo FROM utenti WHERE email = :email");
    $stmt->execute([':email' => $email_input]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['idutente'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['ruolo'] = $user['ruolo'];
        header("Location: " . BASE_URL . "/dashboard");
        exit;
    } else {
        // TRADUZIONE APPLICATA QUI
        $error = __('error_creds');
    }
}

require SRC_PATH . '/templates/header.php';
?>
<div class="min-h-[80vh] flex items-center justify-center px-4">
    <div class="w-full max-w-md bg-card p-8 rounded-xl shadow-2xl border border-zinc-800">
        <h2 class="text-2xl font-bold text-center mb-6 text-white">
            <?= __('login_title') ?>
        </h2>

        <?php if (!empty($error)): ?>
            <div class="bg-red-900/30 border border-red-800 text-red-300 p-3 rounded mb-4 text-sm text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/login" class="space-y-4">
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1 uppercase">
                    <?= __('email_label') ?>
                </label>
                <input type="email" name="email" required
                    value="<?= htmlspecialchars($email_input) ?>"
                    class="w-full bg-zinc-800 border border-zinc-700 rounded p-3 text-white focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all placeholder-zinc-600">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1 uppercase">
                    <?= __('password_label') ?>
                </label>
                <div class="relative">
                    <input type="password" name="password" id="passwordInput" required
                        class="w-full bg-zinc-800 border border-zinc-700 rounded p-3 text-white focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition-all pr-10">
                    <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-400 hover:text-white transition-colors focus:outline-none">
                        <svg id="eyeIconClosed" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                        </svg>
                        <svg id="eyeIconOpen" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                    </button>
                </div>
            </div>
            <button type="submit" class="w-full bg-accent hover:bg-blue-600 text-white font-bold py-3 rounded transition-colors shadow-lg shadow-blue-900/20">
                <?= __('btn_enter') ?>
            </button>
        </form>
        <p class="text-center text-sm text-gray-500 mt-6">
            <?= __('link_no_account') ?>
            <a href="<?= BASE_URL ?>/register" class="text-accent hover:underline">
                <?= __('link_create_account') ?>
            </a>
        </p>
    </div>
</div>
<script>
    function togglePassword() {
        const input = document.getElementById('passwordInput');
        const iconClosed = document.getElementById('eyeIconClosed');
        const iconOpen = document.getElementById('eyeIconOpen');
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
</script>
<?php require SRC_PATH . '/templates/footer.php'; ?>