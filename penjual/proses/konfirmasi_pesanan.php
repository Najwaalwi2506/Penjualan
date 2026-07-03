<?php
include '../../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../pesanan.php');
    exit;
}

$pesanan_id = isset($_POST['pesanan_id']) ? sanitize($koneksi, $_POST['pesanan_id']) : 0;
$catatan = isset($_POST['catatan']) ? sanitize($koneksi, $_POST['catatan']) : null;

// Pastikan pesanan milik toko penjual ini
$cek = mysqli_query($koneksi, "SELECT p.* FROM pesanan p JOIN toko t ON p.toko_id = t.id WHERE p.id = $pesanan_id AND t.user_id = $user_id");
if (!$cek || mysqli_num_rows($cek) == 0) {
    header('Location: ../pesanan.php?error=Pesanan+tidak+ditemukan');
    exit;
}

$pesanan = mysqli_fetch_assoc($cek);

// Hanya izinkan transisi dari 'menunggu_konfirmasi' -> 'dikonfirmasi'
if ($pesanan['status'] !== 'menunggu_konfirmasi') {
    header('Location: ../pesanan_detail.php?id=' . $pesanan_id . '&error=Status+tidak+dapat+dikofirmasi');
    exit;
}

$update = mysqli_query($koneksi, "UPDATE pesanan SET status = 'dikonfirmasi', updated_at = NOW() WHERE id = $pesanan_id");
if (!$update) {
    header('Location: ../pesanan_detail.php?id=' . $pesanan_id . '&error=Gagal+konfirmasi');
    exit;
}

// Catat konfirmasi penjual (INSERT ON DUPLICATE KEY UPDATE)
$catatan_sql = $catatan !== null ? "'" . $catatan . "'" : "NULL";
$insert = "INSERT INTO konfirmasi_penjual (pesanan_id, penjual_id, aksi, catatan, dilakukan_pada) 
           VALUES ($pesanan_id, $user_id, 'dikonfirmasi', $catatan_sql, NOW()) 
           ON DUPLICATE KEY UPDATE aksi='dikonfirmasi', catatan=$catatan_sql, dilakukan_pada=NOW()";
mysqli_query($koneksi, $insert);

header('Location: ../pesanan_detail.php?id=' . $pesanan_id . '&success=Pesanan+dikonfirmasi');
exit;

?>
<?php
include '../../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

$pesanan_id = sanitize($koneksi, $_POST['pesanan_id']);

// Update status pesanan
$query = "UPDATE pesanan SET status = 'dikonfirmasi' WHERE id = $pesanan_id AND toko_id = $toko_id";
if (mysqli_query($koneksi, $query)) {
    // Insert ke konfirmasi_penjual
    $insert = "INSERT INTO konfirmasi_penjual (pesanan_id, toko_id, konfirmasi_date) VALUES ($pesanan_id, $toko_id, NOW())";
    mysqli_query($koneksi, $insert);
    
    header('Location: ../pesanan.php?success=Pesanan berhasil dikonfirmasi');
} else {
    die('Error: ' . mysqli_error($koneksi));
}
?>
