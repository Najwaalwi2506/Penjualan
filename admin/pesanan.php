<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

// Handle hapus pesanan
if (isset($_POST['hapus_id'])) {
    $hapus_id = (int)$_POST['hapus_id'];
    mysqli_query($koneksi, "DELETE FROM pesanan WHERE id = {$hapus_id}");
    $redirect_status = isset($_GET['status']) ? $_GET['status'] : 'all';
    header("Location: pesanan.php?status={$redirect_status}&msg=deleted");
    exit;
}

$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$allowed_status = ['menunggu_konfirmasi', 'dikonfirmasi', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];

if (in_array($status_filter, $allowed_status)) {
    $safe_status = sanitize($koneksi, $status_filter);
    $where = "WHERE p.status = '$safe_status'";
} else {
    $where = "";
    $status_filter = 'all';
}

$pesanan = mysqli_query($koneksi, "
    SELECT p.id, p.kode_pesanan, p.grand_total, p.status, p.created_at,
           u.nama AS pembeli,
           t.nama_toko
    FROM pesanan p
    JOIN users u ON p.pembeli_id = u.id
    JOIN toko t ON p.toko_id = t.id
    {$where}
    ORDER BY p.created_at DESC
");

$total = $pesanan ? mysqli_num_rows($pesanan) : 0;

function count_by_status($status, $koneksi) {
    $status = sanitize($koneksi, $status);
    $q = "SELECT COUNT(*) as cnt FROM pesanan WHERE status='{$status}'";
    $r = mysqli_fetch_assoc(mysqli_query($koneksi, $q));
    return (int)($r['cnt'] ?? 0);
}
$cnt_menunggu = count_by_status('menunggu_konfirmasi', $koneksi);
$cnt_dikonfirmasi = count_by_status('dikonfirmasi', $koneksi);
$cnt_dikirim = count_by_status('dikirim', $koneksi);
$cnt_selesai = count_by_status('selesai', $koneksi);
$cnt_dibatalkan = count_by_status('dibatalkan', $koneksi);
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pesanan - Admin</title>
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
            <li><a href="toko.php"><span class="material-symbols-outlined icon">store</span> Kelola Toko</a></li>
            <li><a href="pesanan.php" class="active"><span class="material-symbols-outlined icon">inventory_2</span> Pesanan</a></li>
            <li><a href="laporan_penjualan.php"><span class="material-symbols-outlined icon">bar_chart</span> Laporan Penjualan</a></li>
            <li><a href="kelola_kategori.php"><span class="material-symbols-outlined icon">label</span> Kategori & Jenis Produk</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="navbar">
            <div class="navbar-brand">Data Pesanan</div>
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

        <p class="page-subtitle">Admin hanya memantau transaksi. Konfirmasi pesanan dilakukan oleh penjual/toko.</p>

        <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div style="background:#d4edda; color:#155724; padding:10px 16px; border-radius:6px; margin-bottom:16px;">
                Pesanan berhasil dihapus.
            </div>
        <?php endif; ?>

        <!-- Filter Tabs -->
        <div style="margin-bottom: 20px; display: flex; gap: 10px; flex-wrap: wrap;">
            <a href="pesanan.php?status=all"
               class="btn <?php echo $status_filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">
                Semua
            </a>
            <a href="pesanan.php?status=menunggu_konfirmasi"
               class="btn <?php echo $status_filter === 'menunggu_konfirmasi' ? 'btn-primary' : 'btn-secondary'; ?>">
                Menunggu Penjual (<?php echo $cnt_menunggu; ?>)
            </a>
            <a href="pesanan.php?status=dikonfirmasi"
               class="btn <?php echo $status_filter === 'dikonfirmasi' ? 'btn-primary' : 'btn-secondary'; ?>">
                Dikonfirmasi (<?php echo $cnt_dikonfirmasi; ?>)
            </a>
            <a href="pesanan.php?status=dikirim"
               class="btn <?php echo $status_filter === 'dikirim' ? 'btn-primary' : 'btn-secondary'; ?>">
                Dikirim (<?php echo $cnt_dikirim; ?>)
            </a>
            <a href="pesanan.php?status=selesai"
               class="btn <?php echo $status_filter === 'selesai' ? 'btn-primary' : 'btn-secondary'; ?>">
                Selesai (<?php echo $cnt_selesai; ?>)
            </a>
            <a href="pesanan.php?status=dibatalkan"
               class="btn <?php echo $status_filter === 'dibatalkan' ? 'btn-primary' : 'btn-secondary'; ?>">
                Dibatalkan (<?php echo $cnt_dibatalkan; ?>)
            </a>
        </div>

        <div class="card">
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Pesanan</th>
                            <th>Pembeli</th>
                            <th>Penjual</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($total === 0): ?>
                            <tr>
                                <td colspan="7" style="text-align:center; color:#999; padding:20px;">
                                    Tidak ada pesanan.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($pesanan)): ?>
                                <tr>
                                    <td><?php echo $no++; ?></td>
                                    <td><strong><?php echo htmlspecialchars($row['kode_pesanan']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['pembeli']); ?></td>
                                    <td><?php echo htmlspecialchars($row['nama_toko']); ?></td>
                                    <td><?php echo format_rupiah($row['grand_total']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo htmlspecialchars($row['status']); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', htmlspecialchars($row['status']))); ?>
                                        </span>
                                    </td>
                                    <td style="white-space: nowrap;">
                                        <!-- Tombol Detail -->
                                        <a href="kelola_pesanan_detail.php?id=<?php echo (int)$row['id']; ?>"
                                           class="btn btn-primary btn-sm">
                                            Detail
                                        </a>
                                        <!-- Tombol Hapus -->
                                        <form method="POST"
                                              action="pesanan.php?status=<?php echo urlencode($status_filter); ?>"
                                              style="display:inline;"
                                              onsubmit="return confirm('Yakin ingin menghapus pesanan ini?');">
                                            <input type="hidden" name="hapus_id" value="<?php echo (int)$row['id']; ?>">
                                            <button type="submit" class="btn btn-sm"
                                                    style="background:#dc3545; color:#fff; border:none; cursor:pointer;">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
