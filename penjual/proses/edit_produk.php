<?php
include '../../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

// Validasi input
$produk_id = isset($_POST['produk_id']) ? (int)$_POST['produk_id'] : 0;
$jenis_produk_id = sanitize($koneksi, $_POST['jenis_produk_id']);
$harga_jual = (int) sanitize($koneksi, $_POST['harga_jual']);
$jumlah_stok = (float) sanitize($koneksi, $_POST['jumlah_stok']);
$deskripsi = sanitize($koneksi, $_POST['deskripsi']);
$is_tersedia = isset($_POST['is_tersedia']) ? 1 : 0;

if (!$produk_id || !$jenis_produk_id || $harga_jual < 0 || $jumlah_stok < 0) {
    die('Data tidak valid');
}

// Verifikasi produk milik penjual
$check = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id FROM produk WHERE id = $produk_id AND toko_id = $toko_id"));
if (!$check) {
    die('Produk tidak ditemukan atau Anda tidak memiliki akses');
}

// Update produk
$query = "UPDATE produk SET jenis_produk_id = $jenis_produk_id, harga_jual = $harga_jual, 
          jumlah_stok = $jumlah_stok, deskripsi = '$deskripsi', is_tersedia = $is_tersedia 
          WHERE id = $produk_id AND toko_id = $toko_id";

if (mysqli_query($koneksi, $query)) {
    // Handle file upload
    if (!empty($_FILES['foto_produk']['name'])) {
        $file = $_FILES['foto_produk'];
        $allowed_ext = array('jpg', 'jpeg', 'png', 'gif');
        
        $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (in_array($file_ext, $allowed_ext) && $file['size'] <= 2000000) {
            // Hapus foto lama jika ada
            $old_foto = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT foto_produk FROM produk WHERE id = $produk_id"));
            if (!empty($old_foto['foto_produk']) && file_exists('../../uploads/' . $old_foto['foto_produk'])) {
                unlink('../../uploads/' . $old_foto['foto_produk']);
            }
            
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
