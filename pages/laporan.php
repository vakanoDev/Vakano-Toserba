<?php
$page_title = 'Laporan Penjualan';
require_once __DIR__ . '/../includes/header.php';

requireSuperAdmin();

// Tanggal filter untuk SP sp_laporan_penjualan
$filter_date = clean($_GET['tanggal'] ?? date('Y-m-d'));

// Panggil Stored Procedure sp_laporan_penjualan
$sp = $pdo->prepare("CALL sp_laporan_penjualan(?)");
$sp->execute([$filter_date]);
$daily_sales = $sp->fetchAll();
$sp->closeCursor();

// Ambil data rekap penjualan per barang dari view vw_rekap_penjualan
$rekap_barang = $pdo->query("SELECT * FROM vw_rekap_penjualan ORDER BY total_pendapatan DESC")->fetchAll();

// Hitung total harian
$daily_total = 0;
$daily_ppn   = 0;
foreach ($daily_sales as $ds) {
    $daily_total += $ds['total_nilai'];
    $daily_ppn   += $ds['ppn'];
}
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Laporan Rekap Penjualan</h1>
            <p class="text-xs text-slate-500 mt-1">Laporan omzet penjualan harian dan performa produk terlaris.</p>
        </div>
        <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs shadow-md transition flex items-center gap-2 self-start">
            <i class="fa-solid fa-print"></i>
            <span>Cetak Laporan</span>
        </button>
    </div>

    <!-- Date Filter Form -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" class="flex flex-col sm:flex-row sm:items-end gap-3 text-xs">
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Tanggal Transaksi</label>
                <input type="date" name="tanggal" value="<?= htmlspecialchars($filter_date) ?>"
                    class="px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
            </div>
            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition flex items-center gap-1.5 self-start">
                <i class="fa-solid fa-calendar-check"></i> Tampilkan
            </button>
        </form>
    </div>

    <!-- 2 Cards Summary for the day -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Omzet Tanggal <?= date('d/m/Y', strtotime($filter_date)) ?></span>
            <h3 class="text-xl font-extrabold text-emerald-800 mt-1"><?= formatRupiah($daily_total) ?></h3>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total PPN (11%)</span>
            <h3 class="text-xl font-extrabold text-slate-800 mt-1"><?= formatRupiah($daily_ppn) ?></h3>
        </div>
        <div class="bg-white p-5 rounded-2xl border border-slate-200">
            <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Jumlah Transaksi</span>
            <h3 class="text-xl font-extrabold text-teal-800 mt-1"><?= count($daily_sales) ?> Transaksi</h3>
        </div>
    </div>

    <!-- Tabel 1: Laporan Harian via sp_laporan_penjualan -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">
                Rincian Transaksi Tanggal <?= date('d M Y', strtotime($filter_date)) ?>
            </h3>
            <span class="text-[10px] bg-emerald-50 text-emerald-700 font-bold px-2 py-0.5 rounded-md border border-emerald-200">
                Data SP sp_laporan_penjualan
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="py-3 px-4">No. Nota</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Kasir</th>
                        <th class="py-3 px-4">Subtotal</th>
                        <th class="py-3 px-4">PPN (11%)</th>
                        <th class="py-3 px-4">Total Nilai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($daily_sales)): ?>
                        <tr><td colspan="6" class="py-8 text-center text-slate-400">Tidak ada transaksi pada tanggal ini.</td></tr>
                    <?php else: ?>
                        <?php foreach ($daily_sales as $ds): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">#<?= $ds['idpenjualan'] ?></td>
                                <td class="py-3 px-4 text-slate-500"><?= date('H:i:s', strtotime($ds['created_at'])) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($ds['kasir']) ?></td>
                                <td class="py-3 px-4 font-mono text-slate-600"><?= formatRupiah($ds['subtotal_nilai']) ?></td>
                                <td class="py-3 px-4 font-mono text-slate-600"><?= formatRupiah($ds['ppn']) ?></td>
                                <td class="py-3 px-4 font-mono font-extrabold text-emerald-700"><?= formatRupiah($ds['total_nilai']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tabel 2: Rekap Performa Penjualan per Barang via vw_rekap_penjualan -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">
                Rekap Akumulasi Produk Terjual & Pendapatan
            </h3>
            <span class="text-[10px] bg-blue-50 text-blue-700 font-bold px-2 py-0.5 rounded-md border border-blue-200">
                Data View vw_rekap_penjualan
            </span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="py-3 px-4">ID Barang</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4 text-center">Total Unit Terjual</th>
                        <th class="py-3 px-4">Total Pendapatan Kotor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($rekap_barang)): ?>
                        <tr><td colspan="4" class="py-8 text-center text-slate-400">Belum ada barang yang terjual.</td></tr>
                    <?php else: ?>
                        <?php foreach ($rekap_barang as $rb): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-400">#<?= $rb['idbarang'] ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($rb['nama_barang']) ?></td>
                                <td class="py-3 px-4 text-center font-bold text-slate-800">
                                    <?= (int)($rb['total_terjual'] ?? 0) ?>
                                </td>
                                <td class="py-3 px-4 font-mono font-extrabold text-emerald-700">
                                    <?= formatRupiah($rb['total_pendapatan'] ?? 0) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
