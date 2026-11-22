<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

if (isLoggedIn()) {
    if (isSuperAdmin()) {
        header('Location: dashboard.php');
    } else {
        header('Location: pos.php');
    }
    exit;
}

$error = '';
$info = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        $stmt = $pdo->prepare("
            SELECT u.iduser, u.username, u.password, u.idrole, r.nama_role
            FROM user u
            JOIN role r ON u.idrole = r.idrole
            WHERE u.username = ?
            LIMIT 1
        ");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && verifyUserPassword($password, $user['password'])) {
            // Role check: Hanya Super Admin (1) dan Kasir (2) yang diizinkan saat ini
            if ($user['idrole'] == 1 || $user['idrole'] == 2) {
                $_SESSION['iduser']    = $user['iduser'];
                $_SESSION['username']  = $user['username'];
                $_SESSION['idrole']    = (int)$user['idrole'];
                $_SESSION['nama_role'] = $user['nama_role'];

                if ($user['idrole'] == 1) {
                    header('Location: dashboard.php');
                } else {
                    header('Location: pos.php');
                }
                exit;
            } else {
                $error = 'Akses untuk role "' . htmlspecialchars($user['nama_role']) . '" belum dibuka. Saat ini sistem hanya diaktifkan untuk Super Admin & Kasir.';
            }
        } else {
            $error = 'Username atau password tidak sesuai!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Fast Mart</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            800: '#065f46',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Font Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-gradient-to-br from-emerald-950 via-emerald-900 to-teal-950 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 mb-3 shadow-lg shadow-emerald-500/10">
                <i class="fa-solid fa-cart-shopping text-3xl"></i>
            </div>
            <h1 class="text-3xl font-extrabold text-white tracking-tight">Fast <span class="text-emerald-400">Mart</span></h1>
            <p class="text-emerald-200/70 text-sm mt-1">Sistem Manajemen Toko & Point of Sale</p>
        </div>

        <!-- Card Login -->
        <div class="bg-white/95 backdrop-blur-md rounded-3xl p-8 shadow-2xl border border-emerald-100">
            <h2 class="text-xl font-bold text-gray-800 mb-2">Masuk ke Sistem</h2>
            <p class="text-sm text-gray-500 mb-6">Silakan masukkan akun Anda untuk melanjutkan</p>

            <?php if (!empty($error)): ?>
                <div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-700 text-sm flex items-start gap-3">
                    <i class="fa-solid fa-triangle-exclamation text-rose-500 mt-0.5"></i>
                    <div><?= $error ?></div>
                </div>
            <?php endif; ?>

            <form method="POST" action="login.php" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Username</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-600">
                            <i class="fa-solid fa-user"></i>
                        </span>
                        <input type="text" name="username" required autofocus
                            placeholder="Masukkan username"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            class="w-full pl-11 pr-4 py-3 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-600 uppercase tracking-wider mb-2">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-4 text-emerald-600">
                            <i class="fa-solid fa-lock"></i>
                        </span>
                        <input type="password" name="password" id="passwordInput" required
                            placeholder="Masukkan password"
                            class="w-full pl-11 pr-11 py-3 bg-gray-50 rounded-xl border border-gray-200 text-gray-800 placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:bg-white transition text-sm">
                        <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-gray-400 hover:text-gray-600">
                            <i class="fa-solid fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full mt-2 py-3.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-xl shadow-lg shadow-emerald-600/30 transition duration-150 flex items-center justify-center gap-2 group">
                    <span>Masuk</span>
                    <i class="fa-solid fa-arrow-right text-sm group-hover:translate-x-1 transition"></i>
                </button>
            </form>
 </div>
        <!-- Footer -->
        <p class="text-center text-xs text-emerald-200/50 mt-6">
            &copy; <?= date('Y') ?> Fast Mart Store System. All rights reserved.
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('passwordInput');
            const icon = document.getElementById('eyeIcon');
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
