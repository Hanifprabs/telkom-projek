<?php
// ================== KONEKSI DATABASE ================== //
$host = "localhost";
$user = "root";
$pass = "";
$db   = "db_teknisi";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);

   
}

// ================== QUERY DATA AWAL ================== //
$qDetail = $conn->query("SELECT d.*, t.namatek 
                         FROM teknisi_detail d
                         JOIN teknisi t ON d.teknisi_id = t.id
                         ORDER BY d.id DESC");

$qMaterial = $conn->query("SELECT m.*, t.namatek 
                           FROM material_used m
                           JOIN teknisi t ON m.teknisi_id = t.id
                           ORDER BY m.id DESC");

// ================== HELPER: Pastikan Kolom Ada ================== //
function ensure_column($conn, $table, $column, $definition) {
    $res = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
    if ($res && $res->num_rows === 0) {
        $conn->query("ALTER TABLE `$table` ADD COLUMN $definition");
    }
}

// Pastikan kolom tambahan ada
ensure_column($conn, 'teknisi_detail', 'precont_json', "TEXT NULL AFTER soc_value");
ensure_column($conn, 'teknisi_detail', 'tanggal', "DATE NULL AFTER tiang");

// ================== AUTOCOMPLETE TEKNISI ================== //
if (isset($_GET['action']) && $_GET['action'] === 'search') {
    $term = $_GET['term'] ?? '';
    $like = $term . "%";  

    $stmt = $conn->prepare("SELECT id, namatek, nik FROM teknisi WHERE namatek LIKE ? ORDER BY namatek ASC LIMIT 10");
    $stmt->bind_param("s", $like);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "value" => $row['id'],
            "label" => $row['namatek'] . " - " . $row['nik']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// ================== CRUD TEKNISI ================== //
if (isset($_POST['submit'])) {
    $namatek = $_POST['namatek'] ?? '';
    $nik     = $_POST['nik'] ?? '';
    $sektor  = $_POST['sektor'] ?? '';
    $mitra   = $_POST['mitra'] ?? '';
    $idtele  = $_POST['idtele'] ?? '';
    $crew    = $_POST['crew'] ?? '';
    $valid   = $_POST['valid'] ?? '';

    if ($namatek === '' || $nik === '') {
        die("Nama teknisi dan NIK wajib diisi.");
    }

    $stmt = $conn->prepare("INSERT INTO teknisi (namatek, nik, sektor, mitra, idtele, crew, valid) 
                            VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $namatek, $nik, $sektor, $mitra, $idtele, $crew, $valid);

    if ($stmt->execute()) {
        header("Location: input_teknisi.php?status=added");
    } else {
        echo "Gagal menambahkan teknisi: " . $stmt->error;
    }
    exit;
}

if (isset($_POST['update'])) {
    $stmt = $conn->prepare("UPDATE teknisi 
                            SET namatek=?, nik=?, sektor=?, mitra=?, idtele=?, crew=?, valid=? 
                            WHERE id=?");
    $stmt->bind_param("sssssssi",
        $_POST['namatek'], $_POST['nik'], $_POST['sektor'],
        $_POST['mitra'], $_POST['idtele'], $_POST['crew'],
        $_POST['valid'], $_POST['id']
    );
    $stmt->execute();
    header("Location: basic_elements.php?status=updated");
    exit;
}

if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM teknisi WHERE id=?");
    $stmt->bind_param("i", $_GET['delete']);
    $stmt->execute();
    header("Location: basic_elements.php?status=deleted");
    exit;
}

// ================== AMBIL DATA TEKNISI ================== //
$result = $conn->query("SELECT * FROM teknisi ORDER BY namatek ASC");

$editTeknisi = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM teknisi WHERE id=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editTeknisi = $stmt->get_result()->fetch_assoc();
}

// ================== CRUD DETAIL TEKNISI ================== //
if (isset($_POST['submit_detail'])) {
    $teknisi_id = (int)($_POST['teknisi_id'] ?? 0);
    if (!$teknisi_id) die("Error: teknisi tidak valid.");

    $chk = $conn->prepare("SELECT id FROM teknisi WHERE id=?");
    $chk->bind_param("i", $teknisi_id);
    $chk->execute();
    if ($chk->get_result()->num_rows === 0) die("Error: teknisi_id tidak ditemukan.");

    $rfs        = $_POST['rfs'] ?? null;
    $dc         = $_POST['dc'] ?: null;
    $s_calm     = $_POST['s_calm'] ?: null;
    $clam_hook  = $_POST['clam_hook'] ?: null;
    $otp        = $_POST['otp'] ?: null;
    $prekso     = $_POST['prekso'] ?: null;
    $tiang      = $_POST['tiang'] ?: null;
    $tanggal    = $_POST['tanggal'] ?: null;
    $soc_option = $_POST['soc_option'] ?? null;
    $soc_value  = $_POST['soc_value'] ?: null;
    $precont_json = json_encode($_POST['precont'] ?? []);

    $stmt = $conn->prepare("INSERT INTO teknisi_detail 
        (teknisi_id, rfs, dc, s_calm, clam_hook, otp, prekso, tiang, tanggal, soc_option, soc_value, precont_json) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");

    $stmt->bind_param("isiiiiiissis",
        $teknisi_id, $rfs, $dc, $s_calm, $clam_hook, $otp, $prekso,
        $tiang, $tanggal, $soc_option, $soc_value, $precont_json
    );
    $stmt->execute();
    header("Location: input_material.php?status=added");
    exit;
}

if (isset($_POST['update_detail'])) {
    $detail_id  = (int)($_POST['id'] ?? 0);
    if (!$detail_id) die("Error: id detail tidak valid.");

    $teknisi_id = (int)($_POST['teknisi_id'] ?? 0);
    if (!$teknisi_id) die("Error: teknisi_id tidak valid.");

    $rfs        = $_POST['rfs'] ?? null;
    $dc         = $_POST['dc'] ?: null;
    $s_calm     = $_POST['s_calm'] ?: null;
    $clam_hook  = $_POST['clam_hook'] ?: null;
    $otp        = $_POST['otp'] ?: null;
    $prekso     = $_POST['prekso'] ?: null;
    $tiang      = $_POST['tiang'] ?: null;
    $tanggal    = $_POST['tanggal'] ?: null;
    $soc_option = $_POST['soc_option'] ?? null;
    $soc_value  = $_POST['soc_value'] ?: null;
    $precont_json = json_encode($_POST['precont'] ?? []);

    $stmt = $conn->prepare("UPDATE teknisi_detail SET 
        teknisi_id=?, rfs=?, dc=?, s_calm=?, clam_hook=?, otp=?, prekso=?, tiang=?, tanggal=?, soc_option=?, soc_value=?, precont_json=? 
        WHERE id=?");

    $stmt->bind_param("isiiiiiissisi",
        $teknisi_id, $rfs, $dc, $s_calm, $clam_hook, $otp, $prekso,
        $tiang, $tanggal, $soc_option, $soc_value, $precont_json, $detail_id
    );
    $stmt->execute();
    header("Location: data_material.php?status=updated");
    exit;
}

if (isset($_GET['delete_detail'])) {
    $stmt = $conn->prepare("DELETE FROM teknisi_detail WHERE id=?");
    $stmt->bind_param("i", $_GET['delete_detail']);
    $stmt->execute();
    header("Location: data_material.php?status=deleted");
    exit;
}

// ================== TAMBAH MATERIAL DIPAKAI ================== //
if (isset($_POST['submit_material'])) {
    $teknisi_id = (int)($_POST['teknisi_id'] ?? 0);
    if ($teknisi_id <= 0) die("Error: teknisi_id tidak valid.");

    // Cek user_id valid
    $user_id = (int)($_POST['user_id'] ?? 0);
    $cekUser = $conn->prepare("SELECT id FROM users WHERE id=?");
    $cekUser->bind_param("i", $user_id);
    $cekUser->execute();
    $resUser = $cekUser->get_result();
    if ($resUser->num_rows === 0) {
        $user_id = null; // NULL supaya tidak error foreign key
    }

    // Ambil data form
    $wo                = $_POST['wo'] ?? null;
    $dc                = (int)($_POST['dc'] ?? 0);
    $s_calm            = (int)($_POST['s_calm'] ?? 0);
    $clam_hook         = (int)($_POST['clam_hook'] ?? 0);
    $otp               = (int)($_POST['otp'] ?? 0);
    $prekso            = (int)($_POST['prekso'] ?? 0);
    $soc_option        = $_POST['soc_option'] ?? null;
    $soc_value         = (int)($_POST['soc_value'] ?? 0);
    $precont_json      = json_encode($_POST['precont'] ?? []);
    $tiang             = (int)($_POST['tiang'] ?? 0);
    $tanggal           = $_POST['tanggal'] ?: date('Y-m-d');
    $precont_option    = (int)($_POST['precont_option'] ?? 0);
    $precont_value     = (int)($_POST['precont_value'] ?? 0);
    $deskripsi_masalah = $_POST['deskripsi_masalah'] ?? null;
    $status_masalah    = "Belum Dilihat";

    // ================== UPLOAD FOTO ================== //
    $dc_foto = null;
    if (isset($_FILES['dc_foto']) && $_FILES['dc_foto']['error'] === 0) {
        $target_dir = __DIR__ . "/uploads/";

        if (!file_exists($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $foto_nama = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES["dc_foto"]["name"]));
        $target_file = $target_dir . $foto_nama;

        if (move_uploaded_file($_FILES["dc_foto"]["tmp_name"], $target_file)) {
            // simpan path relatif (bukan absolut)
            $dc_foto = "uploads/" . $foto_nama;
        }
    }

    // ================== SIMPAN KE DATABASE ================== //
    $stmt = $conn->prepare("
        INSERT INTO material_used (
            user_id, teknisi_id, wo, dc, s_calm, clam_hook, otp, prekso, 
            soc_option, soc_value, precont_json, tiang, tanggal, 
            precont_option, precont_value, dc_foto, deskripsi_masalah, status_masalah
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $stmt->bind_param(
        "iisiiiiisissiiisss",
        $user_id, $teknisi_id, $wo, $dc, $s_calm, $clam_hook, $otp, $prekso,
        $soc_option, $soc_value, $precont_json, $tiang, $tanggal,
        $precont_option, $precont_value, $dc_foto, $deskripsi_masalah, $status_masalah
    );

    if ($stmt->execute()) {
        header("Location: input_material_used.php?status=added");
        exit;
    } else {
        die("Gagal menyimpan: " . $stmt->error);
    }
}



// ================== HAPUS MATERIAL DIPAKAI ================== //
if (isset($_GET['delete_material'])) {
    $id = (int) $_GET['delete_material'];

    // Ambil foto untuk dihapus dari folder
    $res = $conn->query("SELECT dc_foto FROM material_used WHERE id=$id");
    if ($res && $row = $res->fetch_assoc()) {
        $foto_path = __DIR__ . "/" . $row['dc_foto'];
        if (is_file($foto_path)) {
            unlink($foto_path);
        }
    }

    // Hapus dari database
    $del = $conn->prepare("DELETE FROM material_used WHERE id=?");
    $del->bind_param("i", $id);
    if ($del->execute()) {
        header("Location: data_material_used.php?status=deleted");
        exit;
    } else {
        die("Gagal menghapus: " . $del->error);
    }
}

// ================== UPDATE MATERIAL DIPAKAI ================== //
if (isset($_POST['update_material'])) {
    $id = (int)($_POST['id'] ?? 0);
    if ($id <= 0) die("Error: ID tidak valid.");

    $teknisi_id = (int)($_POST['teknisi_id'] ?? 0);
    $user_id    = (int)($_POST['user_id'] ?? 0);
    $wo         = $_POST['wo'] ?? null;
    $dc         = (int)($_POST['dc'] ?? 0);
    $s_calm     = (int)($_POST['s_calm'] ?? 0);
    $clam_hook  = (int)($_POST['clam_hook'] ?? 0);
    $otp        = (int)($_POST['otp'] ?? 0);
    $prekso     = (int)($_POST['prekso'] ?? 0);
    $soc_option = $_POST['soc_option'] ?? null;
    $soc_value  = (int)($_POST['soc_value'] ?? 0);
    $precont_json   = json_encode($_POST['precont'] ?? []);
    $tiang          = (int)($_POST['tiang'] ?? 0);
    $tanggal        = $_POST['tanggal'] ?: date('Y-m-d');
    $deskripsi_masalah = $_POST['deskripsi_masalah'] ?? null;

    // --- Ambil foto lama ---
    $old_foto = null;
    $res = $conn->query("SELECT dc_foto FROM material_used WHERE id=$id");
    if ($res && $r = $res->fetch_assoc()) {
        $old_foto = $r['dc_foto'];
    }

    // --- Upload foto baru jika ada ---
    $dc_foto = $old_foto;
    if (isset($_FILES['dc_foto']) && $_FILES['dc_foto']['error'] === 0) {
        $target_dir = __DIR__ . "/uploads/";
        if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

        if ($old_foto && file_exists(__DIR__ . "/" . $old_foto)) {
            unlink(__DIR__ . "/" . $old_foto);
        }

        $foto_nama = time() . "_" . preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($_FILES["dc_foto"]["name"]));
        $target_file = $target_dir . $foto_nama;

        if (move_uploaded_file($_FILES["dc_foto"]["tmp_name"], $target_file)) {
            $dc_foto = "uploads/" . $foto_nama;
        }
    }

    // --- Update ke database ---
    $stmt = $conn->prepare("
        UPDATE material_used SET
            user_id = ?,
            teknisi_id = ?, 
            wo = ?, 
            dc = ?, 
            s_calm = ?, 
            clam_hook = ?, 
            otp = ?, 
            prekso = ?, 
            soc_option = ?, 
            soc_value = ?, 
            precont_json = ?, 
            tiang = ?, 
            tanggal = ?, 
            dc_foto = ?, 
            deskripsi_masalah = ?
        WHERE id = ?
    ");

    $stmt->bind_param(
        "iisiiiiisisssssi",
        $user_id, $teknisi_id, $wo, $dc, $s_calm, $clam_hook, $otp, $prekso,
        $soc_option, $soc_value, $precont_json, $tiang, $tanggal,
        $dc_foto, $deskripsi_masalah, $id
    );

    if ($stmt->execute()) {
        header("Location: data_material_used.php?status=updated");
        exit;
    } else {
        die("Gagal memperbarui: " . $stmt->error);
    }
}




// ================== QUERY UNTUK TAMPILAN ================== //
$result   = $conn->query("SELECT * FROM teknisi ORDER BY namatek");
$qDetail  = $conn->query("SELECT d.*, t.namatek FROM teknisi_detail d 
                          JOIN teknisi t ON d.teknisi_id = t.id 
                          ORDER BY d.id DESC");
$qMaterial= $conn->query("SELECT m.*, t.namatek 
                          FROM material_used m 
                          LEFT JOIN teknisi t ON t.id = m.teknisi_id
                          ORDER BY m.id DESC");

// ================== AMBIL DATA UNTUK EDIT ================== //
$editTeknisi = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM teknisi WHERE id=?");
    $stmt->bind_param("i", $_GET['edit']);
    $stmt->execute();
    $editTeknisi = $stmt->get_result()->fetch_assoc();
}

$editDetail = null;
$editTeknisiLabel = '';
if (isset($_GET['edit_detail'])) {
    $stmt = $conn->prepare("SELECT * FROM teknisi_detail WHERE id=?");
    $stmt->bind_param("i", $_GET['edit_detail']);
    $stmt->execute();
    $editDetail = $stmt->get_result()->fetch_assoc();

    if ($editDetail && !empty($editDetail['teknisi_id'])) {
        $tstmt = $conn->prepare("SELECT namatek, nik FROM teknisi WHERE id=?");
        $tstmt->bind_param("i", $editDetail['teknisi_id']);
        $tstmt->execute();
        $tr = $tstmt->get_result()->fetch_assoc();
        if ($tr) {
            $editTeknisiLabel = $tr['namatek'] . ' - ' . $tr['nik'];
        }
    }
}

$editMaterial = null;
if (isset($_GET['edit_material'])) {
    $stmt = $conn->prepare("SELECT * FROM material_used WHERE id=?");
    $stmt->bind_param("i", $_GET['edit_material']);
    $stmt->execute();
    $editMaterial = $stmt->get_result()->fetch_assoc();

    if ($editMaterial) {
        $tek = $conn->query("SELECT namatek FROM teknisi WHERE id=".(int)$editMaterial['teknisi_id'])->fetch_assoc();
        if ($tek) $editTeknisiLabel = $tek['namatek'];
    }
}

?>
