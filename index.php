<?php
// index.php (diperbaiki)
// Pastikan koneksi dibuat di koneksi.php dan menghasilkan $conn (mysqli)
include "koneksi.php"; // koneksi ke DB (harus set $conn)
include "total_dashboard.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'auth_check.php';

// Pastikan $conn ada dan valid
if (!isset($conn) || !($conn instanceof mysqli)) {
    // Jika koneksi tidak tersedia, keluarkan pesan yang jelas dan hentikan eksekusi
    die("Database connection not found. Periksa koneksi di koneksi.php");
}
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

// Cegah teknisi mengakses dashboard admin
if ($_SESSION['role'] !== 'admin') {
    header('Location: input_material_used.php');
    exit();
}

$sql = "SELECT id, namatek, nik, sektor, mitra, idtele, crew, valid 
        FROM teknisi";
$result = $conn->query($sql);

// ================== CONFIGURASI DASAR ================== //
$PRECONT_SIZES = [50,75,80,100,120,135,150,180];

// helper: decode GROUP_CONCAT JSON (separator '||') -> sum per size
function sum_json_concat(string $concat): array {
    $out = [];
    if ($concat === '' || $concat === null) return $out;
    $parts = explode('||', $concat);
    foreach ($parts as $p) {
        $arr = json_decode($p, true);
        if (!is_array($arr)) continue;
        foreach ($arr as $key => $val) {
            $k = (int)$key;
            $out[$k] = ($out[$k] ?? 0) + (int)$val;
        }
    }
    return $out;
}

// ================== 1) DATA TEKNISI ================== //
$sql_teknisi = "SELECT id, namatek FROM teknisi";
$res = $conn->query($sql_teknisi);
$teknisiRows = [];
if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $teknisiRows[$r['id']] = $r['namatek'];
    }
}

// ================== 2) MATERIAL MASUK ================== //
$sql_masuk = "
  SELECT 
    teknisi_id,
    COALESCE(SUM(dc),0) AS dc,
    COALESCE(SUM(s_calm),0) AS s_calm,
    COALESCE(SUM(clam_hook),0) AS clam_hook,
    COALESCE(SUM(otp),0) AS otp,
    COALESCE(SUM(prekso),0) AS prekso,
    COALESCE(SUM(tiang),0) AS tiang,
    COALESCE(SUM(ad_sc),0) AS ad_sc,
    COALESCE(SUM(CASE WHEN soc_option='Fuji' THEN soc_value ELSE 0 END),0) AS soc_fuji,
    COALESCE(SUM(CASE WHEN soc_option='Sum'  THEN soc_value ELSE 0 END),0) AS soc_sum,
    GROUP_CONCAT(precont_json SEPARATOR '||') AS precont_concat,
    GROUP_CONCAT(spliter_json SEPARATOR '||') AS spliter_concat,
    GROUP_CONCAT(smoove_json SEPARATOR '||') AS smoove_concat
  FROM teknisi_detail
  GROUP BY teknisi_id
";
$res = $conn->query($sql_masuk);
$aggMasuk = [];
if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $tid = $r['teknisi_id'];

        $pre = sum_json_concat($r['precont_concat'] ?? '');
        $spliter = sum_json_concat($r['spliter_concat'] ?? '');
        $smoove = sum_json_concat($r['smoove_concat'] ?? '');

        // Normalisasi ukuran
        $pre_norm = [];
        foreach ($PRECONT_SIZES as $s) { $pre_norm[$s] = $pre[$s] ?? 0; }

        $aggMasuk[$tid] = [
            'dc' => (int)$r['dc'],
            's_calm' => (int)$r['s_calm'],
            'clam_hook' => (int)$r['clam_hook'],
            'otp' => (int)$r['otp'],
            'prekso' => (int)$r['prekso'],
            'tiang' => (int)$r['tiang'],
            'ad_sc' => (int)$r['ad_sc'],
            'soc_fuji' => (int)$r['soc_fuji'],
            'soc_sum'  => (int)$r['soc_sum'],
            'precont'  => $pre_norm,
            'spliter'  => $spliter,
            'smoove'   => $smoove,
        ];
    }
}

// ================== 3) MATERIAL PAKAI ================== //
$sql_pakai = "
  SELECT 
    teknisi_id,
    COALESCE(SUM(dc),0) AS dc,
    COALESCE(SUM(s_calm),0) AS s_calm,
    COALESCE(SUM(clam_hook),0) AS clam_hook,
    COALESCE(SUM(otp),0) AS otp,
    COALESCE(SUM(prekso),0) AS prekso,
    COALESCE(SUM(tiang),0) AS tiang,
    COALESCE(SUM(ad_sc),0) AS ad_sc,
    COALESCE(SUM(CASE WHEN soc_option='Fuji' THEN soc_value ELSE 0 END),0) AS soc_fuji,
    COALESCE(SUM(CASE WHEN soc_option='Sum'  THEN soc_value ELSE 0 END),0) AS soc_sum,
    GROUP_CONCAT(precont_json SEPARATOR '||') AS precont_concat,
    GROUP_CONCAT(spliter_json SEPARATOR '||') AS spliter_concat,
    GROUP_CONCAT(smoove_json SEPARATOR '||') AS smoove_concat
  FROM material_used
  GROUP BY teknisi_id
";
$res = $conn->query($sql_pakai);
$aggPakai = [];
if ($res && $res->num_rows > 0) {
    while ($r = $res->fetch_assoc()) {
        $tid = $r['teknisi_id'];

        $pre = sum_json_concat($r['precont_concat'] ?? '');
        $spliter = sum_json_concat($r['spliter_concat'] ?? '');
        $smoove = sum_json_concat($r['smoove_concat'] ?? '');

        $pre_norm = [];
        foreach ($PRECONT_SIZES as $s) { $pre_norm[$s] = $pre[$s] ?? 0; }

        $aggPakai[$tid] = [
            'dc' => (int)$r['dc'],
            's_calm' => (int)$r['s_calm'],
            'clam_hook' => (int)$r['clam_hook'],
            'otp' => (int)$r['otp'],
            'prekso' => (int)$r['prekso'],
            'tiang' => (int)$r['tiang'],
            'ad_sc' => (int)$r['ad_sc'],
            'soc_fuji' => (int)$r['soc_fuji'],
            'soc_sum'  => (int)$r['soc_sum'],
            'precont'  => $pre_norm,
            'spliter'  => $spliter,
            'smoove'   => $smoove,
        ];
    }
}

// ================== 4) HITUNG TOTAL, PAKAI, SISA ================== //
$fields = ['dc','s_calm','clam_hook','otp','prekso','tiang','ad_sc','soc_fuji','soc_sum'];

$sum_total = array_fill_keys($fields, 0);
$sum_used  = array_fill_keys($fields, 0);
$sum_sisa  = array_fill_keys($fields, 0);

$sum_pre_total = array_fill_keys($PRECONT_SIZES, 0);
$sum_pre_used  = array_fill_keys($PRECONT_SIZES, 0);
$sum_pre_sisa  = array_fill_keys($PRECONT_SIZES, 0);

$sum_spliter_total = [];
$sum_spliter_used  = [];
$sum_spliter_sisa  = [];

$sum_smoove_total = [];
$sum_smoove_used  = [];
$sum_smoove_sisa  = [];

foreach ($teknisiRows as $tid => $nama) {
    $masuk = $aggMasuk[$tid] ?? array_merge(array_fill_keys($fields, 0), [
        'precont'=>array_fill_keys($PRECONT_SIZES,0),
        'spliter'=>[],
        'smoove'=>[],
    ]);
    $hasPakai = isset($aggPakai[$tid]);
    $pakai = $aggPakai[$tid] ?? array_merge(array_fill_keys($fields, 0), [
        'precont'=>array_fill_keys($PRECONT_SIZES,0),
        'spliter'=>[],
        'smoove'=>[],
    ]);

    // total dan used
    foreach ($fields as $f) {
        $sum_total[$f] += $masuk[$f];
        if ($hasPakai) $sum_used[$f] += $pakai[$f];
    }

    // precont
    foreach ($PRECONT_SIZES as $size) {
        $sum_pre_total[$size] += ($masuk['precont'][$size] ?? 0);
        if ($hasPakai) $sum_pre_used[$size] += ($pakai['precont'][$size] ?? 0);
    }

    // spliter & smoove
    foreach (['spliter','smoove'] as $key) {
        foreach (($masuk[$key] ?? []) as $k => $v) {
            $sumName = 'sum_'.$key.'_total';
            ${$sumName}[$k] = (${$sumName}[$k] ?? 0) + $v;
        }
        if ($hasPakai) {
            foreach (($pakai[$key] ?? []) as $k => $v) {
                $sumName = 'sum_'.$key.'_used';
                ${$sumName}[$k] = (${$sumName}[$k] ?? 0) + $v;
            }
        }
    }

    // sisa (jika sudah ada data pakai)
    if ($hasPakai) {
        foreach ($fields as $f) {
            $sum_sisa[$f] += ($masuk[$f] - $pakai[$f]);
        }
        foreach ($PRECONT_SIZES as $size) {
            $sum_pre_sisa[$size] += (($masuk['precont'][$size] ?? 0) - ($pakai['precont'][$size] ?? 0));
        }
        foreach (['spliter','smoove'] as $key) {
            foreach (($masuk[$key] ?? []) as $k => $v) {
                $sumName = 'sum_'.$key.'_sisa';
                ${$sumName}[$k] = (${$sumName}[$k] ?? 0) + ($v - ($pakai[$key][$k] ?? 0));
            }
        }
    }
}

// buat nilai total precont untuk chart (jumlah semua ukuran)
$totalPrecont = array_sum($sum_pre_total);
$usedPrecont  = array_sum($sum_pre_used);
$sisaPrecont  = array_sum($sum_pre_sisa);

// gabungkan SOC (Fuji+Sum) untuk chart (atau bisa dibuat 2 dataset terpisah)
$sum_total_soc = $sum_total['soc_fuji'] + $sum_total['soc_sum'];
$sum_used_soc  = $sum_used['soc_fuji']  + $sum_used['soc_sum'];
$sum_sisa_soc  = $sum_sisa['soc_fuji']  + $sum_sisa['soc_sum'];

// siapkan array urutan sesuai labels chart
$labels = ['DC','S-calm','Clam Hook','OTP','Prekso','Tiang','SOC','Precont'];
$data_total = [
    $sum_total['dc'],
    $sum_total['s_calm'],
    $sum_total['clam_hook'],
    $sum_total['otp'],
    $sum_total['prekso'],
    $sum_total['tiang'],
    $sum_total_soc,
    $totalPrecont
];
$data_used = [
    $sum_used['dc'],
    $sum_used['s_calm'],
    $sum_used['clam_hook'],
    $sum_used['otp'],
    $sum_used['prekso'],
    $sum_used['tiang'],
    $sum_used_soc,
    $usedPrecont
];
$data_sisa = [
    $sum_sisa['dc'],
    $sum_sisa['s_calm'],
    $sum_sisa['clam_hook'],
    $sum_sisa['otp'],
    $sum_sisa['prekso'],
    $sum_sisa['tiang'],
    $sum_sisa_soc,
    $sisaPrecont
];

// siap kirim ke Chart.js

// ambil data jumlah untuk pie chart
$total_teknisi     = $conn->query("SELECT COUNT(*) AS jml FROM teknisi")->fetch_assoc()['jml'];
$ambil_material    = $conn->query("SELECT COUNT(DISTINCT teknisi_id) AS jml FROM teknisi_detail")->fetch_assoc()['jml'];
$laporan_material  = $conn->query("SELECT COUNT(DISTINCT teknisi_id) AS jml FROM material_used")->fetch_assoc()['jml'];

// NOTE: jangan menutup koneksi di sini karena kita masih pakai $conn di bawah
// $conn->close();  <-- dihapus

// --- Query Slide 2 ---
// Total teknisi ambil material
$q1 = $conn->query("SELECT DISTINCT teknisi_id FROM teknisi_detail");
$ambil_ids = [];
if ($q1 && $q1->num_rows > 0) {
    while($r = $q1->fetch_assoc()){
        $ambil_ids[] = $r['teknisi_id'];
    }
}

// Total teknisi sudah laporan
$q2 = $conn->query("SELECT DISTINCT teknisi_id FROM material_used");
$lapor_ids = [];
if ($q2 && $q2->num_rows > 0) {
    while($r = $q2->fetch_assoc()){
        $lapor_ids[] = $r['teknisi_id'];
    }
}

// Cari teknisi ambil material tapi belum laporan
$belum_lapor_ids = array_values(array_diff($ambil_ids, $lapor_ids));
$teknisi_belum_lapor = count($belum_lapor_ids);

// ====================
// Material Belum Lapor
// ====================
$fields = ["dc","s_calm","clam_hook","otp","prekso","tiang","soc_value"];

$data_belum = array_fill_keys($fields, 0);

// Loop teknisi yang belum laporan
if (!empty($belum_lapor_ids)) {
    foreach($belum_lapor_ids as $tid){
        // Ambil dari teknisi_detail
        $tid_esc = (int)$tid;
        $q3 = $conn->query("SELECT ".implode(",",$fields)." FROM teknisi_detail WHERE teknisi_id='$tid_esc'");
        if ($q3 && $q3->num_rows > 0) {
            while($r = $q3->fetch_assoc()){
                foreach($fields as $f){
                    $data_belum[$f] += (int)$r[$f];
                }
            }
        }
        // Kurangi jika ada di material_used
        $q4 = $conn->query("SELECT ".implode(",",$fields)." FROM material_used WHERE teknisi_id='$tid_esc'");
        if ($q4 && $q4->num_rows > 0) {
            while($r = $q4->fetch_assoc()){
                foreach($fields as $f){
                    $data_belum[$f] -= (int)$r[$f];
                }
            }
        }
    }
}

// ====================== PHP Bagian Card Sisa Bawah ======================
// --- Inisialisasi ukuran Precont
// (sudah didefinisikan sebelumnya, tidak perlu redefine — tapi harmless jika sama)
// $PRECONT_SIZES = [50, 75, 80, 100, 120, 135, 150, 180];

// --- Fungsi bantu buat baris kosong
// --- Inisialisasi ukuran Precont + pilihan Spliter & Smoove
$PRECONT_SIZES = [50, 75, 80, 100, 120, 135, 150, 180];
$SPLITER_TYPES = ['1.2', '1.4', '1.8', '1.16'];
$SMOOVE_TYPES  = ['Kecil', 'Tipe 3'];

// --- Fungsi bantu buat baris kosong
function initAggRow($precontSizes = [], $spliterTypes = [], $smooveTypes = [])
{
    $row = [
        'dc'        => 0,
        's_calm'    => 0,
        'clam_hook' => 0,
        'otp'       => 0,
        'prekso'    => 0,
        'tiang'     => 0,
        'soc_fuji'  => 0,
        'soc_sum'   => 0,
        'ad_sc'     => 0,
        'precont'   => [],
        'spliter'   => [],
        'smoove'    => []
    ];

    foreach ($precontSizes as $s) $row['precont'][$s] = 0;
    foreach ($spliterTypes as $t) $row['spliter'][$t] = 0;
    foreach ($smooveTypes as $t)  $row['smoove'][$t]  = 0;

    return $row;
}

// --- Pagination setup
$limit  = 5;
$page   = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$offset = ($page - 1) * $limit;

// --- Hitung total teknisi dari teknisi_detail
$sqlCount = "
    SELECT COUNT(DISTINCT t.id) AS total
    FROM teknisi t
    JOIN teknisi_detail d ON t.id = d.teknisi_id
";
$resCount = $conn->query($sqlCount);
$totalRows = ($resCount && $resCount->num_rows > 0)
    ? (int)$resCount->fetch_assoc()['total']
    : 0;
$totalPages = ($totalRows > 0) ? ceil($totalRows / $limit) : 1;

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

$listTekIds = !empty($teknisiRows) ? implode(",", array_map('intval', array_keys($teknisiRows))) : "0";

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
        $tid = (int)$d['teknisi_id'];
        if (!isset($aggMasuk[$tid])) {
            $aggMasuk[$tid] = initAggRow($PRECONT_SIZES, $SPLITER_TYPES, $SMOOVE_TYPES);
        }

        // Material numerik
        foreach (['dc', 's_calm', 'clam_hook', 'otp', 'prekso', 'tiang', 'ad_sc'] as $k) {
            $aggMasuk[$tid][$k] += (int)($d[$k] ?? 0);
        }

        // SOC
        $socOpt = strtolower(trim($d['soc_option'] ?? ''));
        if ($socOpt === 'fuji') {
            $aggMasuk[$tid]['soc_fuji'] += (int)($d['soc_value'] ?? 0);
        } elseif ($socOpt === 'sum') {
            $aggMasuk[$tid]['soc_sum']  += (int)($d['soc_value'] ?? 0);
        }

        // === Precont JSON ===
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
        }

        // === Spliter JSON ===
        if (!empty($d['spliter_json'])) {
            $decoded = json_decode($d['spliter_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $type => $val) {
                    if (in_array($type, $SPLITER_TYPES, true) && is_numeric($val)) {
                        $aggMasuk[$tid]['spliter'][$type] += (int)$val;
                    }
                }
            }
        }

        // === Smoove JSON ===
        if (!empty($d['smoove_json'])) {
            $decoded = json_decode($d['smoove_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $type => $val) {
                    if (in_array($type, $SMOOVE_TYPES, true) && is_numeric($val)) {
                        $aggMasuk[$tid]['smoove'][$type] += (int)$val;
                    }
                }
            }
        }

        if (!isset($detailInfo[$tid]['rfs'])) {
            $detailInfo[$tid]['rfs'] = $d['rfs'] ?? '';
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
        $tid = (int)$d['teknisi_id'];
        if (!isset($aggPakai[$tid])) {
            $aggPakai[$tid] = initAggRow($PRECONT_SIZES, $SPLITER_TYPES, $SMOOVE_TYPES);
        }

        foreach (['dc', 's_calm', 'clam_hook', 'otp', 'prekso', 'tiang', 'ad_sc'] as $k) {
            $aggPakai[$tid][$k] += (int)($d[$k] ?? 0);
        }

        $socOpt = strtolower(trim($d['soc_option'] ?? ''));
        if ($socOpt === 'fuji') {
            $aggPakai[$tid]['soc_fuji'] += (int)($d['soc_value'] ?? 0);
        } elseif ($socOpt === 'sum') {
            $aggPakai[$tid]['soc_sum']  += (int)($d['soc_value'] ?? 0);
        }

        // === Precont JSON ===
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
        }

        // === Spliter JSON ===
        if (!empty($d['spliter_json'])) {
            $decoded = json_decode($d['spliter_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $type => $val) {
                    if (in_array($type, $SPLITER_TYPES, true) && is_numeric($val)) {
                        $aggPakai[$tid]['spliter'][$type] += (int)$val;
                    }
                }
            }
        }

        // === Smoove JSON ===
        if (!empty($d['smoove_json'])) {
            $decoded = json_decode($d['smoove_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $type => $val) {
                    if (in_array($type, $SMOOVE_TYPES, true) && is_numeric($val)) {
                        $aggPakai[$tid]['smoove'][$type] += (int)$val;
                    }
                }
            }
        }

        if (!isset($detailInfo[$tid]['wo'])) {
            $detailInfo[$tid]['wo'] = $d['wo'] ?? '';
        }
    }
}

// Tutup koneksi setelah semua query selesai (di akhir file)
$conn->close();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>Telkom Akses</title>
     <!-- endinject -->
    <link rel="shortcut icon" href="assets/images/TLK.png" />
    <!-- plugins:css -->
    <link rel="stylesheet" href="assets/vendors/feather/feather.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
    <link rel="stylesheet" href="assets/vendors/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- <link rel="stylesheet" href="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.css"> -->
    <link rel="stylesheet" href="assets/vendors/datatables.net-bs5/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="assets/vendors/ti-icons/css/themify-icons.css">
    <link rel="stylesheet" type="text/css" href="assets/js/select.dataTables.min.css">
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <link rel="stylesheet" href="assets/css/style.css">
   


</head>

<body>
      <div class="container-scroller">
   
    <!-- Navbar -->
    <nav class="navbar col-lg-12 col-12 p-0 fixed-top d-flex flex-row">
      <div class="text-center navbar-brand-wrapper d-flex align-items-center justify-content-start">
        <a class="navbar-brand brand-logo me-5" href="index.php">
          <img src="assets/images/logotelkom.png" alt="Telkom Indonesia Logo" class="logo-telkom" />
        </a>
      </div>

      <div class="navbar-menu-wrapper d-flex align-items-center justify-content-end">
        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
          <span class="icon-menu"></span>
        </button>

        <!-- Search -->
        <ul class="navbar-nav mr-lg-2">
          <li class="nav-item nav-search d-none d-lg-block">
            <div class="input-group">
              <div class="input-group-prepend hover-cursor" id="navbar-search-icon">
                <span class="input-group-text" id="search"><i class="icon-search"></i></span>
              </div>
              <input type="text" class="form-control" id="navbar-search-input" placeholder="Search now"
                aria-label="search" aria-describedby="search">
            </div>
          </li>
        </ul>

        <!-- Right menu -->
        <ul class="navbar-nav navbar-nav-right">
          <li class="nav-item dropdown">
            <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-bs-toggle="dropdown">
              <i class="icon-bell mx-0"></i>
              <span class="count"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list"
              aria-labelledby="notificationDropdown">
              <p class="mb-0 font-weight-normal float-left dropdown-header">Notifications</p>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-success"><i class="ti-info-alt mx-0"></i></div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-normal">Application Error</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">Just now</p>
                </div>
              </a>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-warning"><i class="ti-settings mx-0"></i></div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-normal">Settings</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">Private message</p>
                </div>
              </a>
              <a class="dropdown-item preview-item">
                <div class="preview-thumbnail">
                  <div class="preview-icon bg-info"><i class="ti-user mx-0"></i></div>
                </div>
                <div class="preview-item-content">
                  <h6 class="preview-subject font-weight-normal">New user registration</h6>
                  <p class="font-weight-light small-text mb-0 text-muted">2 days ago</p>
                </div>
              </a>
            </div>
          </li>

        <li class="nav-item nav-profile dropdown">
  <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown" id="profileDropdown">
    <img src="assets/images/TLK.png" alt="profile" class="rounded-circle me-2" width="35" height="35" />
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
    <a class="dropdown-item text-danger" href="logout.php">
      <i class="ti-power-off me-2"></i> Logout
    </a>
  </div>
</li>


          <li class="nav-item nav-settings d-none d-lg-flex">
            <a class="nav-link" href="#"><i class="icon-ellipsis"></i></a>
          </li>
        </ul>

        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button"
          data-toggle="offcanvas">
          <span class="icon-menu"></span>
        </button>
      </div>
    </nav>
    <!-- End Navbar -->

    <div class="container-fluid page-body-wrapper">
      <!-- Sidebar -->
      <nav class="sidebar sidebar-offcanvas" id="sidebar">
        <ul class="nav">
          <li class="nav-item">
            <a class="nav-link" href="index.php">
              <i class="icon-grid menu-icon"></i>
              <span class="menu-title">Dashboard</span>
            </a>
          </li>

          <!-- Teknisi -->
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#form-elements" aria-expanded="false"
              aria-controls="form-elements">
              <i class="icon-head menu-icon"></i>
              <span class="menu-title">Teknisi</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="form-elements">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"><a class="nav-link" href="pages/forms/input_teknisi.php">Tambah Teknisi</a></li>
                <li class="nav-item"><a class="nav-link" href="pages/forms/basic_elements.php">Data Teknisi</a></li>
                <li class="nav-item"><a class="nav-link" href="pages/forms/info_login.php">Info Login</a></li>
              </ul>
            </div>
          </li>

          <!-- Material -->
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#charts" aria-expanded="false"
              aria-controls="charts">
              <i class="icon-grid-2 menu-icon"></i>
              <span class="menu-title">Material</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="charts">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"><a class="nav-link" href="pages/charts/input_material.php">Tambah Material</a></li>
                <li class="nav-item"><a class="nav-link" href="pages/charts/data_material.php">Data Material</a></li>
              
              </ul>
            </div>
          </li>

          <!-- Material Dipakai -->
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#icons" aria-expanded="false"
              aria-controls="icons">
              <i class="icon-layout menu-icon"></i>
              <span class="menu-title">Material Dipakai</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="icons">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"><a class="nav-link" href="pages/icons/input_material_used.php">Tambah Material</a></li>
                <li class="nav-item"><a class="nav-link" href="pages/icons/data_material_used.php">Data Material</a></li>
                <li class="nav-item"> <a class="nav-link" href="pages/icons/foto_material_used.php">Keluhan</a></li>
              </ul>
            </div>
          </li>

          <!-- Sisa Material -->
          <li class="nav-item">
            <a class="nav-link" data-bs-toggle="collapse" href="#tables" aria-expanded="false"
              aria-controls="tables">
              <i class="icon-paper menu-icon"></i>
              <span class="menu-title">Sisa Material</span>
              <i class="menu-arrow"></i>
            </a>
            <div class="collapse" id="tables">
              <ul class="nav flex-column sub-menu">
                <li class="nav-item"><a class="nav-link" href="pages/tables/sisa_data.php">Data Sisa</a></li>
              </ul>
            </div>
          </li>
        </ul>
      </nav>
      <!-- End Sidebar -->

    
<div class="main-panel d-flex flex-column min-vh-100">
  <div class="content-wrapper flex-grow-1">
    <!-- Welcome Section -->
    <div class="row">
      <div class="col-md-12 grid-margin">
        <div class="row">
          <div class="col-12 col-xl-8 mb-4 mb-xl-0">
            <h3 class="font-weight-bold">
              Halo <?= htmlspecialchars($_SESSION['username']); ?> (<?= htmlspecialchars($_SESSION['role']); ?>)
            </h3>
            <h6 class="font-weight-normal mb-0">
              Selamat datang di dashboard. Semua sistem berjalan lancar!
              <?php if ($_SESSION['role'] === 'admin'): ?>
                <span class="text-primary">Anda login sebagai Admin.</span>
              <?php else: ?>
                <span class="text-success">Anda login sebagai Teknisi.</span>
              <?php endif; ?>
            </h6>
          </div>
          <div class="col-12 col-xl-4 d-flex justify-content-end">
            <div class="dropdown flex-md-grow-1 flex-xl-grow-0">
              <button class="btn btn-sm btn-light bg-white dropdown-toggle" type="button"
                      id="dropdownMenuDate2" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <i class="mdi mdi-calendar"></i> <?= date("d M Y"); ?>
              </button>
              <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuDate2">
                <a class="dropdown-item" href="#">January - March</a>
                <a class="dropdown-item" href="#">March - June</a>
                <a class="dropdown-item" href="#">June - August</a>
                <a class="dropdown-item" href="#">August - November</a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Dashboard Cards -->
    <div class="row">
      <!-- Foto Telkom -->
      <div class="col-md-6 grid-margin stretch-card">
        <div class="card tale-bg">
          <div class="card-people mt-auto">
            <img src="assets/images/dashboard/foto_telkom.jpg" alt="people">
          </div>
        </div>
      </div>

      <!-- Info Cards -->
      <div class="col-md-6 grid-margin transparent">
        <div class="row">
          <!-- Jumlah Teknisi -->
          <div class="col-md-6 mb-4 stretch-card transparent">
            <a href="pages/forms/basic_elements.php" class="text-decoration-none text-dark">
              <div class="card card-tale" style="cursor:pointer;">
                <div class="card-body">
                  <p class="mb-4">Jumlah Teknisi</p>
                  <p class="fs-30 mb-2"><?= number_format($total_teknisi); ?></p>
                  <p>Total teknisi yang terdaftar di database</p>
                </div>
              </div>
            </a>
          </div>

          <!-- Total Material -->
          <div class="col-md-6 mb-4 stretch-card transparent">
            <a href="pages/charts/data_material.php" class="text-decoration-none text-dark">
              <div class="card card-dark-blue" style="cursor:pointer;">
                <div class="card-body">
                  <p class="mb-4">Total Material</p>
                  <p class="fs-30 mb-2"><?= number_format($total_material); ?></p>
                  <p>Total semua material yang diambil teknisi</p>
                </div>
              </div>
            </a>
          </div>
        </div>

        <div class="row">
          <!-- Total Material Digunakan -->
          <div class="col-md-6 mb-4 mb-lg-0 stretch-card transparent">
            <a href="pages/icons/data_material_used.php" class="text-decoration-none text-dark">
              <div class="card card-light-blue" style="cursor:pointer;">
                <div class="card-body">
                  <p class="mb-4">Total Material Digunakan</p>
                  <p class="fs-30 mb-2"><?= number_format($total_material_used); ?></p>
                  <p>Akumulasi semua material yang terpakai teknisi</p>
                </div>
              </div>
            </a>
          </div>

          <!-- Sisa Material -->
          <div class="col-md-6 stretch-card transparent">
            <a href="pages/tables/sisa_data.php" class="text-decoration-none text-dark">
              <div class="card card-light-danger" style="cursor:pointer;">
                <div class="card-body">
                  <p class="mb-4">Sisa Material</p>
                  <p class="fs-30 mb-2"><?= number_format($sisa_material); ?></p>
                  <p>
                    <?php 
                      if ($total_material_used <= 0) {
                        echo "Belum ada laporan material digunakan.";
                      } elseif ($sisa_material < 0) {
                        echo "Material yang digunakan melebihi stok.";
                      } else {
                        echo "Total material sisa setelah penggunaan.";
                      }
                    ?>
                  </p>
                </div>
              </div>
            </a>
          </div>
        </div>
      </div>
    </div>

    <!-- Perbandingan Material Chart -->
    <div class="row">
      <div class="col-md-12 grid-margin stretch-card">
        <div class="card">
          <div class="card-body">
            <div class="d-flex justify-content-between">
              <p class="card-title">Perbandingan Material</p>
              <a href="#" class="text-info">View all</a>
            </div>
            <canvas id="material-bar-chart"></canvas>
          </div>
        </div>
      </div>
    </div>


    <style>
#material-table th, 
#material-table td {
    white-space: nowrap;
    font-size: 12px;     /* kecilkan teks */
    padding: 4px;        /* rapatkan cell */
}
</style>

  <!-- ================= REKAP SISA MATERIAL ================= -->
<div class="row mt-4" id="rekapMaterial">
  <div class="col-md-12 grid-margin stretch-card">
    <div class="card shadow">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">📦 Rekap Sisa Material</h5>
        <div class="d-flex gap-1">
          <!-- Filter Buttons -->
          <a href="#" class="btn btn-light btn-sm filter-btn <?= (!isset($_GET['filter']) || $_GET['filter'] === 'all') ? 'active' : '' ?>" 
             data-url="?filter=all&page=<?= $page ?>">Semua</a>
          <a href="#" class="btn btn-success btn-sm filter-btn <?= (isset($_GET['filter']) && $_GET['filter'] === 'lapor') ? 'active' : '' ?>" 
             data-url="?filter=lapor&page=<?= $page ?>">Sudah Lapor</a>
          <a href="#" class="btn btn-danger btn-sm filter-btn <?= (isset($_GET['filter']) && $_GET['filter'] === 'belum') ? 'active' : '' ?>" 
             data-url="?filter=belum&page=<?= $page ?>">Belum Lapor</a>
          <input type="text" id="searchTeknisi" class="form-control form-control-sm ms-2"
                 placeholder="🔍 Cari teknisi..." style="width:180px;">
        </div>
      </div>

      <div class="card-body p-2" id="rekapTableWrapper">
        <div>
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
                <th>Spliter</th>
                <th>Smoove</th>
                <th>AD-SC</th>
              </tr>
            </thead>
            <tbody>
    <?php
// --- Pastikan definisi ini ada DI SINI (sebelum loop tabel) ---
$SPLITER_TYPES = ['1.2', '1.4', '1.8', '1.16'];
$SMOOVE_TYPES  = ['Kecil', 'Tipe 3'];

// Pastikan fungsi initAggRow menerima parameter (jika belum ada di file lain)
if (!function_exists('initAggRow')) {
    function initAggRow($PRECONT_SIZES = [], $SPLITER_TYPES = [], $SMOOVE_TYPES = []) {
        $row = [
            'dc' => 0,
            's_calm' => 0,
            'clam_hook' => 0,
            'otp' => 0,
            'prekso' => 0,
            'tiang' => 0,
            'ad_sc' => 0,
            'soc_fuji' => 0,
            'soc_sum' => 0,
            'precont' => [],
            'spliter' => [],
            'smoove' => []
        ];
        foreach ($PRECONT_SIZES as $s) $row['precont'][$s] = 0;
        foreach ($SPLITER_TYPES as $s)  $row['spliter'][$s] = 0;
        foreach ($SMOOVE_TYPES as $s)   $row['smoove'][$s] = 0;
        return $row;
    }
}

// Pastikan var pagination / filter / startIndex / no tersedia
$filter = $_GET['filter'] ?? 'all';
$startIndex = isset($page) ? (($page - 1) * ($limit ?? 5)) : 0;
$no = $startIndex + 1;

// ------------------ LOOP TABEL ------------------
foreach ($teknisiRows as $tid => $nama):
    // ambil agregat, fallback ke row kosong ter-normalisasi
    $masuk = $aggMasuk[$tid] ?? initAggRow($PRECONT_SIZES, $SPLITER_TYPES, $SMOOVE_TYPES);
    $pakai = $aggPakai[$tid] ?? initAggRow($PRECONT_SIZES, $SPLITER_TYPES, $SMOOVE_TYPES);

    // pastikan format array
    if (!is_array($masuk)) $masuk = initAggRow($PRECONT_SIZES, $SPLITER_TYPES, $SMOOVE_TYPES);
    if (!is_array($pakai)) $pakai = initAggRow($PRECONT_SIZES, $SPLITER_TYPES, $SMOOVE_TYPES);

    $wo = !empty($detailInfo[$tid]['wo']) ? htmlspecialchars($detailInfo[$tid]['wo']) : null;
    if ($filter === 'lapor' && $wo === null) continue;
    if ($filter === 'belum' && $wo !== null) continue;

    // hitung sisa precont / spliter / smoove
    $sisaPrecont = [];
    foreach ($PRECONT_SIZES as $size) {
        $sisaPrecont[$size] = ($masuk['precont'][$size] ?? 0) - ($pakai['precont'][$size] ?? 0);
    }

    $sisaSpliter = [];
    foreach ($SPLITER_TYPES as $type) {
        $sisaSpliter[$type] = ($masuk['spliter'][$type] ?? 0) - ($pakai['spliter'][$type] ?? 0);
    }

    $sisaSmoove = [];
    foreach ($SMOOVE_TYPES as $type) {
        $sisaSmoove[$type] = ($masuk['smoove'][$type] ?? 0) - ($pakai['smoove'][$type] ?? 0);
    }

    // material tunggal termasuk ad_sc
    $sisa = [];
    foreach (['dc','s_calm','clam_hook','otp','prekso','tiang','ad_sc'] as $key) {
        $sisa[$key] = ($masuk[$key] ?? 0) - ($pakai[$key] ?? 0);
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

  <td class="spliter-cell">
    <?php foreach ($SPLITER_TYPES as $type): ?>
      <div class="spliter-item">
        <?= $type ?> (<span class="badge bg-info"><?= (int)$sisaSpliter[$type] ?></span>)
      </div>
    <?php endforeach; ?>
  </td>

  <td class="smoove-cell">
    <?php foreach ($SMOOVE_TYPES as $type): ?>
      <div class="smoove-item">
        <?= $type ?> (<span class="badge bg-warning text-dark"><?= (int)$sisaSmoove[$type] ?></span>)
      </div>
    <?php endforeach; ?>
  </td>

  <td><?= (int)$sisa['ad_sc'] ?></td>
</tr>
<?php endforeach; ?>

            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>


  <!-- Footer (Sticky) -->
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
</div>



            <!-- main-panel ends -->

        <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
  </div>
    <!-- plugins:js -->
    <script src="assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <script src="assets/vendors/chart.js/chart.umd.js"></script>
    <script src="assets/vendors/datatables.net/jquery.dataTables.js"></script>
    <!-- <script src="assets/vendors/datatables.net-bs4/dataTables.bootstrap4.js"></script> -->
    <script src="assets/vendors/datatables.net-bs5/dataTables.bootstrap5.js"></script>
    <script src="assets/js/dataTables.select.min.js"></script>
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="assets/js/off-canvas.js"></script>
    <script src="assets/js/template.js"></script>
    <script src="assets/js/settings.js"></script>
    <script src="assets/js/todolist.js"></script>
    <!-- endinject -->
    <!-- Custom js for this page-->
    <script src="assets/js/jquery.cookie.js" type="text/javascript"></script>
    <script src="assets/js/dashboard.js"></script>
    <!-- <script src="assets/js/Chart.roundedBarCharts.js"></script> -->

    <!-- ChartJS -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// ======================
// BAR CHART MATERIAL (Slide 1)
// ======================
const ctxMaterial = document.getElementById("material-bar-chart").getContext("2d");

const dataMaterial = {
    labels: ["DC", "S-calm", "Clam Hook", "OTP", "Prekso", "Tiang", "SOC", "Precont"],
    datasets: [
        {
            label: "Total Material",
            data: <?= json_encode($data_total) ?>,
            backgroundColor: "#3D3B92",
        },
        {
            label: "Dipakai",
            data: <?= json_encode($data_used) ?>,
            backgroundColor: "#7A6BF0",
        },
        {
            label: "Sisa",
            data: <?= json_encode($data_sisa) ?>,
            backgroundColor: "#E57373",
        },
    ],
};

new Chart(ctxMaterial, {
    type: "bar",
    data: dataMaterial,
    options: {
        responsive: true,
        plugins: {
            legend: { position: "top" },
            title: { display: true, text: "Rekap Material per Barang" },
        },
        scales: {
            x: { stacked: false },
            y: { beginAtZero: true },
        },
    },
});


// ======================
// PIE CHART TEKNISI (Slide 1)
// ======================
const ctxTeknisi = document.getElementById("teknisiChart").getContext("2d");
new Chart(ctxTeknisi, {
    type: "pie",
    data: {
        labels: ["Total Teknisi", "Teknisi Ambil Material", "Teknisi Sudah Laporan"],
        datasets: [{
            data: [
                <?= (int)$total_teknisi ?>,
                <?= (int)$ambil_material ?>,
                <?= (int)$laporan_material ?>
            ],
            backgroundColor: ["#007bff", "#ffc107", "#28a745"],
            hoverOffset: 10,
        }],
    },
    options: { responsive: true, plugins: { legend: { position: "bottom" } } }
});


// ======================
// PIE CHART TEKNISI (Slide 2)
// ======================
const ctxTeknisi2 = document.getElementById("teknisiChart2").getContext("2d");
new Chart(ctxTeknisi2, {
    type: "pie",
    data: {
        labels: ["Belum Lapor", "Sudah Lapor"],
        datasets: [{
            data: [<?= $teknisi_belum_lapor ?>, <?= count($lapor_ids) ?>],
            backgroundColor: ["#dc3545", "#28a745"],
        }]
    },
    options: { responsive: true, plugins: { legend: { position: "bottom" } } }
});


// ======================
// BAR CHART MATERIAL (Slide 2 - Belum Lapor)
// ======================
const ctxMaterialBelum = document.getElementById("materialBelumChart").getContext("2d");
new Chart(ctxMaterialBelum, {
    type: "bar",
    data: {
        labels: <?= json_encode(array_keys($data_belum)) ?>,
        datasets: [{
            label: "Belum Dilaporkan",
            data: <?= json_encode(array_values($data_belum)) ?>,
            backgroundColor: "#ffc107"
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: "top" } },
        scales: { y: { beginAtZero: true } }
    }
});
</script>


<!-- jQuery untuk AJAX + Pencarian -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).on("click", ".filter-btn, .page-btn", function(e){
    e.preventDefault();
    let url = $(this).data("url");
    $("#rekapTableWrapper").fadeTo(200, 0.3);

    $.get(url, function(data){
        let newContent = $(data).find("#rekapTableWrapper").html();
        $("#rekapTableWrapper").html(newContent).fadeTo(200, 1);
    });
});

// Live search teknisi
$("#searchTeknisi").on("keyup", function(){
    let keyword = $(this).val().toLowerCase();
    $("#material-table tbody tr").filter(function(){
        $(this).toggle($(this).find(".teknisi-col").text().toLowerCase().indexOf(keyword) > -1);
    });
});
</script>
    <!-- End custom js for this page-->
</body>

</html>