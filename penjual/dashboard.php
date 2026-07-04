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
        <div class="sidebar-head">
            <h3><span class="material-symbols-outlined icon">storefront</span> Toko Saya</h3>
            <p><?php echo htmlspecialchars($toko['nama_toko']); ?></p>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-title">Menu Utama</li>
            <li><a href="dashboard.php" class="active"><span class="material-symbols-outlined icon">dashboard</span> Dashboard</a></li>
            <li><a href="produk.php"><span class="material-symbols-outlined icon">inventory_2</span> Produk Saya</a></li>
            <li><a href="pesanan.php"><span class="material-symbols-outlined icon">receipt_long</span> Pesanan Masuk</a></li>
            <li><a href="riwayat.php"><span class="material-symbols-outlined icon">bar_chart</span> Riwayat Penjualan</a></li>
            <li class="sidebar-title">Pengaturan</li>
            <li><a href="toko_edit.php"><span class="material-symbols-outlined icon">settings</span> Atur Toko</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>
    
    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- NAVBAR -->
        <div class="navbar">
            <div class="navbar-brand"><span class="material-symbols-outlined">dashboard</span> Dashboard Penjual</div>
            <div class="navbar-right">
                <div class="navbar-user">
                    <div class="avatar">
                        <?php if (!empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../uploads/' . $_SESSION['avatar'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="avatar">
                        <?php else: ?>
                            <span class="avatar-initials"><?php echo strtoupper(substr(trim($_SESSION['nama'] ?? 'P'),0,1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Penjual'); ?></div>
                        <div class="user-role"><span class="badge badge-<?php echo strtolower($_SESSION['role'] ?? 'penjual'); ?>"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Penjual')); ?></span></div>
                    </div>
                    <div class="navbar-links">
                        <a href="produk.php" class="btn btn-primary btn-sm">+ Produk Baru</a>
                        <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
                    </div>
                </div>
            </div>
        </div>
        
        <h1 class="page-title">Dashboard Penjual</h1>
        
        <?php if ($pesanan_menunggu > 0) { ?>
        <div class="alert alert-warning">
            Ada <strong><?php echo $pesanan_menunggu; ?></strong> pesanan menunggu konfirmasi Anda! 
            <a href="pesanan.php" style="margin-left: 10px; font-weight: bold;">Lihat Sekarang <span class="material-symbols-outlined">arrow_forward</span></a>
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
                        <a href="pesanan_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary" style="padding: 10px 14px; font-size: 0.95rem;">Lihat Detail</a>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
