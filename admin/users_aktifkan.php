<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: users.php');
    exit;
}

mysqli_query($koneksi, "UPDATE users SET is_active = 1 WHERE id = $id");
header('Location: users.php');
exit;
