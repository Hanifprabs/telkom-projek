<?php
include "../../koneksi.php"; // koneksi DB

require '../../auth_check.php';
if ($_SESSION['role'] !== 'admin') {
    header('Location: input_material_used.php');
    exit();
}

// --- Pagination default ---
$limit  = 10; 
$page   = isset($_GET['page']) ? max((int)$_GET['page'], 1) : 1;
$offset = ($page - 1) * $limit;

$totalQuery = $conn->query("SELECT COUNT(*) AS total FROM teknisi");
$totalRow   = $totalQuery->fetch_assoc();
$totalData  = $totalRow['total'];
$totalPages = ceil($totalData / $limit);

// --- Ambil data teknisi default (tanpa filter search) ---
$sql = "
  SELECT id, namatek, nik, sektor, mitra, idtele, crew, valid
  FROM teknisi
  ORDER BY TRIM(LOWER(namatek)) COLLATE utf8mb4_unicode_ci ASC
  LIMIT $limit OFFSET $offset
";
$result = $conn->query($sql);
?>


<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title>Telkom Akses</title>
     <!-- endinject -->
    <link rel="shortcut icon" href="../../assets/images/TLK.png" />
  <!-- plugins:css -->
  <link rel="stylesheet" href="../../assets/vendors/feather/feather.css">
  <link rel="stylesheet" href="../../assets/vendors/ti-icons/css/themify-icons.css">
  <link rel="stylesheet" href="../../assets/vendors/css/vendor.bundle.base.css">
  <link rel="stylesheet" href="../../assets/vendors/font-awesome/css/font-awesome.min.css">
  <link rel="stylesheet" href="../../assets/vendors/mdi/css/materialdesignicons.min.css">
  <!-- endinject -->
  <!-- Plugin css for this page -->
  <link rel="stylesheet" href="../../assets/vendors/select2/select2.min.css">
  <link rel="stylesheet" href="../../assets/vendors/select2-bootstrap-theme/select2-bootstrap.min.css">
  <!-- End plugin css for this page -->
  <!-- inject:css -->
  <link rel="stylesheet" href="../../assets/css/style.css">
  
</head>

<body>


  <div class="container-scroller">
    <!-- partial:../../partials/_navbar.html -->
      <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
  <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
   <a class="navbar-brand brand-logo me-5" href="../../index.php">
  <img src="../../assets/images/logotelkom.png" alt="Telkom Indonesia Logo" class="logo-telkom" />
</a>
  </div>
  <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
    <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
      <span class="icon-menu"></span>
    </button>
    <ul class="navbar-nav mr-lg-2">
      <li class="nav-item nav-search d-none d-lg-block">
        <div class="input-group">
          <div class="input-group-prepend hover-cursor" id="navbar-search-icon">
            <span class="input-group-text" id="search">
              <i class="icon-search"></i>
            </span>
          </div>
          <input type="text" class="form-control" id="navbar-search-input" placeholder="Search now" aria-label="search" aria-describedby="search">
        </div>
      </li>
    </ul>
    <ul class="navbar-nav navbar-nav-right">
      <li class="nav-item dropdown">
        <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
          <i class="icon-bell mx-0"></i>
          <span class="count"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
          <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
          <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-success">
                <i class="ti-info-alt mx-0"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <h6 class="preview-subject font-weight-normal">Application Error</h6>
              <p class="font-weight-light small-text mb-0 text-muted"> Just now </p>
            </div>
          </a>
          <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-warning">
                <i class="ti-settings mx-0"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <h6 class="preview-subject font-weight-normal">Settings</h6>
              <p class="font-weight-light small-text mb-0 text-muted"> Private message </p>
            </div>
          </a>
          <a class="dropdown-item preview-item">
            <div class="preview-thumbnail">
              <div class="preview-icon bg-info">
                <i class="ti-user mx-0"></i>
              </div>
            </div>
            <div class="preview-item-content">
              <h6 class="preview-subject font-weight-normal">New user registration</h6>
              <p class="font-weight-light small-text mb-0 text-muted"> 2 days ago </p>
            </div>
          </a>
        </div>
      </li>
       <li class="nav-item nav-profile dropdown">
  <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown" id="profileDropdown">
    <img src="../../assets/images/TLK.png" alt="profile" class="rounded-circle me-2" width="35" height="35" />
    <span class="d-none d-md-inline text-black">
      <?= htmlspecialchars($_SESSION['username']); ?>
    </span>
  </a>
  <div class="dropdown-menu dropdown-menu-right navbar-dropdown" aria-labelledby="profileDropdown">
    <a class="dropdown-item" href="settings.php">
      <i class="ti-settings text-primary me-2"></i> Settings
    </a>
    <div class="dropdown-divider"></div>
    <!-- Tombol logout mengarah ke logout.php -->
    <a class="dropdown-item text-danger" href="../../logout.php">
      <i class="ti-power-off me-2"></i> Logout
    </a>
  </div>
</li>

      <li class="nav-item nav-settings d-none d-lg-flex">
        <a class="nav-link" href="#">
          <i class="icon-ellipsis"></i>
        </a>
      </li>
    </ul>
    <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
      <span class="icon-menu"></span>
    </button>
  </div>
</nav>


    <!-- partial -->
    <div class="container-fluid page-body-wrapper">
      <!-- partial:../../partials/_sidebar.html -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link" href="../../index.php">
              <i class="icon-grid menu-icon"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>

          <!-- ==== Sise Bar ==== -->
          </li>
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false" aria-controls="form-elements">
              <i class="icon-head menu-icon"></i>
              <span class="menu-title">Teknisi</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="form-elements">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"><a class="nav-link" href="input_teknisi.php">Input Teknisi</a></li>
              
                <li class="nav-item"><a class="nav-link" href="basic_elements.php">Data Teknisi</a></li>
            
              </ul>

            </div>
          </li>

          <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false" aria-controls="charts">
        <i class="icon-grid-2 menu-icon"></i>
        <span class="menu-title">Material</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="charts">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="../charts/input_material.php">Tambah Material</a></li>
          <li class="nav-item"> <a class="nav-link" href="../charts/data_material.php">Data Material</a></li>
        </ul>
      </div>
    </li>
    <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#icons" aria-expanded="false" aria-controls="icons">
       <i class="icon-layout menu-icon"></i>
        <span class="menu-title">Material Dipakai</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="icons">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="../icons/input_material_used.php">Tambah Material</a></li>
          <li class="nav-item"> <a class="nav-link" href="../icons/data_material_used.php">Data Material</a></li>
          <li class="nav-item"> <a class="nav-link" href="../icons/foto_material_used.php">Keluhan</a></li>
        </ul>
      </div>
    </li>
          <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#tables" aria-expanded="false" aria-controls="tables">
        <i class="icon-paper menu-icon"></i>
        <span class="menu-title">Sisa Material</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="tables">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"> <a class="nav-link" href="../tables/sisa_data.php">Data sisa</a></li>
        </ul>
      </div>
    </li>
          
          
        </ul>
      </nav>

      <!-- partial -->
      <div class="main-panel">
        <div class="content-wrapper">


       <?php
// Ambil data untuk edit
if (isset($_GET['edit'])) {
  $id = intval($_GET['edit']);
  $stmt = $conn->prepare("SELECT * FROM teknisi WHERE id=?");
  $stmt->bind_param("i", $id);
  $stmt->execute();
  $resultEdit = $stmt->get_result();
  $dataEdit = $resultEdit->fetch_assoc();
}
?>

<!-- ==== Form Tempat Edit Teknisi ==== -->
<?php if (isset($dataEdit)): ?>
  <div class="row mt-4">
    <div class="col-md-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h4 class="mb-4">Edit Data Teknisi</h4>
          <form method="post">
            <input type="hidden" name="id" value="<?= $dataEdit['id'] ?>">

            <div class="row">
              <!-- Kolom Kiri -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>Nama</label>
                  <input type="text" name="namatek" class="form-control"
                         value="<?= htmlspecialchars($dataEdit['namatek']) ?>" required>
                </div>
                <div class="form-group">
                  <label>NIK</label>
                  <input type="text" name="nik" class="form-control"
                         value="<?= htmlspecialchars($dataEdit['nik']) ?>" required>
                </div>
                <div class="form-group">
                  <label>Sektor</label>
                  <input type="text" name="sektor" class="form-control"
                         value="<?= htmlspecialchars($dataEdit['sektor']) ?>">
                </div>
                <div class="form-group">
                  <label>Mitra</label>
                  <input type="text" name="mitra" class="form-control"
                         value="<?= htmlspecialchars($dataEdit['mitra']) ?>">
                </div>
              </div>

              <!-- Kolom Kanan -->
              <div class="col-md-6">
                <div class="form-group">
                  <label>ID Tele</label>
                  <input type="text" name="idtele" class="form-control"
                         value="<?= htmlspecialchars($dataEdit['idtele']) ?>">
                </div>
                <div class="form-group">
                  <label>Crew</label>
                  <input type="text" name="crew" class="form-control"
                         value="<?= htmlspecialchars($dataEdit['crew']) ?>">
                </div>
                <div class="form-group">
                  <label>Valid</label>
                  <select name="valid" class="form-control">
                    <option value="Y" <?= $dataEdit['valid'] == "Y" ? "selected" : "" ?>>Y</option>
                    <option value="N" <?= $dataEdit['valid'] == "N" ? "selected" : "" ?>>N</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Tombol -->
            <div class="mt-3">
              <button type="submit" name="update" class="btn btn-primary">Update</button>
              <a href="basic_elements.php" class="btn btn-secondary">Batal</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
<?php endif; ?>

          <style>
  /* Pastikan tabel menyesuaikan container */
  .table-responsive {
    overflow-x: auto;
  }
  /* Hilangkan pembungkusan teks hanya jika kolom sempit */
  .table td, .table th {
    white-space: nowrap;
    vertical-align: middle;
    font-size: 0.9rem;
    padding: 0.4rem 0.6rem;
  }
  /* Batasi lebar teks panjang agar tidak memaksa scroll */
  .table td:nth-child(2),
  .table td:nth-child(5) {
    white-space: normal; /* Izinkan wrap untuk kolom Nama dan Mitra */
    word-wrap: break-word;
    max-width: 160px;
  }
  /* Buat tombol lebih kompak */
  .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
  }
</style>


          <!-- Rekap Data Teknisi -->
          <style>
  .table td, .table th {
    white-space: nowrap;
    vertical-align: middle;
    font-size: 0.9rem;
    padding: 0.4rem 0.6rem;
  }
  .table td:nth-child(2), .table td:nth-child(5) {
    white-space: normal;
    word-wrap: break-word;
    max-width: 160px;
  }
  .btn-sm { padding: 0.25rem 0.5rem; font-size: 0.8rem; }
  .pagination { margin-top: 10px; }
</style>

<div class="row">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <p class="card-title mb-3">Rekap Data Teknisi</p>
        <div class="table-responsive">
  <table class="table table-striped table-sm text-center align-middle">
    <thead class="table-light">
      <tr>
        <th>NO</th>
        <th>Nama</th>
        <th>NIK</th>
        <th>Sektor</th>
        <th>Mitra</th>
        <th>ID Tele</th>
        <th>Crew</th>
        <th>Valid</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody id="teknisi-table-body">
      <?php if ($result && $result->num_rows > 0): 
        $noTek = $offset + 1;
        while ($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?= $noTek++; ?></td>
        <td class="fw-semibold"><?= htmlspecialchars($row['namatek']) ?></td>
        <td><?= htmlspecialchars($row['nik']) ?></td>
        <td><?= htmlspecialchars($row['sektor']) ?></td>
        <td><?= htmlspecialchars($row['mitra']) ?></td>
        <td><?= htmlspecialchars($row['idtele']) ?></td>
        <td><?= htmlspecialchars($row['crew']) ?></td>
        <td>
          <span class="badge <?= $row['valid']=="Y"?'bg-success':'bg-danger' ?>">
            <?= htmlspecialchars($row['valid']) ?>
          </span>
        </td>
        <td>
          <a href="?edit=<?= $row['id'] ?>&page=<?= $page ?>" class="btn btn-warning btn-sm mb-1">
            <i class="bi bi-pencil"></i> Edit
          </a>
          <a href="?delete=<?= $row['id'] ?>&page=<?= $page ?>" class="btn btn-danger btn-sm"
             onclick="return confirm('Hapus data ini?')">
            <i class="bi bi-trash"></i> Delete
          </a>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="9">Tidak ada data.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>


        <!-- Navigasi Halaman -->
        <nav>
  <ul class="pagination justify-content-center">
    <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
      <a class="page-link" href="?page=<?= $page-1 ?>">Previous</a>
    </li>
    <?php for ($i=1; $i<=$totalPages; $i++): ?>
      <li class="page-item <?= ($i==$page)?'active':'' ?>">
        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
      </li>
    <?php endfor; ?>
    <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
      <a class="page-link" href="?page=<?= $page+1 ?>">Next</a>
    </li>
  </ul>
</nav>


      </div>
    </div>
  </div>
</div>
           <!-- Notifikasi Berhasil Tambah Data, Update, Delete -->
   <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <!-- Script notifikasi PHP -->
  <?php if (isset($_GET['status'])): ?>
    <script>
      document.addEventListener("DOMContentLoaded", function() {
          <?php if ($_GET['status'] == 'added'): ?>
              Swal.fire({
                  icon: 'success',
                  title: 'Berhasil!',
                  text: 'Data berhasil ditambahkan.',
                  showConfirmButton: false,
                  timer: 2000
              });
          <?php elseif ($_GET['status'] == 'updated'): ?>
              Swal.fire({
                  icon: 'success',
                  title: 'Berhasil!',
                  text: 'Data berhasil diperbarui.',
                  showConfirmButton: false,
                  timer: 2000
              });
          <?php elseif ($_GET['status'] == 'deleted'): ?>
              Swal.fire({
                  icon: 'success',
                  title: 'Berhasil!',
                  text: 'Data berhasil dihapus.',
                  showConfirmButton: false,
                  timer: 2000
              });
          <?php endif; ?>
      });
    </script>
  <?php endif; ?>

        </div>
        <!-- content-wrapper ends -->
        <!-- partial:../../partials/_footer.html -->
        <!-- Footer -->
                <footer class="footer">
                    <div class="d-sm-flex justify-content-center justify-content-sm-between">
                        <span class="text-muted text-center text-sm-left d-block d-sm-inline-block">
                            © <?= date('Y') ?>. <strong>Telkom Akses</strong> by Telkom Indonesia. All rights reserved.</span>
                        </span>
                        <span class="float-none float-sm-right d-block mt-1 mt-sm-0 text-center">
                            Hand-crafted &amp; made with
                            <i class="ti-heart text-danger ms-1"></i>
                        </span>
                    </div>
                </footer>
        <!-- partial -->
      </div>
      <!-- main-panel ends -->
    </div>
    <!-- page-body-wrapper ends -->
  </div>
  <!-- container-scroller -->

  <script>
const searchInput = document.getElementById("navbar-search-input");
const tableBody   = document.getElementById("teknisi-table-body");
const defaultTable = tableBody.innerHTML; // simpan isi default tabel

searchInput.addEventListener("keyup", function() {
    let query = this.value.trim();

    if (query.length === 0) {
        // kalau input kosong -> kembali ke default tabel
        tableBody.innerHTML = defaultTable;
        return;
    }

    fetch("search_teknisi.php?q=" + encodeURIComponent(query))
        .then(response => response.text())
        .then(data => {
            tableBody.innerHTML = data;
        });
});
</script>

  <!-- plugins:js -->
  <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
  <!-- endinject -->
  <!-- Plugin js for this page -->
  <script src="../../assets/vendors/typeahead.js/typeahead.bundle.min.js"></script>
  <script src="../../assets/vendors/select2/select2.min.js"></script>
  <!-- End plugin js for this page -->
  <!-- inject:js -->
  <script src="../../assets/js/off-canvas.js"></script>
  <script src="../../assets/js/template.js"></script>
  <script src="../../assets/js/settings.js"></script>
  <script src="../../assets/js/todolist.js"></script>
  <!-- endinject -->
  <!-- Custom js for this page-->
  <script src="../../assets/js/file-upload.js"></script>
  <script src="../../assets/js/typeahead.js"></script>
  <script src="../../assets/js/select2.js"></script>
  <!-- End custom js for this page-->
</body>

</html>