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
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Velohertz</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        /* Background Langit Cerah */
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
        }
        
        .login-container {
            background: rgba(255, 255, 255, 0.85);
            padding: 40px;
            border-radius: 24px; 
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.6);
        }

        /* Judul Velohertz: Gradien Biru Kalem ke Biru Muda */
        .velohertz-title {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 38px;
            margin-bottom: 25px;
            letter-spacing: 1px;
            background: linear-gradient(135deg, #3b71ca 0%, #74b9ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .error-msg {
            background-color: #ffe8e8;
            color: #ff4757;
            padding: 12px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid #ffcccc;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .input-wrapper { position: relative; width: 100%; margin-bottom: 20px; }
        
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 15px;
            padding-right: 45px; 
            border: 2px solid #e1e5ee;
            border-radius: 16px; 
            font-size: 15px;
            background-color: #f8f9fa;
            transition: all 0.3s ease;
            outline: none;
        }
        
        .login-container input:focus {
            border-color: #3b71ca;
            background-color: #fff;
            box-shadow: 0 0 0 4px rgba(59, 113, 202, 0.2);
        }
        
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            font-size: 20px;
            user-select: none;
            transition: 0.2s;
            color: #888;
        }
        
        .toggle-password:hover {
            color: #3b71ca;
        }

        /* --- Link Lupa Password --- */
        .forgot-link {
            display: block;
            text-align: right;
            margin-top: -10px; /* Biar deket sama kotak password */
            margin-bottom: 15px;
            font-size: 13px;
            color: #3b71ca;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .forgot-link:hover {
            text-decoration: underline;
            color: #2a5298;
        }

        /* Tombol Masuk */
        .login-container button {
            width: 100%;
            padding: 15px;
            border: none;
            border-radius: 16px; 
            background: linear-gradient(135deg, #3b71ca 0%, #74b9ff 100%);
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        
        .login-container button:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 113, 202, 0.3);
        }
        
        .login-container button:disabled {
            background: #ccc;
            cursor: not-allowed;
            box-shadow: none;
        }

        /* --- Link Daftar (Hijau Link Cerah) --- */
        .register-link {
            display: inline-block;
            margin-top: 20px;
            color: #10b981; /* Warna hijau cerah web link / Emerald */
            text-decoration: underline; 
            font-size: 14px;
            font-weight: 500;
            transition: color 0.3s ease;
        }
        
        .register-link:hover { 
            color: #059669; /* Hijau yang lebih pekat dikit saat di-hover */
        }
    </style>
</head>
<body>

    <div class="login-container">
        <h2 class="velohertz-title">Velohertz</h2>
        
        <?= $pesan ?>
        
        <form action="" method="POST">
            <div class="input-wrapper">
                <input type="text" name="username" placeholder="Username atau Email" required>
            </div>
            
            <div class="input-wrapper">
                <input type="password" name="password" id="passInput" placeholder="Password" required>
                <span class="toggle-password" id="passIcon" onclick="toggleVisibility('passInput', 'passIcon')">🙉</span>
            </div>
            
            <a href="lupa_password.php" class="forgot-link">Lupa Password?</a>
            
            <button type="submit" name="login" <?= $lockout ? 'disabled' : '' ?>>Masuk</button>
        </form>
        
        <a href="register.php" class="register-link">Belum punya akun? Daftar disini</a>
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