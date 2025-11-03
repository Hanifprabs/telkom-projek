<?php
// Nonaktifkan error warning untuk produksi
error_reporting(0);
ini_set('display_errors', 0);

// TOKEN BOT
$token = "8098921875:AAEJdDGjk6PFuCSJy8fK76MTnc-yNXCooKU"; 
$apiURL = "https://api.telegram.org/bot$token/";

// === KONEKSI DATABASE ===
require_once 'koneksi.php'; 

if ($conn->connect_error) {
    http_response_code(500);
    echo "DB Connection Failed: " . $conn->connect_error;
    exit;
}

// AMBIL UPDATE DARI TELEGRAM
$update = json_decode(file_get_contents("php://input"), true);
if (!$update || !isset($update["message"])) {
    http_response_code(200);
    echo "OK";
    exit;
}

$chat_id = $update["message"]["chat"]["id"] ?? null;
$text    = trim($update["message"]["text"] ?? "");
$user_id = $chat_id;

// FUNGSI KIRIM PESAN
function sendMessage($chat_id, $text, $reply_markup = null) {
    global $apiURL;
    $data = [
        "chat_id" => $chat_id,
        "text" => $text,
        "parse_mode" => "Markdown"
    ];
    if ($reply_markup) $data["reply_markup"] = $reply_markup;

    file_get_contents($apiURL . "sendMessage?" . http_build_query($data));
}

// FUNGSI AMBIL STEP USER
function getUserStep($user_id, $conn) {
    $cek = $conn->query("SELECT step FROM users WHERE telegram_id='$user_id'");
    if ($cek && $cek->num_rows > 0) {
        $row = $cek->fetch_assoc();
        return $row['step'];
    }
    return null;
}

// CEK LOGIN
function isLogin($user_id, $conn) {
    $cek = $conn->query("SELECT * FROM users WHERE telegram_id='$user_id' AND status='active'");
    return $cek && $cek->num_rows > 0;
}

// AMBIL DATA USER
$user = $conn->query("SELECT * FROM users WHERE telegram_id='$user_id'")->fetch_assoc();

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
}

// ==================== LOGIN ====================
elseif ($text == "🔑 Login") {
    $conn->query("UPDATE users SET step='login' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "🔐 Silakan kirim data login dengan format:\n\n`username|password`");
}
elseif ($user && $user['step'] == 'login') {
    if (strpos($text, "|") !== false) {
        list($username, $password) = explode("|", $text, 2);
        $cek = $conn->query("SELECT * FROM users WHERE username='$username' LIMIT 1");
        if ($cek->num_rows > 0) {
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
}

// ==================== REGISTER ====================
elseif ($text == "📝 Register") {
    $conn->query("UPDATE users SET step='register_username' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan username untuk registrasi:");
}
elseif ($user && $user['step'] == 'register_username') {
    $username = $text;
    $cek = $conn->query("SELECT * FROM users WHERE username='$username'");
    if ($cek->num_rows > 0) {
        sendMessage($chat_id, "❌ Username sudah dipakai, masukkan username lain:");
    } else {
        $conn->query("UPDATE users SET step='register_password', temp_data='$username' WHERE telegram_id='$user_id'");
        sendMessage($chat_id, "Masukkan password:");
    }
}
elseif ($user && $user['step'] == 'register_password') {
    $username = $user['temp_data'];
    $password = password_hash($text, PASSWORD_BCRYPT);
    $conn->query("INSERT INTO users (telegram_id, username, password, role, status, step) 
                     VALUES ('$user_id','$username','$password','teknisi','active',NULL)");
    $keyboard = [
        "keyboard" => [
            [["text" => "📋 Lapor"], ["text" => "🚪 Logout"]]
        ],
        "resize_keyboard" => true
    ];
    sendMessage($chat_id, "✅ Registrasi berhasil! Silakan mulai dengan 📋 Lapor.", json_encode($keyboard));
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
}

// ==================== STEP LAPOR TEKNISI ====================
elseif (getUserStep($user_id, $conn) == "lapor_teknisi") {
    $cek = $conn->prepare("SELECT id FROM teknisi WHERE namatek = ?");
    $cek->bind_param("s", $text);
    $cek->execute();
    $result = $cek->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $data = ["teknisi_id" => $row['id']];
        $conn->query("UPDATE users SET step='lapor_wo', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");

        $removeKeyboard = ["remove_keyboard" => true];
        sendMessage($chat_id, "📝 Masukkan nomor WO:", json_encode($removeKeyboard));
    } else {
        sendMessage($chat_id, "❌ Nama teknisi tidak ditemukan. Silakan pilih dari tombol yang ada.");
    }
}


// WO
elseif ($user && $user['step'] == 'lapor_wo') {
    if ($text !== "-" && !preg_match('/^[a-zA-Z0-9\-\/ ]+$/', $text)) {
        sendMessage($chat_id, "❌ Format WO tidak valid.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['wo'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_dc', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah DC:");
}

// DC
// DC (input jumlah)
elseif ($user && $user['step'] == 'lapor_dc') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah DC harus angka.");
        return;
    }

    $data = json_decode($user['temp_data'], true);
    $data['dc'] = ($text === "-" ? "" : $text);

    // Simpan jumlah dc dan minta foto
    $conn->query("UPDATE users SET step='lapor_dc_foto', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "📷 Kirim foto DC:");
}

// DC (foto)
elseif ($user && $user['step'] == 'lapor_dc_foto') {
    if (isset($update['message']['photo'])) {
        $file_id = end($update['message']['photo'])['file_id'];

        // Ambil path file dari Telegram
        $file_info = file_get_contents("https://api.telegram.org/bot$token/getFile?file_id=$file_id");
        $file_info = json_decode($file_info, true);

        if (isset($file_info['result']['file_path'])) {
            $file_path = $file_info['result']['file_path'];
           $file_url = "https://api.telegram.org/file/bot$token/$file_path";
            $localPath = "uploads/" . basename($file_path); // simpan ke folder uploads

            // buat folder uploads jika belum ada
            if (!file_exists("uploads")) mkdir("uploads", 0777, true);

            // unduh dan simpan foto ke folder lokal
            file_put_contents($localPath, file_get_contents($file_url));

            // simpan path lokal ke database
            $data = json_decode($user['temp_data'], true);
            $data['dc_foto'] = $localPath;


            $conn->query("UPDATE users SET step='lapor_s_calm', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
            sendMessage($chat_id, "✅ Foto DC diterima.\nSekarang masukkan jumlah s-calm:");
        } else {
            sendMessage($chat_id, "❌ Gagal mengambil foto. Coba lagi kirim ulang.");
        }
    } else {
        sendMessage($chat_id, "❌ Silakan kirim foto, bukan teks.");
    }
}


// s_calm
elseif ($user && $user['step'] == 'lapor_s_calm') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah s-calm harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['s_calm'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_clam_hook', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Clam Hook:");
}

// clam_hook
elseif ($user && $user['step'] == 'lapor_clam_hook') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah Clam Hook harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['clam_hook'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_otp', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah OTP:");
}

// otp
elseif ($user && $user['step'] == 'lapor_otp') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah OTP harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['otp'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_prekso', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Prekso:");
}

// dst... (prekso → soc_option → soc_value → tiang → tanggal → precont chain → insert)


// ==================== prekso ====================
elseif ($user && $user['step'] == 'lapor_prekso') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah Prekso harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['prekso'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_soc_option', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");

    $keyboard = [
        "keyboard" => [
            [["text" => "Sum"], ["text" => "Fuji"]]
        ],
        "resize_keyboard" => true
    ];
    sendMessage($chat_id, "Pilih tipe SOC:", json_encode($keyboard));
}

// ==================== SOC option ====================
elseif ($user && $user['step'] == 'lapor_soc_option') {
    if (!in_array(strtolower($text), ["sum", "fuji"])) {
        sendMessage($chat_id, "❌ Pilih hanya 'Sum' atau 'Fuji'.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['soc_option'] = strtolower($text);
    $conn->query("UPDATE users SET step='lapor_soc_value', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah SOC ($text):");
}

// ==================== SOC value ====================
elseif ($user && $user['step'] == 'lapor_soc_value') {
    if ($text !== "-" && !is_numeric($text)) {
        sendMessage($chat_id, "❌ Jumlah SOC harus angka.");
        return;
    }
    $data = json_decode($user['temp_data'], true);
    $data['soc_value'] = ($text === "-" ? "" : $text);
    $conn->query("UPDATE users SET step='lapor_tiang', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Tiang:");
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
    $conn->query("UPDATE users SET step='lapor_tanggal', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");

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

    $conn->query("UPDATE users SET step='lapor_precont_50', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 50:");
}


// STEP Precont 50
elseif ($user && $user['step'] == 'lapor_precont_50') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['50'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_75', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 75:");
}

// STEP Precont 75
elseif ($user && $user['step'] == 'lapor_precont_75') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['75'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_80', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 80:");
}

// STEP Precont 80
elseif ($user && $user['step'] == 'lapor_precont_80') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['80'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_100', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 100:");
}

// STEP Precont 100
elseif ($user && $user['step'] == 'lapor_precont_100') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['100'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_120', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 120:");
}

// STEP Precont 120
elseif ($user && $user['step'] == 'lapor_precont_120') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['120'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_135', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 135:");
}

// STEP Precont 135
elseif ($user && $user['step'] == 'lapor_precont_135') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['135'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_150', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 150:");
}

// STEP Precont 150
elseif ($user && $user['step'] == 'lapor_precont_150') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['150'] = $text;
    $conn->query("UPDATE users SET step='lapor_precont_180', temp_data='" . json_encode($data) . "' WHERE telegram_id='$user_id'");
    sendMessage($chat_id, "Masukkan jumlah Precont 180:");
}

// STEP Precont 180 (Terakhir)
elseif ($user && $user['step'] == 'lapor_precont_180') {
    $data = json_decode($user['temp_data'], true);
    $data['precont']['180'] = $text;

    // simpan ke DB
    $precont_json = $conn->real_escape_string(json_encode($data['precont']));
    $sql = "INSERT INTO material_used 
            (teknisi_id, wo, dc, dc_foto, s_calm, clam_hook, otp, prekso, soc_option, soc_value, tiang, tanggal, precont_json) 
            VALUES (
                '{$data['teknisi_id']}',
                '{$data['wo']}',
                '{$data['dc']}',
                '{$data['dc_foto']}',
                '{$data['s_calm']}',
                '{$data['clam_hook']}',
                '{$data['otp']}',
                '{$data['prekso']}',
                '{$data['soc_option']}',
                '{$data['soc_value']}',
                '{$data['tiang']}',
                '{$data['tanggal']}',
                '$precont_json'
            )";

    if ($conn->query($sql)) {
        $conn->query("UPDATE users SET step=NULL, temp_data=NULL WHERE telegram_id='$user_id'");
        $keyboard = [
            "keyboard" => [
                [["text" => "📋 Lapor"], ["text" => "🚪 Logout"]]
            ],
            "resize_keyboard" => true
        ];
        sendMessage($chat_id, "✅ Data berhasil disimpan ke database.", json_encode($keyboard));
    } else {
        sendMessage($chat_id, "❌ Gagal menyimpan data: " . $conn->error);
    }
}


// ==================== Logout tombol ====================
elseif ($text == "🚪 Logout") {
    $conn->query("UPDATE users SET step=NULL, temp_data=NULL, status='inactive' WHERE telegram_id='$user_id'");
    
    $keyboard = [
        "keyboard" => [
            [["text" => "🔑 Login"], ["text" => "📝 Register"]]
        ],
        "resize_keyboard" => true
    ];
    
    sendMessage($chat_id, "✅ Anda berhasil logout.", json_encode($keyboard));
}


?>