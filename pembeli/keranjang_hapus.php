<?php
include '../Koneksi.php';
check_login();
check_role(['pembeli']);

$user_id = $_SESSION['user_id'];
$keranjang_id = sanitize($koneksi, $_GET['id']);

// Hapus dari keranjang
mysqli_query($koneksi, "DELETE FROM keranjang WHERE id = $keranjang_id AND user_id = $user_id");

header('Location: keranjang.php?deleted=1');
?>
