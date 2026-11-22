<?php
$page_title = 'Data Vendor';
require_once __DIR__ . '/../includes/header.php';

requireSuperAdmin();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $nama        = clean($_POST['nama_vendor']);
        $badan_hukum = clean($_POST['badan_hukum']);
        $status      = clean($_POST['status'] ?? 'A');

        if (empty($nama)) {
            $err = 'Nama vendor tidak boleh kosong!';
        } else {
            try {
                $stmt = $pdo->prepare("INSERT INTO vendor (nama_vendor, badan_hukum, status) VALUES (?, ?, ?)");
                $stmt->execute([$nama, $badan_hukum, $status]);
                $msg = 'Vendor baru berhasil ditambahkan!';
            } catch (Exception $e) {
                $err = 'Gagal menambah vendor: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update') {
        $idvendor    = (int)$_POST['idvendor'];
        $nama        = clean($_POST['nama_vendor']);
        $badan_hukum = clean($_POST['badan_hukum']);
        $status      = clean($_POST['status'] ?? 'A');

        try {
            $stmt = $pdo->prepare("UPDATE vendor SET nama_vendor = ?, badan_hukum = ?, status = ? WHERE idvendor = ?");
            $stmt->execute([$nama, $badan_hukum, $status, $idvendor]);
            $msg = 'Data vendor berhasil diperbarui!';
        } catch (Exception $e) {
            $err = 'Gagal memperbarui vendor: ' . $e->getMessage();
        }
    } elseif ($action === 'toggle_status') {
        $idvendor   = (int)$_POST['idvendor'];
        $new_status = clean($_POST['new_status']);
        try {
            $stmt = $pdo->prepare("UPDATE vendor SET status = ? WHERE idvendor = ?");
            $stmt->execute([$new_status, $idvendor]);
            $msg = 'Status vendor berhasil diubah!';
        } catch (Exception $e) {
            $err = 'Gagal mengubah status: ' . $e->getMessage();
        }
    }
}

// Ambil list vendor
$vendor_list = $pdo->query("
    SELECT v.*, COUNT(p.idpengadaan) AS total_pengadaan
    FROM vendor v
    LEFT JOIN pengadaan p ON v.idvendor = p.vendor_idvendor
    GROUP BY v.idvendor
    ORDER BY v.idvendor ASC
")->fetchAll();
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Master Vendor / Supplier</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar pemasok barang untuk pengadaan Fast Mart.</p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2 self-start">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Vendor</span>
        </button>
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
                        <th class="py-3 px-4">Nama Vendor</th>
                        <th class="py-3 px-4">Badan Hukum</th>
                        <th class="py-3 px-4">Total Pengadaan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php if (empty($vendor_list)): ?>
                        <tr><td colspan="6" class="py-8 text-center text-slate-400">Belum ada vendor terdaftar.</td></tr>
                    <?php else: ?>
                        <?php foreach ($vendor_list as $v): ?>
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3 px-4 font-mono font-bold text-slate-400">#<?= $v['idvendor'] ?></td>
                                <td class="py-3 px-4 font-bold text-slate-800"><?= htmlspecialchars($v['nama_vendor']) ?></td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded-md font-semibold text-[10px] <?= $v['badan_hukum'] === 'Y' ? 'bg-purple-50 text-purple-700 border border-purple-200' : 'bg-slate-100 text-slate-600' ?>">
                                        <?= $v['badan_hukum'] === 'Y' ? 'Badan Hukum (PT/CV)' : 'Non-Badan Hukum' ?>
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-600"><?= $v['total_pengadaan'] ?> Transaksi</td>
                                <td class="py-3 px-4">
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="toggle_status">
                                        <input type="hidden" name="idvendor" value="<?= $v['idvendor'] ?>">
                                        <input type="hidden" name="new_status" value="<?= $v['status'] === 'A' ? 'N' : 'A' ?>">
                                        <button type="submit" class="px-2.5 py-1 rounded-full text-[10px] font-bold <?= $v['status'] === 'A' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' ?>">
                                            <?= $v['status'] === 'A' ? 'Aktif' : 'Non-Aktif' ?>
                                        </button>
                                    </form>
                                </td>
                                <td class="py-3 px-4 text-center">
                                    <button type="button" onclick='openEditModal(<?= json_encode($v) ?>)'
                                        class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 transition" title="Edit">
                                        <i class="fa-solid fa-pen-to-square"></i>
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

<!-- Modal Vendor -->
<div id="modalVendor" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative">
        <button onclick="closeModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 id="modalTitle" class="text-base font-extrabold text-slate-800 mb-4">Tambah Vendor Baru</h3>

        <form method="POST" id="formVendor" class="space-y-4 text-xs">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="idvendor" id="formIdvendor" value="">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Nama Vendor / Perusahaan</label>
                <input type="text" name="nama_vendor" id="formNama" required
                    placeholder="Contoh: PT Sumber Pangan Makmur"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Badan Hukum</label>
                    <select name="badan_hukum" id="formBadanHukum"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="Y">Ya (PT/CV)</option>
                        <option value="N">Tidak (Perorangan)</option>
                    </select>
                </div>
                <div>
                    <label class="block font-bold text-slate-700 mb-1">Status</label>
                    <select name="status" id="formStatus"
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                        <option value="A">Aktif</option>
                        <option value="N">Non-Aktif</option>
                    </select>
                </div>
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
    document.getElementById('modalTitle').innerText = 'Tambah Vendor Baru';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formIdvendor').value = '';
    document.getElementById('formNama').value = '';
    document.getElementById('formBadanHukum').value = 'Y';
    document.getElementById('formStatus').value = 'A';
    document.getElementById('modalVendor').classList.remove('hidden');
}

function openEditModal(v) {
    document.getElementById('modalTitle').innerText = 'Edit Vendor #' + v.idvendor;
    document.getElementById('formAction').value = 'update';
    document.getElementById('formIdvendor').value = v.idvendor;
    document.getElementById('formNama').value = v.nama_vendor;
    document.getElementById('formBadanHukum').value = v.badan_hukum;
    document.getElementById('formStatus').value = v.status;
    document.getElementById('modalVendor').classList.remove('hidden');
}

function closeModal() {
    document.getElementById('modalVendor').classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
