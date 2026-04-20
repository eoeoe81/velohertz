<?php
session_start();
include 'koneksi.php';

// Kalau sudah login, langsung ke index
if (isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$pesan = "";

// 1. SISTEM ANTI BRUTE-FORCE (Rate Limiting)
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
}

// Cek apakah user sedang dalam masa "Lockout" (Dikunci)
if (isset($_SESSION['lockout_time']) && time() < $_SESSION['lockout_time']) {
    $wait_time = $_SESSION['lockout_time'] - time();
    $pesan = "<div class='error-msg'><i class='fa-solid fa-lock'></i> Terlalu banyak percobaan salah. Silakan tunggu <b>$wait_time detik</b> lagi.</div>";
    $lockout = true;
} else {
    $lockout = false;
}

if (isset($_POST['login']) && !$lockout) {
    $username_input = $conn->real_escape_string(trim($_POST['username']));
    $password_input = $_POST['password'];

    // Cek user berdasarkan email ATAU username
    $stmt = $conn->prepare("SELECT uid, uname, upassword FROM User WHERE uname = ? OR email = ?");
    $stmt->bind_param("ss", $username_input, $username_input);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        
        // 2. VERIFIKASI HASH PASSWORD
        if (password_verify($password_input, $row['upassword'])) {
            // Berhasil login! Reset percobaan salah
            $_SESSION['login_attempts'] = 0;
            unset($_SESSION['lockout_time']);
            
            $_SESSION['username'] = $row['uname'];
            $_SESSION['uid'] = $row['uid'];
            header("Location: index.php");
            exit();
        } else {
            // Salah password -> Tambah attempt
            $_SESSION['login_attempts'] += 1;
            $sisa = 3 - $_SESSION['login_attempts'];
            
            if ($_SESSION['login_attempts'] >= 3) {
                $_SESSION['lockout_time'] = time() + 30; // Kunci 30 detik
                $pesan = "<div class='error-msg'><i class='fa-solid fa-shield-halved'></i> Akses diblokir! Terlalu banyak percobaan. Tunggu 30 detik.</div>";
            } else {
                $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Password salah! Sisa percobaan: $sisa</div>";
            }
        }
    } else {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-user-xmark'></i> Akun tidak ditemukan!</div>";
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - Velohertz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Desain Glassmorphism sesuai tema Velohertz */
        body { margin: 0; font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; color: #0f172a; }
        .container { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(15px); padding: 45px 40px; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); width: 360px; text-align: center; border: 1px solid rgba(255,255,255,0.5); border-top: 5px solid #0f52ba; }
        .logo { color: #0f52ba; font-style: italic; font-size: 32px; font-weight: 800; margin-bottom: 10px; letter-spacing: -1px; }
        h2 { font-size: 22px; margin-bottom: 25px; margin-top: 0; }
        .input-group { margin-bottom: 20px; text-align: left; position: relative; }
        .input-group label { display: block; font-size: 13px; margin-bottom: 8px; font-weight: 600; }
        .input-group input { width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.5); background: rgba(255, 255, 255, 0.7); box-sizing: border-box; outline: none; transition: 0.3s; font-size: 14px; font-family: inherit; }
        .input-group input:focus { border-color: #0f52ba; background: #ffffff; box-shadow: 0 0 0 4px rgba(15,82,186,0.1); }
        .password-wrapper { position: relative; }
        .toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 18px; cursor: pointer; color: #475569; padding: 0; }
        .forgot-pass { text-align: right; margin-top: 10px; margin-bottom: 25px; }
        .forgot-pass a { color: #0f52ba; font-size: 13px; text-decoration: none; font-weight: 600; }
        .btn-submit { width: 100%; padding: 14px; background-color: #0f52ba; color: white; border: none; border-radius: 12px; font-weight: 600; font-size: 16px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(15,82,186,0.2); font-family: inherit; }
        .btn-submit:hover { background-color: #0c4399; transform: translateY(-2px); }
        .btn-submit:disabled { background-color: #94a3b8; cursor: not-allowed; transform: none; box-shadow: none; }
        .footer-link { margin-top: 25px; font-size: 14px; color: #475569; }
        .footer-link a { color: #0f52ba; font-weight: 600; text-decoration: none; }
        .error-msg { background: rgba(254,226,226,0.9); color: #e53e3e; font-size: 13px; padding: 12px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #fecaca; font-weight: 500; text-align: left;}
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">Velohertz</div>
        <h2>Masuk ke Akunmu</h2>
        
        <?php echo $pesan; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label>Email atau Username</label>
                <input type="text" name="username" required placeholder="Email atau username" <?php if($lockout) echo "disabled"; ?> />
            </div>

            <div class="input-group">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" id="login-password" name="password" required placeholder="Password" <?php if($lockout) echo "disabled"; ?> />
                    <button type="button" class="toggle-password" onclick="toggleVisibility('login-password', 'eye-icon')" id="eye-icon">🙉</button>
                </div>
            </div>

            <div class="forgot-pass">
                <a href="lupa_password.php">Lupa password?</a>
            </div>

            <button type="submit" name="login" class="btn-submit" <?php if($lockout) echo "disabled"; ?>>Masuk</button>
        </form>

        <div class="footer-link">Belum punya akun? <a href="register.php">Daftar di sini</a></div>
    </div>

    <script>
        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") {
                input.type = "text";
                icon.textContent = "🙈";
            } else {
                input.type = "password";
                icon.textContent = "🙉";
            }
        }
    </script>
</body>
</html>