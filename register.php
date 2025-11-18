<?php
require 'koneksi.php';
session_start();

$message = "";
$message_type = ""; // untuk jenis alert: success / danger

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $nik      = trim($_POST['nik']);

    // 🔎 Cek apakah NIK ada di tabel teknisi
    $cekNik = $conn->prepare("SELECT id FROM teknisi WHERE nik = ?");
    $cekNik->bind_param("s", $nik);
    $cekNik->execute();
    $cekNik->store_result();

    if ($cekNik->num_rows > 0) {
        // ➕ Cek apakah NIK sudah dipakai untuk registrasi sebelumnya
        $cekNikUsed = $conn->prepare("
            SELECT u.id
            FROM users u
            JOIN teknisi t ON t.nik = ?
            WHERE u.username = t.nik OR u.username = ?
        ");
        $cekNikUsed->bind_param("ss", $nik, $username);
        $cekNikUsed->execute();
        $cekNikUsed->store_result();

        // 🔎 Alternatif cek langsung di tabel users
        $cekNikDirect = $conn->prepare("SELECT id FROM users WHERE username = ?");
        $cekNikDirect->bind_param("s", $nik);
        $cekNikDirect->execute();
        $cekNikDirect->store_result();

        if ($cekNikUsed->num_rows > 0 || $cekNikDirect->num_rows > 0) {
            $message = "❌ NIK ini sudah diregistrasikan sebelumnya.";
            $message_type = "danger";
        } else {
            // ✅ Pastikan username belum dipakai
            $cekUser = $conn->prepare("SELECT id FROM users WHERE username = ?");
            $cekUser->bind_param("s", $username);
            $cekUser->execute();
            $cekUser->store_result();

          if ($cekUser->num_rows === 0) {
                // Masukkan user baru + simpan NIK ke table users
                $stmt = $conn->prepare("INSERT INTO users (username, password, role, nik) VALUES (?, ?, 'teknisi', ?)");
                $stmt->bind_param("sss", $username, $password, $nik);

                if ($stmt->execute()) {
                    $message = "✅ Registrasi berhasil. Silakan login.";
                    $message_type = "success";
                } else {
                    $message = "❌ Gagal menyimpan data pengguna.";
                    $message_type = "danger";
                }
            }

        }
    } else {
        $message = "❌ NIK tidak ditemukan di data teknisi.";
        $message_type = "danger";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<title>Telkom Akses</title>
     <!-- endinject -->
    <link rel="shortcut icon" href="assets/images/TLK.png" />
<link rel="stylesheet" href="assets/vendors/feather/feather.css">
<link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
<link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
<link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
<link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
<link rel="stylesheet" href="assets/css/style.css">
<style>
.password-wrapper { position: relative; }
.toggle-password {
    position: absolute; top: 50%; right: 15px;
    transform: translateY(-50%); cursor: pointer; color: #6c757d;
}
</style>
</head>
<body>
<div class="container-scroller">
<div class="container-fluid page-body-wrapper full-page-wrapper">
<div class="content-wrapper d-flex align-items-center auth px-0">
<div class="row w-100 mx-0">
<div class="col-lg-4 mx-auto">
<div class="auth-form-light text-left py-5 px-4 px-sm-5">
<div class="brand-logo text-center mb-3">
    <img src="assets/images/logotelkom.png" alt="Telkom Indonesia Logo" class="logo-telkom" />
</div>
<h4>Daftar Teknisi</h4>
<h6 class="font-weight-light">Masukkan NIK terdaftar untuk membuat akun.</h6>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?= $message_type ?> mt-2" id="alertMessage">
        <?= $message ?>
    </div>
<?php endif; ?>

<form class="pt-3" method="POST">
    <div class="form-group">
        <input type="text" class="form-control form-control-lg" name="nik" placeholder="NIK" required>
    </div>
    <div class="form-group">
        <input type="text" class="form-control form-control-lg" name="username" placeholder="Username" required>
    </div>
    <div class="form-group password-wrapper">
        <input type="password" class="form-control form-control-lg" id="password" name="password" placeholder="Password" required>
        <span class="mdi mdi-eye-off toggle-password" id="togglePassword"></span>
    </div>
    <div class="mt-3 d-grid gap-2">
        <button type="submit" class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
            SIGN UP
        </button>
    </div>
    <div class="text-center mt-4 font-weight-light">
        Sudah punya akun? <a href="login.php" class="text-primary">Login</a>
    </div>
</form>

</div>
</div>
</div>
</div>
</div>

<script src="assets/vendors/js/vendor.bundle.base.js"></script>
<script src="assets/js/off-canvas.js"></script>
<script src="assets/js/template.js"></script>
<script src="assets/js/settings.js"></script>
<script src="assets/js/todolist.js"></script>
<script>
// Toggle password visibility
const togglePassword = document.getElementById('togglePassword');
const passwordField = document.getElementById('password');
togglePassword.addEventListener('click', function () {
    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordField.setAttribute('type', type);
    this.classList.toggle('mdi-eye');
    this.classList.toggle('mdi-eye-off');
});

// Hapus alert otomatis dengan fade-out lebih cepat dan smooth
const alertBox = document.getElementById('alertMessage');
if (alertBox) {
    setTimeout(() => {
        alertBox.style.transition = "opacity 0.3s ease, transform 0.3s ease";
        alertBox.style.opacity = 0;
        alertBox.style.transform = "translateY(-10px)";
        setTimeout(() => alertBox.remove(), 300); // 300ms sesuai durasi transisi
    }, 1000); // 3000ms = 3 detik sebelum mulai memudar
}
</script>

</body>
</html>
