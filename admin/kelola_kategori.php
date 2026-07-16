<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$selected_month = isset($_GET['bulan']) ? trim($_GET['bulan']) : '';
$month_filter = '';
if ($selected_month !== '') {
    $month_filter = " AND DATE_FORMAT(pe.created_at, '%Y-%m') = '$selected_month'";
}

$kategori = mysqli_query($koneksi, "SELECT * FROM kategori_produk ORDER BY nama");
$jenis = mysqli_query($koneksi, "SELECT j.*, k.nama as kategori,
                                 COALESCE(COUNT(DISTINCT p.id),0) AS total_produk,
                                 COALESCE(COUNT(DISTINCT t.user_id),0) AS total_penjual,
                                 COALESCE(SUM(dp.jumlah),0) AS jumlah_barang_terjual,
                                 COALESCE(SUM(dp.subtotal),0) AS total_nilai_penjualan,
                                 COUNT(DISTINCT pe.pembeli_id) AS terjual_orang
                                 FROM jenis_produk j 
                                 JOIN kategori_produk k ON j.kategori_id = k.id 
                                 LEFT JOIN produk p ON p.jenis_produk_id = j.id
                                 LEFT JOIN toko t ON t.id = p.toko_id
                                 LEFT JOIN detail_pesanan dp ON dp.produk_id = p.id
                                 LEFT JOIN pesanan pe ON pe.id = dp.pesanan_id $month_filter
                                 GROUP BY j.id, k.nama
                                 ORDER BY k.nama, j.nama_jenis");

$available_months = [];
$months_result = mysqli_query($koneksi, "SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m') AS bulan FROM pesanan WHERE created_at IS NOT NULL ORDER BY bulan DESC");
while ($month_row = mysqli_fetch_assoc($months_result)) {
    $available_months[] = $month_row['bulan'];
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori & Jenis Produk - Admin</title>
    <link rel="stylesheet" href="../admin/Style.css">
</head>
<body>

<div class="wrapper">

    <div class="sidebar">
            <div class="sidebar-head">
                <h3>Admin Panel</h3>
            </div>

        <ul class="sidebar-menu">
            <li class="sidebar-title">Menu Utama</li>
            <li><a href="index.php"><span class="material-symbols-outlined icon">dashboard</span> Dashboard</a></li>
            <li><a href="users.php"><span class="material-symbols-outlined icon">people</span> Kelola User</a></li>
            <li><a href="kelola_toko.php"><span class="material-symbols-outlined icon">store</span> Kelola Toko</a></li>
            <li><a href="kelola_pesanan.php"><span class="material-symbols-outlined icon">inventory_2</span> Kelola Pesanan</a></li>
            <li><a href="laporan_penjualan.php"><span class="material-symbols-outlined icon">bar_chart</span> Laporan Penjualan</a></li>
            <li><a href="kelola_kategori.php" class="active"><span class="material-symbols-outlined icon">label</span> Kategori & Jenis Produk</a></li>

            <li class="sidebar-title">Akun</li>
                <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">

        <div class="navbar">
            <div class="navbar-brand">Kategori & Jenis Produk</div>
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

        

        <div class="card" style="margin: 20px 0; padding:16px;">
            <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; justify-content:space-between;">
                <div><strong>Unduh Data Produk</strong><br><small style="color:#666;">Ekspor data produk dan kategori untuk laporan administrasi.</small></div>
                <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                    <form method="GET" style="display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                        <select name="bulan">
                            <option value="">Semua Bulan</option>
                            <?php foreach ($available_months as $month): ?>
                                <option value="<?php echo htmlspecialchars($month); ?>" <?php echo $selected_month === $month ? 'selected' : ''; ?>><?php echo htmlspecialchars(date('F Y', strtotime($month . '-01'))); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="btn btn-primary btn-sm">Terapkan</button>
                        <a href="kelola_kategori.php" class="btn btn-secondary btn-sm">Reset</a>
                    </form>
                    <a href="export.php?module=produk&format=pdf&bulan=<?php echo urlencode($selected_month); ?>" class="btn btn-secondary btn-sm">PDF</a>
                    <a href="export.php?module=produk&format=docx&bulan=<?php echo urlencode($selected_month); ?>" class="btn btn-secondary btn-sm">Word</a>
                    <a href="export.php?module=produk&format=xlsx&bulan=<?php echo urlencode($selected_month); ?>" class="btn btn-secondary btn-sm">Excel</a>
                </div>
            </div>
        </div>

        <!-- TABEL KATEGORI -->
        <h2 style="margin: 30px 0 20px 0; color: #333;">
            Kategori Produk
        </h2>

        <div class="card">
            <div class="table-wrapper">
                <table class="table mobile-table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                    if (!$kategori) {
                        echo '<tr><td colspan="2">Gagal memuat kategori: '
                             . htmlspecialchars(mysqli_error($koneksi))
                             . '</td></tr>';
                    } else {

                        $noKategori = 1;

                        while ($row = mysqli_fetch_assoc($kategori)) {
                    ?>
                            <tr>
                                <td><?php echo $noKategori++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama']); ?></td>
                            </tr>
                    <?php
                        }
                    }
                    ?>

                    </tbody>
                </table>
            </div>
        </div>

        <!-- TABEL JENIS PRODUK -->
        <h2 style="margin: 30px 0 20px 0; color: #333;">
            Jenis Produk
        </h2>

        <div class="card">
            <div style="overflow-x:auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Jenis</th>
                            <th>Kategori</th>
                            <th>Satuan</th>
                            <th>Total Produk</th>
                            <th>Total Penjual</th>
                            <th>Jumlah Barang Terjual</th>
                            <th>Total Nilai Penjualan</th>
                        </tr>
                    </thead>
                    <tbody>

                    <?php
                    if (!$jenis) {
                        echo '<tr><td colspan="4">Gagal memuat jenis produk: '
                             . htmlspecialchars(mysqli_error($koneksi))
                             . '</td></tr>';
                    } else {

                        $noJenis = 1; // Nomor urut dimulai dari 1

                        while ($row = mysqli_fetch_assoc($jenis)) {
                    ?>
                            <tr>
                                <td><?php echo $noJenis++; ?></td>
                                <td><?php echo htmlspecialchars($row['nama_jenis']); ?></td>
                                <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($row['satuan'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars((int)($row['total_produk'] ?? 0)); ?></td>
                                <td><?php echo htmlspecialchars((int)($row['total_penjual'] ?? 0)); ?></td>
                                <td><?php echo htmlspecialchars((int)($row['jumlah_barang_terjual'] ?? 0)); ?></td>
                                <td><?php echo htmlspecialchars(format_rupiah((float)($row['total_nilai_penjualan'] ?? 0))); ?></td>
                            </tr>
                    <?php
                        }
                    }
                    ?>

                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

<footer class="app-footer">© Petani Sejati (PTS_Jatim)</footer>
<script src="../js/admin-responsive.js"></script>
</body>
</html>