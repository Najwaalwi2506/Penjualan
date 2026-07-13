<?php
include '../Koneksi.php';
check_login();
check_role(['penjual']);
ensure_toko_rekening_columns($koneksi);

$user_id = (int)$_SESSION['user_id'];
$success = '';
$error = '';

$toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
if (!$toko) {
    $nama_toko = sanitize($koneksi, 'Toko ' . ($_SESSION['nama'] ?? 'Penjual'));
    mysqli_query($koneksi, "INSERT INTO toko (user_id, nama_toko, is_active) VALUES ($user_id, '$nama_toko', 1)");
    $toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_toko = sanitize($koneksi, $_POST['nama_toko'] ?? '');
    $deskripsi = sanitize($koneksi, $_POST['deskripsi'] ?? '');
    $bank_nama = sanitize($koneksi, $_POST['bank_nama'] ?? '');
    $no_rekening = sanitize($koneksi, $_POST['no_rekening'] ?? '');
    $nama_rekening = sanitize($koneksi, $_POST['nama_rekening'] ?? '');

    if ($nama_toko === '') {
        $error = 'Nama toko wajib diisi.';
    } else {
        $query = "
            UPDATE toko SET
                nama_toko = '$nama_toko',
                deskripsi = " . ($deskripsi !== '' ? "'$deskripsi'" : "NULL") . ",
                bank_nama = " . ($bank_nama !== '' ? "'$bank_nama'" : "NULL") . ",
                no_rekening = " . ($no_rekening !== '' ? "'$no_rekening'" : "NULL") . ",
                nama_rekening = " . ($nama_rekening !== '' ? "'$nama_rekening'" : "NULL") . ",
                updated_at = NOW()
            WHERE user_id = $user_id
        ";

        if (mysqli_query($koneksi, $query)) {
            $success = 'Data toko dan rekening berhasil disimpan.';
            $toko = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM toko WHERE user_id = $user_id"));
        } else {
            $error = 'Gagal menyimpan data: ' . mysqli_error($koneksi);
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Toko</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<div class="wrapper">
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
            <li><a href="riwayat.php"><span class="material-symbols-outlined icon">bar_chart</span> Riwayat Penjualan</a></li>
            <li class="sidebar-title">Pengaturan</li>
            <li><a href="toko_edit.php" class="active"><span class="material-symbols-outlined icon">settings</span> Atur Toko</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
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

        <h1 class="page-title">Atur Toko</h1>
        <p class="page-subtitle">Rekening ini akan ditampilkan kepada pembeli saat checkout.</p>

        <?php if ($success !== '') { ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php } ?>
        <?php if ($error !== '') { ?>
            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
        <?php } ?>

        <div class="card">
            <div class="card-header">Informasi Toko dan Rekening</div>
            <div style="padding: 20px;">
                <form method="POST">
                    <div class="form-group">
                        <label>Nama Toko</label>
                        <input type="text" name="nama_toko" value="<?php echo htmlspecialchars($toko['nama_toko'] ?? ''); ?>" required>
                    </div>

                    <div class="form-group">
                        <label>Deskripsi Toko</label>
                        <textarea name="deskripsi" rows="4"><?php echo htmlspecialchars($toko['deskripsi'] ?? ''); ?></textarea>
                    </div>

                    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 15px;">
                        <div class="form-group">
                            <label>Nama Bank</label>
                            <input type="text" name="bank_nama" placeholder="Contoh: BCA, BRI, Mandiri" value="<?php echo htmlspecialchars($toko['bank_nama'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>No Rekening</label>
                            <input type="text" name="no_rekening" value="<?php echo htmlspecialchars($toko['no_rekening'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label>Atas Nama</label>
                            <input type="text" name="nama_rekening" value="<?php echo htmlspecialchars($toko['nama_rekening'] ?? ''); ?>">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                </form>
            </div>
        </div>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>
