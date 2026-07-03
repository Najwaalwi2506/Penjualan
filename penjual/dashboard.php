<?php
include '../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];

// Ambil data toko
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

// Statistik
$total_produk = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM produk WHERE toko_id = $toko_id AND is_tersedia = 1"))['total'];
$total_pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan WHERE toko_id = $toko_id"))['total'];
$total_penjualan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(total_harga) as total FROM pesanan p JOIN detail_pesanan dp ON p.id = dp.pesanan_id WHERE p.toko_id = $toko_id AND p.status = 'selesai'"))['total'] ?? 0;
$pesanan_menunggu = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as total FROM pesanan WHERE toko_id = $toko_id AND status = 'menunggu_konfirmasi'"))['total'];

// Pesanan terbaru
$pesanan_terbaru = mysqli_query($koneksi, "
    SELECT p.*, u.nama as pembeli 
    FROM pesanan p
    JOIN users u ON p.pembeli_id = u.id
    WHERE p.toko_id = $toko_id
    ORDER BY p.created_at DESC LIMIT 5
");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Penjual</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="wrapper">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div style="padding: 20px; border-bottom: 1px solid #444;">
            <h3 style="color: #667eea; font-size: 18px;">🏪 Toko Saya</h3>
            <p style="font-size: 12px; color: #aaa;"><?php echo $toko['nama_toko']; ?></p>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-title">Menu Utama</li>
            <li><a href="dashboard.php" class="active">📊 Dashboard</a></li>
            <li><a href="produk.php">📦 Produk Saya</a></li>
            <li><a href="pesanan.php">📋 Pesanan Masuk</a></li>
            <li><a href="riwayat.php">📈 Riwayat Penjualan</a></li>
            <li class="sidebar-title">Pengaturan</li>
            <li><a href="toko_edit.php">⚙️ Atur Toko</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php">🚪 Logout</a></li>
        </ul>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- NAVBAR -->
        <div class="navbar">
            <div class="navbar-brand">Dashboard Penjual</div>
            <div class="navbar-right">
                <div class="navbar-user">
                    <span><?php echo $_SESSION['nama']; ?></span>
                </div>
                <div class="navbar-links">
                    <a href="produk.php" class="btn btn-primary" style="padding: 8px 15px;">+ Produk Baru</a>
                    <a href="../auth/logout.php">Logout</a>
                </div>
            </div>
        </div>
        
        <h1 class="page-title">📊 Dashboard Penjual</h1>
        
        <?php if ($pesanan_menunggu > 0) { ?>
        <div class="alert alert-warning">
            ⚠️ Ada <strong><?php echo $pesanan_menunggu; ?></strong> pesanan menunggu konfirmasi Anda! 
            <a href="pesanan.php" style="margin-left: 10px; font-weight: bold;">Lihat Sekarang →</a>
        </div>
        <?php } ?>
        
        <!-- STATS ROW -->
        <div class="grid grid-4">
            <div class="stat-box green">
                <div class="stat-label">Produk Aktif</div>
                <div class="stat-number"><?php echo $total_produk; ?></div>
            </div>
            <div class="stat-box blue">
                <div class="stat-label">Total Pesanan</div>
                <div class="stat-number"><?php echo $total_pesanan; ?></div>
            </div>
            <div class="stat-box orange">
                <div class="stat-label">Menunggu Konfirmasi</div>
                <div class="stat-number"><?php echo $pesanan_menunggu; ?></div>
            </div>
            <div class="stat-box red">
                <div class="stat-label">Total Penjualan</div>
                <div class="stat-number"><?php echo format_rupiah($total_penjualan); ?></div>
            </div>
        </div>
        
        <!-- PESANAN TERBARU -->
        <div class="card" style="margin-top: 30px;">
            <div class="card-header">Pesanan Terbaru</div>
            <div class="data-card-list">
                <?php while ($row = mysqli_fetch_assoc($pesanan_terbaru)) { ?>
                <div class="data-card">
                    <div class="data-card-header">
                        <div>
                            <div class="data-card-title"><?php echo htmlspecialchars($row['kode_pesanan']); ?></div>
                            <div class="data-card-meta"><?php echo htmlspecialchars($row['pembeli']); ?></div>
                        </div>
                        <span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?></span>
                    </div>
                    <div class="data-card-body">
                        <div class="data-card-row">
                            <span class="data-card-label">Total</span>
                            <span class="data-card-value"><?php echo format_rupiah($row['grand_total']); ?></span>
                        </div>
                        <div class="data-card-row">
                            <span class="data-card-label">Tanggal</span>
                            <span class="data-card-value"><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></span>
                        </div>
                    </div>
                    <div class="data-card-action">
                        <a href="pesanan_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary" style="padding: 8px 12px; font-size: 13px;">Lihat Detail</a>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
</body>
</html>
