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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
    
    /* Dark Mode + Modern Mesh Gradient Background */
    body {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #0b0f19;
        background-image: 
            radial-gradient(at 0% 0%, rgba(59, 113, 202, 0.15) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(116, 185, 255, 0.1) 0px, transparent 50%);
        color: #fff;
    }
    
    /* Glassmorphism Card */
    .login-container {
        background: rgba(20, 25, 35, 0.6);
        padding: 45px 40px;
        border-radius: 28px; 
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        width: 100%;
        max-width: 420px;
        text-align: center;
        backdrop-filter: blur(20px);
        -webkit-backdrop-filter: blur(20px);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }

    /* Streaming App Title Vibe (Sleek Gradient) */
    .velohertz-title {
        font-family: 'Outfit', sans-serif;
        font-weight: 800;
        font-size: 42px;
        margin-bottom: 30px;
        letter-spacing: -0.5px;
        background: linear-gradient(135deg, #74b9ff 0%, #a29bfe 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    
    /* Modern Error Message */
    .error-msg {
        background-color: rgba(255, 71, 87, 0.1);
        color: #ff6b81;
        padding: 14px;
        border-radius: 14px;
        margin-bottom: 25px;
        font-size: 13px;
        font-weight: 500;
        border: 1px solid rgba(255, 71, 87, 0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        backdrop-filter: blur(5px);
    }

    .input-wrapper { 
        position: relative; 
        width: 100%; 
        margin-bottom: 22px; 
    }
    
    /* Minimalist Dark Inputs */
    .login-container input[type="text"],
    .login-container input[type="password"] {
        width: 100%;
        padding: 16px 20px;
        padding-right: 50px; 
        border: 1px solid rgba(255, 255, 255, 0.1);
        border-radius: 16px; 
        font-size: 14px;
        color: #fff;
        background-color: rgba(255, 255, 255, 0.03);
        transition: all 0.3s ease;
        outline: none;
    }

    .login-container input::placeholder {
        color: rgba(255, 255, 255, 0.4);
        font-weight: 300;
    }
    
    .login-container input:focus {
        border-color: #74b9ff;
        background-color: rgba(255, 255, 255, 0.08);
        box-shadow: 0 0 15px rgba(116, 185, 255, 0.1);
    }
    
    /* Premium Icon Toggle (Masih support emoji bawaan kamu) */
    .toggle-password {
        position: absolute;
        right: 18px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        font-size: 18px;
        user-select: none;
        transition: 0.3s;
        /* Biar emoji monyetnya nggak terlalu redup di dark mode */
        opacity: 0.8; 
    }
    
    .toggle-password:hover {
        opacity: 1;
        transform: translateY(-50%) scale(1.1);
    }

    /* Subtle Forgot Password Link */
    .forgot-link {
        display: block;
        text-align: right;
        margin-top: -12px;
        margin-bottom: 25px;
        font-size: 13px;
        color: rgba(255, 255, 255, 0.5);
        text-decoration: none;
        font-weight: 400;
        transition: color 0.3s ease;
    }
    
    .forgot-link:hover {
        color: #fff;
    }

    /* Gen-Z Glowing Button */
    .login-container button {
        width: 100%;
        padding: 16px;
        border: none;
        border-radius: 16px; 
        background: linear-gradient(135deg, #3b71ca 0%, #a29bfe 100%);
        color: white;
        font-size: 16px;
        font-weight: 600;
        letter-spacing: 0.5px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .login-container button:hover:not(:disabled) {
        transform: translateY(-3px);
        box-shadow: 0 10px 20px rgba(162, 155, 254, 0.3);
    }
    
    .login-container button:disabled {
        background: rgba(255, 255, 255, 0.1);
        color: rgba(255, 255, 255, 0.3);
        cursor: not-allowed;
        box-shadow: none;
    }

    /* Clean Register Link */
    .register-link {
        display: inline-block;
        margin-top: 25px;
        color: rgba(255, 255, 255, 0.6);
        text-decoration: none; 
        font-size: 13px;
        font-weight: 400;
        transition: color 0.3s ease;
    }
    
    .register-link:hover { 
        color: #a29bfe; 
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