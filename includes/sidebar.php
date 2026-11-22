<?php
$current_page = basename($_SERVER['PHP_SELF']);
$base_url = getBasePath();

function isActiveMenu($pages, $current_page) {
    if (is_array($pages)) {
        return in_array($current_page, $pages) ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-emerald-700';
    }
    return ($pages === $current_page) ? 'bg-emerald-600 text-white font-semibold shadow-sm' : 'text-slate-600 hover:bg-slate-100 hover:text-emerald-700';
}
?>
<!-- Sidebar Overlay for Mobile -->
<div id="sidebarBackdrop" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs z-40 hidden lg:hidden"></div>

<aside id="sidebar" class="fixed lg:static inset-y-0 left-0 z-40 w-64 bg-white border-r border-slate-200 flex flex-col justify-between transform -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out">
    <div class="p-4 overflow-y-auto space-y-6">

        <!-- Role Badge Header inside Sidebar -->
        <div class="p-3.5 rounded-2xl bg-gradient-to-br <?= isSuperAdmin() ? 'from-emerald-50 to-teal-50 border border-emerald-200/60' : 'from-teal-50 to-cyan-50 border border-teal-200/60' ?>">
            <div class="flex items-center gap-2.5">
                <div class="w-8 h-8 rounded-xl <?= isSuperAdmin() ? 'bg-emerald-600 text-white' : 'bg-teal-600 text-white' ?> flex items-center justify-center text-xs shadow-sm">
                    <i class="fa-solid <?= isSuperAdmin() ? 'fa-shield-halved' : 'fa-cash-register' ?>"></i>
                </div>
                <div>
                    <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500">Mode Akses</div>
                    <div class="text-xs font-extrabold text-slate-800"><?= htmlspecialchars(getRoleName()) ?></div>
                </div>
            </div>
        </div>

        <?php if (isSuperAdmin()): ?>
            <!-- SUPER ADMIN MENUS -->
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 mb-2">Utama</div>
                <div class="space-y-1 text-sm">
                    <a href="<?= $base_url ?>dashboard.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('dashboard.php', $current_page) ?>">
                        <i class="fa-solid fa-chart-pie w-5 text-center text-sm"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="<?= $base_url ?>pos.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('pos.php', $current_page) ?>">
                        <i class="fa-solid fa-cash-register w-5 text-center text-sm"></i>
                        <span>Kasir (POS)</span>
                    </a>
                    <a href="<?= $base_url ?>pages/penjualan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('penjualan.php', $current_page) ?>">
                        <i class="fa-solid fa-receipt w-5 text-center text-sm"></i>
                        <span>Riwayat Penjualan</span>
                    </a>
                </div>
            </div>

            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 mb-2">Inventory & Transaksi</div>
                <div class="space-y-1 text-sm">
                    <a href="<?= $base_url ?>pages/kartu_stok.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('kartu_stok.php', $current_page) ?>">
                        <i class="fa-solid fa-boxes-stacked w-5 text-center text-sm"></i>
                        <span>Kartu Stok</span>
                    </a>
                    <a href="<?= $base_url ?>pages/pengadaan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('pengadaan.php', $current_page) ?>">
                        <i class="fa-solid fa-truck-ramp-box w-5 text-center text-sm"></i>
                        <span>Pengadaan (PO)</span>
                    </a>
                    <a href="<?= $base_url ?>pages/penerimaan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('penerimaan.php', $current_page) ?>">
                        <i class="fa-solid fa-box-open w-5 text-center text-sm"></i>
                        <span>Penerimaan Barang</span>
                    </a>
                    <a href="<?= $base_url ?>pages/retur.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('retur.php', $current_page) ?>">
                        <i class="fa-solid fa-rotate-left w-5 text-center text-sm"></i>
                        <span>Retur Barang</span>
                    </a>
                </div>
            </div>

            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 mb-2">Master Data</div>
                <div class="space-y-1 text-sm">
                    <a href="<?= $base_url ?>pages/barang.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('barang.php', $current_page) ?>">
                        <i class="fa-solid fa-tag w-5 text-center text-sm"></i>
                        <span>Data Barang</span>
                    </a>
                    <a href="<?= $base_url ?>pages/satuan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('satuan.php', $current_page) ?>">
                        <i class="fa-solid fa-scale-balanced w-5 text-center text-sm"></i>
                        <span>Satuan</span>
                    </a>
                    <a href="<?= $base_url ?>pages/vendor.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('vendor.php', $current_page) ?>">
                        <i class="fa-solid fa-building-wheat w-5 text-center text-sm"></i>
                        <span>Vendor</span>
                    </a>
                    <a href="<?= $base_url ?>pages/margin.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('margin.php', $current_page) ?>">
                        <i class="fa-solid fa-percent w-5 text-center text-sm"></i>
                        <span>Margin Penjualan</span>
                    </a>
                    <a href="<?= $base_url ?>pages/user.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('user.php', $current_page) ?>">
                        <i class="fa-solid fa-users-gear w-5 text-center text-sm"></i>
                        <span>Kelola Pengguna</span>
                    </a>
                </div>
            </div>

            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 mb-2">Laporan</div>
                <div class="space-y-1 text-sm">
                    <a href="<?= $base_url ?>pages/laporan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('laporan.php', $current_page) ?>">
                        <i class="fa-solid fa-file-invoice-dollar w-5 text-center text-sm"></i>
                        <span>Laporan Penjualan</span>
                    </a>
                </div>
            </div>

        <?php elseif (isKasir()): ?>
            <!-- KASIR MENUS -->
            <div>
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-400 px-3 mb-2">Menu Kasir</div>
                <div class="space-y-1 text-sm">
                    <a href="<?= $base_url ?>pos.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('pos.php', $current_page) ?>">
                        <i class="fa-solid fa-cash-register w-5 text-center text-sm"></i>
                        <span>Point of Sale (POS)</span>
                    </a>
                    <a href="<?= $base_url ?>pages/penjualan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl transition <?= isActiveMenu('penjualan.php', $current_page) ?>">
                        <i class="fa-solid fa-receipt w-5 text-center text-sm"></i>
                        <span>Riwayat Transaksi Saya</span>
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Bottom User Bar inside Sidebar -->
    <div class="p-4 border-t border-slate-200 bg-slate-50">
        <a href="<?= $base_url ?>logout.php" onclick="return confirm('Apakah Anda yakin ingin keluar?');" class="flex items-center justify-center gap-2 w-full py-2.5 px-3 rounded-xl bg-white border border-slate-200 text-rose-600 hover:bg-rose-50 hover:border-rose-200 text-xs font-semibold transition">
            <i class="fa-solid fa-arrow-right-from-bracket"></i>
            <span>Keluar Akun</span>
        </a>
    </div>
</aside>
