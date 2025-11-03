<?php
include "../../koneksi.php";

$term = $_GET['term'] ?? '';

// query dasar
$sql = "
  SELECT d.*, t.namatek 
  FROM teknisi_detail d 
  JOIN teknisi t ON d.teknisi_id = t.id
";

if ($term != '') {
    $safe = $conn->real_escape_string($term);
    // hanya cari yang diawali keyword
    $sql .= " WHERE t.namatek LIKE '$safe%'";
}

$sql .= " ORDER BY t.namatek ASC, d.id DESC";

$qDetail = $conn->query($sql);

if ($qDetail && $qDetail->num_rows > 0) {
    $no = 1;
    while ($d = $qDetail->fetch_assoc()) {
        ?>
        <tr>
          <td><?= $no++ ?></td>
          <td class="fw-semibold"><?= htmlspecialchars($d['namatek']) ?></td>
          <td><?= htmlspecialchars($d['tanggal']) ?></td>
          <td><?= !empty($d['rfs']) ? htmlspecialchars($d['rfs']) : 0 ?></td>
          <td><?= !empty($d['dc']) ? htmlspecialchars($d['dc']) : 0 ?></td>
          <td><?= !empty($d['s_calm']) ? htmlspecialchars($d['s_calm']) : 0 ?></td>
          <td><?= !empty($d['clam_hook']) ? htmlspecialchars($d['clam_hook']) : 0 ?></td>
          <td><?= !empty($d['otp']) ? htmlspecialchars($d['otp']) : 0 ?></td>
          <td><?= !empty($d['prekso']) ? htmlspecialchars($d['prekso']) : 0 ?></td>
          <td><?= !empty($d['tiang']) ? htmlspecialchars($d['tiang']) : 0 ?></td>

          <td class="soc-cell">
            <?= !empty($d['soc_option']) ? htmlspecialchars($d['soc_option']) : 0 ?>
            <?php if (!empty($d['soc_value'])): ?>
              (<?= htmlspecialchars($d['soc_value']) ?>)
            <?php endif; ?>
          </td>

          <td>
            <?php
            if (!empty($d['precont_json'])) {
              $pc = json_decode($d['precont_json'], true);
              if (is_array($pc) && count($pc) > 0) {
                echo '<div class="precont-list">';
                foreach ($pc as $k => $v) {
                  echo '<span class="precont-item">' . htmlspecialchars($k) . " ($v)</span>";
                }
                echo '</div>';
              } else {
                echo 0;
              }
            } else {
              echo 0;
            }
            ?>
          </td>

          <td>
            <a href="data_material.php?edit_detail=<?= $d['id'] ?>" 
               class="btn btn-sm btn-warning d-inline-flex align-items-center gap-1">
              <i class="bi bi-pencil"></i> Update
            </a>
            <a href="data_material.php?delete_detail=<?= $d['id'] ?>" 
               class="btn btn-sm btn-danger d-inline-flex align-items-center gap-1"
               onclick="return confirm('Yakin hapus data ini?')">
              <i class="bi bi-trash"></i> Delete
            </a>
          </td>
        </tr>
        <?php
    }
} else {
    echo '<tr><td colspan="13">Tidak ada data material.</td></tr>';
}
?>
