<?php
include '../../Koneksi.php';
check_login();
check_role(['admin']);

$pesanan_id = isset($_POST['pesanan_id']) ? (int)$_POST['pesanan_id'] : 0;

header('Location: ../kelola_pesanan_detail.php?id=' . $pesanan_id . '&success=Admin+hanya+memantau.+Penolakan+dilakukan+oleh+penjual');
exit;
?>
