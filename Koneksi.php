<?php 
session_start();
$host = "localhost";
$user = "root";
$password = "";
$database = "pupuk_pts_jatim(1)";

$koneksi = mysqli_connect($host, $user, $password, $database);

// Set charset to utf8mb4
mysqli_set_charset($koneksi, "utf8mb4");

// Cek koneksi
if (!$koneksi){
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Jika tabel setting belum ada, buat otomatis
mysqli_query($koneksi, "CREATE TABLE IF NOT EXISTS app_settings (
    setting_key VARCHAR(100) PRIMARY KEY,
    setting_value TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// Helper function untuk sanitasi input
function sanitize($koneksi, $input) {
    return mysqli_real_escape_string($koneksi, trim($input));
}

// Helper function untuk format rupiah
function format_rupiah($nominal) {
    return "Rp " . number_format($nominal, 0, ',', '.');
}

function format_number_input($value) {
    if ($value === null || $value === '') {
        return '';
    }
    $value = (string)$value;
    if (strpos($value, '.') !== false) {
        $value = rtrim($value, '0');
        $value = rtrim($value, '.');
    }
    return $value;
}

function format_stock($value) {
    return format_number_input($value);
}

function get_general_location($address) {
    if (empty($address)) {
        return '';
    }

    $clean = trim(preg_replace('/\s+/', ' ', (string)$address));
    if ($clean === '') {
        return '';
    }

    if (preg_match('/\b(?:Kabupaten|Kota)\s+[A-Za-z\s\-]+/i', $clean, $match)) {
        return ucwords(strtolower(trim($match[0])));
    }

    $parts = array_map('trim', explode(',', $clean));
    $first = $parts[0] ?? '';

    return $first;
}

function get_app_setting($koneksi, $key, $default = null) {
    $key = mysqli_real_escape_string($koneksi, $key);
    $result = mysqli_query($koneksi, "SELECT setting_value FROM app_settings WHERE setting_key = '$key' LIMIT 1");
    if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result)['setting_value'];
    }
    return $default;
}

function set_app_setting($koneksi, $key, $value) {
    $key = mysqli_real_escape_string($koneksi, $key);
    $value = mysqli_real_escape_string($koneksi, $value);
    mysqli_query($koneksi, "INSERT INTO app_settings (setting_key, setting_value) VALUES ('$key', '$value') ON DUPLICATE KEY UPDATE setting_value = '$value'");
}

function send_system_email($koneksi, $to, $subject, $message, $additional_headers = []) {
    $smtp_host = get_app_setting($koneksi, 'smtp_host', '');
    $smtp_port = get_app_setting($koneksi, 'smtp_port', '25');
    $smtp_user = get_app_setting($koneksi, 'smtp_user', '');
    $smtp_pass = get_app_setting($koneksi, 'smtp_pass', '');
    $smtp_secure = strtolower(get_app_setting($koneksi, 'smtp_secure', ''));
    $from_address = get_app_setting($koneksi, 'mail_from_address', 'noreply@example.com');
    $from_name = get_app_setting($koneksi, 'mail_from_name', 'Sistem Penjualan Pupuk');
    $reply_to = get_app_setting($koneksi, 'mail_reply_to', $from_address);

    $headers = array_merge([
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=UTF-8',
        'From: ' . $from_name . ' <' . $from_address . '>',
    ], $additional_headers);

    if ($reply_to) {
        $headers[] = 'Reply-To: ' . $reply_to;
    }

    if ($smtp_host !== '') {
        try {
            return send_email_via_smtp($to, $subject, $message, $headers, [
                'host' => $smtp_host,
                'port' => intval($smtp_port),
                'user' => $smtp_user,
                'pass' => $smtp_pass,
                'secure' => $smtp_secure,
                'from' => $from_address,
            ]);
        } catch (Exception $e) {
            return false;
        }
    }

    return mail($to, $subject, $message, implode("\r\n", $headers));
}

function send_email_via_smtp($to, $subject, $message, $headers, $transport) {
    $remote_socket = $transport['host'] . ':' . $transport['port'];
    $context_options = [];
    if ($transport['secure'] === 'ssl') {
        $remote_socket = 'ssl://' . $remote_socket;
        $context_options['ssl'] = [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true,
        ];
    }

    $context = stream_context_create($context_options);
    $fp = @stream_socket_client($remote_socket, $errno, $errstr, 15, STREAM_CLIENT_CONNECT, $context);
    if (!$fp) {
        return false;
    }

    $response = smtp_get_response($fp);
    if (strpos($response, '220') !== 0) {
        fclose($fp);
        return false;
    }

    $hostname = gethostname() ?: 'localhost';
    smtp_command($fp, "EHLO $hostname", ['250']);

    if ($transport['secure'] === 'tls') {
        smtp_command($fp, 'STARTTLS', ['220']);
        stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
        smtp_command($fp, "EHLO $hostname", ['250']);
    }

    if (!empty($transport['user'])) {
        smtp_command($fp, 'AUTH LOGIN', ['334']);
        smtp_command($fp, base64_encode($transport['user']), ['334']);
        smtp_command($fp, base64_encode($transport['pass']), ['235']);
    }

    smtp_command($fp, "MAIL FROM: <" . $transport['from'] . ">", ['250']);
    smtp_command($fp, "RCPT TO: <" . $to . ">", ['250', '251']);
    smtp_command($fp, 'DATA', ['354']);

    $headers[] = 'Subject: ' . $subject;
    $headers[] = 'To: ' . $to;
    $headers[] = 'Date: ' . date('r');
    $raw_message = implode("\r\n", $headers) . "\r\n\r\n" . $message . "\r\n.";
    fwrite($fp, $raw_message . "\r\n");

    $response = smtp_get_response($fp);
    $ok = strpos($response, '250') === 0;
    smtp_command($fp, 'QUIT', ['221']);
    fclose($fp);

    return $ok;
}

function smtp_get_response($fp) {
    $response = '';
    while (($line = fgets($fp, 515)) !== false) {
        $response .= $line;
        if (isset($line[3]) && $line[3] === ' ') {
            break;
        }
    }
    return $response;
}

function smtp_command($fp, $command, $expectedCodes = []) {
    fwrite($fp, $command . "\r\n");
    $response = smtp_get_response($fp);
    foreach ($expectedCodes as $code) {
        if (strpos($response, $code) === 0) {
            return $response;
        }
    }
    throw new Exception('SMTP command failed: ' . trim($response));
}

function send_notification_email($koneksi, $subject, $message) {
    $notify_to = get_app_setting($koneksi, 'notification_email', '');
    if (empty($notify_to)) {
        return false;
    }
    return send_system_email($koneksi, $notify_to, $subject, $message);
}

// Helper function untuk cek login
function check_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . dirname($_SERVER['PHP_SELF'], 2) . '/index.php');
        exit;
    }
}

// Helper function untuk cek role
function check_role($allowed_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        header('Location: ' . dirname($_SERVER['PHP_SELF'], 2) . '/index.php');
        exit;
    }
}

// Helper function untuk generate kode pesanan
function generate_kode_pesanan() {
    return "ORD-" . date('Ymd') . "-" . strtoupper(substr(md5(microtime()), 0, 5));
}

// Helper function untuk upload file
function upload_file($file_input, $allowed_ext = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($_FILES[$file_input]) || $_FILES[$file_input]['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    
    $file = $_FILES[$file_input];
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if (!in_array($file_ext, $allowed_ext)) {
        return false;
    }
    
    $new_filename = md5(microtime()) . '.' . $file_ext;
    $upload_dir = dirname(__FILE__) . '/uploads/';
    
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    if (move_uploaded_file($file['tmp_name'], $upload_dir . $new_filename)) {
        return $new_filename;
    }
    
    return false;
}

// Upload file dari input array, misalnya name="bukti_pembayaran_toko[3]".
function upload_file_array($file_input, $key, $allowed_ext = ['jpg', 'jpeg', 'png', 'gif']) {
    if (!isset($_FILES[$file_input]) || !isset($_FILES[$file_input]['error'][$key])) {
        return null;
    }

    if ($_FILES[$file_input]['error'][$key] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($_FILES[$file_input]['error'][$key] !== UPLOAD_ERR_OK) {
        return false;
    }

    $file_name = $_FILES[$file_input]['name'][$key];
    $tmp_name = $_FILES[$file_input]['tmp_name'][$key];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    if (!in_array($file_ext, $allowed_ext)) {
        return false;
    }

    $new_filename = md5(microtime() . $key) . '.' . $file_ext;
    $upload_dir = dirname(__FILE__) . '/uploads/';

    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if (move_uploaded_file($tmp_name, $upload_dir . $new_filename)) {
        return $new_filename;
    }

    return false;
}

// Helper untuk mengecek kolom agar kode tetap aman di database lama.
function column_exists($koneksi, $table, $column) {
    $table = mysqli_real_escape_string($koneksi, $table);
    $column = mysqli_real_escape_string($koneksi, $column);
    $result = mysqli_query($koneksi, "SHOW COLUMNS FROM `$table` LIKE '$column'");
    return $result && mysqli_num_rows($result) > 0;
}

// Tambahkan kolom rekening toko jika database lama belum memilikinya.
function ensure_toko_rekening_columns($koneksi) {
    if (!column_exists($koneksi, 'toko', 'bank_nama')) {
        mysqli_query($koneksi, "ALTER TABLE toko ADD bank_nama VARCHAR(100) NULL AFTER foto_toko");
    }
    if (!column_exists($koneksi, 'toko', 'no_rekening')) {
        mysqli_query($koneksi, "ALTER TABLE toko ADD no_rekening VARCHAR(50) NULL AFTER bank_nama");
    }
    if (!column_exists($koneksi, 'toko', 'nama_rekening')) {
        mysqli_query($koneksi, "ALTER TABLE toko ADD nama_rekening VARCHAR(150) NULL AFTER no_rekening");
    }
}
?>
