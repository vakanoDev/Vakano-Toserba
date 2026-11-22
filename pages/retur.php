<?php
$page_title = 'Retur Barang ke Vendor';
require_once __DIR__ . '/../includes/header.php';

requireSuperAdmin();

$msg = '';
$err = '';

// Proses Retur
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create_retur') {
    $idpenerimaan          = (int)$_POST['idpenerimaan'];
    $iddetail_penerimaan  = (int)$_POST['iddetail_penerimaan'];
    $jumlah                = (int)$_POST['jumlah'];
    $alasan                = clean($_POST['alasan']);
    $iduser                = getUserId();

    if ($idpenerimaan <= 0 || $iddetail_penerimaan <= 0 || $jumlah <= 0 || empty($alasan)) {
        $err = 'Harap lengkapi semua data retur!';
    } else {
        $pdo->beginTransaction();
        try {
            // Buat record retur induk
            $stmt = $pdo->prepare("INSERT INTO retur (idpenerimaan, iduser, created_at) VALUES (?, ?, NOW())");
            $stmt->execute([$idpenerimaan, $iduser]);
            $idretur = $pdo->lastInsertId();

            // Panggil SP sp_tambah_detail_retur
            $sp = $pdo->prepare("CALL sp_tambah_detail_retur(?, ?, ?, ?)");
            $sp->execute([$idretur, $iddetail_penerimaan, $jumlah, $alasan]);
            $res = $sp->fetch();
            $sp->closeCursor();

            if ($res && isset($res['pesan']) && strpos($res['pesan'], 'DITOLAK') !== false) {
                $pdo->rollBack();
                $err = 'Retur ditolak: ' . $res['pesan'];
            } else {
                $pdo->commit();
                $msg = "Retur #$idretur berhasil dicatat! Stok barang otomatis dikurangi.";
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $err = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Ambil list penerimaan + vendor dari VIEW vw_daftar_penerimaan
$penerimaan_list = $pdo->query("SELECT * FROM vw_daftar_penerimaan ORDER BY idpenerimaan DESC")->fetchAll();

// Detail item penerimaan yang bisa diretur dari VIEW vw_sisa_retur
$selected_terima = (int)($_GET['idpenerimaan'] ?? ($penerimaan_list[0]['idpenerimaan'] ?? 0));
$items_terima = [];
if ($selected_terima > 0) {
    $stmtItems = $pdo->prepare("SELECT * FROM vw_sisa_retur WHERE idpenerimaan = ?");
    $stmtItems->execute([$selected_terima]);
    $items_terima = $stmtItems->fetchAll();
}

// Riwayat retur dari view vw_retur
$retur_list = $pdo->query("SELECT * FROM vw_retur ORDER BY idretur DESC")->fetchAll();
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Retur Barang ke Vendor</h1>
            <p class="text-xs text-slate-500 mt-1">Pengembalian barang rusak, cacat, atau tidak sesuai ke pihak pemasok.</p>
        </div>
        <?php if (!empty($penerimaan_list)): ?>
            <button type="button" onclick="openReturModal()"
                class="px-4 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-md shadow-rose-600/20 transition flex items-center gap-2 self-start">
                <i class="fa-solid fa-rotate-left"></i>
                <span>Buat Retur Baru</span>
            </button>
        <?php endif; ?>
    </div>

    <?php if ($msg): ?>
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
            <span><?= $msg ?></span>
        </div>
    <?php endif; ?>

    <?php if ($err): ?>
        <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2">
            <i class="fa-solid fa-triangle-exclamation text-rose-600 text-sm"></i>
            <span><?= $err ?></span>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">Riwayat Retur Barang</h3>
            <span class="text-xs text-slate-500"><?= count($retur_list) ?> Catatan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="py-3 px-4">No. Retur</th>
                        <th class="py-3 px-4">Ref. Terima</th>
                        <th class="py-3 px-4">Tanggal Retur</th>
                        <th class="py-3 px-4">Nama Barang</th>
                        <th class="py-3 px-4">Jumlah Retur</th>
                        <th class="py-3 px-4">Alasan Retur</th>
                        <th class="py-3 px-4">Petugas</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($retur_list)): ?>
                        <tr><td colspan="7" class="py-8 text-center text-slate-400">Belum ada barang yang diretur.</td></tr>
                    <?php else: ?>
                        <?php foreach ($retur_list as $rt): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-rose-700">#<?= $rt['idretur'] ?></td>
                                <td class="py-3 px-4 font-mono text-slate-600">#<?= $rt['idpenerimaan'] ?></td>
                                <td class="py-3 px-4 text-slate-500"><?= date('d M Y, H:i', strtotime($rt['tanggal'])) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($rt['nama_barang']) ?></td>
                                <td class="py-3 px-4 font-extrabold text-rose-600">-<?= $rt['jumlah_retur'] ?></td>
                                <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($rt['alasan']) ?></td>
                                <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($rt['petugas']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Buat Retur -->
<div id="modalRetur" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl relative">
        <button onclick="closeReturModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 class="text-base font-extrabold text-slate-800 mb-4">Input Retur Barang ke Vendor</h3>

        <form method="POST" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="create_retur">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih No. Penerimaan</label>
                <select name="idpenerimaan" id="selectPenerimaan" onchange="window.location.href='retur.php?idpenerimaan=' + this.value"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500 font-bold">
                    <?php foreach ($penerimaan_list as $p): ?>
                        <option value="<?= $p['idpenerimaan'] ?>" <?= $selected_terima == $p['idpenerimaan'] ? 'selected' : '' ?>>
                            Terima #<?= $p['idpenerimaan'] ?> (PO #<?= $p['idpengadaan'] ?> - <?= htmlspecialchars($p['nama_vendor']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Barang yang Diretur</label>
                <select name="iddetail_penerimaan" required
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500">
                    <option value="">-- Pilih Barang --</option>
                    <?php foreach ($items_terima as $it): 
                        $sisaBisaRetur = $it['jumlah_terima'] - $it['sudah_diretur'];
                        if ($sisaBisaRetur > 0):
                    ?>
                        <option value="<?= $it['iddetail_penerimaan'] ?>">
                            <?= htmlspecialchars($it['nama_barang']) ?> (Diterima: <?= $it['jumlah_terima'] ?>, Sisa: <?= $sisaBisaRetur ?>)
                        </option>
                    <?php endif; endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Jumlah Diretur</label>
                <input type="number" min="1" name="jumlah" required
                    placeholder="Masukkan kuantitas retur"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500 font-bold">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Alasan Retur</label>
                <textarea name="alasan" required rows="2"
                    placeholder="Contoh: Kemasan sobek, barang bocor, tanggal kadaluarsa terlalu dekat"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-rose-500"></textarea>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white font-bold shadow-md">
                    Konfirmasi Retur
                </button>
                <button type="button" onclick="closeReturModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReturModal() {
    document.getElementById('modalRetur').classList.remove('hidden');
}
function closeReturModal() {
    document.getElementById('modalRetur').classList.add('hidden');
}

<?php if (isset($_GET['idpenerimaan'])): ?>
document.addEventListener('DOMContentLoaded', () => {
    openReturModal();
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>