<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

// Statistik
$total_penjualan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(grand_total) as total FROM pesanan WHERE status != 'dibatalkan'"))['total'];
$total_pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM pesanan WHERE status != 'dibatalkan'"))['count'];
$rata_pesanan = $total_pesanan > 0 ? $total_penjualan / $total_pesanan : 0;

// Per toko
$penjualan_toko = mysqli_query($koneksi, "
    SELECT t.nama_toko, u.nama as penjual, COUNT(p.id) as jumlah_pesanan, SUM(p.grand_total) as total
    FROM pesanan p
    JOIN toko t ON p.toko_id = t.id
    JOIN users u ON t.user_id = u.id
    WHERE p.status != 'dibatalkan'
    GROUP BY t.id
    ORDER BY total DESC
");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Admin</title>
    <link rel="stylesheet" href="../admin/Style.css">
</head>
<body>
<div class="wrapper">
    <div class="sidebar">
        <div style="padding: 20px; border-bottom: 1px solid #444;">
            <h3 style="color: #16a34a; font-size: 18px;">Admin Panel</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-title">Menu Utama</li>
            <li><a href="index.php"><span class="material-symbols-outlined icon">dashboard</span> Dashboard</a></li>
            <li><a href="users.php"><span class="material-symbols-outlined icon">people</span> Kelola User</a></li>
            <li><a href="kelola_toko.php"><span class="material-symbols-outlined icon">store</span> Kelola Toko</a></li>
            <li><a href="kelola_pesanan.php"><span class="material-symbols-outlined icon">inventory_2</span> Kelola Pesanan</a></li>
            <li><a href="laporan_penjualan.php" class="active"><span class="material-symbols-outlined icon">bar_chart</span> Laporan Penjualan</a></li>
            <li><a href="kelola_kategori.php"><span class="material-symbols-outlined icon">label</span> Kategori & Jenis Produk</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>
    
    <div class="main-content">
        <div class="navbar">
            <div class="navbar-brand">Laporan Penjualan</div>
            <div class="navbar-right">
                <div class="navbar-user">
                    <div class="avatar">
                        <?php if (!empty($_SESSION['avatar']) && file_exists(__DIR__ . '/../uploads/' . $_SESSION['avatar'])): ?>
                            <img src="../uploads/<?php echo htmlspecialchars($_SESSION['avatar']); ?>" alt="avatar">
                        <?php else: ?>
                            <span class="avatar-initials"><?php echo strtoupper(substr(trim($_SESSION['nama'] ?? 'A'),0,1)); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="user-info">
                        <div class="user-name"><?php echo htmlspecialchars($_SESSION['nama'] ?? 'Admin'); ?></div>
                        <div class="user-role"><span class="badge <?php echo 'badge-' . (strtolower($_SESSION['role'] ?? 'admin')); ?>"><?php echo htmlspecialchars(ucfirst($_SESSION['role'] ?? 'Admin')); ?></span></div>
                    </div>
                    <div class="navbar-links">
                        <a href="index.php" class="btn btn-sm">Dashboard</a>
                        <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
                    </div>
                </div>
            </div>
        </div>
        
       
        
        <!-- STATISTIK -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 20px; text-align: center;">
                <div style="font-size: 28px; font-weight: bold;"><?php echo format_rupiah($total_penjualan); ?></div>
                <small>Total Penjualan</small>
            </div>
            
            <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; text-align: center;">
                <div style="font-size: 28px; font-weight: bold;"><?php echo $total_pesanan; ?></div>
                <small>Total Pesanan</small>
            </div>
            
            <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; text-align: center;">
                <div style="font-size: 20px; font-weight: bold;"><?php echo format_rupiah($rata_pesanan); ?></div>
                <small>Rata-rata Pesanan</small>
            </div>
        </div>
        
        <div class="card" style="margin-bottom:20px; padding:16px;">
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; justify-content:space-between;">
                <div><strong>Unduh Laporan</strong><br><small style="color:#666;">Ekspor data penjualan, toko, produk, dan pengguna dalam format yang diinginkan.</small></div>
                <div style="display:flex; gap:10px; flex-wrap:wrap;">
                    <a href="export.php?module=penjualan&format=pdf" class="btn btn-secondary btn-sm">PDF</a>
                    <a href="export.php?module=penjualan&format=docx" class="btn btn-secondary btn-sm">Word</a>
                    <a href="export.php?module=penjualan&format=xlsx" class="btn btn-secondary btn-sm">Excel</a>
                </div>
            </div>
        </div>

        <!-- PENJUALAN PER TOKO -->
        <h2 style="margin: 30px 0 20px 0; color: #333;">Penjualan Per Toko</h2>
        <div class="card">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Toko</th>
                            <th>Penjual</th>
                            <th>Jumlah Pesanan</th>
                            <th>Total Penjualan</th>
                            <th>Rata-rata</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($penjualan_toko)) { ?>
                        <tr>
                            <td><strong><?php echo $row['nama_toko']; ?></strong></td>
                            <td><?php echo $row['penjual']; ?></td>
                            <td><?php echo $row['jumlah_pesanan']; ?></td>
                            <td><?php echo format_rupiah($row['total']); ?></td>
                            <td><?php echo format_rupiah($row['total'] / $row['jumlah_pesanan']); ?></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
