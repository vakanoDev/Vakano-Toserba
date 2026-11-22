<?php
require_once __DIR__ . '/includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

if (isSuperAdmin()) {
    header('Location: dashboard.php');
} else {
    header('Location: pos.php');
}
exit;
