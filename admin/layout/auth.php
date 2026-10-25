<?php
// Dipanggil paling atas di setiap halaman admin: koneksi + cek login Admin (idrole = 1)
require_once __DIR__ . '/../../config/koneksi.php';
require_once __DIR__ . '/../../config/tabel_config.php';
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['iduser']) || ($_SESSION['idrole'] ?? 0) != 1) {
    header('Location: ../login.php');
    exit;
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // error SQL jadi exception
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
