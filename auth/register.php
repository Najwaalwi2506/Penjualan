<?php
include '../Koneksi.php';

$error = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($koneksi, $_POST['nama']);
    $angkatan = sanitize($koneksi, $_POST['angkatan']);
    $jenis = sanitize($koneksi, $_POST['jenis_keanggotaan']);
    $alamat = sanitize($koneksi, $_POST['alamat']);
    $no_telp = sanitize($koneksi, $_POST['no_telp']);
    $email = sanitize($koneksi, $_POST['email']);
    $password = $_POST['password'];
    $password_confirm = $_POST['password_confirm'];
    
    // Validasi
    if (strlen($password) < 6) {
        $error = 'Password minimal 6 karakter!';
    } elseif ($password !== $password_confirm) {
        $error = 'Password tidak cocok!';
    } elseif (empty($no_telp)) {
        $error = 'No telepon wajib diisi!';
    } else {
        // Cek email
        $cek_email = mysqli_query($koneksi, "SELECT id FROM users WHERE email = '$email'");
        if (mysqli_num_rows($cek_email) > 0) {
            $error = 'Email sudah terdaftar!';
        } else {
            // Insert user baru
            $password_hash = password_hash($password, PASSWORD_BCRYPT);
            $role = ($jenis == 'penjual') ? 'penjual' : 'pembeli';
            
            $query = "INSERT INTO users (nama, angkatan, jenis_keanggotaan, alamat, no_telp, email, password, role, is_active) 
                      VALUES ('$nama', '$angkatan', '$jenis', '$alamat', '$no_telp', '$email', '$password_hash', '$role', 1)";
            
            if (mysqli_query($koneksi, $query)) {
                $success = true;
                $user_id = mysqli_insert_id($koneksi);
                // Jika penjual, buat toko otomatis
                if ($jenis == 'penjual') {
                    $nama_toko = "Penjual " . $nama;
                    $query_toko = "INSERT INTO toko (user_id, nama_toko, is_active) VALUES ($user_id, '$nama_toko', 1)";
                    mysqli_query($koneksi, $query_toko);
                }
                // Kirim notifikasi ke email admin jika sudah dikonfigurasi
                $subject = 'Pendaftaran Akun Baru di Sistem Pupuk';
                $message = '<p>Akun baru telah didaftarkan dengan rincian berikut:</p>' .
                           '<ul>' .
                           '<li>Nama: ' . htmlspecialchars($nama) . '</li>' .
                           '<li>Email: ' . htmlspecialchars($email) . '</li>' .
                           '<li>Role: ' . htmlspecialchars($role) . '</li>' .
                           '<li>Jenis keanggotaan: ' . htmlspecialchars($jenis) . '</li>' .
                           '</ul>';
                send_notification_email($koneksi, $subject, $message);
            } else {
                $error = 'Gagal membuat akun: ' . mysqli_error($koneksi);
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
    <title>Daftar Akun - Sistem Penjualan Pupuk</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f4fbf1 0%, #e7f3e2 100%);
            min-height: 100vh;
            padding: 24px;
            color: #264229;
        }
        .container {
            max-width: 780px;
            margin: 0 auto;
            background: linear-gradient(145deg, #ffffff 0%, #fbfef8 100%);
            padding: 36px;
            border-radius: 24px;
            box-shadow: 0 20px 50px rgba(33, 67, 31, 0.12);
            border: 1px solid #dcebd8;
            position: relative;
            overflow: hidden;
        }
        .container::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(circle at top right, rgba(93, 143, 77, 0.08), transparent 35%);
            pointer-events: none;
        }
        .brand {
            text-align: center;
            margin-bottom: 20px;
        }
        .brand-icon {
            width: 58px;
            height: 58px;
            margin: 0 auto 12px;
            border-radius: 18px;
            background: linear-gradient(135deg, #dcefd3, #b7d7a8);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            box-shadow: 0 8px 18px rgba(93, 143, 77, 0.18);
        }
        h1 { color: #21431f; margin-bottom: 6px; text-align: center; }
        .subtitle { text-align: center; color: #6b7b6b; margin-bottom: 24px; }
        .form-group { margin-bottom: 16px; }
        label { display: block; margin-bottom: 8px; color: #2f5a2b; font-weight: 600; }
        input, select, textarea {
            width: 100%;
            min-height: 46px;
            padding: 12px 14px;
            border: 1.5px solid #dcebd8;
            border-radius: 12px;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
            background: #f6fbf3;
        }
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #5d8f4d;
            box-shadow: 0 0 0 3px rgba(93, 143, 77, 0.16);
            transform: translateY(-1px);
        }
        textarea { resize: vertical; min-height: 90px; }
        button {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #2f6b3f 0%, #5d8f4d 100%);
            color: white;
            border: none;
            border-radius: 12px;
            font-weight: bold;
            cursor: pointer;
            font-size: 16px;
            box-shadow: 0 10px 20px rgba(47, 107, 63, 0.2);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        button:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(47, 107, 63, 0.25);
        }
        .alert {
            padding: 12px 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .alert-danger { background: #fceceb; color: #7d2e2e; border-color: #e8a0a0; }
        .alert-success { background: #edf7ea; color: #2f6b3f; border-color: #8eb56b; }
        .links { text-align: center; margin-top: 20px; color: #6b7b6b; }
        .links a { color: #2f6b3f; text-decoration: none; font-weight: 600; }
        .links a:hover { text-decoration: underline; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-card {
            background: linear-gradient(135deg, #f8fcf6 0%, #f2f9ec 100%);
            padding: 18px;
            border-radius: 18px;
            border: 1px solid #e8f2e3;
            margin-bottom: 16px;
            box-shadow: inset 0 1px 0 rgba(255,255,255,0.7);
        }
        .form-note {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            background: linear-gradient(135deg, #eef8e8 0%, #f6fcef 100%);
            border: 1px solid #d9ebce;
            border-radius: 12px;
            padding: 12px 14px;
            margin-bottom: 16px;
            color: #4a6548;
            font-size: 13px;
        }
        .form-note-icon {
            font-size: 16px;
            margin-top: 1px;
        }
        @media (max-width: 600px) {
            .form-row { grid-template-columns: 1fr; }
            .container { padding: 24px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="brand">
            <div class="brand-icon">🌱</div>
            <div>
                <h1>Daftar Akun Baru</h1>
                <p class="subtitle">Bergabunglah untuk mulai berbelanja atau menjual produk organik.</p>
            </div>
        </div>
        
        <?php if ($error) { ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php } ?>
        
        <?php if ($success) { ?>
            <div class="alert alert-success">
                ✓ Akun berhasil dibuat! <a href="../index.php">Login di sini</a>
            </div>
        <?php } else { ?>
        
        <form method="POST">
            <div class="form-card">
            <div class="form-note">
                <span class="form-note-icon">🌿</span>
                <div>Isi data dengan lengkap agar akun Anda siap dipakai untuk belanja atau jualan produk organik dengan cepat.</div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="nama">Nama Lengkap *</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                
                <div class="form-group">
                    <label for="angkatan">Angkatan/Alumni *</label>
                    <input type="text" id="angkatan" name="angkatan" placeholder="2020, 2019, dll" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="email">Email *</label>
                <input type="email" id="email" name="email" required>
            </div>
            
            <div class="form-group">
                <label for="no_telp">No Telepon *</label>
                <input type="text" id="no_telp" name="no_telp" placeholder="0812xxxxxxx" required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <div class="form-group">
                    <label for="password_confirm">Konfirmasi Password *</label>
                    <input type="password" id="password_confirm" name="password_confirm" required>
                </div>
            </div>
            
            <div class="form-group">
                <label for="jenis_keanggotaan">Jenis Keanggotaan *</label>
                <select id="jenis_keanggotaan" name="jenis_keanggotaan" required>
                    <option value="">-- Pilih --</option>
                    <option value="penjual">Penjual (Input & Jual Produk)</option>
                    <option value="bukan_penjual">Pembeli (Hanya Belanja)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="alamat">Alamat Lengkap *</label>
                <textarea id="alamat" name="alamat" required></textarea>
            </div>
            </div>
            
            <button type="submit">Daftar Akun</button>
        </form>
        
        <?php } ?>
        
        <div class="links">
            <p>Sudah punya akun? <a href="../index.php">Login di sini</a></p>
        </div>
    </div>
</body>
</html>
