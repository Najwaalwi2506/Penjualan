<?php
include '../Koneksi.php';
check_login();
check_role(['pembeli']);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../pembeli/dashboard.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$produk_id = sanitize($koneksi, $_POST['produk_id']);
$jumlah = sanitize($koneksi, $_POST['jumlah']);

// Validasi jumlah
if ($jumlah < 1) {
    $jumlah = 1;
}

// Cek produk ada
$produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM produk WHERE id = $produk_id AND is_tersedia = 1"));
if (!$produk) {
    die('Produk tidak ditemukan');
}

// Cek apakah produk sudah ada di keranjang
$cek_keranjang = mysqli_query($koneksi, "SELECT * FROM keranjang WHERE user_id = $user_id AND produk_id = $produk_id");

if (mysqli_num_rows($cek_keranjang) > 0) {
    // Update jumlah
    $keranjang = mysqli_fetch_assoc($cek_keranjang);
    $jumlah_baru = $keranjang['jumlah'] + $jumlah;
    mysqli_query($koneksi, "UPDATE keranjang SET jumlah = $jumlah_baru WHERE user_id = $user_id AND produk_id = $produk_id");
} else {
    // Insert ke keranjang
    mysqli_query($koneksi, "INSERT INTO keranjang (user_id, produk_id, jumlah) VALUES ($user_id, $produk_id, $jumlah)");
}

header('Location: ../pembeli/keranjang.php?success=1');
?>
