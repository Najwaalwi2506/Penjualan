<?php
include '../Koneksi.php';
check_login();
check_role(['pembeli']);

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: ../pembeli/keranjang.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$keranjang_id = sanitize($koneksi, $_POST['keranjang_id']);
$jumlah = sanitize($koneksi, $_POST['jumlah']);

// Validasi jumlah
if ($jumlah < 1) {
    $jumlah = 1;
}

// Update jumlah keranjang
mysqli_query($koneksi, "UPDATE keranjang SET jumlah = $jumlah WHERE id = $keranjang_id AND user_id = $user_id");

header('Location: ../pembeli/keranjang.php?updated=1');
?>
