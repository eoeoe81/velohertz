<?php
session_start();
include 'koneksi.php';

$pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // 1. Cek Konfirmasi Password Dulu
    if ($password !== $confirm_password) {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Konfirmasi password tidak cocok!</div>";
    } else {
        // 2. Cek apakah Username atau Email sudah terdaftar
        $stmt_cek = $conn->prepare("SELECT uid FROM user WHERE uname = ? OR email = ?");
        $stmt_cek->bind_param("ss", $username, $email);
        $stmt_cek->execute();
        $stmt_cek->store_result();

        if ($stmt_cek->num_rows > 0) {
            $pesan = "<div class='error-msg'><i class='fa-solid fa-user-xmark'></i> Username atau Email sudah terdaftar!</div>";
        } else {
            // 3. Validasi Regex Server
            $uppercase = preg_match('@[A-Z]@', $password);
            $lowercase = preg_match('@[a-z]@', $password);
            $number    = preg_match('@[0-9]@', $password);
            $special   = preg_match('@[^\w]@', $password);

            if (!$uppercase || !$lowercase || !$number || !$special || strlen($password) < 8) {
                $pesan = "<div class='error-msg'><i class='fa-solid fa-shield'></i> Password gagal memenuhi standar keamanan server!</div>";
            } else {
                // 4. BCRYPT HASHING
                $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                // 5. Simpan ke DB
                $stmt_insert = $conn->prepare("INSERT INTO user (uname, email, upassword) VALUES (?, ?, ?)");
                $stmt_insert->bind_param("sss", $username, $email, $hashed_password);

                if ($stmt_insert->execute()) {
                    echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location='login.php';</script>";
                    exit();
                } else {
                    $pesan = "<div class='error-msg'>Gagal mendaftar. Silakan coba lagi.</div>";
                }
            }
        }
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Velohertz</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@700;800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        /* Dark Mode + Modern Mesh Gradient Background (Sesuai dengan Login) */
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
            padding: 20px;
        }

        /* Glassmorphism Card */
        .container {
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

        /* Streaming App Title Vibe */
        h2 {
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 34px;
            margin-bottom: 30px;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #74b9ff 0%, #a29bfe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .input-group { margin-bottom: 22px; text-align: left; }
        .input-group label { 
            display: block; 
            font-size: 13px; 
            margin-bottom: 8px; 
            font-weight: 500; 
            color: rgba(255, 255, 255, 0.7); 
        }
        
        /* Minimalist Dark Inputs */
        .input-group input { 
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

        .input-group input::placeholder {
            color: rgba(255, 255, 255, 0.4);
            font-weight: 300;
        }

        .input-group input:focus { 
            border-color: #74b9ff;
            background-color: rgba(255, 255, 255, 0.08);
            box-shadow: 0 0 15px rgba(116, 185, 255, 0.1);
        }
        
        .password-wrapper { position: relative; }
        
        /* Premium Icon Toggle (Menggunakan Emoji Sesuai Permintaan) */
        .toggle-password { 
            position: absolute;
            right: 18px;
            top: 50%;
            transform: translateY(-50%);
            background: none; 
            border: none; 
            font-size: 18px; 
            cursor: pointer; 
            opacity: 0.8;
            transition: 0.3s;
            padding: 0;
            user-select: none;
        }

        .toggle-password:hover {
            opacity: 1;
            transform: translateY(-50%) scale(1.1);
        }
        
        /* Password Meter - Dark Mode Version */
        .strength-meter { 
            margin-top: 14px; 
            padding: 14px; 
            background: rgba(0, 0, 0, 0.2); 
            border-left: 4px solid rgba(255, 255, 255, 0.2); 
            border-radius: 14px; 
            font-size: 12px;
            transition: all 0.3s ease;
        }
        
        .strength-badge { 
            display: inline-block; 
            padding: 4px 10px; 
            border-radius: 8px; 
            font-size: 10px; 
            font-weight: 700; 
            color: white; 
            margin-bottom: 8px; 
            background: rgba(255, 255, 255, 0.2);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .recommendation { color: rgba(255, 255, 255, 0.5); line-height: 1.5; }

        /* Gen-Z Glowing Button */
        .btn-submit { 
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
            margin-top: 10px;
        }

        .btn-submit:hover:not(:disabled) { 
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(162, 155, 254, 0.3);
        }

        .btn-submit:disabled { 
            background: rgba(255, 255, 255, 0.1);
            color: rgba(255, 255, 255, 0.3);
            cursor: not-allowed;
            box-shadow: none;
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

        /* Clean Login Link */
        .login-link { 
            display: inline-block;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none; 
            font-size: 13px;
            font-weight: 400;
            transition: color 0.3s ease;
        }

        .login-link span {
            color: #74b9ff;
            font-weight: 500;
        }

        .login-link:hover span { 
            text-decoration: underline;
            color: #a29bfe; 
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Daftar Akun</h2>
        
        <?php echo $pesan; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Pilih username" />
            </div>

            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="email@contoh.com" />
            </div>
            
            <div class="input-group">
                <label>Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required placeholder="Buat password kuat" onkeyup="checkPasswordStrength()" />
                    <button type="button" class="toggle-password" onclick="toggleVisibility('password', 'eye-icon-pass')" id="eye-icon-pass">🙉</button>
                </div>
                
                <div class="strength-meter" id="strengthBox">
                    <span class="strength-badge" id="strengthBadge">KOSONG</span>
                    <div class="recommendation" id="recommendation">Min. 8 Karakter, Huruf Besar, Angka, & Simbol (!@#$).</div>
                </div>
            </div>

            <div class="input-group">
                <label>Konfirmasi Password</label>
                <div class="password-wrapper">
                    <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ketik ulang password" />
                    <button type="button" class="toggle-password" onclick="toggleVisibility('confirm_password', 'eye-icon-confirm')" id="eye-icon-confirm">🙉</button>
                </div>
            </div>

            <button type="submit" name="register" id="btn-submit" class="btn-submit" disabled>Daftar Sekarang</button>
        </form>

        <a href="login.php" class="login-link">Sudah punya akun? <span>Login disini</span></a>
    </div>

    <script>
        function checkPasswordStrength() {
            const val = document.getElementById("password").value;
            const strengthBadge = document.getElementById("strengthBadge");
            const strengthBox = document.getElementById("strengthBox");
            const recommendation = document.getElementById("recommendation");
            const btnSubmit = document.getElementById("btn-submit");

            let strength = 0;

            if (val.length >= 8) strength += 1; 
            if (val.match(/[A-Z]/)) strength += 1; 
            if (val.match(/[0-9]/)) strength += 1; 
            if (val.match(/[^a-zA-Z0-9]/)) strength += 1; 

            const syaratMutlak = "Wajib: Min. 8 Karakter, Huruf Besar, Angka, & Simbol.";

            // Warna disesuaikan dengan tema Dark Mode Gen-Z
            if (val.length === 0) {
                strengthBadge.textContent = "KOSONG";
                strengthBadge.style.backgroundColor = "rgba(255, 255, 255, 0.2)";
                strengthBox.style.borderLeftColor = "rgba(255, 255, 255, 0.2)";
                recommendation.textContent = syaratMutlak;
                btnSubmit.disabled = true;
            } else if (strength < 4) {
                strengthBadge.textContent = "BELUM AMAN";
                strengthBadge.style.backgroundColor = "#ff6b81"; // Pastel Red
                strengthBox.style.borderLeftColor = "#ff6b81";
                recommendation.innerHTML = "<b>Belum memenuhi syarat.</b> <br>" + syaratMutlak;
                btnSubmit.disabled = true; 
            } else if (strength === 4) {
                strengthBadge.textContent = "SANGAT KUAT";
                strengthBadge.style.backgroundColor = "#00cec9"; // Modern Cyan/Teal
                strengthBox.style.borderLeftColor = "#00cec9";
                recommendation.textContent = "Mantap! Password sudah aman.";
                btnSubmit.disabled = false; 
            }
        }

        // Logic toggle di-restore kembali pakai teks/emoji
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