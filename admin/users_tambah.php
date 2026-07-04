<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $angkatan = mysqli_real_escape_string($koneksi, $_POST['angkatan']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_telp = isset($_POST['no_telp']) ? mysqli_real_escape_string($koneksi, $_POST['no_telp']) : null;
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);
    $jenis_keanggotaan = ($role === 'penjual') ? 'penjual' : 'bukan_penjual';
    $is_active = isset($_POST['is_active']) ? 1 : 0;
    $password = $_POST['password'] ?? '';

    $role_allowed = ['admin', 'penjual', 'pembeli'];
    if (!in_array($role, $role_allowed, true)) {
        $message = 'Role tidak valid.';
    } elseif (trim($nama) === '' || trim($angkatan) === '' || trim($alamat) === '' || trim($email) === '' || trim($password) === '') {
        $message = 'Semua field wajib diisi.';
    } else {
        // Validasi email unik
        $cek = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT id FROM users WHERE email='{$email}' LIMIT 1"));
        if ($cek) {
            $message = 'Email sudah digunakan.';
        } else {
            // Hash password (bcrypt). Jika password database kamu pakai md5 lama, minimal tetap hash
            $hash = password_hash($password, PASSWORD_BCRYPT);

            mysqli_query($koneksi, "
                INSERT INTO users (nama, angkatan, jenis_keanggotaan, alamat, no_telp, email, password, role, is_active)
                VALUES (
                    '{$nama}',
                    '{$angkatan}',
                    '{$jenis_keanggotaan}',
                    '{$alamat}',
                    " . ($no_telp !== null && $no_telp !== '' ? "'{$no_telp}'" : 'NULL') . ",
                    '{$email}',
                    '{$hash}',
                    '{$role}',
                    {$is_active}
                )
            ");

            if ($role === 'penjual') {
                $user_id = mysqli_insert_id($koneksi);
                $nama_toko = 'Penjual ' . $nama;
                mysqli_query($koneksi, "INSERT INTO toko (user_id, nama_toko, is_active, created_at) VALUES ($user_id, '$nama_toko', 1, NOW())");
            }

            header('Location: users.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah User - Admin</title>
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
            <li><a href="users.php" class="active"><span class="material-symbols-outlined icon">people</span> Kelola User</a></li>
            <li><a href="toko.php"><span class="material-symbols-outlined icon">store</span> Kelola Toko</a></li>
            <li><a href="pesanan.php"><span class="material-symbols-outlined icon">inventory_2</span> Pesanan</a></li>
            <li><a href="laporan_penjualan.php"><span class="material-symbols-outlined icon">bar_chart</span> Laporan Penjualan</a></li>
            <li><a href="kelola_kategori.php"><span class="material-symbols-outlined icon">label</span> Kategori & Jenis Produk</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="navbar">
            <div class="navbar-brand">Tambah User</div>
            <div class="navbar-right">
                <div class="navbar-links">
                    <a href="users.php">← Kembali</a>
                </div>
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

        <h1 class="page-title">Tambah User</h1>

        <div class="card" style="max-width: 720px;">
            <?php if ($message) { ?>
                <div class="alert" style="margin-bottom: 15px;">🎯 <?php echo htmlspecialchars($message); ?></div>
            <?php } ?>

            <form method="POST">
                <div class="grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">
                    <div class="form-group">
                        <label>Nama</label>
                        <input class="input" type="text" name="nama" required>

                    </div>
                    <div class="form-group">
                        <label>Angkatan</label>
                        <input class="input" type="text" name="angkatan" required>
                    </div>
                    <div class="form-group">
                        <label>Alamat</label>
                        <input class="input" type="text" name="alamat" required>
                    </div>
                    <div class="form-group">
                        <label>No. Telp (opsional)</label>
                        <input class="input" type="text" name="no_telp">
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input class="input" type="email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label>Role</label>
                        <select class="input" name="role" required>
                            <option value="admin">admin</option>
                            <option value="penjual">penjual</option>
                            <option value="pembeli">pembeli</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Password</label>
                        <input class="input" type="password" name="password" required>
                    </div>
                    <div class="form-group" style="display:flex; align-items:flex-end;">
                        <label style="display:flex; gap:10px; align-items:center;">
                            <input type="checkbox" name="is_active" checked>
                            Aktif
                        </label>
                    </div>
                </div>

                <div style="margin-top: 16px; display:flex; gap: 10px;">
                    <button type="submit" name="simpan" class="btn btn-primary">Simpan</button>
                    <a href="users.php" class="btn btn-secondary" style="display:inline-flex; align-items:center; justify-content:center;">Batal</a>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>

