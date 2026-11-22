<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireKasirOrSuperAdmin();

$idpenjualan = (int)($_GET['id'] ?? 0);
if ($idpenjualan <= 0) {
    echo '<p class="text-center text-rose-500">ID Nota tidak valid.</p>';
    exit;
}

// Ambil seluruh data transaksi dan detail item langsung dari VIEW vw_penjualan
$stmt = $pdo->prepare("SELECT * FROM vw_penjualan WHERE idpenjualan = ?");
$stmt->execute([$idpenjualan]);
$items = $stmt->fetchAll();

if (empty($items)) {
    echo '<p class="text-center text-rose-500">Nota tidak ditemukan.</p>';
    exit;
}

$sale = $items[0];
$subtotal = 0;
foreach ($items as $it) {
    $subtotal += $it['subtotal'];
}
?>

<div class="space-y-0.5 text-[10px] pb-2 border-b border-dashed border-slate-300">
    <div class="flex justify-between">
        <span>No. Nota</span>
        <span class="font-bold">#<?= $sale['idpenjualan'] ?></span>
    </div>
    <div class="flex justify-between">
        <span>Tanggal</span>
        <span><?= date('d/m/Y H:i', strtotime($sale['tanggal'])) ?></span>
    </div>
    <div class="flex justify-between">
        <span>Kasir</span>
        <span><?= htmlspecialchars($sale['kasir']) ?></span>
    </div>
</div>

<div class="py-2 border-b border-dashed border-slate-300 space-y-1.5 text-xs">
    <?php foreach ($items as $it): ?>
        <div>
            <div class="font-bold text-slate-800"><?= htmlspecialchars($it['nama_barang']) ?></div>
            <div class="flex justify-between text-[10px] text-slate-500">
                <span><?= $it['jumlah'] ?> x <?= formatRupiah($it['harga_satuan']) ?></span>
                <span class="font-bold text-slate-700"><?= formatRupiah($it['subtotal']) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="pt-2 text-[11px] space-y-1">
    <div class="flex justify-between text-slate-500">
        <span>Subtotal</span>
        <span><?= formatRupiah($subtotal) ?></span>
    </div>
    <div class="flex justify-between text-slate-500">
        <span>PPN (11%)</span>
        <span><?= formatRupiah($sale['ppn']) ?></span>
    </div>
    <div class="flex justify-between font-extrabold text-xs text-slate-900 pt-1 border-t border-slate-200">
        <span>TOTAL</span>
        <span class="text-emerald-700"><?= formatRupiah($sale['total_nota']) ?></span>
    </div>
</div>
