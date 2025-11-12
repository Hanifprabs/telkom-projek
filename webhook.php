<?php
// ==================== KONFIGURASI ERROR (Produksi: nonaktifkan) ====================
error_reporting(E_ALL);
ini_set('display_errors', 1);
// ==================== SETUP DIREKTORI LOG ====================
$logDir = __DIR__ . '/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0777, true);
}
$errorLog = $logDir . '/error_log.txt';
$sqlLog = $logDir . '/debug_sql.txt';

// ==================== FUNGSI LOG ERROR ====================
function logError($message) {
    global $errorLog;
    $timestamp = date('Y-m-d H:i:s');
    $entry = "[$timestamp] $message\n";
    file_put_contents($errorLog, $entry, FILE_APPEND);
}

// ==================== UJI TULIS LOG ====================
if (@file_put_contents($logDir . '/test_log.txt', "✅ Log test " . date('Y-m-d H:i:s') . "\n", FILE_APPEND) === false) {
    logError("Gagal menulis test_log.txt di $logDir");
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
    if ($reply_markup) $data["reply_markup"] = $reply_markup;
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


// ==================== START & LOGOUT ====================
if ($text == "/start" || $text == "/logout") {
    $conn->query("UPDATE users SET step=NULL, temp_data=NULL, status='inactive' WHERE telegram_id='$user_id'");

    $keyboard = [
        "keyboard" => [
            [["text" => "🔑 Login"], ["text" => "📝 Register"]]
        ],
        "resize_keyboard" => true,
        "one_time_keyboard" => false
    ];

    sendMessage(
        $chat_id,
        "👋 Selamat datang di *Sistem Lapor Material*!\n\nSilakan pilih salah satu opsi di bawah ini untuk memulai:",
        json_encode($keyboard)
    );

    exit;
}


// ==================== LOGIN ====================
elseif ($text == "🔑 Login") {
    $conn->query("UPDATE users SET step='login' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "🔐 Silakan kirim data login dengan format:\n\n`username|password`");
    exit;
}
elseif ($user && $user['step'] == 'login') {
    if (strpos($text, "|") !== false) {
        list($username, $password) = explode("|", $text, 2);
        $username = trim($username);
        $cek = $conn->query("SELECT * FROM users WHERE username='" . $conn->real_escape_string($username) . "' LIMIT 1");
        if ($cek && $cek->num_rows > 0) {
            $row = $cek->fetch_assoc();
            if (password_verify($password, $row['password'])) {
                $conn->query("UPDATE users 
                    SET telegram_id='$user_id', step=NULL, temp_data=NULL, last_login=NOW(), status='active' 
                    WHERE id='{$row['id']}'");

                $keyboard = [
                    "keyboard" => [
                        [["text" => "📋 Lapor"], ["text" => "🚪 Logout"]]
                    ],
                    "resize_keyboard" => true
                ];
                sendMessage($chat_id, "✅ Login berhasil, selamat datang {$row['username']}!", json_encode($keyboard));
            } else {
                sendMessage($chat_id, "❌ Password salah.");
            }
        } else {
            sendMessage($chat_id, "❌ Username tidak ditemukan.");
        }
    } else {
        sendMessage($chat_id, "⚠️ Format login salah. Gunakan `username|password`.");
    }
    exit;
}


// ==================== REGISTER ====================
elseif ($text == "📝 Register") {
    $conn->query("UPDATE users SET step='register_username' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan username untuk registrasi:");
    exit;
}
elseif ($user && $user['step'] == 'register_username') {
    $username = trim($text);
    $cek = $conn->query("SELECT * FROM users WHERE username='" . $conn->real_escape_string($username) . "'");
    if ($cek && $cek->num_rows > 0) {
        sendMessage($chat_id, "❌ Username sudah dipakai, masukkan username lain:");
    } else {
        $conn->query("UPDATE users SET step='register_password', temp_data='" . $conn->real_escape_string($username) . "' WHERE telegram_id='$user_id'");
        sendMessage($chat_id, "Masukkan password:");
    }
    exit;
}
elseif ($user && $user['step'] == 'register_password') {
    $username = $user['temp_data'];
    $password = password_hash($text, PASSWORD_BCRYPT);
    $conn->query("INSERT INTO users (telegram_id, username, password, role, status, step) 
                 VALUES ('" . $conn->real_escape_string($user_id) . "','" . $conn->real_escape_string($username) . "','" . $conn->real_escape_string($password) . "','teknisi','active',NULL)");
    $keyboard = [
        "keyboard" => [
            [["text" => "📋 Lapor"], ["text" => "🚪 Logout"]]
        ],
        "resize_keyboard" => true
    ];
    sendMessage($chat_id, "✅ Registrasi berhasil! Silakan mulai dengan 📋 Lapor.", json_encode($keyboard));
    exit;
}


// ==================== LAPOR ====================
elseif ($text == "📋 Lapor") {
    if (!isLogin($user_id, $conn)) {
        sendMessage($chat_id, "⚠️ Anda harus login dulu sebelum melapor.\nSilakan tekan 🔑 Login.");
    } else {
        $teknisi = $conn->query("SELECT id, namatek FROM teknisi");
        $options = [];
        while ($row = $teknisi->fetch_assoc()) {
            $options[] = [["text" => $row['namatek']]];
        }
        $keyboard = ["keyboard" => $options, "resize_keyboard" => true];
        $conn->query("UPDATE users SET step='lapor_teknisi', temp_data=NULL WHERE telegram_id='$user_id'");
        sendMessage($chat_id, "👷 Pilih nama teknisi:", json_encode($keyboard));
    }
    exit;
}


// ==================== STEP LAPOR TEKNISI ====================
elseif (getUserStep($user_id, $conn) == "lapor_teknisi") {
    $cek = $conn->prepare("SELECT id FROM teknisi WHERE namatek = ?");
    $cek->bind_param("s", $text);
    $cek->execute();
    $result = $cek->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $data = ["teknisi_id" => $row['id']];
        $conn->query("UPDATE users SET step='lapor_wo', temp_data='" . $conn->real_escape_string(json_encode($data)) . "' WHERE telegram_id='$user_id'");

        $removeKeyboard = ["remove_keyboard" => true];
        sendMessage($chat_id, "📝 Masukkan nomor WO:", json_encode($removeKeyboard));
    } else {
        sendMessage($chat_id, "❌ Nama teknisi tidak ditemukan. Silakan pilih dari tombol yang ada.");
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
function saveLaporanToDB($data, $conn, $chat_id, $user_id) {
    // Escape semua field
    $escape = fn($val) => $conn->real_escape_string($val ?? '');

    $precont_json  = $escape(json_encode($data['precont'] ?? []));
    $spliter_json  = $escape(json_encode($data['splitter'] ?? [])); // sesuaikan nama kolom!
    $smoove_json   = $escape(json_encode($data['smoove'] ?? []));

    $user_id_val        = $escape($user_id);
    $teknisi_id         = $escape($data['teknisi_id'] ?? '');
    $wo                 = $escape($data['wo'] ?? '');
    $dc                 = $escape($data['dc'] ?? '');
    $dc_foto            = $escape($data['dc_foto'] ?? '');
    $s_calm             = $escape($data['s_calm'] ?? '');
    $clam_hook          = $escape($data['clam_hook'] ?? '');
    $otp                = $escape($data['otp'] ?? '');
    $prekso             = $escape($data['prekso'] ?? '');
    $soc_option         = $escape($data['soc_option'] ?? '');
    $soc_value          = $escape($data['soc_value'] ?? '');
    $tiang              = $escape($data['tiang'] ?? '');
    $tanggal            = $escape($data['tanggal'] ?? date('Y-m-d'));
    $ad_sc              = $escape($data['ad_sc'] ?? '');
    $tipe_pekerjaan     = $escape($data['tipe_pekerjaan'] ?? '');
    $deskripsi_masalah  = $escape($data['deskripsi_masalah'] ?? '');
    $precont_option     = $escape($data['precont_option'] ?? '');
    $precont_value      = $escape($data['precont_value'] ?? '');
    $status_masalah     = 'Belum Dilihat';

    // Cegah field kosong penting
    if (empty($user_id_val) || empty($teknisi_id) || empty($wo)) {
        sendMessage($chat_id, "⚠️ Gagal menyimpan laporan: data penting kosong (user_id / teknisi_id / WO).");
        return;
    }

    // Query sudah disesuaikan dengan tabel asli
    $sql = "INSERT INTO material_used (
        user_id, teknisi_id, wo, dc, s_calm, clam_hook, otp, prekso,
        soc_option, soc_value, precont_json, spliter_json, smoove_json,
        ad_sc, tipe_pekerjaan, tiang, tanggal,
        precont_option, precont_value, dc_foto, deskripsi_masalah, status_masalah
    ) VALUES (
        '$user_id_val', '$teknisi_id', '$wo', '$dc', '$s_calm', '$clam_hook', '$otp', '$prekso',
        '$soc_option', '$soc_value', '$precont_json', '$spliter_json', '$smoove_json',
        '$ad_sc', '$tipe_pekerjaan', '$tiang', '$tanggal',
        '$precont_option', '$precont_value', '$dc_foto', '$deskripsi_masalah', '$status_masalah'
    )";

    // ========== LOGGING ==========
    $log_dir = __DIR__ . '/logs';
    if (!is_dir($log_dir)) mkdir($log_dir, 0777, true);
    $log_file = $log_dir . '/debug_sql.txt';
    $timestamp = date('Y-m-d H:i:s');

    if ($conn->query($sql)) {
        $status = "✅ BERHASIL SIMPAN DATA";
        $conn->query("UPDATE users SET step=NULL, temp_data=NULL WHERE telegram_id='$user_id'");

        $keyboard = [
            "keyboard" => [
                [["text" => "📋 Lapor"], ["text" => "🚪 Logout"]]
            ],
            "resize_keyboard" => true
        ];

        sendMessage($chat_id, "✅ *Laporan berhasil disimpan!*", json_encode($keyboard));
    } else {
        $status = "❌ GAGAL SIMPAN DATA: " . $conn->error;
        sendMessage($chat_id, "❌ Gagal menyimpan laporan.\n\nError: " . $conn->error);
    }

    // Catat log hasil eksekusi
    $log_entry = "=============================\n"
               . "⏰ $timestamp\n"
               . "👤 User ID: $user_id_val\n"
               . "📋 Query: $sql\n"
               . "📣 Status: $status\n"
               . "=============================\n\n";
    file_put_contents($log_file, $log_entry, FILE_APPEND);
}



// ==================== AKHIR FILE ====================

?>
