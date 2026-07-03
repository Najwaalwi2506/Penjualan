<?php
include '../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

$produk_id = sanitize($koneksi, $_GET['id']);

// Cek produk milik penjual
$produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM produk WHERE id = $produk_id AND toko_id = $toko_id"));

if (!$produk) {
    die('Produk tidak ditemukan');
}

// Hapus produk
mysqli_query($koneksi, "DELETE FROM produk WHERE id = $produk_id");

header('Location: produk.php?deleted=1');
?>
