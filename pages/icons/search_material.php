<?php
include "../../koneksi.php";

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "
  SELECT m.*, t.namatek
  FROM material_used m
  JOIN teknisi t ON m.teknisi_id = t.id
";

if ($q !== '') {
  $safe = $conn->real_escape_string($q);
  // hanya tampilkan nama yang diawali huruf tertentu
  $sql .= " WHERE t.namatek LIKE '".$safe."%'";
}

$sql .= " ORDER BY t.namatek ASC, m.id DESC";

$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
  $no = 1;
  while ($d = $result->fetch_assoc()) {
    echo "<tr>
      <td>{$no}</td>
      <td>".htmlspecialchars($d['namatek'])."</td>
      <td>".htmlspecialchars($d['tanggal'])."</td>
      <td>".htmlspecialchars($d['wo'])."</td>
      <td>".htmlspecialchars($d['dc'])."</td>
      <td>".htmlspecialchars($d['s_calm'])."</td>
      <td>".htmlspecialchars($d['clam_hook'])."</td>
      <td>".htmlspecialchars($d['otp'])."</td>
      <td>".htmlspecialchars($d['prekso'])."</td>
      <td>".htmlspecialchars($d['tiang'])."</td>
      <td>".htmlspecialchars($d['soc_option'])."</td>
      <td>";
      
      if (!empty($d['precont_json'])) {
        $pc = json_decode($d['precont_json'], true);
        if (is_array($pc)) {
          foreach ($pc as $k => $v) {
            echo htmlspecialchars($k)." (".htmlspecialchars($v).")<br>";
          }
        }
      } else {
        echo "-";
      }

    echo "</td>
      <td>
        <a href='?edit_detail={$d['id']}' class='btn btn-sm btn-warning'>
          <i class='bi bi-pencil'></i> Edit
        </a>
        <a href='?delete_material={$d['id']}' class='btn btn-sm btn-danger'
           onclick=\"return confirm('Yakin hapus data ini?')\">
           <i class='bi bi-trash'></i> Delete
        </a>
      </td>
    </tr>";
    $no++;
  }
} else {
  echo "<tr><td colspan='13'>Tidak ada data material.</td></tr>";
}
