<?php
$page_title = 'Riwayat Penjualan';
require_once __DIR__ . '/../includes/header.php';

requireKasirOrSuperAdmin();

$iduser_filter = isKasir() ? getUserId() : (int)($_GET['kasir'] ?? 0);

$query = "
    SELECT p.*, u.username, m.persen AS margin_persen
    FROM penjualan p
    JOIN user u ON p.iduser = u.iduser
    JOIN margin_penjualan m ON p.idmargin_penjualan = m.idmargin_penjualan
    WHERE 1=1
";
$params = [];

if ($iduser_filter > 0) {
    $query .= " AND p.iduser = ?";
    $params[] = $iduser_filter;
}

$query .= " ORDER BY p.idpenjualan DESC";

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$sales = $stmt->fetchAll();

// List kasir untuk filter (hanya Super Admin)
$cashiers = [];
if (isSuperAdmin()) {
    $cashiers = $pdo->query("SELECT iduser, username FROM user WHERE idrole IN (1, 2) ORDER BY username ASC")->fetchAll();
}
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Riwayat Transaksi Kasir</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar struk nota penjualan yang berhasil diproses di kasir.</p>
        </div>
        <a href="../pos.php" class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2 self-start">
            <i class="fa-solid fa-cash-register"></i>
            <span>Buka Kasir (POS)</span>
        </a>
    </div>

    <?php if (isSuperAdmin()): ?>
        <!-- Filter Kasir -->
        <div class="bg-white rounded-2xl border border-slate-200 p-4 shadow-xs">
            <form method="GET" class="flex items-center gap-3 text-xs">
                <span class="font-bold text-slate-700">Filter Kasir:</span>
                <select name="kasir" onchange="this.form.submit()" class="px-3 py-1.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="">-- Semua Kasir --</option>
                    <?php foreach ($cashiers as $c): ?>
                        <option value="<?= $c['iduser'] ?>" <?= $iduser_filter == $c['iduser'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($c['username']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if ($iduser_filter > 0): ?>
                    <a href="penjualan.php" class="text-slate-500 hover:text-slate-700 underline">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="py-3 px-4">No. Nota</th>
                        <th class="py-3 px-4">Waktu Transaksi</th>
                        <th class="py-3 px-4">Kasir Bertugas</th>
                        <th class="py-3 px-4">Margin</th>
                        <th class="py-3 px-4">Subtotal</th>
                        <th class="py-3 px-4">PPN (11%)</th>
                        <th class="py-3 px-4">Total Bayar</th>
                        <th class="py-3 px-4 text-center">Detail & Nota</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($sales)): ?>
                        <tr><td colspan="8" class="py-8 text-center text-slate-400">Belum ada transaksi penjualan yang tercatat.</td></tr>
                    <?php else: ?>
                        <?php foreach ($sales as $s): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">#<?= $s['idpenjualan'] ?></td>
                                <td class="py-3 px-4 text-slate-500"><?= date('d/m/Y H:i', strtotime($s['created_at'])) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800 flex items-center gap-1.5">
                                    <div class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold flex items-center justify-center">
                                        <?= strtoupper(substr($s['username'], 0, 1)) ?>
                                    </div>
                                    <span><?= htmlspecialchars($s['username']) ?></span>
                                </td>
                                <td class="py-3 px-4 text-slate-600 font-semibold"><?= $s['margin_persen'] ?>%</td>
                                <td class="py-3 px-4 font-mono text-slate-600"><?= formatRupiah($s['subtotal_nilai']) ?></td>
                                <td class="py-3 px-4 font-mono text-slate-600"><?= formatRupiah($s['ppn']) ?></td>
                                <td class="py-3 px-4 font-mono font-extrabold text-emerald-700"><?= formatRupiah($s['total_nilai']) ?></td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" onclick="loadSaleDetail(<?= $s['idpenjualan'] ?>)"
                                        class="px-3 py-1 rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 font-bold border border-emerald-200 transition">
                                        <i class="fa-solid fa-receipt mr-1"></i> Lihat Nota
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Detail Nota -->
<div id="modalSaleDetail" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative">
        <button onclick="closeModalDetail()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div id="saleReceiptContent" class="font-mono text-xs text-slate-700 leading-relaxed">
            <div class="text-center pb-3 border-b border-dashed border-slate-300">
                <div class="font-extrabold text-base tracking-widest text-emerald-800 uppercase">FAST MART</div>
                <div class="text-[10px] text-slate-500">Jl. Raya Kampus No. 1, Kota</div>
            </div>
            <div id="detailBody" class="py-3">
                <p class="text-center text-slate-400">Memuat detail transaksi...</p>
            </div>
        </div>

        <div class="mt-4 flex gap-2">
            <button onclick="window.print()" class="flex-1 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs flex items-center justify-center gap-1.5 shadow-md">
                <i class="fa-solid fa-print"></i> Cetak Ulang
            </button>
            <button onclick="closeModalDetail()" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs">
                Tutup
            </button>
        </div>
    </div>
</div>

<script>
async function loadSaleDetail(id) {
    document.getElementById('modalSaleDetail').classList.remove('hidden');
    const body = document.getElementById('detailBody');
    body.innerHTML = '<p class="text-center text-slate-400 py-4"><i class="fa-solid fa-circle-notch fa-spin"></i> Memuat...</p>';

    try {
        const res = await fetch(`../pages/ajax_detail_penjualan.php?id=${id}`);
        const html = await res.text();
        body.innerHTML = html;
    } catch (err) {
        body.innerHTML = '<p class="text-center text-rose-500 py-4">Gagal memuat detail nota.</p>';
    }
}

function closeModalDetail() {
    document.getElementById('modalSaleDetail').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
