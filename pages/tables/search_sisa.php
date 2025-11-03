<?php
// === Koneksi Database ===
include __DIR__ . "/../../koneksi.php";

// === Konstanta Precont ===
$PRECONT_SIZES = [50, 75, 80, 100, 120, 135, 150, 180];
$limit = 5;

// === Fungsi inisialisasi row agregasi ===
function initAggRow($sizes) {
    $row = [
        'dc'=>0,'s_calm'=>0,'clam_hook'=>0,'otp'=>0,'prekso'=>0,'tiang'=>0,
        'soc_fuji'=>0,'soc_sum'=>0,'precont'=>[]
    ];
    foreach ($sizes as $s) $row['precont'][$s] = 0;
    return $row;
}

// === Ambil parameter pencarian ===
$q = isset($_GET['q']) ? trim($_GET['q']) : "";

// === Ambil Teknisi Default dari teknisi_detail ===
if ($q === '') {
    $sql = "SELECT DISTINCT t.id, t.namatek 
            FROM teknisi t
            INNER JOIN teknisi_detail td ON t.id = td.teknisi_id
            ORDER BY LTRIM(RTRIM(LOWER(t.namatek))) ASC 
            LIMIT $limit";
    $resTek = $conn->query($sql);
} else {
    $stmt = $conn->prepare(
        "SELECT DISTINCT t.id, t.namatek 
         FROM teknisi t
         INNER JOIN teknisi_detail td ON t.id = td.teknisi_id
         WHERE t.namatek LIKE CONCAT(?, '%') 
         ORDER BY LTRIM(RTRIM(LOWER(t.namatek))) ASC 
         LIMIT ?"
    );
    $stmt->bind_param("si", $q, $limit);
    $stmt->execute();
    $resTek = $stmt->get_result();
}

// === Siapkan array teknisi ===
$teknisiRows = [];
if ($resTek && $resTek->num_rows > 0) {
    while ($row = $resTek->fetch_assoc()) {
        $teknisiRows[$row['id']] = $row['namatek'];
    }
}
$listTekIds = !empty($teknisiRows) ? implode(",", array_keys($teknisiRows)) : "0";

// === Ambil Data Masuk (teknisi_detail) ===
$aggMasuk = $detailInfo = [];
$resMasuk = $conn->query("SELECT * FROM teknisi_detail WHERE teknisi_id IN ($listTekIds)");
if ($resMasuk && $resMasuk->num_rows > 0) {
    while ($d = $resMasuk->fetch_assoc()) {
        $tid = $d['teknisi_id'];
        if (!isset($aggMasuk[$tid])) $aggMasuk[$tid] = initAggRow($PRECONT_SIZES);

        foreach (['dc','s_calm','clam_hook','otp','prekso','tiang'] as $k) {
            $aggMasuk[$tid][$k] += (int)$d[$k];
        }

        ($d['soc_option']==='Fuji')
            ? $aggMasuk[$tid]['soc_fuji'] += (int)$d['soc_value']
            : $aggMasuk[$tid]['soc_sum']  += (int)$d['soc_value'];

        if (!isset($detailInfo[$tid]['rfs'])) $detailInfo[$tid]['rfs'] = $d['rfs'];

        // Precont JSON atau option
        if (!empty($d['precont_json'])) {
            $decoded = json_decode($d['precont_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $k=>$v) {
                    $size=(int)$k;
                    if (in_array($size,$PRECONT_SIZES,true)) $aggMasuk[$tid]['precont'][$size]+=(int)$v;
                }
            }
        } elseif (!empty($d['precont_option'])) {
            $size=(int)$d['precont_option']; 
            $val=(int)$d['precont_value'];
            if (in_array($size,$PRECONT_SIZES,true)) $aggMasuk[$tid]['precont'][$size]+=$val;
        }
    }
}

// === Ambil Data Pakai (material_used) ===
$aggPakai = [];
$resPakai = $conn->query("SELECT * FROM material_used WHERE teknisi_id IN ($listTekIds)");
if ($resPakai && $resPakai->num_rows > 0) {
    while ($d = $resPakai->fetch_assoc()) {
        $tid = $d['teknisi_id'];
        if (!isset($aggPakai[$tid])) $aggPakai[$tid] = initAggRow($PRECONT_SIZES);

        foreach (['dc','s_calm','clam_hook','otp','prekso','tiang'] as $k) {
            $aggPakai[$tid][$k] += (int)$d[$k];
        }

        ($d['soc_option']==='Fuji')
            ? $aggPakai[$tid]['soc_fuji'] += (int)$d['soc_value']
            : $aggPakai[$tid]['soc_sum']  += (int)$d['soc_value'];

        if (!isset($detailInfo[$tid]['wo'])) $detailInfo[$tid]['wo'] = $d['wo'];

        // Precont JSON atau option
        if (!empty($d['precont_json'])) {
            $decoded = json_decode($d['precont_json'], true);
            if (is_array($decoded)) {
                foreach ($decoded as $k=>$v) {
                    $size=(int)$k;
                    if (in_array($size,$PRECONT_SIZES,true)) $aggPakai[$tid]['precont'][$size]+=(int)$v;
                }
            }
        } elseif (!empty($d['precont_option'])) {
            $size=(int)$d['precont_option']; 
            $val=(int)$d['precont_value'];
            if (in_array($size,$PRECONT_SIZES,true)) $aggPakai[$tid]['precont'][$size]+=$val;
        }
    }
}

// === Sekarang $teknisiRows, $aggMasuk, dan $aggPakai siap dipakai di tampilan ===
// === Bangun Output ===
$output = '';
$no = 1;
if (!empty($teknisiRows)) {
    foreach ($teknisiRows as $tid=>$nama) {
        $masuk = $aggMasuk[$tid] ?? initAggRow($PRECONT_SIZES);
        $pakai = $aggPakai[$tid] ?? initAggRow($PRECONT_SIZES);

        $wo  = !empty($detailInfo[$tid]['wo']) ? htmlspecialchars($detailInfo[$tid]['wo']) : null;
        $rfs = !empty($detailInfo[$tid]['rfs']) ? htmlspecialchars($detailInfo[$tid]['rfs']) : '0';

        if ($wo === null) {
            $wo = "<span class='text-danger'>belum ada laporan material digunakan</span>";

            $sisa = [
                'dc'=>0,'s_calm'=>0,'clam_hook'=>0,'otp'=>0,
                'prekso'=>0,'tiang'=>0,'soc_fuji'=>0,'soc_sum'=>0
            ];
            $sisaPrecont = [];
            foreach ($PRECONT_SIZES as $size) {
                $sisaPrecont[$size] = 0;
            }
        } else {
            // === Hitung sisa precont ===
            $sisaPrecont = [];
            foreach ($PRECONT_SIZES as $size) {
                $masukVal = $masuk['precont'][$size] ?? 0;
                $pakaiVal = $pakai['precont'][$size] ?? 0;
                $sisaPrecont[$size] = $masukVal - $pakaiVal;
            }

            // === Hitung sisa material umum ===
            $sisa = [];
            foreach(['dc','s_calm','clam_hook','otp','prekso','tiang'] as $k){
                $masukVal = $masuk[$k] ?? 0;
                $pakaiVal = $pakai[$k] ?? 0;
                $sisa[$k] = $masukVal - $pakaiVal;
            }

            // === SOC (Fuji vs Sum, dipisah sesuai soc_option) ===
            $sisa['soc_fuji'] = ($masuk['soc_fuji'] ?? 0) - ($pakai['soc_fuji'] ?? 0);
            $sisa['soc_sum']  = ($masuk['soc_sum']  ?? 0) - ($pakai['soc_sum']  ?? 0);
        }

        // Label SOC
        $socLabel="Fuji (" . (int)$sisa['soc_fuji'] . "), Sum (" . (int)$sisa['soc_sum'] . ")";

        // Precont HTML
        $preHTML='';
        foreach($PRECONT_SIZES as $s){
            $val = $sisaPrecont[$s] ?? 0;
            $preHTML .= $s." (<span class='badge bg-secondary'>".(int)$val."</span>)<br>";
        }

        // Output row
        $output .= "<tr>
            <td>{$no}</td>
            <td>".htmlspecialchars($nama)."</td>
            <td>{$rfs}</td>
            <td>{$wo}</td>
            <td>".(int)$sisa['dc']."</td>
            <td>".(int)$sisa['s_calm']."</td>
            <td>".(int)$sisa['clam_hook']."</td>
            <td>".(int)$sisa['otp']."</td>
            <td>".(int)$sisa['prekso']."</td>
            <td>".(int)$sisa['tiang']."</td>
            <td>{$socLabel}</td>
            <td class='text-start'>{$preHTML}</td>
        </tr>";
        $no++;
    }
} else {
    $output .= "<tr>
        <td colspan='12' class='text-center text-danger'>
            Tidak ada data ditemukan
        </td>
    </tr>";
}

echo $output;


?>
