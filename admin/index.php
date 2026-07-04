<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$total_users = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM users"))['count'];
$total_toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM toko"))['count'];
$total_pesanan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM pesanan"))['count'];
$total_penjualan = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT SUM(grand_total) as total FROM pesanan WHERE status != 'dibatalkan'"))['total'];
$pesanan_menunggu_penjual = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT COUNT(*) as count FROM pesanan WHERE status = 'menunggu_konfirmasi'"))['count'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../admin/Style.css">
</head>
<body>
<div class="wrapper">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div style="padding: 20px; border-bottom: 1px solid #444;">
            <h3 style="color: #16a34a; font-size: 18px;">Admin Panel</h3>
        </div>
        <ul class="sidebar-menu">
            <li class="sidebar-title">Menu Utama</li>
            <li><a href="index.php" class="active"><span class="material-symbols-outlined icon">dashboard</span> Dashboard</a></li>
            <li><a href="users.php"><span class="material-symbols-outlined icon">people</span> Kelola User</a></li>
            <li><a href="kelola_toko.php"><span class="material-symbols-outlined icon">store</span> Kelola Toko</a></li>
            <li><a href="pesanan.php"><span class="material-symbols-outlined icon">inventory_2</span> Data Pesanan <?php echo $pesanan_menunggu_penjual > 0 ? '<span class="notif-badge">' . $pesanan_menunggu_penjual . '</span>' : ''; ?></a></li>
            <li><a href="laporan_penjualan.php"><span class="material-symbols-outlined icon">bar_chart</span> Laporan Penjualan</a></li>
            <li><a href="settings.php"><span class="material-symbols-outlined icon">email</span> Notifikasi Email</a></li>
            <li><a href="kelola_kategori.php"><span class="material-symbols-outlined icon">label</span> Kategori & Jenis Produk</a></li>
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
        
        
        <p class="page-subtitle">Selamat datang di panel administrasi sistem penjualan pupuk</p>
        
        <!-- STATISTIK CARDS -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 30px;">
            <!-- Total Users -->
            <div class="card" style="background: linear-gradient(135deg, #16a34a 0%, #059669 100%); color: white; padding: 20px; text-align: center;">
                    <div style="font-size: 32px; margin-bottom: 10px;"><span class="material-symbols-outlined" style="font-size:32px;">people</span></div>
                <div style="font-size: 28px; font-weight: bold;"><?php echo $total_users; ?></div>
                <div style="font-size: 12px; margin-top: 5px;">Total User</div>
            </div>
            
            <!-- Total Toko -->
            <div class="card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; padding: 20px; text-align: center;">
                <div style="font-size: 32px; margin-bottom: 10px;"><span class="material-symbols-outlined" style="font-size:32px;">store</span></div>
                <div style="font-size: 28px; font-weight: bold;"><?php echo $total_toko; ?></div>
                <div style="font-size: 12px; margin-top: 5px;">Total Toko</div>
            </div>
            
            <!-- Total Pesanan -->
            <div class="card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; padding: 20px; text-align: center;">
                <div style="font-size: 32px; margin-bottom: 10px;"><span class="material-symbols-outlined" style="font-size:32px;">inventory_2</span></div>
                <div style="font-size: 28px; font-weight: bold;"><?php echo $total_pesanan; ?></div>
                <div style="font-size: 12px; margin-top: 5px;">Total Pesanan</div>
            </div>
            
            <!-- Total Penjualan -->
            <div class="card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; padding: 20px; text-align: center;">
                <div style="font-size: 32px; margin-bottom: 10px;"><span class="material-symbols-outlined" style="font-size:32px;">paid</span></div>
                <div style="font-size: 20px; font-weight: bold;"><?php echo format_rupiah($total_penjualan); ?></div>
                <div style="font-size: 12px; margin-top: 5px;">Total Penjualan</div>
            </div>
        </div>
        
        <!-- PESANAN MENUNGGU PENJUAL -->
        <?php if ($pesanan_menunggu_penjual > 0) { ?>
        <div class="card" style="background: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin-bottom: 30px;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <strong style="color: #856404;">Ada <?php echo $pesanan_menunggu_penjual; ?> pesanan menunggu konfirmasi penjual</strong><br>
                    <small style="color: #856404;">Admin dapat memantau, konfirmasi tetap dilakukan oleh toko/penjual.</small>
                </div>
                <a href="pesanan.php?status=menunggu_konfirmasi" class="btn btn-primary">Lihat Pesanan</a>
            </div>
        </div>
        <?php } ?>
        
        <!-- QUICK LINKS -->
        <h2 style="margin: 30px 0 20px 0; color: #333;">Akses Cepat</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div class="card" style="padding: 20px; cursor: pointer; transition: 0.3s;" onclick="window.location='kelola_user.php'">
                <div style="font-size: 28px; margin-bottom: 10px;"><span class="material-symbols-outlined" style="font-size:28px;">people</span></div>
                <strong>Kelola User</strong><br>
                <small style="color: #999;">Tambah, edit, atau hapus user</small>
            </div>
            
            <div class="card" style="padding: 20px; cursor: pointer; transition: 0.3s;" onclick="window.location='kelola_toko.php'">
                <div style="font-size: 28px; margin-bottom: 10px;"><span class="material-symbols-outlined" style="font-size:28px;">store</span></div>
                <strong>Kelola Toko</strong><br>
                <small style="color: #999;">Kelola toko dan informasinya</small>
            </div>
            
            <div class="card" style="padding: 20px; cursor: pointer; transition: 0.3s;" onclick="window.location='kelola_pesanan.php'">
                <div style="font-size: 28px; margin-bottom: 10px;"><span class="material-symbols-outlined" style="font-size:28px;">inventory_2</span></div>
                <strong>Data Pesanan</strong><br>
                <small style="color: #999;">Pantau pembeli, toko, dan status pesanan</small>
            </div>
            
            <div class="card" style="padding: 20px; cursor: pointer; transition: 0.3s;" onclick="window.location='laporan_penjualan.php'">
                <div style="font-size: 28px; margin-bottom: 10px;"><span class="material-symbols-outlined" style="font-size:28px;">bar_chart</span></div>
                <strong>Laporan Penjualan</strong><br>
                <small style="color: #999;">Lihat statistik penjualan</small>
            </div>
            
            <div class="card" style="padding: 20px; cursor: pointer; transition: 0.3s;" onclick="window.location='kelola_kategori.php'">
                <div style="font-size: 28px; margin-bottom: 10px;"><span class="material-symbols-outlined" style="font-size:28px;">label</span></div>
                <strong>Kategori & Jenis</strong><br>
                <small style="color: #999;">Kelola kategori dan jenis produk</small>
            </div>
        </div>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
