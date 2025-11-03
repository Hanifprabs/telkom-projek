<?php
// Mulai session jika belum dimulai
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Jika belum login → lempar ke login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Dapatkan nama file yang sedang diakses
$current = basename($_SERVER['PHP_SELF']);

// Jika role teknisi → batasi akses hanya input_material_used.php & logout.php
if ($_SESSION['role'] === 'teknisi') {
    if ($current !== 'input_material_used.php' && $current !== 'logout.php') {
        header('Location: input_material_used.php');
        exit();
    }
}

// Role admin tidak dibatasi, jadi tidak perlu aturan tambahan
?>
