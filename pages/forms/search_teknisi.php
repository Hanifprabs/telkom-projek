<?php
include "../../koneksi.php";

$keyword = isset($_GET['q']) ? trim($_GET['q']) : "";

$where = "";
if ($keyword !== "") {
    $safeKeyword = $conn->real_escape_string($keyword);
    $where = "WHERE namatek LIKE '$safeKeyword%'";
}

$sql = "
  SELECT id, namatek, nik, sektor, mitra, idtele, crew, valid
  FROM teknisi
  $where
  ORDER BY TRIM(LOWER(namatek)) COLLATE utf8mb4_unicode_ci ASC
  LIMIT 50
";
$result = $conn->query($sql);

$no = 1;
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>".$no++."</td>
                <td class='fw-semibold'>".htmlspecialchars($row['namatek'])."</td>
                <td>".htmlspecialchars($row['nik'])."</td>
                <td>".htmlspecialchars($row['sektor'])."</td>
                <td>".htmlspecialchars($row['mitra'])."</td>
                <td>".htmlspecialchars($row['idtele'])."</td>
                <td>".htmlspecialchars($row['crew'])."</td>
                <td><span class='badge ".($row['valid']=="Y"?'bg-success':'bg-danger')."'>".$row['valid']."</span></td>
                <td>
                  <a href='?edit=".$row['id']."' class='btn btn-warning btn-sm mb-1'>
                    <i class='bi bi-pencil'></i> Update
                  </a>
                  <a href='?delete=".$row['id']."' class='btn btn-danger btn-sm'
                     onclick=\"return confirm('Hapus data ini?')\">
                    <i class='bi bi-trash'></i> Delete
                  </a>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='9'>Tidak ada data.</td></tr>";
}
