<?php
include "../../koneksi.php"; // koneksi DB

require '../../auth_check.php';
if ($_SESSION['role'] !== 'admin') {
    header('Location: input_material_used.php');
    exit();
}
// --- Inisialisasi ukuran Precont
$PRECONT_SIZES = [50, 75, 80, 100, 120, 135, 150, 180];

// --- Fungsi bantu buat baris kosong
function initAggRow($sizes = []) {
    $row = [
        'dc'        => 0,
        's_calm'    => 0,
        'clam_hook' => 0,
        'otp'       => 0,
        'prekso'    => 0,
        'tiang'     => 0,
        'soc_fuji'  => 0,
        'soc_sum'   => 0,
        'precont'   => []
    ];
    foreach ($sizes as $s) {
        $row['precont'][$s] = 0;
    }
    return $row;
}

// --- Pagination setup
$limit  = 5;
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// --- Hitung total teknisi dari teknisi_detail
$sqlCount = "SELECT COUNT(DISTINCT t.id) as total
             FROM teknisi t 
             JOIN teknisi_detail d ON t.id = d.teknisi_id";
$resCount = $conn->query($sqlCount);
$totalRows = ($resCount && $resCount->num_rows > 0) ? $resCount->fetch_assoc()['total'] : 0;
$totalPages = ceil($totalRows / $limit);

// --- Ambil teknisi yang punya data di teknisi_detail
$teknisiRows = [];
$sqlTeknisi  = "
    SELECT DISTINCT t.id, t.namatek 
    FROM teknisi t 
    JOIN teknisi_detail d ON t.id = d.teknisi_id
    ORDER BY t.namatek ASC
    LIMIT $limit OFFSET $offset
";
$resTeknisi = $conn->query($sqlTeknisi);
if ($resTeknisi && $resTeknisi->num_rows > 0) {
    while ($row = $resTeknisi->fetch_assoc()) {
        $teknisiRows[$row['id']] = $row['namatek'];
    }
}
$listTekIds = !empty($teknisiRows) ? implode(",", array_keys($teknisiRows)) : "0";

// --- Variabel agregasi
$aggMasuk   = [];
$aggPakai   = [];
$detailInfo = [];

// =============================
// === Data Masuk (Admin) ======
// =============================
$sqlDetail = "SELECT * FROM teknisi_detail WHERE teknisi_id IN ($listTekIds)";
$resDetail = $conn->query($sqlDetail);
if ($resDetail && $resDetail->num_rows > 0) {
    while ($d = $resDetail->fetch_assoc()) {
        $tid = $d['teknisi_id'];

        if (!isset($aggMasuk[$tid])) $aggMasuk[$tid] = initAggRow($PRECONT_SIZES);

        // Agregasi material masuk
        foreach (['dc','s_calm','clam_hook','otp','prekso','tiang'] as $k) {
            $aggMasuk[$tid][$k] += (int)$d[$k];
        }

        // SOC: bedakan Fuji & Sum
        $socOpt = strtolower(trim($d['soc_option']));
        if ($socOpt === 'fuji') {
            $aggMasuk[$tid]['soc_fuji'] += (int)$d['soc_value'];
        } elseif ($socOpt === 'sum') {
            $aggMasuk[$tid]['soc_sum']  += (int)$d['soc_value'];
        }

        // Precont
        if (!empty($d['precont_json'])) {
            $decoded = json_decode($d['precont_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $sizeKey => $sizeVal) {
                    $sizeInt = (int)$sizeKey;
                    if (in_array($sizeInt, $PRECONT_SIZES, true) && is_numeric($sizeVal)) {
                        $aggMasuk[$tid]['precont'][$sizeInt] += (int)$sizeVal;
                    }
                }
            }
        } elseif (!empty($d['precont_option']) && isset($d['precont_value'])) {
            $size = (int)$d['precont_option'];
            $val  = (int)$d['precont_value'];
            if (in_array($size, $PRECONT_SIZES, true)) {
                $aggMasuk[$tid]['precont'][$size] += $val;
            }
        }

        if (!isset($detailInfo[$tid]['rfs'])) {
            $detailInfo[$tid]['rfs'] = $d['rfs'];
        }
    }
}

// =============================
// === Data Pakai (Dipakai) ====
// =============================
$sqlUsed = "SELECT * FROM material_used WHERE teknisi_id IN ($listTekIds)";
$resUsed = $conn->query($sqlUsed);
if ($resUsed && $resUsed->num_rows > 0) {
    while ($d = $resUsed->fetch_assoc()) {
        $tid = $d['teknisi_id'];
        if (!isset($aggPakai[$tid])) $aggPakai[$tid] = initAggRow($PRECONT_SIZES);

        foreach (['dc','s_calm','clam_hook','otp','prekso','tiang'] as $k) {
            $aggPakai[$tid][$k] += (int)$d[$k];
        }

        // SOC: bedakan Fuji & Sum
        $socOpt = strtolower(trim($d['soc_option']));
        if ($socOpt === 'fuji') {
            $aggPakai[$tid]['soc_fuji'] += (int)$d['soc_value'];
        } elseif ($socOpt === 'sum') {
            $aggPakai[$tid]['soc_sum']  += (int)$d['soc_value'];
        }

        if (!isset($detailInfo[$tid]['wo'])) {
            $detailInfo[$tid]['wo'] = $d['wo'];
        }

        if (!empty($d['precont_json'])) {
            $decoded = json_decode($d['precont_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $sizeKey => $sizeVal) {
                    $sizeInt = (int)$sizeKey;
                    if (in_array($sizeInt, $PRECONT_SIZES, true) && is_numeric($sizeVal)) {
                        $aggPakai[$tid]['precont'][$sizeInt] += (int)$sizeVal;
                    }
                }
            }
        } elseif (!empty($d['precont_option']) && isset($d['precont_value'])) {
            $size = (int)$d['precont_option'];
            $val  = (int)$d['precont_value'];
            if (in_array($size, $PRECONT_SIZES, true)) {
                $aggPakai[$tid]['precont'][$size] += $val;
            }
        }
    }
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

<style>
  /* ================== Tabel Umum ================== */
  .table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.75rem; /* 🔹 perkecil font */
  }

  .table th,
  .table td {
    padding: 0.25rem 0.4rem; /* 🔹 perkecil padding */
    vertical-align: middle;
    text-align: center;
    border: 1px solid #dee2e6;
  }

  .table thead th {
    background-color: #212529;
    color: #fff;
    font-weight: 600;
    font-size: 0.78rem; /* header sedikit lebih besar */
  }

  /* ================== Kolom Precont ================== */
  .precont-cell {
    white-space: normal;  /* 🔹 biar bisa turun ke bawah */
    text-align: left;
    min-width: 120px;     /* biar tetap terbaca */
    font-size: 0.7rem;
  }

  .precont-item {
    display: block;
    background: #f8f9fa;
    padding: 1px 4px;
    border-radius: 4px;
    font-size: 0.7rem;
    margin: 1px 0;
  }

  /* ================== Tombol Aksi ================== */
  .aksi-btn {
    margin: 1px 0;
    font-size: 0.7rem; /* tombol juga dikecilkan */
    padding: 2px 6px;
  }

  /* ================== Table Wrapper ================== */
  .card-body .table-responsive {
    max-height: none;   /* ❌ hilangkan batas tinggi */
    overflow: visible;  /* ❌ tidak pakai scroll */
  }
</style>


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
    <input type="text" class="form-control" 
           id="navbar-search-input" 
           placeholder="Search now" 
           aria-label="search" aria-describedby="search">
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
          <li class="nav-item"> <a class="nav-link" href="sisa_data.php">Data sisa</a></li>
        </ul>
      </div>
    </li>
    
    
  </ul>
</nav>

<!-- ====================== HTML Output ====================== -->
<div class="main-panel">
  <div class="content-wrapper">
    <div class="row mt-4">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card shadow">

          <!-- Header + Filter Button -->
          <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">📦 Rekap Sisa Material (Admin − Dipakai)</h5>
            <div class="btn-group btn-group-sm" role="group">
              <button type="button" class="btn btn-light filter-btn active" data-filter="all">Semua</button>
              <button type="button" class="btn btn-success filter-btn" data-filter="lapor">Sudah Lapor</button>
              <button type="button" class="btn btn-danger filter-btn" data-filter="belum">Belum Lapor</button>
            </div>
          </div>

          <!-- Card Body -->
          <div class="card-body p-3">

            <div id="table-wrapper">
              <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm align-middle text-center" id="material-table">
                  <thead class="table-dark">
                    <tr>
                      <th>No</th>
                      <th>Nama Teknisi</th>
                      <th>DC</th>
                      <th>S-CALM</th>
                      <th>CLAM HOOK</th>
                      <th>OTP</th>
                      <th>PREKSO</th>
                      <th>TIANG</th>
                      <th>SOC</th>
                      <th>Precont</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    $startIndex = ($page - 1) * $limit;
                    $no = $startIndex + 1;
                    $filter = $_GET['filter'] ?? 'all';

                    foreach ($teknisiRows as $tid => $nama) {
                      $masuk = $aggMasuk[$tid] ?? initAggRow($PRECONT_SIZES);
                      $pakai = $aggPakai[$tid] ?? initAggRow($PRECONT_SIZES);

                      $wo = $detailInfo[$tid]['wo'] ?? null;

                      // Filter kondisi
                      if ($filter === 'lapor' && $wo === null) continue;
                      if ($filter === 'belum' && $wo !== null) continue;

                      // Hitung sisa precont
                      $sisaPrecont = [];
                      foreach ($PRECONT_SIZES as $size) {
                        $masukVal = $masuk['precont'][$size] ?? 0;
                        $pakaiVal = $pakai['precont'][$size] ?? 0;
                        $sisaPrecont[$size] = $masukVal - $pakaiVal;
                      }

                      // Hitung sisa material lain
                      $sisa = [];
                      foreach (['dc', 's_calm', 'clam_hook', 'otp', 'prekso', 'tiang'] as $key) {
                        $masukVal = $masuk[$key] ?? 0;
                        $pakaiVal = $pakai[$key] ?? 0;
                        $sisa[$key] = $masukVal - $pakaiVal;
                      }

                      // SOC
                      $sisa['soc_fuji'] = ($masuk['soc_fuji'] ?? 0) - ($pakai['soc_fuji'] ?? 0);
                      $sisa['soc_sum']  = ($masuk['soc_sum']  ?? 0) - ($pakai['soc_sum']  ?? 0);

                      $socLabel = "Fuji (" . (int)$sisa['soc_fuji'] . "), Sum (" . (int)$sisa['soc_sum'] . ")";
                    ?>
                    <tr>
                      <td><?= $no++ ?></td>
                      <td><?= htmlspecialchars($nama) ?></td>
                      <td><?= (int)$sisa['dc'] ?></td>
                      <td><?= (int)$sisa['s_calm'] ?></td>
                      <td><?= (int)$sisa['clam_hook'] ?></td>
                      <td><?= (int)$sisa['otp'] ?></td>
                      <td><?= (int)$sisa['prekso'] ?></td>
                      <td><?= (int)$sisa['tiang'] ?></td>
                      <td><?= $socLabel ?></td>
                      <td class="precont-cell">
                        <?php foreach ($PRECONT_SIZES as $size): ?>
                          <div class="precont-item">
                            <?= $size ?> (<span class="badge bg-secondary"><?= (int)$sisaPrecont[$size] ?></span>)
                          </div>
                        <?php endforeach; ?>
                      </td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>

              <!-- Pagination -->
              <nav aria-label="Page navigation">
                <ul class="pagination pagination-sm justify-content-center">
                  <?php if ($page > 1): ?>
                    <li class="page-item">
                      <a class="page-link" href="?page=<?= $page-1 ?>&filter=<?= $filter ?>">«</a>
                    </li>
                  <?php endif; ?>

                  <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                    <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                      <a class="page-link" href="?page=<?= $i ?>&filter=<?= $filter ?>"><?= $i ?></a>
                    </li>
                  <?php endfor; ?>

                  <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                      <a class="page-link" href="?page=<?= $page+1 ?>&filter=<?= $filter ?>">»</a>
                    </li>
                  <?php endif; ?>
                </ul>
              </nav>

            </div><!-- /#table-wrapper -->

          </div><!-- /card-body -->

        </div>
      </div>
    </div>
  </div>
</div>


  <!-- Notifikasi SweetAlert -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <?php if (isset($_GET['status'])): ?>
    <script>
      document.addEventListener("DOMContentLoaded", function () {
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

</div> <!-- End main-panel -->


    <!-- container-scroller -->
</div>
    <!-- 🔎 Search Script -->
<script>
document.getElementById('navbar-search-input').addEventListener('keyup', function(){
    const keyword = this.value.trim();
    const xhr = new XMLHttpRequest();
    const url = keyword === "" 
      ? "search_sisa.php?reset=1"    // Jika kosong → tampilkan ulang default
      : "search_sisa.php?q=" + encodeURIComponent(keyword);

    xhr.open('GET', url, true);
    xhr.onload = function(){
        if(xhr.status === 200){
            // Perbarui isi <tbody>
            document.querySelector('#material-table tbody').innerHTML = xhr.responseText;
        }
    };
    xhr.send();
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

<!-- Pastikan jQuery sudah ada -->
<!-- ====================== AJAX Script (Smooth) ====================== -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
    // Fungsi load data lebih cepat (tanpa spinner)
    function loadData(url, filter) {
        $("#table-wrapper").fadeTo(150, 0.3); // redup sebentar

        $.ajax({
            url: url,
            type: "GET",
            data: { filter: filter },
            success: function (response) {
                var newContent = $(response).find("#table-wrapper").html();

                // Ganti isi tabel lebih cepat
                $("#table-wrapper").html(newContent).fadeTo(150, 1);

                // Scroll smooth ke tabel
                $("html, body").animate(
                    { scrollTop: $("#table-wrapper").offset().top - 60 },
                    300, "swing"
                );
            },
            error: function () {
                $("#table-wrapper").html(`
                  <div class="text-danger text-center p-3">
                    ❌ Gagal memuat data
                  </div>
                `).fadeTo(150, 1);
            }
        });
    }

    // Klik filter
    $(".filter-btn").on("click", function () {
        var filter = $(this).data("filter");
        $(".filter-btn").removeClass("active");
        $(this).addClass("active");
        loadData("sisa_data.php", filter);
    });

    // Klik pagination
    $(document).on("click", ".pagination a", function (e) {
        e.preventDefault();
        var url = $(this).attr("href");
        var filter = $(".filter-btn.active").data("filter");
        loadData(url, filter);
    });
});
</script>



  </body>
</html>