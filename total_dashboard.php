<?php
// ================== KONEKSI DATABASE ================== //
require_once 'koneksi.php'; // pastikan file koneksi.php sudah berisi variabel $conn

// ================== CARD TOTAL TEKNISI ================== //

// Query hitung teknisi
$result = $conn->query("SELECT COUNT(*) AS total_teknisi FROM teknisi");
$row = $result->fetch_assoc();
$total_teknisi = $row['total_teknisi'] ?? 0;

// ================= TOTAL MATERIAL (stok awal dari teknisi_detail) ================= //
$q_material = "
  SELECT 
    COALESCE(SUM(dc),0) +
    COALESCE(SUM(s_calm),0) +
    COALESCE(SUM(clam_hook),0) +
    COALESCE(SUM(otp),0) +
    COALESCE(SUM(prekso),0) +
    COALESCE(SUM(tiang),0) +
    COALESCE(SUM(soc_value),0) AS total_material
  FROM teknisi_detail";
$total_material = $conn->query($q_material)->fetch_assoc()['total_material'] ?? 0;

// Tambahkan precont dari teknisi_detail (jika ada JSON)
$total_precont = 0;
$res_precont = $conn->query("SELECT precont_json FROM teknisi_detail WHERE precont_json IS NOT NULL");
while ($row = $res_precont->fetch_assoc()) {
  $json = json_decode($row['precont_json'], true);
  if (is_array($json)) {
    foreach ($json as $val) {
      if (is_numeric($val)) {
        $total_precont += $val;
      }
    }
  }
}

// Total material akhir = sum kolom + nilai precont_json
$total_material += $total_precont;


// ================= TOTAL MATERIAL DIGUNAKAN (material_used) ================= //
$query_used = "
  SELECT 
    COALESCE(SUM(dc),0) +
    COALESCE(SUM(s_calm),0) +
    COALESCE(SUM(clam_hook),0) +
    COALESCE(SUM(otp),0) +
    COALESCE(SUM(prekso),0) +
    COALESCE(SUM(tiang),0) +
    COALESCE(SUM(soc_value),0) AS total_material_used
  FROM material_used";
$result_used = $conn->query($query_used);
$total_material_used = $result_used->fetch_assoc()['total_material_used'] ?? 0;

// Tambahkan precont dari material_used (jika ada JSON)
$total_precont_used = 0;
$res_precont_used = $conn->query("SELECT precont_json FROM material_used WHERE precont_json IS NOT NULL");
while ($row = $res_precont_used->fetch_assoc()) {
  $json = json_decode($row['precont_json'], true);
  if (is_array($json)) {
    foreach ($json as $val) {
      if (is_numeric($val)) {
        $total_precont_used += $val;
      }
    }
  }
}

// Total material digunakan = sum kolom + nilai precont_json
$total_material_used += $total_precont_used;


// ================= HITUNG SISA MATERIAL ================= //
$q_sisa = "
  SELECT 
    td.teknisi_id,
    (COALESCE(SUM(td.dc),0) +
     COALESCE(SUM(td.s_calm),0) +
     COALESCE(SUM(td.clam_hook),0) +
     COALESCE(SUM(td.otp),0) +
     COALESCE(SUM(td.prekso),0) +
     COALESCE(SUM(td.tiang),0) +
     COALESCE(SUM(td.soc_value),0)) AS total_diambil,
    (COALESCE(SUM(mu.dc),0) +
     COALESCE(SUM(mu.s_calm),0) +
     COALESCE(SUM(mu.clam_hook),0) +
     COALESCE(SUM(mu.otp),0) +
     COALESCE(SUM(mu.prekso),0) +
     COALESCE(SUM(mu.tiang),0) +
     COALESCE(SUM(mu.soc_value),0)) AS total_dipakai
  FROM teknisi_detail td
  JOIN material_used mu ON td.teknisi_id = mu.teknisi_id
  GROUP BY td.teknisi_id
";

$res_sisa = $conn->query($q_sisa);

$sisa_material = 0;
if ($res_sisa && $res_sisa->num_rows > 0) {
  while ($row = $res_sisa->fetch_assoc()) {
    $sisa_material += ($row['total_diambil'] - $row['total_dipakai']);
  }
}

// ================= HITUNG SISA PRECONT ================= //
$sisa_precont = 0;

$q_precont = "
  SELECT td.teknisi_id, td.precont_json AS precont_masuk, mu.precont_json AS precont_pakai
  FROM teknisi_detail td
  JOIN material_used mu ON td.teknisi_id = mu.teknisi_id
  WHERE td.precont_json IS NOT NULL OR mu.precont_json IS NOT NULL
";
$res_precont = $conn->query($q_precont);

if ($res_precont && $res_precont->num_rows > 0) {
  while ($row = $res_precont->fetch_assoc()) {
    $masuk = json_decode($row['precont_masuk'], true) ?: [];
    $pakai = json_decode($row['precont_pakai'], true) ?: [];

    foreach ($masuk as $size => $val) {
      $val_masuk = is_numeric($val) ? (int)$val : 0;
      $val_pakai = isset($pakai[$size]) && is_numeric($pakai[$size]) ? (int)$pakai[$size] : 0;

      $sisa_precont += ($val_masuk - $val_pakai);
    }
  }
}

// Gabungkan hasil precont ke total sisa
$sisa_material += $sisa_precont;

?>
