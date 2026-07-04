<?php
include '../Koneksi.php';
check_login();
check_role(['penjual']);

$user_id = $_SESSION['user_id'];
$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
$toko_id = $toko['id'];

// Ambil produk penjual (ONE-TO-MANY: 1 Penjual memiliki MANY Produk)
$produk = mysqli_query($koneksi, "
    SELECT p.*, j.nama_jenis, j.satuan, k.nama as kategori
    FROM produk p
    JOIN jenis_produk j ON p.jenis_produk_id = j.id
    JOIN kategori_produk k ON j.kategori_id = k.id
    WHERE p.toko_id = $toko_id
    ORDER BY p.created_at DESC
");

$total_produk = mysqli_num_rows($produk);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Saya</title>
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
            <li><a href="dashboard.php"><span class="material-symbols-outlined icon">dashboard</span> Dashboard</a></li>
            <li><a href="produk.php" class="active"><span class="material-symbols-outlined icon">inventory_2</span> Produk Saya</a></li>
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
            <div class="navbar-brand"><span class="material-symbols-outlined">inventory_2</span> Produk Saya</div>
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
                        <a href="produk_tambah.php" class="btn btn-primary btn-sm">+ Tambah Produk</a>
                        <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
                    </div>
                </div>
            </div>
        </div>
        
        <h1 class="page-title">Daftar Produk</h1>
        <p class="page-subtitle">Relasi ONE-TO-MANY: 1 Penjual memiliki MANY Produk</p>
        
        <?php if ($total_produk > 0) { ?>
        
        <div class="card">
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th>Kategori</th>
                            <th>Harga/<?php echo 'Satuan'; ?></th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Tanggal Input</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($produk)) { ?>
                        <tr>
                            <td><strong><?php echo $row['nama_jenis']; ?></strong></td>
                            <td><?php echo $row['kategori']; ?></td>
                            <td><?php echo format_rupiah($row['harga_jual']); ?>/<?php echo $row['satuan']; ?></td>
                            <td><span class="stock-info"><span class="stock-value"><?php echo format_stock($row['jumlah_stok']); ?></span><span class="unit"><?php echo $row['satuan']; ?></span></span></td>
                            <td><span class="badge badge-<?php echo $row['is_tersedia'] ? 'success' : 'danger'; ?>"><?php echo $row['is_tersedia'] ? 'Aktif' : 'Nonaktif'; ?></span></td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td>
                                <div class="table-actions">
                                    <a href="produk_edit.php?id=<?php echo $row['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="produk_hapus.php?id=<?php echo $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin hapus?')">Hapus</a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <?php } else { ?>
        <div class="card text-center">
            <p style="color: #999; padding: 40px;">Anda belum memiliki produk</p>
            <a href="produk_tambah.php" class="btn btn-primary">Tambah Produk Pertama</a>
        </div>
        <?php } ?>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
