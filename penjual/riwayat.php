<?php
include '../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

// Ambil riwayat penjualan (Pesanan selesai)
$riwayat = mysqli_query($koneksi, "
    SELECT p.*, u.nama as pembeli, 
    COUNT(dp.id) as jumlah_item,
    SUM(dp.subtotal) as total_item
    FROM pesanan p
    JOIN users u ON p.pembeli_id = u.id
    LEFT JOIN detail_pesanan dp ON p.id = dp.pesanan_id
    WHERE p.toko_id = $toko_id AND p.status IN ('dikirim', 'selesai')
    GROUP BY p.id
    ORDER BY p.created_at DESC
");

$total_pesanan = mysqli_num_rows($riwayat);

// Hitung total penjualan
$total_penjualan = mysqli_fetch_assoc(mysqli_query($koneksi, 
    "SELECT SUM(grand_total) as total FROM pesanan WHERE toko_id = $toko_id AND status IN ('dikirim', 'selesai')"));
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Penjualan</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="wrapper">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-head">
            <h3><span class="material-symbols-outlined icon">storefront</span> Halo</h3>
            <p><?php echo htmlspecialchars($toko['nama_toko']); ?></p>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-title">Menu Utama</li>
            <li><a href="dashboard.php"><span class="material-symbols-outlined icon">dashboard</span> Dashboard</a></li>
            <li><a href="produk.php"><span class="material-symbols-outlined icon">inventory_2</span> Produk Saya</a></li>
            <li><a href="pesanan.php"><span class="material-symbols-outlined icon">receipt_long</span> Pesanan Masuk</a></li>
            <li><a href="riwayat.php" class="active"><span class="material-symbols-outlined icon">bar_chart</span> Riwayat Penjualan</a></li>
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
                        <a href="dashboard.php" class="btn btn-secondary btn-sm">Dashboard</a>
                    </div>
                </div>
            </div>
        </div>
        
        <h1 class="page-title">Riwayat Penjualan</h1>
        <p class="page-subtitle">Pesanan yang sudah selesai</p>
        
        <!-- STATISTIK -->
        <div style="display: flex; justify-content: center; margin-bottom: 2rem;">
            <div class="grid grid-2" style="max-width: 700px; width: 100%;">
                <div class="stat-box" style="border-left: 4px solid #27ae60;">
                    <div class="stat-number"><?php echo $total_pesanan; ?></div>
                    <div class="stat-label">Total Pesanan Selesai</div>
                </div>
                <div class="stat-box" style="border-left: 4px solid #f39c12;">
                    <div class="stat-number"><?php echo format_rupiah($total_penjualan['total'] ?? 0); ?></div>
                    <div class="stat-label">Total Penjualan</div>
                </div>
            </div>
        </div>
        
        <?php if ($total_pesanan > 0) { ?>
        
        <div class="card" style="margin-top: 30px;">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No Pesanan</th>
                            <th>Pembeli</th>
                            <th>Items</th>
                            <th>Total Item</th>
                            <th>Total Pesanan</th>
                            <th>Tanggal Selesai</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($riwayat)) { ?>
                        <tr>
                            <td><strong><?php echo $row['kode_pesanan']; ?></strong></td>
                            <td><?php echo $row['pembeli']; ?></td>
                            <td><?php echo $row['jumlah_item']; ?> produk</td>
                            <td><?php echo $row['jumlah_item']; ?></td>
                            <td><?php echo format_rupiah($row['grand_total']); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td><a href="pesanan_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Lihat</a></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php } else { ?>
        <div class="card text-center">
            <p style="color: #999; padding: 40px;">Belum ada pesanan yang selesai</p>
        </div>
        <?php } ?>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
