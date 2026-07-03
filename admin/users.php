<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

// Ambil data admin yang login
$admin_id = $_SESSION['user_id'];
$admin = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id, nama, email, role FROM users WHERE id='" . (int)$admin_id . "'"));

// Ambil statistik user (untuk halaman ini)
$users = mysqli_query($koneksi, "SELECT * FROM users ORDER BY created_at DESC");
$total = mysqli_num_rows($users);

// Ambil produk yang "dijual" oleh user yang bertipe penjual.
// Karena admin biasanya bukan penjual, di sini kita tampilkan ringkasan: semua produk milik penjual yang aktif.
// Kalau suatu saat admin juga role=penjual dan punya toko, query bisa diganti sesuai kebutuhan.
$produk_nav = mysqli_query($koneksi, "
    SELECT p.id, j.nama_jenis, p.harga_jual, p.jumlah_stok, p.is_tersedia,
           t.nama_toko
    FROM produk p
    JOIN toko t ON p.toko_id = t.id
    JOIN jenis_produk j ON p.jenis_produk_id = j.id
    WHERE t.is_active=1 AND p.is_tersedia=1
    ORDER BY p.created_at DESC
    LIMIT 8
");


?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="wrapper">
    <!-- SIDEBAR -->
    <div class="sidebar">
        <div class="sidebar-head">
            <h3>Admin Panel</h3>
        </div>
       <ul class="sidebar-menu">
            <li class="sidebar-title">Menu Utama</li>
            <li><a href="index.php"><span class="material-symbols-outlined icon">dashboard</span> Dashboard</a></li>
            <li><a href="users.php" class="active"><span class="material-symbols-outlined icon">people</span> Kelola User</a></li>
            <li><a href="toko.php" ><span class="material-symbols-outlined icon">store</span> Kelola Toko</a></li>
            <li><a href="pesanan.php"><span class="material-symbols-outlined icon">inventory_2</span> Kelola Pesanan</a></li>
            <li><a href="laporan_penjualan.php"><span class="material-symbols-outlined icon">bar_chart</span> Laporan Penjualan</a></li>
            <li><a href="kelola_kategori.php"><span class="material-symbols-outlined icon">label</span> Kategori & Jenis Produk</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- NAVBAR (USER NAVBAR untuk admin) -->
        <div class="navbar">
            <div class="navbar-brand"> Kelola User</div>

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
                        <div class="user-name"><?php echo htmlspecialchars($admin['nama'] ?? ($_SESSION['nama'] ?? 'Admin')); ?></div>
                        <div class="user-role"><span class="badge <?php echo 'badge-' . (strtolower($admin['role'] ?? ($_SESSION['role'] ?? 'admin'))); ?>"><?php echo htmlspecialchars(ucfirst($admin['role'] ?? ($_SESSION['role'] ?? 'admin'))); ?></span></div>
                    </div>
                    <div class="navbar-links">
                        <a href="index.php" class="btn btn-sm">Dashboard</a>
                        <a href="../auth/logout.php" class="btn btn-danger btn-sm">Logout</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tambahan: panel ringkasan produk di navbar (admin bisa lihat konteks penjual/produk) -->
        
     
<p class="page-subtitle">Total: <?php echo $total; ?> user terdaftar</p>

<?php
        if (isset($_GET['status'])) {

        if ($_GET['status'] == 'hapus') {
                echo "<div style='padding:10px; margin-bottom:15px; background:#d4edda; color:#155724; border-radius:5px;'>
                                User berhasil dihapus.
                            </div>";
        }

        if ($_GET['status'] == 'nonaktif') {
                echo "<div style='padding:10px; margin-bottom:15px; background:#fff3cd; color:#856404; border-radius:5px;'>
                                User memiliki riwayat transaksi sehingga akun dinonaktifkan.
                            </div>";
        }

        if ($_GET['status'] == 'self') {
                echo "<div style='padding:10px; margin-bottom:15px; background:#f8d7da; color:#721c24; border-radius:5px;'>
                                Anda tidak dapat menghapus akun sendiri.
                            </div>";
        }
}
?>

<div style="margin: 0 0 16px 0; display:flex; justify-content:flex-end;">
    <a class="btn btn-primary" href="users_tambah.php">Tambah User</a>
</div>
      


        <div class="card">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Tanggal Daftar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Rewind produk_nav tidak perlu; kita sudah habis loop
                        while ($row = mysqli_fetch_assoc($users)) { ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($row['nama']); ?></strong></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <?php
                                if ($row['role'] === 'admin') {
                                    echo '<span style="background: #16a34a; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Admin</span>';
                                } elseif ($row['role'] === 'penjual') {
                                    echo '<span style="background: #f5576c; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Penjual</span>';
                                } else {
                                    echo '<span style="background: #4facfe; color: white; padding: 3px 8px; border-radius: 3px; font-size: 12px;">Pembeli</span>';
                                }
                                ?>
                            </td>
                            <td><?php echo $row['is_active'] ? '✓ Aktif' : '✗ Nonaktif'; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['created_at'])); ?></td>
                            <td>
    <div class="table-actions" style="display:flex; gap:8px;">

        <a class="btn btn-info btn-sm"
           href="users_detail.php?id=<?php echo (int)$row['id']; ?>">
           Detail
        </a>

        <?php if($row['is_active']) { ?>

            <a class="btn btn-danger btn-sm"
               href="users_delete.php?id=<?php echo (int)$row['id']; ?>"
               onclick="return confirm('Yakin ingin menonaktifkan/menghapus user?')">
               Hapus
            </a>

        <?php } else { ?>

            <a class="btn btn-success btn-sm"
               href="users_aktifkan.php?id=<?php echo (int)$row['id']; ?>"
               onclick="return confirm('Aktifkan kembali user ini?')">
               Aktifkan
            </a>

        <?php } ?>

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

