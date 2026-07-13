<?php
include '../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];

$toko_result = mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id");
if (!$toko_result) {
    die('Query toko gagal: ' . mysqli_error($koneksi));
}

$toko = mysqli_fetch_assoc($toko_result);
if (!$toko) {
    die('Toko tidak ditemukan. Pastikan akun penjual sudah terhubung dengan toko.');
}

$toko_id = $toko['id'];

// Ambil pesanan masuk. Pesanan langsung masuk ke penjual tanpa persetujuan admin.
$pesanan = mysqli_query($koneksi, "
    SELECT p.*, u.nama as pembeli, u.alamat, u.no_telp
    FROM pesanan p
    JOIN users u ON p.pembeli_id = u.id
    WHERE p.toko_id = $toko_id
    ORDER BY p.created_at DESC
");

if (!$pesanan) {
    die('Query pesanan gagal: ' . mysqli_error($koneksi));
}

$total_pesanan = mysqli_num_rows($pesanan);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Masuk</title>
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
            <li><a href="pesanan.php" class="active"><span class="material-symbols-outlined icon">receipt_long</span> Pesanan Masuk</a></li>
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
                        <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
                    </div>
                </div>
            </div>
        </div>
        
        <h1 class="page-title">Pesanan Masuk Anda</h1>
        <p class="page-subtitle">Pesanan baru langsung menunggu konfirmasi penjual/toko.</p>
        
        <?php if ($total_pesanan > 0) { ?>
        
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No Pesanan</th>
                            <th>Pembeli</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Tanggal</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($pesanan)) { ?>
                        <tr>
                            <td><strong><?php echo $row['kode_pesanan']; ?></strong></td>
                            <td><?php echo $row['pembeli']; ?></td>
                            <td><?php echo format_rupiah($row['grand_total']); ?></td>
                            <td><span class="badge badge-<?php echo $row['status']; ?>"><?php echo ucfirst(str_replace('_', ' ', $row['status'])); ?></span></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td>
                            <td><a href="pesanan_detail.php?id=<?php echo $row['id']; ?>" class="btn btn-primary btn-sm">Lihat</a></td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php } else { ?>
        <div class="card text-center">
            <p style="color: #999; padding: 40px;">Belum ada pesanan masuk</p>
        </div>
        <?php } ?>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
