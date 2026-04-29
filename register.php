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
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@800&family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        
        body { 
            min-height: 100vh; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%); 
        }

        .container { 
            background: rgba(255, 255, 255, 0.85); 
            backdrop-filter: blur(12px); 
            padding: 40px; 
            border-radius: 24px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
            width: 100%;
            max-width: 420px; 
            text-align: center; 
            border: 1px solid rgba(255,255,255,0.6); 
        }

        h2 { 
            font-family: 'Outfit', sans-serif;
            font-weight: 800;
            font-size: 32px; 
            margin-bottom: 25px; 
            background: linear-gradient(135deg, #3b71ca 0%, #74b9ff 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .input-group { margin-bottom: 18px; text-align: left; }
        .input-group label { display: block; font-size: 13px; margin-bottom: 6px; font-weight: 600; color: #475569; }
        
        .input-group input { 
            width: 100%; 
            padding: 14px; 
            border-radius: 16px; 
            border: 2px solid #e1e5ee; 
            background: #f8f9fa; 
            outline: none; 
            transition: 0.3s; 
            font-size: 14px; 
        }

        .input-group input:focus { 
            border-color: #3b71ca; 
            background: #ffffff; 
            box-shadow: 0 0 0 4px rgba(59, 113, 202, 0.1);
        }
        
        .password-wrapper { position: relative; }
        
        .toggle-password { 
            position: absolute; 
            right: 15px; 
            top: 50%; 
            transform: translateY(-50%); 
            background: none; 
            border: none; 
            font-size: 20px; 
            cursor: pointer; 
            padding: 0; 
        }
        
        /* Password Meter Style */
        .strength-meter { 
            margin-top: 10px; 
            padding: 12px; 
            background: white; 
            border-left: 4px solid #cbd5e1; 
            border-radius: 12px; 
            font-size: 12px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.02);
        }
        
        .strength-badge { 
            display: inline-block; 
            padding: 2px 8px; 
            border-radius: 6px; 
            font-size: 10px; 
            font-weight: 700; 
            color: white; 
            margin-bottom: 6px; 
            background: #94a3b8;
            text-transform: uppercase;
        }

        .recommendation { color: #64748b; line-height: 1.5; }

        /* Tombol Daftar: Gradien Senada Login */
        .btn-submit { 
            width: 100%; 
            padding: 15px; 
            background: linear-gradient(135deg, #3b71ca 0%, #74b9ff 100%); 
            color: white; 
            border: none; 
            border-radius: 16px; 
            font-weight: 600; 
            font-size: 16px; 
            cursor: pointer; 
            transition: transform 0.2s, box-shadow 0.2s; 
            margin-top: 10px; 
        }

        .btn-submit:hover:not(:disabled) { 
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(59, 113, 202, 0.3);
        }

        .btn-submit:disabled { background: #cbd5e1; cursor: not-allowed; }
        
        .error-msg { 
            background: #ffe8e8; 
            color: #ff4757; 
            font-size: 13px; 
            padding: 12px; 
            margin-bottom: 20px; 
            border-radius: 12px; 
            border: 1px solid #ffcccc; 
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        /* Link Kembali: Hijau Emerald Cerah */
        .login-link { 
            display: inline-block; 
            font-size: 14px; 
            color: #10b981; 
            text-decoration: underline; 
            margin-top: 20px; 
            font-weight: 500; 
        }

        .login-link:hover { color: #059669; }
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

        <a href="login.php" class="login-link">Sudah punya akun? Login disini</a>
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

            if (val.length === 0) {
                strengthBadge.textContent = "KOSONG";
                strengthBadge.style.backgroundColor = "#94a3b8";
                strengthBox.style.borderLeftColor = "#cbd5e1";
                recommendation.textContent = syaratMutlak;
                btnSubmit.disabled = true;
            } else if (strength < 4) {
                strengthBadge.textContent = "BELUM AMAN";
                strengthBadge.style.backgroundColor = "#ff4757";
                strengthBox.style.borderLeftColor = "#ff4757";
                recommendation.innerHTML = "<b>Belum memenuhi syarat.</b> " + syaratMutlak;
                btnSubmit.disabled = true; 
            } else if (strength === 4) {
                strengthBadge.textContent = "SANGAT KUAT";
                strengthBadge.style.backgroundColor = "#10b981";
                strengthBox.style.borderLeftColor = "#10b981";
                recommendation.textContent = "Mantap! Password sudah aman.";
                btnSubmit.disabled = false; 
            }
        }

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