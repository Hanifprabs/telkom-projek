<?php
// ================= FUNCTIONS.PHP ===================== //

/**
 * Inisialisasi struktur kosong untuk aggregate row.
 * Bisa dipakai untuk data masuk atau pakai.
 */
function initAggRow($PRECONT_SIZES) {
    $row = [
        'dc' => 0,
        's_calm' => 0,
        'clam_hook' => 0,
        'otp' => 0,
        'prekso' => 0,
        'tiang' => 0,
        'soc_fuji' => 0,
        'soc_sum' => 0,
        'precont' => []
    ];
    foreach ($PRECONT_SIZES as $size) {
        $row['precont'][$size] = 0;
    }
    return $row;
}

/**
 * Hitung sisa material per teknisi.
 * Input: array $masuk, array $pakai
 * Output: array sisa
 */
function hitungSisa($masuk, $pakai, $PRECONT_SIZES) {
    $sisa = [];

    // material selain precont
    foreach (['dc','s_calm','clam_hook','otp','prekso','tiang'] as $key) {
        $masukVal = $masuk[$key] ?? 0;
        $pakaiVal = $pakai[$key] ?? 0;
        $sisa[$key] = max($masukVal - $pakaiVal, 0);
    }

    // SOC
    $sisa['soc_fuji'] = max(($masuk['soc_fuji'] ?? 0) - ($pakai['soc_fuji'] ?? 0), 0);
    $sisa['soc_sum']  = max(($masuk['soc_sum'] ?? 0) - ($pakai['soc_sum'] ?? 0), 0);

    // Precont (boleh negatif)
    $sisa['precont'] = [];
    foreach ($PRECONT_SIZES as $size) {
        $masukVal = $masuk['precont'][$size] ?? 0;
        $pakaiVal = $pakai['precont'][$size] ?? 0;
        $sisa['precont'][$size] = $masukVal - $pakaiVal;
    }

    return $sisa;
}

/**
 * Generate HTML untuk kolom Precont (ditampilkan vertikal)
 */
function generatePrecontHTML($sisaPrecont) {
    $html = '';
    foreach ($sisaPrecont as $size => $val) {
        $html .= htmlspecialchars($size) . 
          " (<span class='badge bg-secondary'>" . (int)$val . "</span>)<br>";
    }
    return $html;
}
