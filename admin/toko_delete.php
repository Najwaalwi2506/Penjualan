<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$toko_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($toko_id <= 0) {
    header('Location: kelola_toko.php');
    exit;
}

// Hapus toko
mysqli_query($koneksi, "DELETE FROM toko WHERE id={$toko_id}");

header('Location: kelola_toko.php?deleted=1');
exit;

