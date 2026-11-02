<?php
// Dipanggil paling atas di halaman HRD: koneksi + cek login HRD (idrole = 7)
require_once __DIR__ . '/../../config/koneksi.php';
/** @var mysqli $conn */
if (session_status() === PHP_SESSION_NONE) session_start();

const IDROLE_ADMIN = 1;
const IDROLE_HRD   = 7;

if (!isset($_SESSION['iduser']) || ($_SESSION['idrole'] ?? 0) != IDROLE_HRD) {
    header('Location: ../login.php');
    exit;
}
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT); // error SQL jadi exception
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(16));

function e($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
