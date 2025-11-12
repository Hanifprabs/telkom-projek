<?php
include "../../koneksi.php"; // koneksi DB

require '../../auth_check.php';
if ($_SESSION['role'] !== 'admin') {
    header('Location: input_material_used.php');
    exit();
}
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
    <input type="text" class="form-control" id="navbar-search-input" 
           placeholder="Search now" aria-label="search" aria-describedby="search">
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


    <!-- ==== Form Tambah/Edit Teknisi ==== -->
    </li>
     </li>
     <li class="nav-item">
      <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false" aria-controls="form-elements">
        <i class="icon-head menu-icon"></i>
        <span class="menu-title">Teknisi</span>
        <i class="menu-arrow"></i>
      </a>
      <div class="collapse" id="form-elements">
        <ul class="nav flex-column sub-menu">
          <li class="nav-item"><a class="nav-link" href="../forms/input_teknisi.php">Tambah Teknisi</a></li>
          <li class="nav-item"><a class="nav-link" href="../forms/basic_elements.php">Data Teknisi</a></li>
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
          <li class="nav-item"> <a class="nav-link" href="input_material_used.php">Tambah Material</a></li>
          <li class="nav-item"> <a class="nav-link" href="data_material_used.php">Data Material</a></li>
          <li class="nav-item"> <a class="nav-link" href="foto_material_used.php">Keluhan</a></li>
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
// ================== AMBIL DATA UNTUK EDIT ================== //
$editDetail = null;
$editTeknisiLabel = "";

if (isset($_GET['edit_detail'])) {
    $id = (int) $_GET['edit_detail'];

    $stmt = $conn->prepare("
        SELECT m.*, t.namatek 
        FROM material_used m
        JOIN teknisi t ON m.teknisi_id = t.id
        WHERE m.id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res && $res->num_rows > 0) {
        $editDetail = $res->fetch_assoc();
        $editTeknisiLabel = $editDetail['namatek'];
    }
    $stmt->close();
}
?>



<!-- ====================== FORM EDIT MATERIAL (STYLE RINGKAS) ====================== -->
<style>
/* ====================== Form & Input (Compact Style) ====================== */
.card-body, .form-control, .input-group-text, label {
  font-size: 0.78rem; /* sedikit lebih kecil dari default, tidak terlalu kecil */
}

.form-control, .input-group-text {
  padding: 0.18rem 0.4rem; /* lebih rapat tapi tetap nyaman */
  height: 28px; /* lebih pendek dan seragam */
  line-height: 1.2;
}

.form-group {
  margin-bottom: 0.35rem; /* jarak antar form diperkecil */
}

.form-control-sm, .input-group-sm > .form-control {
  height: 28px;
  font-size: 12.5px;
}

.input-group-text.small-label, .small-label {
  font-size: 11px;
  padding: 1px 6px;
  white-space: nowrap;
}

.row.align-items-end > .col-md-3 {
  margin-bottom: 6px; /* jarak antar kolom sedikit */
}

/* ====================== Tombol ====================== */
.btn-sm {
  font-size: 12px;
  padding: 2px 8px;
  height: 28px;
  line-height: 1.2;
}

/* ====================== Precont List ====================== */
.precont-list {
  display: flex;
  flex-direction: column;
  gap: 2px;
  align-items: flex-start;
}
.precont-item {
  font-size: 0.72rem;
  background: #f8f9fa;
  border-radius: 4px;
  padding: 1px 5px;
  line-height: 1.1;
  transition: background 0.2s;
}
.precont-item:hover {
  background: #e2e6ea;
}
</style>

<?php if ($editDetail): ?>
  <style>
  .card-body, .form-control, .input-group-text, label { font-size: 0.78rem; }
  .form-control, .input-group-text { padding: 0.18rem 0.4rem; height: 28px; }
  .form-group { margin-bottom: 0.35rem; }
  </style>
<?php endif; ?>


<?php if (isset($editDetail)): ?>
<div class="row mt-4">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card">
      <div class="card-body">
        <h4>✏️ Edit Detail Material Teknisi</h4>

        <form method="POST" enctype="multipart/form-data">
          <input type="hidden" name="id" value="<?= htmlspecialchars($editDetail['id'] ?? '') ?>">
          <input type="hidden" name="user_id" value="<?= $_SESSION['user_id'] ?? '' ?>">

          <!-- Cari Teknisi & WO -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group position-relative">
                <label>Cari Teknisi</label>
                <input type="text" id="teknisiSearch" class="form-control form-control-sm"
                       placeholder="Ketik nama teknisi..." autocomplete="off"
                       value="<?= htmlspecialchars($editTeknisiLabel ?? '') ?>">
                <input type="hidden" name="teknisi_id" id="teknisiId"
                       value="<?= htmlspecialchars($editDetail['teknisi_id'] ?? '') ?>">
                <div id="teknisiList" class="list-group position-absolute w-100"></div>
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>Nomor WO</label>
                <input type="text" name="wo" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($editDetail['wo'] ?? '') ?>">
              </div>
            </div>
          </div>

          <!-- DC, S-calm, Clam Hook -->
          <div class="row">
            <div class="col-md-4"><div class="form-group"><label>DC</label><input type="number" name="dc" class="form-control form-control-sm"
                value="<?= htmlspecialchars($editDetail['dc'] ?? '') ?>"></div></div>
            <div class="col-md-4"><div class="form-group"><label>S-calm</label><input type="number" name="s_calm" class="form-control form-control-sm"
                value="<?= htmlspecialchars($editDetail['s_calm'] ?? '') ?>"></div></div>
            <div class="col-md-4"><div class="form-group"><label>Clam Hook</label><input type="number" name="clam_hook" class="form-control form-control-sm"
                value="<?= htmlspecialchars($editDetail['clam_hook'] ?? '') ?>"></div></div>
          </div>

          <!-- OTP, Prekso, Tiang -->
          <div class="row">
            <div class="col-md-4"><div class="form-group"><label>OTP</label><input type="number" name="otp" class="form-control form-control-sm"
                value="<?= htmlspecialchars($editDetail['otp'] ?? '') ?>"></div></div>
            <div class="col-md-4"><div class="form-group"><label>Prekso</label><input type="number" name="prekso" class="form-control form-control-sm"
                value="<?= htmlspecialchars($editDetail['prekso'] ?? '') ?>"></div></div>
            <div class="col-md-4"><div class="form-group"><label>Tiang</label><input type="number" name="tiang" class="form-control form-control-sm"
                value="<?= htmlspecialchars($editDetail['tiang'] ?? '') ?>"></div></div>
          </div>

          <!-- Tanggal & SOC -->
          <div class="row">
            <div class="col-md-6">
              <div class="form-group">
                <label>Tanggal</label>
                <?php
                  $tanggal_val = '';
                  if (!empty($editDetail['tanggal'])) {
                    $ts = strtotime($editDetail['tanggal']);
                    if ($ts !== false) $tanggal_val = date('Y-m-d', $ts);
                  }
                ?>
                <input type="date" name="tanggal" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($tanggal_val) ?>">
              </div>
            </div>
            <div class="col-md-6">
              <div class="form-group">
                <label>SOC</label>
                <div class="input-group input-group-sm">
                  <select name="soc_option" class="form-control form-control-sm">
                    <option value="">-- Pilih SOC --</option>
                    <option value="Fuji" <?= $editDetail['soc_option']=="Fuji" ? "selected":"" ?>>Fuji</option>
                    <option value="Sum" <?= $editDetail['soc_option']=="Sum" ? "selected":"" ?>>Sum</option>
                  </select>
                  <input type="number" name="soc_value" class="form-control form-control-sm" placeholder="Nilai"
                         value="<?= htmlspecialchars($editDetail['soc_value'] ?? '') ?>">
                </div>
              </div>
            </div>
          </div>

          <!-- Precont -->
          <div class="form-group mt-3">
            <label>Precont</label>
            <div class="row">
              <?php
                $editPrecont = [];
                if (!empty($editDetail['precont_json'])) {
                  $tmp = json_decode($editDetail['precont_json'], true);
                  if (is_array($tmp)) $editPrecont = $tmp;
                }
                foreach ([50,75,80,100,120,135,150,180] as $val):
              ?>
              <div class="col-md-3 col-6 mb-2">
                <div class="input-group input-group-sm">
                  <span class="input-group-text small-label"><?= $val ?></span>
                  <input type="number" name="precont[<?= $val ?>]" class="form-control form-control-sm" placeholder="Nilai"
                         value="<?= htmlspecialchars($editPrecont[$val] ?? '') ?>">
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Spliter -->
          <div class="form-group mt-3">
            <label>Spliter</label>
            <div class="row">
              <?php
                $editSpliter = [];
                if (!empty($editDetail['spliter_json'])) {
                  $tmp = json_decode($editDetail['spliter_json'], true);
                  if (is_array($tmp)) $editSpliter = $tmp;
                }
                foreach (["1.2", "1.4", "1.8", "1.16"] as $val):
              ?>
              <div class="col-md-3 col-6 mb-2">
                <div class="input-group input-group-sm">
                  <span class="input-group-text small-label"><?= $val ?></span>
                  <input type="number" name="spliter[<?= $val ?>]" class="form-control form-control-sm" placeholder="Jumlah"
                         value="<?= htmlspecialchars($editSpliter[$val] ?? '') ?>">
                </div>
              </div>
              <?php endforeach; ?>
            </div>
          </div>

          <!-- Smoove + AD-SC + Tipe Pekerjaan -->
          <div class="form-group mt-3">
            <div class="row align-items-end g-2">
              <?php
                $editSmoove = [];
                if (!empty($editDetail['smoove_json'])) {
                  $tmp = json_decode($editDetail['smoove_json'], true);
                  if (is_array($tmp)) $editSmoove = $tmp;
                }
                foreach (['Kecil','Tipe 3'] as $val):
              ?>
              <div class="col-md-3 col-6">
                <label class="form-label mb-1">Smoove</label>
                <div class="input-group input-group-sm">
                  <span class="input-group-text small-label"><?= $val ?></span>
                  <input type="number" name="smoove[<?= $val ?>]" class="form-control form-control-sm" placeholder="Jumlah"
                         value="<?= htmlspecialchars($editSmoove[$val] ?? '') ?>">
                </div>
              </div>
              <?php endforeach; ?>

              <div class="col-md-3 col-6">
                <label class="form-label mb-1">AD-SC</label>
                <input type="number" name="ad_sc" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($editDetail['ad_sc'] ?? '') ?>">
              </div>

              <div class="col-md-3 col-6">
                <label class="form-label mb-1">Tipe Pekerjaan</label>
                <select name="tipe_pekerjaan" class="form-control form-control-sm">
                  <option value="">Tipe Pekerjaan</option>
                  <?php
                  $tipeOptions = ["IOAN","Provisioning","Maintenance","Konstruksi","Mitratel"];
                  foreach ($tipeOptions as $opt):
                  ?>
                    <option value="<?= $opt ?>" <?= ($editDetail['tipe_pekerjaan'] ?? '') == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
          </div>

          <!-- Tombol Aksi -->
          <div class="mt-3 text-end">
            <button type="submit" name="update_material" class="btn btn-primary btn-sm">
              <i class="bi bi-pencil-square"></i> Update
            </button>
            <a href="data_material_used.php" class="btn btn-secondary btn-sm">Batal</a>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>
<?php endif; ?>



<style>
  /* ======== TABEL DASAR ======== */
  .table {
    border-collapse: collapse !important;
    width: 100%;
  }

  .table td, .table th {
    border: 1px solid #dee2e6;
    padding: 0.3rem 0.5rem;  /* gabungan antara 0.25rem dan 0.4rem */
    font-size: 0.8rem;       /* pas di tengah antara 0.78–0.85rem */
    vertical-align: middle !important;
    text-align: center;
    white-space: nowrap;
    line-height: 1.1;
  }

  .table-dark th {
    background-color: #212529 !important;
    color: #fff !important;
    font-size: 0.8rem;
    font-weight: 600;
    text-align: center;
    vertical-align: middle !important;
  }

  /* ======== WARNA BARIS ======== */
  .table-striped tbody tr:nth-of-type(odd) {
    background-color: #f9fafb;
  }

  tbody tr:hover {
    background-color: #eef6ff;
    transition: background 0.2s ease;
  }

  /* ======== KOLOM PRECONT, SPLITER, SMOOVE ======== */
  .precont-list, .precont-cell {
    display: flex;
    flex-direction: column;
    gap: 2px;
    align-items: flex-start;
    white-space: normal;
    text-align: left;
  }

  .precont-item {
    display: inline-block;
    background: #f1f3f5;
    padding: 2px 5px;
    border-radius: 4px;
    border: 1px solid #dee2e6;
    font-size: 0.75rem;
    margin: 1px 0;
    line-height: 1.1;
  }

  /* ======== KOLOM ANGKA DAN TENGAH ======== */
  .soc-cell,
  td.text-right {
    text-align: right !important;
    vertical-align: middle !important;
  }

  td, th {
    vertical-align: middle !important;
    text-align: center;
  }

  /* ======== PERBAIKAN KHUSUS AD-SC ======== */
  td:nth-child(15),
  th:nth-child(15) {
    width: 60px; /* sedikit lebih lebar biar tidak terpotong */
    text-align: center;
    vertical-align: middle !important;
  }

  /* ======== TOMBOL ======== */
  .btn-sm {
    font-size: 0.7rem;
    padding: 2px 6px;
    vertical-align: middle;
    height: 26px;
    line-height: 1.1;
  }

  .aksi-btn {
    margin: 2px 0;
  }

  /* ======== PAGINATION ======== */
  .pagination .page-link {
    font-size: 0.75rem;
    padding: 3px 8px;
  }
</style>


<!-- REKAP DATA MATERIAL -->
<div class="row mt-4">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card shadow">
      <div class="card-body">
        <p class="card-title mb-0">Rekap Data Material</p>
        <div class="table-responsive">
          <table class="table table-striped table-borderless text-center align-middle">
            <thead class="table-dark">
              <tr>
                <th>NO</th>
                <th>Nama Teknisi</th>
                <th>Tanggal</th>
                <th>WO</th>
                <th>DC</th>
                <th>S-calm</th>
                <th>Clam Hook</th>
                <th>OTP</th>
                <th>Prekso</th>
                <th>Tiang</th>
                <th>SOC</th>
                <th>Precont</th>
                <th>Spliter</th>
                <th>Smoove</th>
                <th>Ad-SC</th>
                <th>Tipe Pekerjaan</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
<?php
include '../../koneksi.php';

// ambil keyword search (jika ada)
$q = isset($_GET['q']) ? trim($_GET['q']) : '';

// --- Pagination Setup --- //
$limit = 5;
$page  = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page > 1) ? ($page * $limit) - $limit : 0;

if ($q !== '') {
  $safe = $conn->real_escape_string($q);
  $sql = "
    SELECT m.*, t.namatek
    FROM material_used m
    JOIN teknisi t ON m.teknisi_id = t.id
    WHERE t.namatek LIKE '%$safe%'
    ORDER BY m.id DESC
  ";
  $qDetail = $conn->query($sql);
} else {
  $resultTotal = $conn->query("SELECT COUNT(*) AS total FROM material_used");
  $totalData   = $resultTotal->fetch_assoc()['total'];
  $totalPages  = ceil($totalData / $limit);

  $qDetail = $conn->query("
    SELECT m.*, t.namatek
    FROM material_used m
    JOIN teknisi t ON m.teknisi_id = t.id
    ORDER BY m.id DESC
    LIMIT $start, $limit
  ");
}

if ($qDetail && $qDetail->num_rows > 0):
  $no = ($q !== '') ? 1 : $start + 1;
  while ($d = $qDetail->fetch_assoc()):
?>
  <tr>
    <td><?= $no++ ?></td>
    <td><?= htmlspecialchars($d['namatek']) ?></td>
    <td><?= htmlspecialchars($d['tanggal']) ?></td>
    <td><?= htmlspecialchars($d['wo']) ?></td>
    <td><?= htmlspecialchars($d['dc']) ?></td>
    <td><?= htmlspecialchars($d['s_calm']) ?></td>
    <td><?= htmlspecialchars($d['clam_hook']) ?></td>
    <td><?= htmlspecialchars($d['otp']) ?></td>
    <td><?= htmlspecialchars($d['prekso']) ?></td>
    <td><?= htmlspecialchars($d['tiang']) ?></td>
    <td>
      <?= htmlspecialchars($d['soc_option']) ?>
      <?php if (!empty($d['soc_value'])): ?>
        (<?= htmlspecialchars($d['soc_value']) ?>)
      <?php endif; ?>
    </td>
    <td>
      <?php
        if (!empty($d['precont_json'])) {
          $pc = json_decode($d['precont_json'], true);
          if (is_array($pc)) {
            foreach ($pc as $k => $v) {
              echo htmlspecialchars($k) . " (" . htmlspecialchars($v) . ")<br>";
            }
          }
        } else {
          echo "-";
        }
      ?>
    </td>
    <td>
      <?php
        if (!empty($d['spliter_json'])) {
          $sp = json_decode($d['spliter_json'], true);
          if (is_array($sp)) {
            foreach ($sp as $k => $v) {
              echo htmlspecialchars($k) . " (" . htmlspecialchars($v) . ")<br>";
            }
          }
        } else {
          echo "-";
        }
      ?>
    </td>
    <td>
      <?php
        if (!empty($d['smoove_json'])) {
          $sm = json_decode($d['smoove_json'], true);
          if (is_array($sm)) {
            foreach ($sm as $k => $v) {
              echo htmlspecialchars($k) . " (" . htmlspecialchars($v) . ")<br>";
            }
          }
        } else {
          echo "-";
        }
      ?>
    </td>
    <td><?= htmlspecialchars($d['ad_sc']) ?></td>
    <td><?= htmlspecialchars($d['tipe_pekerjaan']) ?></td>
    <td>
      <a href="?edit_detail=<?= $d['id'] ?>&page=<?= $page ?>" class="btn btn-sm btn-warning">
        <i class="bi bi-pencil"></i> Edit
      </a>
      <a href="?delete_material=<?= $d['id'] ?>&page=<?= $page ?>" 
         class="btn btn-sm btn-danger"
         onclick="return confirm('Yakin hapus data ini?')">
         <i class="bi bi-trash"></i> Delete
      </a>
    </td>
  </tr>
<?php endwhile; else: ?>
  <tr>
    <td colspan="17">Tidak ada data material.</td>
  </tr>
<?php endif; ?>
</tbody>
          </table>
        </div>

        <!-- Pagination -->
        <nav>
          <ul class="pagination justify-content-center">
            <?php if($page > 1): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $page-1 ?>">Previous</a>
              </li>
            <?php endif; ?>

            <?php for($i = 1; $i <= $totalPages; $i++): ?>
              <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
              </li>
            <?php endfor; ?>

            <?php if($page < $totalPages): ?>
              <li class="page-item">
                <a class="page-link" href="?page=<?= $page+1 ?>">Next</a>
              </li>
            <?php endif; ?>
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

    <!-- End Rekap Data Material -->

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

      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){
  $("#navbar-search-input").on("keyup", function(){
    var keyword = $(this).val();

    if (keyword.length > 0) {
      $.ajax({
        url: "search_material.php",
        type: "GET",
        data: { q: keyword },
        success: function(data){
          $("#material-data").html(data);
          $(".pagination").hide(); // sembunyikan pagination saat search
        }
      });
    } else {
      $.ajax({
        url: "search_material.php",
        type: "GET",
        data: { q: "" },
        success: function(data){
          $("#material-data").html(data);
          $(".pagination").show(); // tampilkan lagi pagination
        }
      });
    }
  });
});
</script>


    <!-- plugins:js -->
    <script src="../../assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="../../assets/vendors/chart.js/chart.umd.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="../../assets/js/off-canvas.js"></script>
    <script src="../../assets/js/template.js"></script>
    <script src="../../assets/js/settings.js"></script>
    <script src="../../assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="../../assets/js/chart.js"></script>
    <!-- End custom js for this page-->
  </body>
</html>