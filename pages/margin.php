<?php
$page_title = 'Margin Penjualan';
require_once __DIR__ . '/../includes/header.php';

requireSuperAdmin();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $persen    = (float)$_POST['persen'];
        $set_aktif = isset($_POST['set_aktif']) ? 1 : 0;
        $iduser    = getUserId();

        if ($persen < 0) {
            $err = 'Persentase margin tidak boleh negatif!';
        } else {
            try {
                $pdo->beginTransaction();
                if ($set_aktif) {
                    $pdo->exec("UPDATE margin_penjualan SET status = 0");
                }
                $stmt = $pdo->prepare("
                    INSERT INTO margin_penjualan (persen, status, iduser, created_at, updated_at)
                    VALUES (?, ?, ?, NOW(), NOW())
                ");
                $stmt->execute([$persen, $set_aktif, $iduser]);
                $pdo->commit();
                $msg = 'Margin penjualan baru berhasil disimpan!';
            } catch (Exception $e) {
                if ($pdo->inTransaction()) $pdo->rollBack();
                $err = 'Gagal menyimpan margin: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'activate') {
        $idmargin = (int)$_POST['idmargin'];
        try {
            $pdo->beginTransaction();
            $pdo->exec("UPDATE margin_penjualan SET status = 0");
            $stmt = $pdo->prepare("UPDATE margin_penjualan SET status = 1, updated_at = NOW() WHERE idmargin_penjualan = ?");
            $stmt->execute([$idmargin]);
            $pdo->commit();
            $msg = 'Margin berhasil diaktifkan!';
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $err = 'Gagal mengaktifkan margin: ' . $e->getMessage();
        }
    }
}

// Ambil list margin
$margin_list = $pdo->query("
    SELECT m.*, u.username
    FROM margin_penjualan m
    LEFT JOIN user u ON m.iduser = u.iduser
    ORDER BY m.idmargin_penjualan DESC
")->fetchAll();

$margin_aktif = getActiveMargin($pdo);
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Kelola Margin Penjualan</h1>
            <p class="text-xs text-slate-500 mt-1">
                Margin menentukan keuntungan otomatis pada harga jual kasir: <code>Harga Jual = Harga Beli + (Harga Beli * Margin %)</code>.
            </p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2 self-start">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Nilai Margin</span>
        </button>
    </div>

    <!-- Active Banner -->
    <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-200 flex items-center justify-between">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-md">
                <i class="fa-solid fa-percent"></i>
            </div>
            <div>
                <div class="text-xs font-bold text-emerald-800 uppercase tracking-wider">Margin Aktif Toko Saat Ini</div>
                <div class="text-2xl font-extrabold text-emerald-900"><?= $margin_aktif['persen'] ?>%</div>
            </div>
        </div>
        <span class="px-3 py-1 rounded-full bg-emerald-200 text-emerald-900 font-bold text-xs">
            Digunakan di POS Kasir
        </span>
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
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-200">
                        <th class="py-3 px-4">ID</th>
                        <th class="py-3 px-4">Persentase Margin</th>
                        <th class="py-3 px-4">Ditetapkan Oleh</th>
                        <th class="py-3 px-4">Tanggal Dibuat</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php foreach ($margin_list as $m): ?>
                        <tr class="hover:bg-slate-50/70 transition <?= $m['status'] == 1 ? 'bg-emerald-50/30' : '' ?>">
                            <td class="py-3 px-4 font-mono font-bold text-slate-400">#<?= $m['idmargin_penjualan'] ?></td>
                            <td class="py-3 px-4 font-extrabold text-slate-800 text-sm"><?= $m['persen'] ?>%</td>
                            <td class="py-3 px-4 text-slate-600"><?= htmlspecialchars($m['username'] ?? '-') ?></td>
                            <td class="py-3 px-4 text-slate-500"><?= date('d M Y, H:i', strtotime($m['created_at'])) ?></td>
                            <td class="py-3 px-4">
                                <?php if ($m['status'] == 1): ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 border border-emerald-300">
                                        <i class="fa-solid fa-check-circle"></i> Sedang Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="px-2.5 py-1 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-500">
                                        Tidak Aktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <?php if ($m['status'] != 1): ?>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="activate">
                                        <input type="hidden" name="idmargin" value="<?= $m['idmargin_penjualan'] ?>">
                                        <button type="submit" class="px-3 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[11px] transition shadow-xs">
                                            Aktifkan
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-[11px] text-slate-400 font-semibold italic">Digunakan</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Margin -->
<div id="modalMargin" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 class="text-base font-extrabold text-slate-800 mb-4">Tambah Margin Baru</h3>

        <form method="POST" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="create">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Persentase Margin (%)</label>
                <div class="relative">
                    <input type="number" step="0.1" name="persen" required min="0" max="100"
                        placeholder="Contoh: 10 atau 12.5"
                        class="w-full px-3 py-2 pr-8 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 font-bold">
                    <span class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 font-bold">%</span>
                </div>
            </div>

            <div class="flex items-center gap-2 pt-1">
                <input type="checkbox" name="set_aktif" id="set_aktif" value="1" checked class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4">
                <label for="set_aktif" class="font-semibold text-slate-700 cursor-pointer">Langsung aktifkan margin ini</label>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">
                    Simpan
                </button>
                <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalMargin').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('modalMargin').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
