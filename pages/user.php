<?php
$page_title = 'Kelola Pengguna';
require_once __DIR__ . '/../includes/header.php';

requireSuperAdmin();

$msg = '';
$err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username = clean($_POST['username']);
        $password = $_POST['password'] ?? '';
        $idrole   = (int)$_POST['idrole'];

        if (empty($username) || empty($password)) {
            $err = 'Username dan password tidak boleh kosong!';
        } else {
            try {
                // Gunakan password_hash bcrypt
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("INSERT INTO user (username, password, idrole) VALUES (?, ?, ?)");
                $stmt->execute([$username, $hashed, $idrole]);
                $msg = 'Pengguna baru berhasil ditambahkan!';
            } catch (Exception $e) {
                $err = 'Gagal menambah pengguna (mungkin username sudah ada): ' . $e->getMessage();
            }
        }
    } elseif ($action === 'reset_password') {
        $iduser   = (int)$_POST['iduser'];
        $new_pass = $_POST['new_password'] ?? '';

        if (empty($new_pass)) {
            $err = 'Password baru tidak boleh kosong!';
        } else {
            try {
                $hashed = password_hash($new_pass, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE user SET password = ? WHERE iduser = ?");
                $stmt->execute([$hashed, $iduser]);
                $msg = 'Password pengguna berhasil direset!';
            } catch (Exception $e) {
                $err = 'Gagal mereset password: ' . $e->getMessage();
            }
        }
    } elseif ($action === 'update_role') {
        $iduser = (int)$_POST['iduser'];
        $idrole = (int)$_POST['idrole'];

        try {
            $stmt = $pdo->prepare("UPDATE user SET idrole = ? WHERE iduser = ?");
            $stmt->execute([$idrole, $iduser]);
            $msg = 'Role pengguna berhasil diperbarui!';
        } catch (Exception $e) {
            $err = 'Gagal memperbarui role: ' . $e->getMessage();
        }
    }
}

// Ambil list role
$roles = $pdo->query("SELECT * FROM role ORDER BY idrole ASC")->fetchAll();

// Ambil list user menggunakan VIEW vw_user_role
$user_list = $pdo->query("SELECT * FROM vw_user_role ORDER BY iduser ASC")->fetchAll();
?>

<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-extrabold text-slate-800 tracking-tight">Kelola Akun Pengguna</h1>
            <p class="text-xs text-slate-500 mt-1">Daftar akun kasir dan administrator sistem Vakano Toserba.</p>
        </div>
        <button type="button" onclick="openAddModal()"
            class="px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-md shadow-emerald-600/20 transition flex items-center gap-2 self-start">
            <i class="fa-solid fa-user-plus"></i>
            <span>Tambah Pengguna</span>
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
                        <th class="py-3 px-4">Username</th>
                        <th class="py-3 px-4">Hak Akses Role</th>
                        <th class="py-3 px-4">Status Akses</th>
                        <th class="py-3 px-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700">
                    <?php foreach ($user_list as $u): ?>
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3 px-4 font-mono font-bold text-slate-400">#<?= $u['iduser'] ?></td>
                            <td class="py-3 px-4 font-bold text-slate-800 flex items-center gap-2">
                                <div class="w-7 h-7 rounded-full bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-[10px]">
                                    <?= strtoupper(substr($u['username'], 0, 2)) ?>
                                </div>
                                <span><?= htmlspecialchars($u['username']) ?></span>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2.5 py-1 rounded-lg text-[10px] font-bold <?= $u['idrole'] == 1 ? 'bg-emerald-100 text-emerald-800' : ($u['idrole'] == 2 ? 'bg-teal-100 text-teal-800' : 'bg-slate-100 text-slate-600') ?>">
                                    <?= htmlspecialchars($u['nama_role']) ?>
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <?php if ($u['idrole'] == 1 || $u['idrole'] == 2): ?>
                                    <span class="text-emerald-600 font-bold flex items-center gap-1">
                                        <i class="fa-solid fa-circle text-[8px]"></i> Aktif Bisa Login
                                    </span>
                                <?php else: ?>
                                    <span class="text-slate-400 font-semibold flex items-center gap-1">
                                        <i class="fa-regular fa-circle text-[8px]"></i> Belum Dibuka
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3 px-4 text-center space-x-1">
                                <button type="button" onclick="openResetModal(<?= $u['iduser'] ?>, '<?= htmlspecialchars($u['username']) ?>')"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-amber-600 hover:bg-amber-50 transition" title="Reset Password">
                                    <i class="fa-solid fa-key"></i>
                                </button>
                                <button type="button" onclick="openRoleModal(<?= $u['iduser'] ?>, <?= $u['idrole'] ?>, '<?= htmlspecialchars($u['username']) ?>')"
                                    class="p-1.5 rounded-lg text-slate-400 hover:text-emerald-700 hover:bg-emerald-50 transition" title="Ganti Role">
                                    <i class="fa-solid fa-shield-halved"></i>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah User -->
<div id="modalUser" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('modalUser')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 class="text-base font-extrabold text-slate-800 mb-4">Tambah Pengguna Baru</h3>

        <form method="POST" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="create">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Username</label>
                <input type="text" name="username" required
                    placeholder="Contoh: kasir1"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Password</label>
                <input type="password" name="password" required
                    placeholder="Masukkan password"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block font-bold text-slate-700 mb-1">Role Pengguna</label>
                <select name="idrole" required
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="1">Super Admin (Akses Penuh)</option>
                    <option value="2" selected>Kasir (Penjualan / POS)</option>
                </select>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">
                    Simpan
                </button>
                <button type="button" onclick="closeModal('modalUser')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Reset Password -->
<div id="modalReset" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('modalReset')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 class="text-base font-extrabold text-slate-800 mb-1">Reset Password</h3>
        <p class="text-xs text-slate-500 mb-4" id="resetTargetUsername"></p>

        <form method="POST" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="reset_password">
            <input type="hidden" name="iduser" id="resetIduser" value="">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Password Baru</label>
                <input type="password" name="new_password" required
                    placeholder="Masukkan password baru"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold shadow-md">
                    Update Password
                </button>
                <button type="button" onclick="closeModal('modalReset')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Role -->
<div id="modalRole" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl max-w-sm w-full p-6 shadow-2xl relative">
        <button onclick="closeModal('modalRole')" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <h3 class="text-base font-extrabold text-slate-800 mb-1">Ubah Role Pengguna</h3>
        <p class="text-xs text-slate-500 mb-4" id="roleTargetUsername"></p>

        <form method="POST" class="space-y-4 text-xs">
            <input type="hidden" name="action" value="update_role">
            <input type="hidden" name="iduser" id="roleIduser" value="">

            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Role</label>
                <select name="idrole" id="roleSelect"
                    class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    <option value="1">Super Admin</option>
                    <option value="2">Kasir</option>
                </select>
            </div>

            <div class="pt-3 flex gap-2">
                <button type="submit" class="flex-1 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold shadow-md">
                    Simpan Perubahan
                </button>
                <button type="button" onclick="closeModal('modalRole')" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddModal() {
    document.getElementById('modalUser').classList.remove('hidden');
}
function openResetModal(id, username) {
    document.getElementById('resetIduser').value = id;
    document.getElementById('resetTargetUsername').innerText = 'Mengubah password untuk akun: ' + username;
    document.getElementById('modalReset').classList.remove('hidden');
}
function openRoleModal(id, roleId, username) {
    document.getElementById('roleIduser').value = id;
    document.getElementById('roleSelect').value = roleId;
    document.getElementById('roleTargetUsername').innerText = 'Mengubah hak akses untuk akun: ' + username;
    document.getElementById('modalRole').classList.remove('hidden');
}
function closeModal(id) {
    document.getElementById(id).classList.add('hidden');
}
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>