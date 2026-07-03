<?php 
session_start();
$host = "localhost";
$user = "root";
$password = "";
$database = "pupuk_pts_jatim";

$koneksi = mysqli_connect($host, $user, $password, $database);

// Set charset to utf8mb4
mysqli_set_charset($koneksi, "utf8mb4");

// Cek koneksi
if (!$koneksi){
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Helper function untuk sanitasi input
function sanitize($koneksi, $input) {
    return mysqli_real_escape_string($koneksi, trim($input));
}

// Helper function untuk format rupiah
function format_rupiah($nominal) {
    return "Rp " . number_format($nominal, 0, ',', '.');
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
