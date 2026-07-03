<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$toko = mysqli_query($koneksi, "
    SELECT t.*, u.nama as penjual, u.email
    FROM toko t
    JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
");
$total = mysqli_num_rows($toko);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Toko - Admin</title>
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
            <li><a href="users.php"><span class="material-symbols-outlined icon">people</span> Kelola User</a></li>
            <li><a href="kelola_toko.php" class="active"><span class="material-symbols-outlined icon">store</span> Kelola Toko</a></li>
            <li><a href="kelola_pesanan.php"><span class="material-symbols-outlined icon">inventory_2</span> Kelola Pesanan</a></li>
            <li><a href="laporan_penjualan.php"><span class="material-symbols-outlined icon">bar_chart</span> Laporan Penjualan</a></li>
            <li><a href="kelola_kategori.php"><span class="material-symbols-outlined icon">label</span> Kategori & Jenis Produk</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="navbar">
            <div class="navbar-brand">Kelola Toko</div>
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

        
        <p class="page-subtitle">Total: <?php echo $total; ?> toko terdaftar</p>

        <div class="card">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama Toko</th>
                            <th>Penjual</th>
                            <th>Email</th>
                            <th>Alamat</th>
                            <th>No Telp</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($toko)) { ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['nama_toko']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['penjual']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['alamat'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($row['no_telp'] ?? ($row['telp'] ?? '-')); ?></td>
                            <td>
                                <div class="table-actions" style="display:flex; gap:8px;">
                                    <a class="btn btn-secondary btn-sm" href="toko_detail.php?id=<?php echo (int)$row['id']; ?>">Detail</a>
                                    <a class="btn btn-danger btn-sm" href="toko_delete.php?id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Yakin hapus toko ini?')">Hapus</a>
                                </div>
                            </td>
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

