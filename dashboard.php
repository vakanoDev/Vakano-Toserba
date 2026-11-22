<?php
$page_title = 'Dashboard';
require_once __DIR__ . '/includes/header.php';

requireSuperAdmin();

// Ambil Statistik
$total_omzet = $pdo->query("SELECT total_omzet FROM vw_omzet")->fetchColumn();
$total_transaksi = $pdo->query("SELECT COUNT(*) FROM penjualan")->fetchColumn();
$total_barang = $pdo->query("SELECT COUNT(*) FROM vw_barang_aktif")->fetchColumn();
$margin_aktif = getActiveMargin($pdo);

// Barang dengan stok kritis (stok <= 5) menggunakan VIEW vw_barang_stok
$stok_kritis = $pdo->query("
    SELECT idbarang, nama_barang AS nama, nama_satuan, stok, harga_jual
    FROM vw_barang_stok
    WHERE status = 1 AND stok <= 5
    ORDER BY stok ASC
    LIMIT 5
")->fetchAll();

// Transaksi Penjualan Terakhir menggunakan VIEW vw_riwayat_penjualan
$recent_sales = $pdo->query("
    SELECT idpenjualan, created_at, total_nilai, username
    FROM vw_riwayat_penjualan
    ORDER BY idpenjualan DESC
    LIMIT 5
")->fetchAll();
?>

<div class="space-y-6">
    <!-- Top Welcome Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-emerald-800 via-emerald-700 to-teal-800 p-6 lg:p-8 text-white shadow-xl shadow-emerald-900/10">
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-200 border border-emerald-400/30 text-xs font-semibold mb-3">
                    <i class="fa-solid fa-sparkles"></i> Selamat Datang Kembali
                </span>
                <h1 class="text-2xl lg:text-3xl font-extrabold tracking-tight">Halo, <?= htmlspecialchars(getUsername()) ?>!</h1>
                <p class="text-emerald-100/80 text-sm mt-1 max-w-xl">
                    Sistem Vakano Toserba aktif. Margin penjualan aktif saat ini adalah <strong class="text-white bg-emerald-600/60 px-2 py-0.5 rounded"><?= $margin_aktif['persen'] ?>%</strong>.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="pos.php" class="px-5 py-3 rounded-2xl bg-white text-emerald-800 font-bold text-sm hover:bg-emerald-50 shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-cash-register text-emerald-600"></i>
                    <span>Buka POS Kasir</span>
                </a>
                <a href="pages/pengadaan.php" class="px-4 py-3 rounded-2xl bg-emerald-600/60 hover:bg-emerald-600 text-white font-semibold text-sm border border-emerald-400/30 transition flex items-center gap-2">
                    <i class="fa-solid fa-cart-plus"></i>
                    <span>Pengadaan</span>
                </a>
            </div>
        </div>
        <!-- Decorative bg circle -->
        <div class="absolute -right-12 -bottom-12 w-64 h-64 rounded-full bg-emerald-500/10 blur-2xl pointer-events-none"></div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Card 1 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-emerald-300 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Penjualan</p>
                    <h3 class="text-xl font-extrabold text-slate-800 mt-1"><?= formatRupiah($total_omzet) ?></h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-slate-500 flex items-center gap-1">
                <span class="text-emerald-600 font-semibold"><i class="fa-solid fa-circle-check"></i> Akumulasi</span>
                <span>semua transaksi kasir</span>
            </div>
        </div>

        <!-- Card 2 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-teal-300 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Transaksi Selesai</p>
                    <h3 class="text-xl font-extrabold text-slate-800 mt-1"><?= number_format($total_transaksi) ?></h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-slate-500 flex items-center gap-1">
                <span class="text-teal-600 font-semibold"><i class="fa-solid fa-bag-shopping"></i> Nota Penjualan</span>
            </div>
        </div>

        <!-- Card 3 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-emerald-300 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Barang Aktif</p>
                    <h3 class="text-xl font-extrabold text-slate-800 mt-1"><?= number_format($total_barang) ?> Item</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-slate-500 flex items-center gap-1">
                <a href="pages/barang.php" class="text-emerald-600 font-semibold hover:underline">Kelola Katalog &rarr;</a>
            </div>
        </div>

        <!-- Card 4 -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-xs hover:border-amber-300 transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Margin Aktif</p>
                    <h3 class="text-xl font-extrabold text-amber-600 mt-1"><?= $margin_aktif['persen'] ?>%</h3>
                </div>
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-percent"></i>
                </div>
            </div>
            <div class="mt-3 text-xs text-slate-500 flex items-center gap-1">
                <a href="pages/margin.php" class="text-amber-600 font-semibold hover:underline">Ubah Margin &rarr;</a>
            </div>
        </div>
    </div>

    <!-- 2 Columns: Recent Transactions & Critical Stock -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Transaksi Terakhir -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-clock-rotate-left"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm">Transaksi Penjualan Terbaru</h3>
                </div>
                <a href="pages/penjualan.php" class="text-xs text-emerald-600 font-semibold hover:underline">Lihat Semua</a>
            </div>

            <?php if (empty($recent_sales)): ?>
                <div class="py-10 text-center text-slate-400 text-sm">
                    <i class="fa-solid fa-inbox text-3xl mb-2 text-slate-300 block"></i>
                    Belum ada transaksi penjualan yang tercatat.
                </div>
            <?php else: ?>
                <div class="divide-y divide-slate-100">
                    <?php foreach ($recent_sales as $sale): ?>
                        <div class="py-3 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-xs font-bold">
                                    #<?= $sale['idpenjualan'] ?>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-slate-800">Kasir: <?= htmlspecialchars($sale['username']) ?></div>
                                    <div class="text-[11px] text-slate-400"><?= date('d M Y, H:i', strtotime($sale['created_at'])) ?></div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs font-extrabold text-emerald-700"><?= formatRupiah($sale['total_nilai']) ?></div>
                                <span class="inline-block text-[10px] px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-semibold">Selesai</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Stok Kritis / Perlu Pengadaan -->
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-xs">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm">Monitoring Stok Barang</h3>
                </div>
                <a href="pages/kartu_stok.php" class="text-xs text-emerald-600 font-semibold hover:underline">Kartu Stok</a>
            </div>

            <?php if (empty($stok_kritis)): ?>
                <div class="py-10 text-center text-slate-400 text-sm">
                    <i class="fa-solid fa-circle-check text-3xl mb-2 text-emerald-400 block"></i>
                    Semua stok barang dalam kondisi aman.
                </div>
            <?php else: ?>
                <div class="space-y-3">
                    <?php foreach ($stok_kritis as $item): ?>
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between">
                            <div>
                                <h4 class="text-xs font-bold text-slate-800"><?= htmlspecialchars($item['nama']) ?></h4>
                                <p class="text-[11px] text-slate-500">Harga Jual: <?= formatRupiah($item['harga_jual']) ?> / <?= htmlspecialchars($item['nama_satuan']) ?></p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-bold <?= $item['stok'] <= 0 ? 'bg-rose-100 text-rose-700' : 'bg-amber-100 text-amber-800' ?>">
                                    Stok: <?= $item['stok'] ?>
                                </span>
                                <a href="pages/pengadaan.php" class="text-xs text-emerald-600 hover:text-emerald-700 font-semibold">
                                    + PO
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>