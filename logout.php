<?php
session_start(); // Mulai sesi

// Hapus semua data sesi
$_SESSION = [];

// Hapus cookie sesi (jika ada)
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Hancurkan sesi
session_destroy();

// Redirect ke halaman login atau index
header("Location: index.php");
exit();
?>
