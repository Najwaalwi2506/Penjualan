<?php
include '../../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

$pesanan_id = sanitize($koneksi, $_POST['pesanan_id']);

// Update status pesanan
$query = "UPDATE pesanan SET status = 'dibatalkan' WHERE id = $pesanan_id AND toko_id = $toko_id";
if (mysqli_query($koneksi, $query)) {
    // Kembalikan stok produk
    $detail = mysqli_query($koneksi, "SELECT * FROM detail_pesanan WHERE pesanan_id = $pesanan_id");
    while ($row = mysqli_fetch_assoc($detail)) {
        $produk_id = $row['produk_id'];
        $jumlah = $row['jumlah'];
        mysqli_query($koneksi, "UPDATE produk SET jumlah_stok = jumlah_stok + $jumlah WHERE id = $produk_id");
    }
    
    header('Location: ../pesanan.php?success=Pesanan berhasil ditolak');
} else {
    die('Error: ' . mysqli_error($koneksi));
}
?>
