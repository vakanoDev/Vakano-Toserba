<?php
$page_title = 'Kartu Stok Barang';
require_once __DIR__ . '/../includes/header.php';

requireSuperAdmin();

// Filter parameters
$idbarang_filter = (int)($_GET['idbarang'] ?? 0);
$jenis_filter    = clean($_GET['jenis'] ?? '');

// Query langsung dari VIEW vw_kartu_stok
$query = "SELECT * FROM vw_kartu_stok WHERE 1=1";
$params = [];

if ($idbarang_filter > 0) {
    $query .= " AND idbarang = ?";
    $params[] = $idbarang_filter;
}
if (!empty($jenis_filter)) {
    $query .= " AND jenis_transaksi = ?";
    $params[] = $jenis_filter;
}

$query .= " ORDER BY idkartu_stok DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$logs = $stmt->fetchAll();

// List barang untuk dropdown filter dari VIEW vw_barang_aktif
$barang_list = $pdo->query("SELECT idbarang, nama FROM vw_barang_aktif ORDER BY nama ASC")->fetchAll();

// Hitung akumulasi
$total_masuk  = 0;
$total_keluar = 0;
foreach ($logs as $l) {
    $total_masuk  += $l['masuk'];
    $total_keluar += $l['keluar'];
}
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Kartu Stok & Riwayat Mutasi</h1>
            <p class="text-xs text-slate-500 mt-1">Audit log pergerakan stok barang Vakano Toserba (Masuk Penerimaan, Keluar Penjualan, dan Retur).</p>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
        <form method="GET" class="flex flex-col sm:flex-row items-end gap-3 text-xs">
            <div class="w-full sm:w-64">
                <label class="block font-bold text-slate-700 mb-1">Filter Barang</label>
                <select name="idbarang" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Semua Barang --</option>
                    <?php foreach ($barang_list as $b): ?>
                        <option value="<?= $b['idbarang'] ?>" <?= $idbarang_filter == $b['idbarang'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($b['nama']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="w-full sm:w-48">
                <label class="block font-bold text-slate-700 mb-1">Jenis Mutasi</label>
                <select name="jenis" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Semua Mutasi --</option>
                    <option value="M" <?= $jenis_filter === 'M' ? 'selected' : '' ?>>Masuk (Penerimaan)</option>
                    <option value="J" <?= $jenis_filter === 'J' ? 'selected' : '' ?>>Keluar (Penjualan)</option>
                    <option value="R" <?= $jenis_filter === 'R' ? 'selected' : '' ?>>Retur (Vendor)</option>
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold transition flex items-center gap-1.5">
                    <i class="fa-solid fa-filter"></i> Filter
                </button>
                <a href="kartu_stok.php" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Kartu Stok -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="py-3 px-4">ID Log</th>
                        <th class="py-3 px-4">Waktu Transaksi</th>
                        <th class="py-3 px-4">Nama Produk</th>
                        <th class="py-3 px-4">Tipe Mutasi</th>
                        <th class="py-3 px-4 text-center">Masuk</th>
                        <th class="py-3 px-4 text-center">Keluar</th>
                        <th class="py-3 px-4 text-center">Sisa Stok Akhir</th>
                        <th class="py-3 px-4">Ref. Transaksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($logs)): ?>
                        <tr><td colspan="8" class="py-8 text-center text-slate-400">Belum ada riwayat mutasi stok.</td></tr>
                    <?php else: ?>
                        <?php foreach ($logs as $l): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-400">#<?= $l['idkartu_stok'] ?></td>
                                <td class="py-3 px-4 text-slate-500"><?= date('d/m/Y H:i:s', strtotime($l['created_at'])) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($l['nama_barang']) ?></td>
                                <td class="py-3 px-4">
                                    <?php if ($l['jenis_transaksi'] === 'M'): ?>
                                        <span class="px-2 py-0.5 rounded-md font-bold text-[10px] bg-emerald-100 text-emerald-800">
                                            <i class="fa-solid fa-arrow-down"></i> Masuk (Penerimaan)
                                        </span>
                                    <?php elseif ($l['jenis_transaksi'] === 'J'): ?>
                                        <span class="px-2 py-0.5 rounded-md font-bold text-[10px] bg-blue-100 text-blue-800">
                                            <i class="fa-solid fa-arrow-up"></i> Keluar (Penjualan)
                                        </span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 rounded-md font-bold text-[10px] bg-rose-100 text-rose-800">
                                            <i class="fa-solid fa-rotate-left"></i> Retur ke Vendor
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-emerald-700">
                                    <?= $l['masuk'] > 0 ? '+' . $l['masuk'] : '-' ?>
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-rose-600">
                                    <?= $l['keluar'] > 0 ? '-' . $l['keluar'] : '-' ?>
                                </td>
                                <td class="py-3 px-4 text-center font-extrabold text-slate-800">
                                    <?= $l['stok'] ?> <?= htmlspecialchars($l['nama_satuan']) ?>
                                </td>
                                <td class="py-3 px-4 font-mono text-slate-500">
                                    Ref ID #<?= $l['idtransaksi'] ?>
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