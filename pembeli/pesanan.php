<?php
include '../Koneksi.php';
check_login();
check_role(['pembeli']);

$user_id = $_SESSION['user_id'];

// Ambil pesanan pembeli (ONE-TO-MANY: 1 Pembeli punya MANY Pesanan)
$pesanan = mysqli_query($koneksi, "
    SELECT p.*, t.nama_toko, u.nama as penjual 
    FROM pesanan p
    JOIN toko t ON p.toko_id = t.id
    JOIN users u ON t.user_id = u.id
    WHERE p.pembeli_id = $user_id
    ORDER BY p.created_at DESC
");

$total_pesanan = mysqli_num_rows($pesanan);
$cart_count = !empty($_SESSION['cart']) && is_array($_SESSION['cart']) ? count($_SESSION['cart']) : 0;
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan</title>
    <link rel="stylesheet" href="../pembeli/Style.css">
</head>
<body>
<!-- NAVBAR -->
<div class="navbar">
    <div class="navbar-brand">
        <span class="brand-icon material-symbols-outlined">shopping_bag</span>
        Toko Pupuk Online
    </div>
    <div class="navbar-actions">
        <div class="navbar-user">
            <span>Halo, <?php echo htmlspecialchars($_SESSION['nama']); ?></span>
        </div>
        <a href="dashboard.php"><span class="material-symbols-outlined">home</span> Beranda</a>
        <a href="keranjang.php" class="cart-badge"><span class="material-symbols-outlined">shopping_cart</span> Keranjang<span class="cart-counter"><?php echo $cart_count; ?></span></a>
        <a href="pesanan.php"><span class="material-symbols-outlined">receipt_long</span> Pesanan Saya</a>
        <a href="../auth/logout.php"><span class="material-symbols-outlined">logout</span> Logout</a>
    </div>
    <details class="navbar-mobile">
        <summary><span>Menu</span><span class="material-symbols-outlined">menu</span></summary>
        <div class="mobile-actions">
            <a href="dashboard.php"><span class="material-symbols-outlined">home</span> Beranda</a>
            <a href="keranjang.php"><span class="material-symbols-outlined">shopping_cart</span> Keranjang</a>
            <a href="pesanan.php"><span class="material-symbols-outlined">receipt_long</span> Pesanan Saya</a>
            <a href="../auth/logout.php"><span class="material-symbols-outlined">logout</span> Logout</a>
        </div>
    </details>
</div>

<div class="main-content page-shell" style="max-width: 1000px; margin: 0 auto;">
    <h1 class="page-title">📋 Riwayat Pesanan Anda</h1>
    <p class="page-subtitle">Lihat status pesanan Anda dengan tampilan yang sederhana dan mudah dipahami.</p>
    
    <?php if ($total_pesanan > 0) { ?>
    
    <div class="history-list">
        <?php while ($row = mysqli_fetch_assoc($pesanan)) { ?>
        <div class="history-card">
            <div class="history-card-top">
                <div>
                    <div class="history-code"><strong><?php echo htmlspecialchars($row['kode_pesanan']); ?></strong></div>
                    <div class="history-store"><?php echo htmlspecialchars($row['nama_toko']); ?></div>
                </div>
                <span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?></span>
            </div>
            <div class="history-meta">
                <span>🗓 <?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></span>
                <span>💰 <?php echo format_rupiah($row['grand_total']); ?></span>
            </div>
            <div class="history-actions">
                <a href="pesanan_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Lihat Detail</a>
            </div>
        </div>
        <?php } ?>
    </div>
    
    <?php } else { ?>
    <div class="card text-center">
        <p style="color: #999; padding: 40px;">Anda belum melakukan pembelian</p>
        <a href="dashboard.php" class="btn btn-primary">Mulai Belanja</a>
    </div>
    <?php } ?>
</div>
</body>
</html>
