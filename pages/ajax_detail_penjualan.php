<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireKasirOrSuperAdmin();

$idpenjualan = (int)($_GET['id'] ?? 0);
if ($idpenjualan <= 0) {
    echo '<p class="text-center text-rose-500">ID Nota tidak valid.</p>';
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.*, u.username, m.persen AS margin_persen
    FROM penjualan p
    JOIN user u ON p.iduser = u.iduser
    JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan
    WHERE p.idpenjualan = ?
");
$stmt->execute([$idpenjualan]);
$sale = $stmt->fetch();

if (!$sale) {
    echo '<p class="text-center text-rose-500">Nota tidak ditemukan.</p>';
    exit;
}

$stmtDet = $pdo->prepare("
    SELECT dp.*, b.nama, s.nama_satuan
    FROM detail_penjualan dp
    JOIN barang b ON dp.idbarang = b.idbarang
    JOIN satuan s ON b.idsatuan = s.idsatuan
    WHERE dp.penjualan_idpenjualan = ?
");
$stmtDet->execute([$idpenjualan]);
$items = $stmtDet->fetchAll();
?>

<div class="space-y-0.5 text-[10px] pb-2 border-b border-dashed border-slate-300">
    <div class="flex justify-between">
        <span>No. Nota</span>
        <span class="font-bold">#<?= $sale['idpenjualan'] ?></span>
    </div>
    <div class="flex justify-between">
        <span>Tanggal</span>
        <span><?= date('d/m/Y H:i', strtotime($sale['created_at'])) ?></span>
    </div>
    <div class="flex justify-between">
        <span>Kasir</span>
        <span><?= htmlspecialchars($sale['username']) ?></span>
    </div>
</div>

<div class="py-2 border-b border-dashed border-slate-300 space-y-1.5 text-xs">
    <?php foreach ($items as $it): ?>
        <div>
            <div class="font-bold text-slate-800"><?= htmlspecialchars($it['nama']) ?></div>
            <div class="flex justify-between text-[10px] text-slate-500">
                <span><?= $it['jumlah'] ?> <?= $it['nama_satuan'] ?> x <?= formatRupiah($it['harga_satuan']) ?></span>
                <span class="font-bold text-slate-700"><?= formatRupiah($it['subtotal']) ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<div class="pt-2 text-[11px] space-y-1">
    <div class="flex justify-between text-slate-500">
        <span>Subtotal</span>
        <span><?= formatRupiah($sale['subtotal_nilai']) ?></span>
    </div>
    <div class="flex justify-between text-slate-500">
        <span>PPN (11%)</span>
        <span><?= formatRupiah($sale['ppn']) ?></span>
    </div>
    <div class="flex justify-between font-extrabold text-xs text-slate-900 pt-1 border-t border-slate-200">
        <span>TOTAL</span>
        <span class="text-emerald-700"><?= formatRupiah($sale['total_nilai']) ?></span>
    </div>
</div>
