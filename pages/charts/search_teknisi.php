<?php
include "../../koneksi.php";

$term = $_GET['term'] ?? '';
$data = [];

if ($term != '') {
    $stmt = $conn->prepare("SELECT id, namatek FROM teknisi WHERE namatek LIKE ?");
    $like = "%$term%";
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "value" => $row['id'],
            "label" => $row['namatek']
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($data);
