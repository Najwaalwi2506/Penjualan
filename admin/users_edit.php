<?php
include '../Koneksi.php';
check_login();
check_role(['admin']);

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user = null;
if ($id > 0) {
    $user = mysqli_fetch_assoc(
        mysqli_query(
            $koneksi,
            "SELECT id, nama, angkatan, jenis_keanggotaan, alamat, no_telp, email, role, is_active FROM users WHERE id={$id}"
        )
    );
}

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save']) && $id > 0) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $angkatan = mysqli_real_escape_string($koneksi, $_POST['angkatan']);
    $jenis_keanggotaan = mysqli_real_escape_string($koneksi, $_POST['jenis_keanggotaan']);
    $alamat = mysqli_real_escape_string($koneksi, $_POST['alamat']);
    $no_telp = isset($_POST['no_telp']) ? mysqli_real_escape_string($koneksi, $_POST['no_telp']) : '';
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $role = mysqli_real_escape_string($koneksi, $_POST['role']);
    $is_active = isset($_POST['is_active']) ? 1 : 0;

    $password = trim($_POST['password'] ?? '');

    // Validasi
    $role_allowed = ['admin', 'penjual', 'pembeli'];
    if (!in_array($role, $role_allowed, true)) {
        $message = 'Role tidak valid.';
    } else {
        $jenis_allowed = ['penjual', 'bukan_penjual'];
        if (!in_array($jenis_keanggotaan, $jenis_allowed, true)) {
            $message = 'Jenis keanggotaan tidak valid.';
        } elseif (trim($nama) === '' || trim($angkatan) === '' || trim($alamat) === '' || trim($email) === '') {
            $message = 'Field wajib tidak boleh kosong.';
        } else {
            // Pastikan email unik (kecuali milik user ini)
            $cek = mysqli_fetch_assoc(
                mysqli_query($koneksi, "SELECT id FROM users WHERE email='{$email}' AND id<>{$id} LIMIT 1")
            );

            if ($cek) {
                $message = 'Email sudah digunakan user lain.';
            } else {
                // Update base (tanpa upload foto profil)
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_BCRYPT);
                    mysqli_query(
                        $koneksi,
                        "UPDATE users SET
                            nama='{$nama}',
                            angkatan='{$angkatan}',
                            jenis_keanggotaan='{$jenis_keanggotaan}',
                            alamat='{$alamat}',
                            no_telp=" . ($no_telp !== '' ? "'{$no_telp}'" : 'NULL') . ",
                            email='{$email}',
                            role='{$role}',
                            password='{$hash}',
                            is_active={$is_active}
                          WHERE id={$id}"
                    );
                } else {
                    mysqli_query(
                        $koneksi,
                        "UPDATE users SET
                            nama='{$nama}',
                            angkatan='{$angkatan}',
                            jenis_keanggotaan='{$jenis_keanggotaan}',
                            alamat='{$alamat}',
                            no_telp=" . ($no_telp !== '' ? "'{$no_telp}'" : 'NULL') . ",
                            email='{$email}',
                            role='{$role}',
                            is_active={$is_active}
                          WHERE id={$id}"
                    );
                }

                header('Location: users.php');
                exit;
            }
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - Admin</title>
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
            <li><a href="kelola_kategori.php"><span class="material-symbols-outlined icon">label</span> Kategori & Jenis Produk</a></li>
            <li class="sidebar-title">Akun</li>
            <li><a href="../auth/logout.php"><span class="material-symbols-outlined icon">logout</span> Logout</a></li>
        </ul>
    </div>

    <div class="main-content">
        <div class="navbar">
            <div class="navbar-brand">Edit User</div>
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

        <h1 class="page-title">Edit User</h1>

        <?php if (!$user) { ?>
            <div class="card"><p style="color:#999; padding:20px;">User tidak ditemukan.</p></div>
        <?php } else { ?>
            <div class="card" style="max-width: 650px;">
                <?php if ($message) { ?>
                    <div class="alert" style="margin:0 0 15px 0;"><?php echo htmlspecialchars($message); ?></div>
                <?php } ?>

<form method="POST">
                    <div class="grid" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 14px;">


                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Nama Lengkap</label>
                            <input class="input" type="text" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Angkatan/Alumni</label>
                            <input class="input" type="text" name="angkatan" value="<?php echo htmlspecialchars($user['angkatan']); ?>" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Jenis Keanggotaan</label>
                            <select name="jenis_keanggotaan" class="input" required>
                                <option value="penjual" <?php echo $user['jenis_keanggotaan']==='penjual'?'selected':''; ?>>Penjual (Input & Jual Produk)</option>
                                <option value="bukan_penjual" <?php echo $user['jenis_keanggotaan']==='bukan_penjual'?'selected':''; ?>>Pembeli (Hanya Belanja)</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Alamat</label>
                            <textarea class="input" name="alamat" required style="min-height: 90px; resize: vertical;"><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>No. Telp (opsional)</label>
                            <input class="input" type="text" name="no_telp" value="<?php echo htmlspecialchars($user['no_telp'] ?? ''); ?>" />
                        </div>


                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Email</label>
                            <input class="input" type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Role</label>
                            <select name="role" class="input" required>
                                <option value="admin" <?php echo $user['role']==='admin'?'selected':''; ?>>admin</option>
                                <option value="penjual" <?php echo $user['role']==='penjual'?'selected':''; ?>>penjual</option>
                                <option value="pembeli" <?php echo $user['role']==='pembeli'?'selected':''; ?>>pembeli</option>
                            </select>
                        </div>

                        <div class="form-group" style="margin-bottom: 0;">
                            <label>Password Baru (opsional)</label>
                            <input class="input" type="password" name="password" placeholder="Biarkan kosong jika tidak ingin mengganti password">
                        </div>

                        <div class="form-group" style="display:flex; align-items:flex-end; margin-bottom: 0;">
                            <label style="display:flex; gap:10px; align-items:center;">
                                <input type="checkbox" name="is_active" <?php echo $user['is_active'] ? 'checked' : ''; ?> />
                                Aktif
                            </label>
                        </div>
                    </div>

                    <div style="margin-top: 16px; display:flex; gap: 10px;">
                        <button type="submit" name="save" class="btn btn-primary" style="width:auto;">Simpan</button>
                        <a href="users.php" class="btn btn-secondary" style="display:inline-flex; align-items:center; justify-content:center;">Batal</a>
                    </div>
                </form>
            </div>
        <?php } ?>
    </div>
</div>
<script src="../js/admin-responsive.js"></script>
</body>
</html>

