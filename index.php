<?php
include 'Koneksi.php';

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['role'] == 'penjual') {
        header('Location: penjual/dashboard.php');
        exit;
    } elseif ($_SESSION['role'] == 'admin') {
        header('Location: admin/index.php');
        exit;
    } else {
        header('Location: pembeli/dashboard.php');
        exit;
    }
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['login'])) {
    $email    = sanitize($koneksi, $_POST['email']);
    $password = $_POST['password'];

    $query  = "SELECT * FROM users WHERE email = '$email' AND is_active = 1";
    $result = mysqli_query($koneksi, $query);

    if (mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password']) || md5($password) == $user['password']) {
            $_SESSION['user_id']           = $user['id'];
            $_SESSION['nama']              = $user['nama'];
            $_SESSION['email']             = $user['email'];
            $_SESSION['role']              = $user['role'];
            $_SESSION['jenis_keanggotaan'] = $user['jenis_keanggotaan'];

            if ($user['role'] == 'penjual')     header('Location: penjual/dashboard.php');
            elseif ($user['role'] == 'admin')   header('Location: admin/index.php');
            else                                header('Location: pembeli/dashboard.php');
            exit;
        } else {
            $error = 'Password salah!';
        }
    } else {
        $error = 'Email tidak ditemukan!';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk — Sistem Penjualan Pupuk PTS Jatim</title>
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        :root {
            --green-50: #f4fbf1;
            --green-100: #dcefd3;
            --green-200: #b7d7a8;
            --green-300: #8eb56b;
            --green-400: #5d8f4d;
            --green-500: #3f6f36;
            --green-600: #2f5a2b;
            --green-700: #21431f;
            --green-800: #162f17;
            --white: #ffffff;
            --error-bg: #fceceb;
            --error-text: #7d2e2e;
            --error-border: #e8a0a0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: var(--green-50);
            background-image:
                radial-gradient(circle at 15% 20%, rgba(93,143,77,0.16) 0%, transparent 48%),
                radial-gradient(circle at 85% 80%, rgba(63,111,54,0.12) 0%, transparent 50%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        .card {
            background: var(--white);
            border-radius: 24px;
            border: 1px solid var(--green-100);
            width: 100%;
            max-width: 940px;
            display: flex;
            overflow: hidden;
            box-shadow: 0 20px 55px rgba(33, 67, 31, 0.12);
        }

        .panel-form {
            flex: 1;
            padding: 46px 40px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 28px;
        }
        .brand-icon {
            width: 44px; height: 44px;
            background: linear-gradient(135deg, var(--green-100), var(--green-200));
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 22px;
        }
        .brand-name {
            font-size: 15px;
            font-weight: 700;
            color: var(--green-600);
            line-height: 1.2;
        }
        .brand-sub {
            font-size: 11px;
            color: var(--green-400);
            font-weight: 400;
        }

        h2 {
            font-size: 24px;
            font-weight: 700;
            color: var(--green-700);
            margin-bottom: 6px;
        }
        .sub-heading {
            font-size: 14px;
            color: var(--green-500);
            margin-bottom: 24px;
        }

        .alert {
            background: var(--error-bg);
            color: var(--error-text);
            border: 1px solid var(--error-border);
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .form-group { margin-bottom: 20px; }
        label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: var(--green-600);
            margin-bottom: 7px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px;
            border: 1.5px solid var(--green-100);
            border-radius: 10px;
            font-size: 14px;
            color: var(--green-800);
            background: var(--green-50);
            transition: border-color .2s, background .2s;
            outline: none;
        }
        input:focus {
            border-color: var(--green-300);
            background: var(--white);
        }
        input::placeholder { color: var(--green-300); }

        .btn-login {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, var(--green-500), var(--green-400));
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-size: 15px;
            font-weight: 700;
            cursor: pointer;
            letter-spacing: .3px;
            transition: transform .1s, box-shadow .2s;
            margin-top: 4px;
            box-shadow: 0 10px 20px rgba(63,111,54,0.2);
        }
        .btn-login:hover  { transform: translateY(-1px); box-shadow: 0 12px 24px rgba(63,111,54,0.24); }
        .btn-login:active { transform: scale(0.99); }

        .link-area {
            text-align: center;
            margin-top: 22px;
            font-size: 13px;
            color: var(--green-500);
        }
        .link-area a {
            color: var(--green-600);
            font-weight: 700;
            text-decoration: none;
        }
        .link-area a:hover { text-decoration: underline; }

        .panel-info {
            flex: 1;
            background: linear-gradient(135deg, var(--green-700) 0%, var(--green-600) 100%);
            padding: 42px 36px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .info-badge {
            display: inline-block;
            background: rgba(255,255,255,0.14);
            color: #f2fbe8;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            padding: 5px 12px;
            border-radius: 999px;
            margin-bottom: 18px;
            width: fit-content;
        }

        .info-headline {
            font-size: 24px;
            font-weight: 800;
            color: #f7fff2;
            line-height: 1.35;
            margin-bottom: 12px;
        }
        .info-headline span { color: var(--green-200); }

        .info-desc {
            font-size: 14px;
            color: #dcefd3;
            line-height: 1.7;
            margin-bottom: 20px;
        }

        .divider {
            border: none;
            border-top: 1px solid rgba(255,255,255,0.14);
            margin-bottom: 18px;
        }

        .fitur-label {
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .8px;
            text-transform: uppercase;
            color: #b7d7a8;
            margin-bottom: 14px;
        }
        .fitur-list { list-style: none; }
        .fitur-list li {
            font-size: 13px;
            color: #f4fbf1;
            padding: 6px 0;
            display: flex;
            align-items: center;
            gap: 10px;
            border-bottom: 1px solid rgba(255,255,255,.08);
        }
        .fitur-list li:last-child { border-bottom: none; }
        .dot {
            width: 7px; height: 7px;
            background: var(--green-200);
            border-radius: 50%;
            flex-shrink: 0;
        }

        @media (max-width: 700px) {
            .card { flex-direction: column; }
            .panel-form { padding: 36px 28px; }
            .panel-info { padding: 36px 28px; }
            .info-headline { font-size: 22px; }
        }
    </style>
</head>
<body>
    <div class="card">

        <!-- PANEL KIRI: FORM LOGIN -->
        <div class="panel-form">
            <div class="brand">
                <div class="brand-icon">🌱</div>
                <div>
                    <div class="brand-name">PTS Jatim</div>
                    <div class="brand-sub">Sistem Informasi Pupuk</div>
                </div>
            </div>

            <h2>Masuk ke akun Anda</h2>
            <p class="sub-heading">Selamat datang kembali, silakan isi data login.</p>

            <?php if ($error): ?>
                <div class="alert">⚠ <?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="email">Alamat Email</label>
                    <input type="email" id="email" name="email"
                           placeholder="contoh@email.com" required>
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password"
                           placeholder="Masukkan password" required>
                </div>

                <button type="submit" name="login" class="btn-login">Masuk</button>
            </form>

            <div class="link-area">
                Belum punya akun? <a href="auth/register.php">Daftar di sini</a>
            </div>
        </div>

        <!-- PANEL KANAN: INFO -->
        <div class="panel-info">
            <div class="info-badge">Anggota PTS Jatim</div>
            <h1 class="info-headline">
                Solusi belanja dan jualan pupuk yang lebih praktis
            </h1>
            <p class="info-desc">
                Platform sederhana untuk mengelola produk, memantau pesanan, dan berbelanja pupuk organik dengan lebih cepat dan terarah.
            </p>
            <hr class="divider">
            <p class="fitur-label">Yang bisa Anda lakukan</p>
            <ul class="fitur-list">
                <li><span class="dot"></span> Kelola produk dan stok dengan mudah</li>
                <li><span class="dot"></span> Pantau pesanan secara real time</li>
                <li><span class="dot"></span> Belanja dari penjual terpercaya</li>
            </ul>
        </div>

    </div>
</body>
</html>