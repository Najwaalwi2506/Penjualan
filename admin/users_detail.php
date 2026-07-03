<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    header('Location: users.php');
    exit;
}

$user = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM users WHERE id = $id"));
if (!$user) {
    header('Location: users.php');
    exit;
}

$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $id"));
$produk = mysqli_query($koneksi, "SELECT p.*, j.nama_jenis, j.satuan FROM produk p JOIN jenis_produk j ON p.jenis_produk_id = j.id WHERE p.toko_id = " . ((int)($toko['id'] ?? 0)) . " ORDER BY p.created_at DESC");
$pesanan = mysqli_query($koneksi, "SELECT p.*, t.nama_toko FROM pesanan p JOIN toko t ON p.toko_id = t.id WHERE p.pembeli_id = $id OR p.toko_id = " . ((int)($toko['id'] ?? 0)) . " ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail User - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
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
            <li><a href="users.php" class="active"><span class="material-symbols-outlined icon">people</span> Kelola User</a></li>
            <li><a href="toko.php"><span class="material-symbols-outlined icon">store</span> Kelola Toko</a></li>
            <li><a href="pesanan.php"><span class="material-symbols-outlined icon">inventory_2</span> Pesanan</a></li>
            <li><a href="laporan_penjualan.php"><span class="material-symbols-outlined icon">bar_chart</span> Laporan Penjualan</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>
    <div class="main-content">
        <div class="navbar">
            <div class="navbar-brand">Detail User</div>
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
                        <a href="users.php" class="btn btn-sm">Kembali</a>
                        <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
                    </div>
                </div>
            </div>
        </div>
        <h1 class="page-title">Detail User</h1>
        <div class="card" style="margin-bottom: 20px;">
            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 14px;">
                <div><strong>Nama</strong><br><?php echo htmlspecialchars($user['nama']); ?></div>
                <div><strong>Email</strong><br><?php echo htmlspecialchars($user['email']); ?></div>
                <div><strong>No. Telepon</strong><br><?php echo htmlspecialchars($user['no_telp'] ?? '-'); ?></div>
                <div><strong>Alamat</strong><br><?php echo htmlspecialchars($user['alamat'] ?? '-'); ?></div>
                <div><strong>Role</strong><br><?php echo htmlspecialchars($user['role']); ?></div>
                <div><strong>Status</strong><br><?php echo $user['is_active'] ? 'Aktif' : 'Nonaktif'; ?></div>
                <div><strong>Angkatan</strong><br><?php echo htmlspecialchars($user['angkatan'] ?? '-'); ?></div>
                <div><strong>Jenis Keanggotaan</strong><br><?php echo htmlspecialchars($user['jenis_keanggotaan'] ?? '-'); ?></div>
            </div>
        </div>
        <?php if ($toko) { ?>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">Informasi Penjual</div>
            <div style="padding: 20px;">
                <strong><?php echo htmlspecialchars($toko['nama_toko']); ?></strong><br>
                <small>Status: <?php echo $toko['is_active'] ? 'Aktif' : 'Nonaktif'; ?></small>
            </div>
        </div>
        <?php } ?>
        <?php if ($toko && mysqli_num_rows($produk) > 0) { ?>
        <div class="card" style="margin-bottom: 20px;">
            <div class="card-header">Riwayat Produk</div>
            <div style="overflow-x:auto; padding: 20px;">
                <table class="table">
                    <thead><tr><th>Produk</th><th>Stok</th><th>Harga</th></tr></thead>
                    <tbody>
                        <?php while ($p = mysqli_fetch_assoc($produk)) { ?>
                        <tr><td><?php echo htmlspecialchars($p['nama_jenis']); ?></td><td><?php echo $p['jumlah_stok']; ?> <?php echo htmlspecialchars($p['satuan']); ?></td><td><?php echo format_rupiah($p['harga_jual']); ?></td></tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php } ?>
        <div class="card">
            <div class="card-header">Riwayat Pembelian / Penjualan</div>
            <div style="overflow-x:auto; padding: 20px;">
                <table class="table">
                    <thead><tr><th>Kode Pesanan</th><th>Penjual</th><th>Status</th><th>Total</th><th>Tanggal</th></tr></thead>
                    <tbody>
                        <?php if (mysqli_num_rows($pesanan) > 0) { while ($row = mysqli_fetch_assoc($pesanan)) { ?>
                        <tr><td><?php echo htmlspecialchars($row['kode_pesanan']); ?></td><td><?php echo htmlspecialchars($row['nama_toko']); ?></td><td><?php echo htmlspecialchars($row['status']); ?></td><td><?php echo format_rupiah($row['grand_total']); ?></td><td><?php echo date('d/m/Y H:i', strtotime($row['created_at'])); ?></td></tr>
                        <?php } } else { ?><tr><td colspan="5">Belum ada riwayat.</td></tr><?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
