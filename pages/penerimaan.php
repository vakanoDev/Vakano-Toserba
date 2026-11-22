<?php
$page_title = 'Penerimaan Barang';
require_once __DIR__ . '/../includes/header.php';

requireSuperAdmin();

$msg = '';
$err = '';

// Handle Penerimaan Barang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'terima_barang') {
    $idpengadaan = (int)$_POST['idpengadaan'];
    $items       = $_POST['items'] ?? [];
    $iduser      = getUserId();

    if (empty($idpengadaan) || empty($items)) {
        $err = 'Harap pilih PO dan barang yang akan diterima!';
    } else {
        $pdo->beginTransaction();
        try {
            // Buat record penerimaan induk
            $stmt = $pdo->prepare("INSERT INTO penerimaan (idpengadaan, iduser, status, created_at) VALUES (?, ?, 'A', NOW())");
            $stmt->execute([$idpengadaan, $iduser]);
            $idpenerimaan = $pdo->lastInsertId();

            $successCount = 0;
            foreach ($items as $it) {
                $idbarang = (int)($it['idbarang'] ?? 0);
                $jumlah   = (int)($it['jumlah_terima'] ?? 0);
                $harga    = (int)($it['harga_terima'] ?? 0);

                if ($jumlah > 0 && $idbarang > 0) {
                    $sp = $pdo->prepare("CALL sp_tambah_detail_penerimaan(?, ?, ?, ?)");
                    $sp->execute([$idpenerimaan, $idbarang, $jumlah, $harga]);
                    $res = $sp->fetch();
                    $sp->closeCursor();

                    if ($res && isset($res['pesan']) && strpos($res['pesan'], 'DITOLAK') !== false) {
                        $pdo->rollBack();
                        $err = 'Gagal memproses item: ' . $res['pesan'];
                        break;
                    }
                    $successCount++;
                }
            }

            if ($successCount > 0 && empty($err)) {
                $pdo->commit();
                $msg = "Penerimaan #$idpenerimaan berhasil diproses! Stok barang dan kartu stok otomatis diperbarui.";
            } elseif (empty($err)) {
                $pdo->rollBack();
                $err = 'Tidak ada jumlah barang yang diterima!';
            }
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $err = 'Terjadi kesalahan: ' . $e->getMessage();
        }
    }
}

// Ambil list pengadaan yang memiliki sisa barang belum diterima
$po_pending = $pdo->query("
    SELECT DISTINCT p.idpengadaan, p.timestamp, v.nama_vendor
    FROM pengadaan p
    JOIN vendor v ON p.vendor_idvendor = v.idvendor
    JOIN detail_pengadaan dp ON p.idpengadaan = dp.idpengadaan
    WHERE fn_sisa_pengadaan(p.idpengadaan, dp.idbarang) > 0
    ORDER BY p.idpengadaan DESC
")->fetchAll();

// Jika ada parameter ?po= di URL, ambil detail sisa barangnya
$selected_po = (int)($_GET['po'] ?? ($po_pending[0]['idpengadaan'] ?? 0));
$po_items = [];
if ($selected_po > 0) {
    $stmtItems = $pdo->prepare("
        SELECT 
            dp.idbarang,
            b.nama,
            s.nama_satuan,
            dp.jumlah AS dipesan,
            dp.harga_satuan,
            fn_sisa_pengadaan(dp.idpengadaan, dp.idbarang) AS sisa
        FROM detail_pengadaan dp
        JOIN barang b ON dp.idbarang = b.idbarang
        JOIN satuan s ON b.idsatuan = s.idsatuan
        WHERE dp.idpengadaan = ? AND fn_sisa_pengadaan(dp.idpengadaan, dp.idbarang) > 0
    ");
    $stmtItems->execute([$selected_po]);
    $po_items = $stmtItems->fetchAll();
}

// Ambil riwayat penerimaan dari view
$penerimaan_list = $pdo->query("
    SELECT * FROM vw_penerimaan ORDER BY idpenerimaan DESC
")->fetchAll();
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Penerimaan Barang Gudang</h1>
            <p class="text-xs text-slate-500 mt-1">Konfirmasi barang fisik yang tiba dari supplier (otomatis mencatat stok masuk ke kartu stok).</p>
        </div>
        <?php if (!empty($po_pending)): ?>
            <button type="button" onclick="openReceiveModal()"
                class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2 self-start">
                <i class="fa-solid fa-box-open"></i>
                <span>Input Penerimaan</span>
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

    <!-- Riwayat Penerimaan -->
    <div class="bg-white rounded-3xl border border-slate-200 shadow-xs overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="font-bold text-slate-800 text-sm">Riwayat Penerimaan Barang</h3>
            <span class="text-xs text-slate-500"><?= count($penerimaan_list) ?> Catatan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="py-3 px-4">No. Terima</th>
                        <th class="py-3 px-4">Ref. PO</th>
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Nama Barang</th>
                        <th class="py-3 px-4">Jumlah Terima</th>
                        <th class="py-3 px-4">Harga Beli</th>
                        <th class="py-3 px-4">Subtotal</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4 text-center">Aksi Retur</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($penerimaan_list)): ?>
                        <tr><td colspan="9" class="py-8 text-center text-slate-400">Belum ada penerimaan barang yang dicatat.</td></tr>
                    <?php else: ?>
                        <?php foreach ($penerimaan_list as $rc): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-800">#<?= $rc['idpenerimaan'] ?></td>
                                <td class="py-3 px-4 font-mono text-emerald-700 font-bold">#<?= $rc['idpengadaan'] ?></td>
                                <td class="py-3 px-4 text-slate-500"><?= date('d M Y, H:i', strtotime($rc['tanggal'])) ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($rc['nama_barang']) ?></td>
                                <td class="py-3 px-4 font-extrabold text-emerald-700">+<?= $rc['jumlah_terima'] ?></td>
                                <td class="py-3 px-4 font-mono text-slate-600"><?= formatRupiah($rc['harga_terima']) ?></td>
                                <td class="py-3 px-4 font-mono font-bold text-slate-800"><?= formatRupiah($rc['sub_total']) ?></td>
                                <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($rc['petugas']) ?></td>
                                <td class="py-3 px-4 text-center">
                                    <a href="retur.php?idpenerimaan=<?= $rc['idpenerimaan'] ?>" class="text-xs text-rose-600 hover:text-rose-700 font-semibold hover:underline">
                                        Retur &rarr;
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Input Penerimaan -->
<div id="modalReceive" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl relative max-h-[90vh] flex flex-col">
        <button onclick="closeReceiveModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 class="text-base font-extrabold text-slate-800 mb-4">Input Penerimaan Barang Masuk</h3>

        <form method="POST" class="space-y-4 text-xs flex-1 flex flex-col overflow-hidden">
            <input type="hidden" name="action" value="terima_barang">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih No. Pengadaan (PO)</label>
                <select name="idpengadaan" id="selectPo" onchange="window.location.href='penerimaan.php?po=' + this.value"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                    <?php foreach ($po_pending as $po): ?>
                        <option value="<?= $po['idpengadaan'] ?>" <?= $selected_po == $po['idpengadaan'] ? 'selected' : '' ?>>
                            PO #<?= $po['idpengadaan'] ?> - Vendor: <?= htmlspecialchars($po['nama_vendor']) ?> (<?= date('d/m/Y', strtotime($po['timestamp'])) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- List Item PO yang akan diterima -->
            <div class="flex-1 overflow-y-auto space-y-2 border border-slate-100 p-3 rounded-2xl bg-slate-50">
                <div class="font-bold text-slate-700 mb-1">Barang yang Dipesan & Jumlah Diterima:</div>
                <?php if (empty($po_items)): ?>
                    <p class="text-slate-400 text-center py-4">Semua barang pada PO ini sudah diterima lengkap.</p>
                <?php else: ?>
                    <?php foreach ($po_items as $idx => $it): ?>
                        <div class="p-3 rounded-xl bg-white border border-slate-200 space-y-2">
                            <div class="flex justify-between items-start">
                                <div>
                                    <div class="font-bold text-slate-800"><?= htmlspecialchars($it['nama']) ?></div>
                                    <div class="text-[10px] text-slate-500">Sisa belum diterima: <strong class="text-amber-700"><?= $it['sisa'] ?> <?= $it['nama_satuan'] ?></strong></div>
                                </div>
                                <span class="font-mono text-[11px] text-slate-500">PO: <?= formatRupiah($it['harga_satuan']) ?></span>
                            </div>
                            <input type="hidden" name="items[<?= $idx ?>][idbarang]" value="<?= $it['idbarang'] ?>">
                            <div class="grid grid-cols-2 gap-2">
                                <div>
                                    <label class="block text-[10px] text-slate-500 mb-0.5 font-semibold">Jumlah Diterima</label>
                                    <input type="number" min="0" max="<?= $it['sisa'] ?>" value="<?= $it['sisa'] ?>" name="items[<?= $idx ?>][jumlah_terima]"
                                        class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-center font-bold focus:ring-2 focus:ring-emerald-500">
                                </div>
                                <div>
                                    <label class="block text-[10px] text-slate-500 mb-0.5 font-semibold">Harga Beli Satuan (Rp)</label>
                                    <input type="number" min="0" value="<?= $it['harga_satuan'] ?>" name="items[<?= $idx ?>][harga_terima]"
                                        class="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-right font-bold focus:ring-2 focus:ring-emerald-500 font-mono">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" <?= empty($po_items) ? 'disabled' : '' ?> class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md disabled:opacity-50">
                    Konfirmasi Penerimaan
                </button>
                <button type="button" onclick="closeReceiveModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openReceiveModal() {
    document.getElementById('modalReceive').classList.remove('hidden');
}
function closeReceiveModal() {
    document.getElementById('modalReceive').classList.add('hidden');
}

// Buka otomatis modal jika ada parameter ?po= di URL
<?php if (isset($_GET['po'])): ?>
document.addEventListener('DOMContentLoaded', () => {
    openReceiveModal();
});
<?php endif; ?>
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
