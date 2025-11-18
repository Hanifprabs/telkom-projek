<?php
// ==================== KONFIGURASI ERROR (Produksi: nonaktifkan) ====================
// ==================== ERROR REPORTING ====================
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ==================== LOG DIRECTORY ====================
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) mkdir($logDir, 0777, true);

function writeLog($filename, $message) {
    global $logDir;
    $f = $logDir . '/' . $filename;
    $timestamp = date('Y-m-d H:i:s');
    file_put_contents($f, "[$timestamp] $message\n", FILE_APPEND);
}

// Log setiap query SQL
function logSQL($sql, $conn) {
    writeLog('sql_log.txt', $sql . " | ERROR: " . $conn->error);
}

// Log alur / step bot
function logStep($msg) {
    writeLog('bot_step.txt', $msg);
}


// ==================== TOKEN BOT TELEGRAM ====================
$token = "8098921875:AAEJdDGjk6PFuCSJy8fK76MTnc-yNXCooKU";
$apiURL = "https://api.telegram.org/bot$token/";

// ==================== KONEKSI DATABASE ====================
require_once 'koneksi.php';
if ($conn->connect_error) {
    http_response_code(500);
    echo "DB Connection Failed: " . $conn->connect_error;
    exit;
}

// ==================== AMBIL UPDATE DARI TELEGRAM ====================
$update = json_decode(file_get_contents("php://input"), true);
if (!$update || !isset($update["message"])) {
    http_response_code(200);
    echo "OK";
    exit;
}

$chat_id = $update["message"]["chat"]["id"] ?? null;
$text = trim($update["message"]["text"] ?? "");
$user_id = $chat_id; // gunakan chat_id sebagai telegram_id di users

// ==================== FUNSI BANTU ====================

/**
 * Kirim pesan ke Telegram
 */
function sendMessage($chat_id, $text, $reply_markup = null) {
    global $apiURL;
    $data = [
        "chat_id" => $chat_id,
        "text" => $text,
        "parse_mode" => "Markdown"
    ];

    if ($reply_markup)
        $data["reply_markup"] = $reply_markup;

    // jangan die(); cukup panggil API
    @file_get_contents($apiURL . "sendMessage?" . http_build_query($data));
}

/**
 * Ambil step user dari tabel users
 */
function getUserStep($user_id, $conn) {
    $cek = $conn->query("SELECT step FROM users WHERE telegram_id='$user_id'");
    if ($cek && $cek->num_rows > 0) {
        $row = $cek->fetch_assoc();
        return $row['step'];
    }
    return null;
}

/**
 * Cek apakah user login (status active)
 */
function isLogin($user_id, $conn) {
    $cek = $conn->query("SELECT * FROM users WHERE telegram_id='$user_id' AND status='active'");
    return $cek && $cek->num_rows > 0;
}

// Ambil data user (bisa null jika belum ada)
$userQuery = $conn->query("SELECT * FROM users WHERE telegram_id='$user_id'");
$user = $userQuery && $userQuery->num_rows ? $userQuery->fetch_assoc() : null;

// ==================== START & LOGOUT GLOBAL ====================
if ($text == "/start" || $text == "/logout" || $text == "🚪 Start") {

    $conn->query("
        UPDATE users 
        SET step=NULL, temp_data=NULL, status='inactive' 
        WHERE telegram_id='$user_id'
    ");

    $keyboard = [
        "keyboard" => [
            [["text" => "🔑 Login"], ["text" => "📝 Register"], ["text" => "🚪 Start"]]
        ],
        "resize_keyboard" => true
    ];

    sendMessage(
        $chat_id,
        "👋 Selamat datang di *Sistem Lapor Material*!\n\nSilakan pilih menu untuk memulai:",
        json_encode($keyboard)
    );
    return;
}



// ==================== LOGIN ====================
elseif ($text == "🔑 Login") {

    $conn->query("
        INSERT INTO users (telegram_id, role, status, step)
        VALUES ('$user_id', 'teknisi', 'inactive', 'login')
        ON DUPLICATE KEY UPDATE step='login', status='inactive'
    ");

    sendMessage($chat_id, "🔐 Kirim data login:\n\nusername/password");
    return;
}



// ==================== PROSES LOGIN ====================
elseif ($user && $user['step'] === 'login') {

    if (strpos($text, "/") === false) {
        sendMessage($chat_id, "⚠️ Format salah. Gunakan: username/password");
        return;
    }

    list($username, $password) = explode("/", $text, 2);
    $username = trim($username);

    $cek = $conn->query("
        SELECT * FROM users 
        WHERE username='" . $conn->real_escape_string($username) . "'
        LIMIT 1
    ");

    if ($cek->num_rows == 0) {
        sendMessage($chat_id, "❌ Username tidak ditemukan.");
        return;
    }

    $row = $cek->fetch_assoc();

    if (!password_verify($password, $row['password'])) {
        sendMessage($chat_id, "❌ Password salah.");
        return;
    }

    // Login sukses
    $conn->query("
        UPDATE users
        SET telegram_id='$user_id',
            step=NULL,
            temp_data=NULL,
            last_login=NOW(),
            status='active'
        WHERE id='{$row['id']}'
    ");

    $keyboard = [
        "keyboard" => [
            [["text" => "📋 Lapor"], ["text" => "🚪 Start"]]
        ],
        "resize_keyboard" => true
    ];

    sendMessage($chat_id, "✅ Login berhasil, selamat datang {$row['username']}!", json_encode($keyboard));
    return;
}



// ==================== LOGOUT ====================
elseif ($text == "🚪 Logout") {

    $conn->query("
        UPDATE users 
        SET step=NULL, temp_data=NULL, status='inactive' 
        WHERE telegram_id='$user_id'
    ");

    $keyboard = [
      
        "keyboard" => [
            [["text" => "🔑 Login"], ["text" => "📝 Register"], ["text" => "🚪 Start"]],
    
        ],
        "resize_keyboard" => true
    ];

    sendMessage($chat_id, "🚪 Anda telah *logout*.\nSilakan pilih menu:", json_encode($keyboard));
    return;
}




// ==================== REGISTER ====================
elseif ($text == "📝 Register") {

    // Pastikan user belum punya akun aktif
    $cekAkun = $conn->query("SELECT * FROM users WHERE telegram_id='$user_id' LIMIT 1");

    if ($cekAkun->num_rows > 0) {
        $u = $cekAkun->fetch_assoc();
        if (!empty($u['nik']) && !empty($u['username']) && $u['status'] === 'active') {
            sendMessage($chat_id, "⚠️ Anda sudah terdaftar dan aktif. Tidak bisa register lagi.");
            return;
        }
    }

    // Buat atau reset account
    $conn->query("
        INSERT INTO users (telegram_id, role, status, step, temp_data)
        VALUES ('$user_id','teknisi','inactive','register_nik', NULL)
        ON DUPLICATE KEY UPDATE
            status='inactive',
            step='register_nik',
            temp_data=NULL
    ");

    sendMessage($chat_id, "Silakan masukkan NIK:");
    return;
}



// ==================== STEP 1: INPUT NIK ====================
elseif ($user && $user['step'] === 'register_nik') {

    $nik = trim($text);

    if ($nik === '') {
        sendMessage($chat_id, "❌ NIK tidak boleh kosong. Masukkan NIK yang valid:");
        return;
    }

    // Validasi hanya angka (opsional)
    if (!ctype_digit($nik)) {
        sendMessage($chat_id, "❌ NIK hanya boleh berisi angka. Masukkan ulang:");
        return;
    }

    // Cek apakah NIK terdaftar di tabel teknisi
    $cekNik = $conn->prepare("SELECT id FROM teknisi WHERE nik=? LIMIT 1");
    $cekNik->bind_param("s", $nik);
    $cekNik->execute();
    $resNik = $cekNik->get_result();

    if ($resNik->num_rows == 0) {
        $cekNik->close();
        sendMessage($chat_id, "❌ NIK tidak terdaftar di sistem teknisi!");
        return;
    }
    $cekNik->close();

    // ❗ NIK tidak boleh pernah dipakai user manapun, termasuk akun ini sendiri
    $cekUsed = $conn->prepare("SELECT id FROM users WHERE nik=? LIMIT 1");
    $cekUsed->bind_param("s", $nik);
    $cekUsed->execute();
    $resUsed = $cekUsed->get_result();

    if ($resUsed->num_rows > 0) {
        $cekUsed->close();
        sendMessage($chat_id, "❌ NIK ini sudah pernah digunakan dan tidak dapat dipakai lagi!");
        return;
    }
    $cekUsed->close();

    // Jika lolos semua validasi → simpan nik sementara
    $update = $conn->prepare("UPDATE users SET step='register_username', temp_data=? WHERE telegram_id=?");
    $update->bind_param("ss", $nik, $user_id);
    $update->execute();
    $update->close();

    sendMessage($chat_id, "NIK valid.\nMasukkan Username:");
    return;
}



// ==================== STEP 2: INPUT USERNAME ====================
elseif ($user && $user['step'] === 'register_username') {

    $username = trim($text);

    // Cek username sudah digunakan user lain
    $cekUser = $conn->query("SELECT * FROM users WHERE username='$username' AND telegram_id!='$user_id' LIMIT 1");
    if ($cekUser->num_rows > 0) {
        sendMessage($chat_id, "❌ Username sudah dipakai. Pilih username lain:");
        return;
    }

    // Ambil nik dari temp_data
    $nik = $user['temp_data'];
    $temp = $nik . "|" . $username;

    $conn->query("
        UPDATE users 
        SET step='register_password', temp_data='$temp'
        WHERE telegram_id='$user_id'
    ");

    sendMessage($chat_id, "Username tersedia.\nMasukkan Password:");
    return;
}



// ==================== STEP 3: SIMPAN PASSWORD ====================
elseif ($user && $user['step'] === 'register_password') {

    list($nik, $username) = explode("|", $user['temp_data']);
    $hashed = password_hash($text, PASSWORD_BCRYPT);

    $conn->query("
        UPDATE users SET
            nik='$nik',
            username='$username',
            password='$hashed',
            role='teknisi',
            status='active',
            step=NULL,
            temp_data=NULL
        WHERE telegram_id='$user_id'
    ");

    $keyboard = [
        "keyboard" => [
            [["text" => "📋 Lapor"], ["text" => "🚪 Logout"]]
        ],
        "resize_keyboard" => true
    ];

    sendMessage($chat_id, "✅ Registrasi berhasil! Anda sudah login.", json_encode($keyboard));
    return;
}



// ==================== LAPOR ====================
// ==================== LAPOR ====================
elseif ($text == "📋 Lapor") {

    if (!isLogin($user_id, $conn)) {
        sendMessage($chat_id, "⚠️ Anda harus login dulu sebelum melapor.\nSilakan tekan 🔑 Login.");
        exit;
    }

    // Ambil nik & role user
    $q = $conn->query("SELECT nik, role FROM users WHERE telegram_id='$user_id' LIMIT 1");
    $u = $q->fetch_assoc();
    $nikLogin = $u['nik'] ?? null;
    $role     = $u['role'] ?? 'user';


    // ==================== Role Teknisi → langsung lanjut, tidak pilih nama ====================
    if ($role !== 'admin' && $nikLogin) {

        $stmt = $conn->prepare("SELECT id, namatek FROM teknisi WHERE nik=? LIMIT 1");
        $stmt->bind_param("s", $nikLogin);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res->num_rows > 0) {
            $tek = $res->fetch_assoc();
            $data = ["teknisi_id" => $tek['id']];

            $conn->query("UPDATE users SET step='lapor_wo',
                          temp_data='".$conn->real_escape_string(json_encode($data))."'
                          WHERE telegram_id='$user_id'");

            sendMessage($chat_id,
                "👷 Hai *{$tek['namatek']}*, laporan siap dibuat.\n\n📝 Silakan masukkan nomor WO:",
                json_encode(["remove_keyboard" => true])
            );
            exit;
        }

        sendMessage($chat_id, "⚠ Data teknisi tidak ditemukan berdasarkan NIK Anda. Hubungi admin.");
        exit;
    }


    // ==================== Role Admin → tampilkan daftar teknisi ====================
    $options = [];
    $rs = $conn->query("SELECT namatek FROM teknisi ORDER BY namatek ASC");
    while ($r = $rs->fetch_assoc()) {
        $options[] = [["text" => $r['namatek']]];
    }

    $keyboard = ["keyboard" => $options, "resize_keyboard" => true];

    $conn->query("UPDATE users SET step='lapor_teknisi', temp_data=NULL
                  WHERE telegram_id='$user_id'");

    sendMessage($chat_id, "👷 Pilih nama teknisi:", json_encode($keyboard));
    exit;
}




// ==================== STEP LAPOR TEKNISI ====================
// ==================== STEP LAPOR TEKNISI ====================
elseif (getUserStep($user_id, $conn) === "lapor_teknisi") {

    $textNama = trim($text);

    // Validasi harus nama teknisi yang tersedia
    $cek = $conn->prepare("SELECT id FROM teknisi WHERE namatek=? LIMIT 1");
    $cek->bind_param("s", $textNama);
    $cek->execute();
    $res = $cek->get_result();

    if ($res && $res->num_rows > 0) {

        $row = $res->fetch_assoc();
        $data = ["teknisi_id" => $row['id']];

        $conn->query("UPDATE users SET step='lapor_wo',
                      temp_data='".$conn->real_escape_string(json_encode($data))."'
                      WHERE telegram_id='$user_id'");

        sendMessage($chat_id, "📝 Masukkan nomor WO:", json_encode(["remove_keyboard" => true]));
    } else {
        sendMessage($chat_id,
            "❌ *Nama teknisi tidak terdaftar!*\n" .
            "Silakan pilih nama dari tombol yang tersedia.\n\n" .
            "Jika nama tidak ada, hubungi admin."
        );
    }
    exit;
}



// ==================== WO ====================
elseif ($user && $user['step'] == 'lapor_wo') {
    if ($text !== "-" && !preg_match('/^[a-zA-Z0-9\-\/ ]+$/', $text)) {
        sendMessage($chat_id, "❌ Format WO tidak valid.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['wo'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_dc', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah DC:");
    exit;
}


// ==================== DC ====================
elseif ($user && $user['step'] == 'lapor_dc') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah DC harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['dc'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_s_calm', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah s-calm:");
    exit;
}


// ==================== s_calm ====================
elseif ($user && $user['step'] == 'lapor_s_calm') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah s-calm harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['s_calm'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_clam_hook', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Clam Hook:");
    exit;
}


// ==================== clam_hook ====================
elseif ($user && $user['step'] == 'lapor_clam_hook') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah Clam Hook harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['clam_hook'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_otp', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah OTP:");
    exit;
}


// ==================== otp ====================
elseif ($user && $user['step'] == 'lapor_otp') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah OTP harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['otp'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_prekso', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Prekso:");
    exit;
}


// ==================== prekso ====================
elseif ($user && $user['step'] == 'lapor_prekso') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah Prekso harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['prekso'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_soc_option', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");

    $keyboard = [
        "keyboard" => [
            [["text" => "Sum"], ["text" => "Fuji"]]
        ],
        "resize_keyboard" => true
    ];
    sendMessage($chat_id, "Pilih tipe SOC:", json_encode($keyboard));
    exit;
}


// ==================== SOC option ====================
elseif ($user && $user['step'] == 'lapor_soc_option') {
    if (!in_array(strtolower($text), ["sum", "fuji"])) {
        sendMessage($chat_id, "❌ Pilih hanya 'Sum' atau 'Fuji'.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['soc_option'] = strtolower($text);
    $conn->query("UPDATE users SET step='lapor_soc_value', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah SOC ($text):");
    exit;
}


// ==================== SOC value ====================
elseif ($user && $user['step'] == 'lapor_soc_value') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah SOC harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['soc_value'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_tiang', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Tiang:");
    exit;
}


// ==================== Tiang ====================
elseif ($user && $user['step'] == 'lapor_tiang') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah Tiang harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['tiang'] = ($text === "-" ? "" : $text);

    // simpan step ke tanggal
    $conn->query("UPDATE users SET step='lapor_tanggal', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");

    // buat opsi tanggal otomatis
    $today = date("Y-m-d");
    $yesterday = date("Y-m-d", strtotime("-1 day"));
    $tomorrow = date("Y-m-d", strtotime("+1 day"));

    $keyboard = [
        "keyboard" => [
            [["text" => $yesterday], ["text" => $today], ["text" => $tomorrow]]
        ],
        "resize_keyboard" => true,
        "one_time_keyboard" => true
    ];

    sendMessage($chat_id, "📅 Pilih tanggal:", json_encode($keyboard));
    exit;
}


// ==================== Tanggal ====================
elseif ($user && $user['step'] == 'lapor_tanggal') {
    $today = date("Y-m-d");
    $yesterday = date("Y-m-d", strtotime("-1 day"));
    $tomorrow = date("Y-m-d", strtotime("+1 day"));

    // validasi: boleh tombol (3 opsi) atau input manual format YYYY-MM-DD
    if (!in_array($text, [$today, $yesterday, $tomorrow]) && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $text)) {
        sendMessage($chat_id, "❌ Format salah.\nGunakan tombol yang tersedia atau ketik manual dengan format YYYY-MM-DD.");
        return;
    }

    $data = json_decode($user['temp_data'], true);
    $data['tanggal'] = $text;

    // isi default precont
    $data['precont'] = [
        "50"  => 0,
        "75"  => 0,
        "80"  => 0,
        "100" => 0,
        "120" => 0,
        "135" => 0,
        "150" => 0,
        "180" => 0
    ];

    $conn->query("UPDATE users SET step='lapor_precont_50', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 50:");
    exit;
}


// ==================== Precont steps (50 → 75 → 80 → 100 → 120 → 135 → 150 → 180) ====================
elseif ($user && $user['step'] == 'lapor_precont_50') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['50'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_75', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 75:");
    exit;
}
elseif ($user && $user['step'] == 'lapor_precont_75') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['75'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_80', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 80:");
    exit;
}
elseif ($user && $user['step'] == 'lapor_precont_80') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['80'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_100', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 100:");
    exit;
}
elseif ($user && $user['step'] == 'lapor_precont_100') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['100'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_120', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 120:");
    exit;
}
elseif ($user && $user['step'] == 'lapor_precont_120') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['120'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_135', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 135:");
    exit;
}
elseif ($user && $user['step'] == 'lapor_precont_135') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['135'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_150', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 150:");
    exit;
}
elseif ($user && $user['step'] == 'lapor_precont_150') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['150'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_180', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 180:");
    exit;
}
elseif ($user && $user['step'] == 'lapor_precont_180') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['180'] = $text;
    $conn->query("UPDATE users SET step='lapor_splitter', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Splitter 1.2:");
    exit;
}


// ==================== STEP SPLITTER (AUTO: 1.2,1.4,1.8,1.16) ====================
elseif ($user && $user['step'] == 'lapor_splitter') {
    $data = json_decode($user['temp_data'], true);
    $labels = ['1.2', '1.4', '1.8', '1.16'];

    if (!isset($data['splitter'])) {
        $data['splitter'] = [];
    }
    $currentIndex = count($data['splitter']);

    // safety check — seharusnya tidak terjadi
    if ($currentIndex > count($labels)) {
        $currentIndex = count($labels);
    }

    // simpan input ke array sesuai urutan label
    if ($currentIndex < count($labels)) {
        $data['splitter'][$labels[$currentIndex]] = $text;
    }

    // lanjut ke input berikutnya
    if (count($data['splitter']) < count($labels)) {
        $next = $labels[count($data['splitter'])];
        $conn->query("UPDATE users 
                      SET temp_data='" . $conn->real_escape_string(json_encode($data)) . "' 
                      WHERE telegram_id='$user_id'");
        sendMessage($chat_id, "Masukkan jumlah Splitter $next:");
    } else {
        $conn->query("UPDATE users 
                      SET step='lapor_smoove', 
                          temp_data='" . $conn->real_escape_string(json_encode($data)) . "' 
                      WHERE telegram_id='$user_id'");
        sendMessage($chat_id, "Masukkan jumlah Smoove Kecil:");
    }
    exit;
}


// ==================== STEP SMOOVE (AUTO: Kecil, Tipe 3) ====================
elseif ($user && $user['step'] == 'lapor_smoove') {
    $data = json_decode($user['temp_data'], true);
    $labels = ['Kecil', 'Tipe 3'];

    if (!isset($data['smoove'])) {
        $data['smoove'] = [];
    }
    $currentIndex = count($data['smoove']);

    if ($currentIndex < count($labels)) {
        $data['smoove'][$labels[$currentIndex]] = $text;
    }

    if (count($data['smoove']) < count($labels)) {
        $next = $labels[count($data['smoove'])];
        $conn->query("UPDATE users 
                      SET temp_data='" . $conn->real_escape_string(json_encode($data)) . "' 
                      WHERE telegram_id='$user_id'");
        sendMessage($chat_id, "Masukkan jumlah Smoove $next:");
    } else {
        $conn->query("UPDATE users 
                      SET step='lapor_ad_sc', 
                          temp_data='" . $conn->real_escape_string(json_encode($data)) . "' 
                      WHERE telegram_id='$user_id'");
        sendMessage($chat_id, "Masukkan jumlah AD-SC:");
    }
    exit;
}


// ==================== STEP AD-SC ====================
elseif ($user && $user['step'] == 'lapor_ad_sc') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah AD-SC harus angka.");
        return;
    }

    $data = json_decode($user['temp_data'], true);
    $data['ad_sc'] = ($text === "-" ? "" : $text);

    $conn->query("UPDATE users 
                  SET step='lapor_foto_keluhan', 
                      temp_data='" . $conn->real_escape_string(json_encode($data)) . "' 
                  WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "📷 Kirim foto keluhan (atau ketik 0 jika tidak ada):");
    exit;
}


// ==================== STEP FOTO KELUHAN ====================
elseif ($user && $user['step'] == 'lapor_foto_keluhan') {
    $data = json_decode($user['temp_data'], true);

    if ($text === "0") {
        $data['dc_foto'] = "";
        $conn->query("UPDATE users 
                      SET step='lapor_deskripsi_masalah', 
                          temp_data='" . $conn->real_escape_string(json_encode($data)) . "' 
                      WHERE telegram_id='$user_id'");
        sendMessage($chat_id, "Silakan tulis deskripsi masalah:");
        return;
    }

    if (isset($update['message']['photo'])) {
        $file_id = end($update['message']['photo'])['file_id'];
        $file_info = json_decode(file_get_contents("https://api.telegram.org/bot$token/getFile?file_id=$file_id"), true);

        if (isset($file_info['result']['file_path'])) {
            $file_path = $file_info['result']['file_path'];
            $file_url = "https://api.telegram.org/file/bot$token/$file_path";
            if (!file_exists("uploads")) mkdir("uploads", 0777, true);
            $localPath = "uploads/" . basename($file_path);
            file_put_contents($localPath, file_get_contents($file_url));

            $data['dc_foto'] = $localPath;
            $conn->query("UPDATE users 
                          SET step='lapor_deskripsi_masalah', 
                              temp_data='" . $conn->real_escape_string(json_encode($data)) . "' 
                          WHERE telegram_id='$user_id'");
            sendMessage($chat_id, "✅ Foto diterima.\nSekarang tulis deskripsi masalah:");
        } else {
            sendMessage($chat_id, "❌ Gagal menerima foto, coba ulangi atau ketik 0 untuk lewati.");
        }
    } else {
        sendMessage($chat_id, "❌ Silakan kirim foto atau ketik 0 untuk melewati.");
    }
    exit;
}


// ==================== STEP DESKRIPSI MASALAH ====================
elseif ($user && $user['step'] == 'lapor_deskripsi_masalah') {
    $data = json_decode($user['temp_data'], true);
    $data['deskripsi_masalah'] = $text;

    // Ganti tombol menjadi sesuai dengan daftar tipe pekerjaan terbaru
    $keyboard = [
        "keyboard" => [
            [["text" => "IOAN"], ["text" => "Provisioning"]],
            [["text" => "Maintenance"], ["text" => "Konstruksi"]],
            [["text" => "Mitratel"]]
        ],
        "resize_keyboard" => true,
        "one_time_keyboard" => true
    ];

    // Update step ke 'lapor_tipe_pekerjaan'
    $conn->query("UPDATE users 
                  SET step='lapor_tipe_pekerjaan', 
                      temp_data='" . $conn->real_escape_string(json_encode($data)) . "' 
                  WHERE telegram_id='$user_id'");

    sendMessage($chat_id, "Pilih tipe pekerjaan:", json_encode($keyboard));
    exit;
}


// ==================== STEP TIPE PEKERJAAN (TERAKHIR) ====================
elseif ($user && $user['step'] == 'lapor_tipe_pekerjaan') {
    // Sesuaikan tipe pekerjaan yang valid
    $valid_tipe = ['IOAN', 'Provisioning', 'Maintenance', 'Konstruksi', 'Mitratel'];

    if (!in_array($text, $valid_tipe)) {
        sendMessage($chat_id, "❌ Pilih tipe pekerjaan dari tombol yang tersedia.");
        return;
    }

    $data = json_decode($user['temp_data'], true);
    $data['tipe_pekerjaan'] = $text;

    // Simpan laporan ke database
    saveLaporanToDB($data, $conn, $chat_id, $user_id);
    exit;
}



// ==================== FUNGSI SIMPAN LAPORAN KE DB ====================
function saveLaporanToDB($data, $conn, $chat_id, $telegram_user_id) {

    // Ambil user.id berdasarkan telegram_id untuk memastikan FK valid
    $stmtUser = $conn->prepare("SELECT id FROM users WHERE telegram_id = ?");
    $stmtUser->bind_param("s", $telegram_user_id);
    $stmtUser->execute();
    $resultUser = $stmtUser->get_result();

    if ($resultUser->num_rows == 0) {
        sendMessage($chat_id, "❌ User tidak terdaftar. Silakan lakukan /start kembali.");
        return;
    }

    $user_id = $resultUser->fetch_assoc()['id']; // user.id yang valid

    // Siapkan data
    $precont_json  = json_encode($data['precont']   ?? []);
    $spliter_json  = json_encode($data['splitter']  ?? []);
    $smoove_json   = json_encode($data['smoove']    ?? []);

    $teknisi_id         = $data['teknisi_id']        ?? '';
    $wo                 = $data['wo']                ?? '';
    $dc                 = $data['dc']                ?? '';
    $dc_foto            = $data['dc_foto']           ?? '';
    $s_calm             = $data['s_calm']            ?? '';
    $clam_hook          = $data['clam_hook']         ?? '';
    $otp                = $data['otp']               ?? '';
    $prekso             = $data['prekso']            ?? '';
    $soc_option         = $data['soc_option']        ?? '';
    $soc_value          = $data['soc_value']         ?? '';
    $tiang              = $data['tiang']             ?? '';
    $tanggal            = $data['tanggal']           ?? date('Y-m-d');
    $ad_sc              = $data['ad_sc']             ?? '';
    $tipe_pekerjaan     = $data['tipe_pekerjaan']    ?? '';
    $deskripsi_masalah  = $data['deskripsi_masalah'] ?? '';
    $precont_option     = $data['precont_option']    ?? '';
    $precont_value      = $data['precont_value']     ?? '';
    $status_masalah     = 'Belum Dilihat';

    // Validasi wajib
    if (empty($user_id) || empty($teknisi_id) || empty($wo)) {
        sendMessage($chat_id, "⚠️ Gagal menyimpan laporan. Data penting kosong (user/teknisi/WO).");
        return;
    }

    // Query insert dengan prepared statement
    $sql = "INSERT INTO material_used (
        user_id, teknisi_id, wo, dc, s_calm, clam_hook, otp, prekso,
        soc_option, soc_value, precont_json, spliter_json, smoove_json,
        ad_sc, tipe_pekerjaan, tiang, tanggal, precont_option, precont_value,
        dc_foto, deskripsi_masalah, status_masalah
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iissssssssssssssssssss",
        $user_id, $teknisi_id, $wo, $dc, $s_calm, $clam_hook, $otp, $prekso,
        $soc_option, $soc_value, $precont_json, $spliter_json, $smoove_json,
        $ad_sc, $tipe_pekerjaan, $tiang, $tanggal, $precont_option, $precont_value,
        $dc_foto, $deskripsi_masalah, $status_masalah
    );

    // Logging
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) mkdir($log_dir, 0777, true);
    $log_file = $log_dir . '/debug_sql.txt';
    $timestamp = date('Y-m-d H:i:s');

    if ($stmt->execute()) {
        // Reset step Telegram
        $conn->query("UPDATE users SET step=NULL, temp_data=NULL WHERE telegram_id='$telegram_user_id'");

        $keyboard = [
            "keyboard" => [
                [["text" => "📋 Lapor"], ["text" => "🚪 Logout"]]
            ],
            "resize_keyboard" => true
        ];

        sendMessage($chat_id, "✅ *Laporan berhasil disimpan!*", json_encode($keyboard));

        $status = "✅ BERHASIL SIMPAN DATA";
    } else {
        $err = $stmt->error;
        sendMessage($chat_id, "❌ Gagal menyimpan laporan.\nError: $err");
        $status = "❌ GAGAL: $err";
    }

    file_put_contents($log_file, "
=============================
⏰ $timestamp
👤 User Telegram: $telegram_user_id
👤 User DB ID    : $user_id
📣 Status        : $status
=============================\n\n", FILE_APPEND);
}


// ==================== AKHIR FILE ====================

?>
