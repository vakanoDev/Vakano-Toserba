<?php
/**
 * Vakano Toserba - Authentication & Session Helper
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['iduser']) && !empty($_SESSION['iduser']);
}

function getUserId() {
    return $_SESSION['iduser'] ?? null;
}

function getUsername() {
    return $_SESSION['username'] ?? 'Tamu';
}

function getRoleId() {
    return (int)($_SESSION['idrole'] ?? 0);
}

function getRoleName() {
    return $_SESSION['nama_role'] ?? 'Guest';
}

function isSuperAdmin() {
    return getRoleId() === 1;
}

function isKasir() {
    return getRoleId() === 2;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . getBasePath() . 'login.php');
        exit;
    }
}

function requireSuperAdmin() {
    requireLogin();
    if (!isSuperAdmin()) {
        header('Location: ' . getBasePath() . 'pos.php?error=unauthorized');
        exit;
    }
}

function requireKasirOrSuperAdmin() {
    requireLogin();
    if (!isSuperAdmin() && !isKasir()) {
        header('Location: ' . getBasePath() . 'login.php?error=forbidden');
        exit;
    }
}

function getBasePath() {
    // Relative base path helper
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script, '/pages/') !== false) {
        return '../';
    }
    return '';
}

// Helper verifikasi password fleksibel (mendukung bcrypt $2b$, $2y$, sha256, atau plain text)
function verifyUserPassword($inputPassword, $storedPassword) {
    if (empty($storedPassword)) return false;

    // Cek bcrypt standard
    if (password_verify($inputPassword, $storedPassword)) {
        return true;
    }

    // Cek jika hash memakai $2b$ (diubah ke format $2y$ untuk PHP)
    if (strpos($storedPassword, '$2b$') === 0) {
        $converted = '$2y$' . substr($storedPassword, 4);
        if (password_verify($inputPassword, $converted)) {
            return true;
        }
    }

    // Cek SHA256
    if (hash('sha256', $inputPassword) === $storedPassword) {
        return true;
    }

    // Cek Plain
    if ($inputPassword === $storedPassword) {
        return true;
    }

    return false;
}
