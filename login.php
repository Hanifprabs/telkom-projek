<?php
require 'koneksi.php';
session_start();

$message = "";
$message_type = "danger"; // default warna alert merah untuk login error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($username === '' || $password === '') {
        $message = "❌ Username dan password wajib diisi!";
    } else {
        // Cek username di DB
        $stmt = $conn->prepare("SELECT id, password, role FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $row = $result->fetch_assoc();

            if (password_verify($password, $row['password'])) {
                // Set session
                $_SESSION['user_id']  = $row['id'];
                $_SESSION['username'] = $username;
                $_SESSION['role']     = $row['role'];

                // Redirect sesuai role
                if ($row['role'] === 'admin') {
                    header("Location: index.php");
                    exit();
                } elseif ($row['role'] === 'teknisi') {
                    header("Location: pages/icons/input_material_used.php");
                    exit();
                } else {
                    $message = "❌ Role tidak valid!";
                }
            } else {
                $message = "❌ Password salah!";
            }
        } else {
            $message = "❌ Username tidak ditemukan!";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1,shrink-to-fit=no">
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
  position: absolute;
  top: 50%;
  right: 15px;
  transform: translateY(-50%);
  cursor: pointer;
  color: #6c757d;
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
    <img src="assets/images/logotelkom.png" alt="Telkom Indonesia Logo" />
</div>
<h4>Welcome Back!</h4>
<h6 class="font-weight-light">Sign in to continue.</h6>

<?php if (!empty($message)): ?>
    <div class="alert alert-<?= $message_type ?> mt-2" id="alertMessage">
        <?= htmlspecialchars($message) ?>
    </div>
<?php endif; ?>

<form class="pt-3" method="POST" novalidate>
    <div class="form-group">
        <input type="text" class="form-control form-control-lg" name="username"
               placeholder="Username" required>
    </div>
    <div class="form-group password-wrapper">
        <input type="password" class="form-control form-control-lg" id="password"
               name="password" placeholder="Password" required>
        <span class="mdi mdi-eye-off toggle-password" id="togglePassword"></span>
    </div>
    <div class="mt-3 d-grid gap-2">
        <button type="submit"
                class="btn btn-block btn-primary btn-lg font-weight-medium auth-form-btn">
            SIGN IN
        </button>
    </div>
    <div class="text-center mt-4 font-weight-light">
        Don't have an account?
        <a href="register.php" class="text-primary">Create</a>
    </div>
</form>

</div>
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
togglePassword.addEventListener('click', () => {
    const type = passwordField.type === 'password' ? 'text' : 'password';
    passwordField.type = type;
    togglePassword.classList.toggle('mdi-eye');
    togglePassword.classList.toggle('mdi-eye-off');
});

// Hapus alert otomatis dengan fade-out smooth
const alertBox = document.getElementById('alertMessage');
if (alertBox) {
    setTimeout(() => {
        alertBox.style.transition = "opacity 0.3s ease, transform 0.3s ease";
        alertBox.style.opacity = 0;
        alertBox.style.transform = "translateY(-10px)";
        setTimeout(() => alertBox.remove(), 300); // 300ms sesuai durasi transisi
    }, 1000); // tampil 3 detik sebelum memudar
}
</script>
</body>
</html>
