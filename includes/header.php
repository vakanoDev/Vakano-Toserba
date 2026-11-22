<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';

requireLogin();

$current_page = basename($_SERVER['PHP_SELF']);
$base_url = getBasePath();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? 'Vakano Toserba' ?> - Sistem Manajemen Toko</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            200: '#a7f3d0',
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
    <!-- Google Font Plus Jakarta Sans -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased min-h-screen flex flex-col">

    <!-- Top Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30 shadow-xs">
        <div class="px-4 lg:px-6 h-16 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <button id="sidebarToggle" class="lg:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100">
                    <i class="fa-solid fa-bars text-lg"></i>
                </button>
                <a href="<?= $base_url . (isSuperAdmin() ? 'dashboard.php' : 'pos.php') ?>" class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-emerald-600 to-teal-500 text-white flex items-center justify-center shadow-md shadow-emerald-500/20">
                        <i class="fa-solid fa-cart-shopping text-sm"></i>
                    </div>
                    <div>
                        <span class="font-extrabold text-lg text-slate-800 tracking-tight">Vakano<span class="text-emerald-600">Toserba</span></span>
                    </div>
                </a>
            </div>

            <!-- Right user info & quick actions -->
            <div class="flex items-center gap-3">
                <!-- Quick POS button for Super Admin -->
                <?php if (isSuperAdmin() && $current_page !== 'pos.php'): ?>
                    <a href="<?= $base_url ?>pos.php" class="hidden sm:inline-flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-semibold text-xs border border-emerald-200 transition">
                        <i class="fa-solid fa-cash-register"></i>
                        <span>Buka Kasir (POS)</span>
                    </a>
                <?php endif; ?>

                <div class="h-6 w-px bg-slate-200 hidden sm:block"></div>

                <!-- User profile badge -->
                <div class="flex items-center gap-2.5">
                    <div class="w-9 h-9 rounded-full bg-emerald-100 border border-emerald-300 text-emerald-800 font-bold flex items-center justify-center text-xs">
                        <?= strtoupper(substr(getUsername(), 0, 2)) ?>
                    </div>
                    <div class="hidden sm:block text-left">
                        <div class="text-xs font-bold text-slate-800 leading-tight"><?= htmlspecialchars(getUsername()) ?></div>
                        <div class="text-[10px] font-semibold uppercase tracking-wider <?= isSuperAdmin() ? 'text-emerald-600' : 'text-teal-600' ?>">
                            <?= htmlspecialchars(getRoleName()) ?>
                        </div>
                    </div>
                    <a href="<?= $base_url ?>logout.php" title="Keluar" onclick="return confirm('Apakah Anda yakin ingin keluar?');" class="ml-1 p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <div class="flex flex-1 overflow-hidden">
        <!-- Sidebar Navigation -->
        <?php include __DIR__ . '/sidebar.php'; ?>

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 lg:p-6 pb-16">
