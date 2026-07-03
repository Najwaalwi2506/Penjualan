<?php
include '../../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

// Validasi input
$jenis_produk_id = sanitize($koneksi, $_POST['jenis_produk_id']);
$harga_jual = (int) sanitize($koneksi, $_POST['harga_jual']);
$jumlah_stok = (float) sanitize($koneksi, $_POST['jumlah_stok']);
$deskripsi = sanitize($koneksi, $_POST['deskripsi']);
$is_tersedia = isset($_POST['is_tersedia']) ? 1 : 0;

if (!$jenis_produk_id || $harga_jual < 0 || $jumlah_stok < 0) {
    die('Data tidak valid');
}

// Insert produk
$query = "INSERT INTO produk (toko_id, jenis_produk_id, harga_jual, jumlah_stok, deskripsi, is_tersedia, created_at) 
          VALUES ($toko_id, $jenis_produk_id, $harga_jual, $jumlah_stok, '$deskripsi', $is_tersedia, NOW())";

if (mysqli_query($koneksi, $query)) {
    $produk_id = mysqli_insert_id($koneksi);
    
    // Handle file upload
    if (!empty($_FILES['foto_produk']['name'])) {
        $file = $_FILES['foto_produk'];
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($file_ext, $allowed_ext) && $file['size'] <= 2000000) {
            $new_filename = 'produk_' . $produk_id . '_' . time() . '.' . $file_ext;
            $upload_path = '../../uploads/' . $new_filename;
            
            if (!is_dir('../../uploads')) {
                mkdir('../../uploads', 0777, true);
            }
            
            if (move_uploaded_file($file['tmp_name'], $upload_path)) {
                mysqli_query($koneksi, "UPDATE produk SET foto_produk = '$new_filename' WHERE id = $produk_id");
            }
        }
    }
    
    header('Location: ../produk.php?success=1');
} else {
    die('Error: ' . mysqli_error($koneksi));
}
?>
