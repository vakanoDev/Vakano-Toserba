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

// Verifikasi password: hash bcrypt dibuat dengan password_hash() di PHP
function verifyUserPassword($inputPassword, $storedPassword) {
    return !empty($storedPassword) && password_verify($inputPassword, $storedPassword);
}