<?php
session_start();
include 'koneksi.php';

$pesan = "";

if (isset($_POST['register'])) {
    $uid = uniqid('usr_'); 
    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    // 1. CEK DUPLIKAT EMAIL & USERNAME DI SERVER
    $stmt_cek = $conn->prepare("SELECT uid FROM User WHERE uname = ? OR email = ?");
    $stmt_cek->bind_param("ss", $username, $email);
    $stmt_cek->execute();
    $stmt_cek->store_result();

    if ($stmt_cek->num_rows > 0) {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Username atau Email sudah terdaftar!</div>";
    } else {
        // 2. BLACKLIST PASSWORD PASARAN
        $blacklist = ['password', '123456', '12345678', 'qwerty', 'admin123', strtolower($username)];
        
        // 3. VALIDASI REGEX SERVER-SIDE
        $uppercase = preg_match('@[A-Z]@', $password);
        $lowercase = preg_match('@[a-z]@', $password);
        $number    = preg_match('@[0-9]@', $password);
        $special   = preg_match('@[^\w]@', $password);

        if (in_array(strtolower($password), $blacklist)) {
            $pesan = "<div class='error-msg'><i class='fa-solid fa-shield'></i> Password terlalu umum/mudah ditebak! Jangan gunakan kata seperti 'password' atau username Anda.</div>";
        } elseif (!$uppercase || !$lowercase || !$number || !$special || strlen($password) < 8) {
            $pesan = "<div class='error-msg'><i class='fa-solid fa-shield'></i> Pendaftaran Ditolak: Password gagal melewati validasi keamanan server (Wajib 8 karakter, huruf besar, huruf kecil, angka, simbol).</div>";
        } else {
            // 4. BCRYPT HASHING (Standar Industri)
            // 4. BCRYPT HASHING (Standar Industri)
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            // Simpan ke DB (TIDAK PERLU MASUKIN UID KARENA SUDAH AUTO-INCREMENT DARI DATABASE)
            $stmt_insert = $conn->prepare("INSERT INTO user (uname, email, upassword) VALUES (?, ?, ?)");
            $stmt_insert->bind_param("sss", $username, $email, $hashed_password);
            
            if ($stmt_insert->execute()) {
                $_SESSION['success_msg'] = "Registrasi Berhasil! Silakan Login.";
                header("Location: login.php");
                exit();
            } else {
                $pesan = "<div class='error-msg'>Gagal: " . $conn->error . "</div>";
            }
        }
    }
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar - Velohertz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { margin: 0; font-family: 'Inter', sans-serif; background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%); display: flex; justify-content: center; align-items: center; min-height: 100vh; color: #0f172a; }
        .container { background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(15px); padding: 40px; border-radius: 24px; box-shadow: 0 15px 35px rgba(0,0,0,0.05); width: 360px; text-align: center; border: 1px solid rgba(255,255,255,0.5); border-top: 5px solid #0f52ba; }
        .logo { color: #0f52ba; font-style: italic; font-size: 32px; font-weight: 800; margin-bottom: 5px; letter-spacing: -1px; }
        h2 { font-size: 20px; margin-bottom: 25px; margin-top: 0; color: #475569; }
        .input-group { margin-bottom: 15px; text-align: left; position: relative; }
        .input-group label { display: block; font-size: 13px; margin-bottom: 5px; font-weight: 600; }
        .input-group input { width: 100%; padding: 12px 15px; border-radius: 12px; border: 1px solid rgba(255,255,255,0.5); background: rgba(255, 255, 255, 0.7); box-sizing: border-box; outline: none; transition: 0.3s; font-size: 14px; font-family: inherit; }
        .input-group input:focus { border-color: #0f52ba; background: #ffffff; }
        .password-wrapper { position: relative; }
        .toggle-password { position: absolute; right: 15px; top: 50%; transform: translateY(-50%); background: none; border: none; font-size: 18px; cursor: pointer; color: #475569; padding: 0; }
        
        .strength-meter { margin-top: 10px; padding: 12px; background: rgba(255,255,255,0.7); border-left: 4px solid #535353; border-radius: 8px; text-align: left;}
        .strength-badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 11px; font-weight: 700; color: white; margin-bottom: 5px; background: #535353;}
        .recommendation { font-size: 12px; color: #475569; line-height: 1.4; }
        
        .btn-submit { width: 100%; padding: 14px; background-color: #0f52ba; color: white; border: none; border-radius: 12px; font-weight: 600; font-size: 16px; cursor: pointer; transition: 0.3s; margin-top: 15px; }
        .btn-submit:hover { background-color: #0c4399; }
        .btn-submit:disabled { background-color: #94a3b8; cursor: not-allowed; }
        .footer-link { margin-top: 20px; font-size: 14px; color: #475569; }
        .footer-link a { color: #0f52ba; font-weight: 600; text-decoration: none; }
        .error-msg { background: rgba(254,226,226,0.9); color: #e53e3e; font-size: 13px; padding: 12px; margin-bottom: 20px; border-radius: 8px; border: 1px solid #fecaca; text-align: left;}
    </style>
</head>
<body>
    <div class="container">
        <div class="logo">Velohertz</div>
        <h2>Buat Akun Kunci Ganda</h2>
        
        <?php echo $pesan; ?>

        <form action="" method="POST">
            <div class="input-group">
                <label>Email</label>
                <input type="email" name="email" required placeholder="email@contoh.com" />
            </div>

            <div class="input-group">
                <label>Username</label>
                <input type="text" name="username" required placeholder="Pilih username" />
            </div>
            
            <div class="input-group">
                <label>Password Master</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password" required placeholder="Buat password kuat" onkeyup="checkPasswordStrength()" />
                    <button type="button" class="toggle-password" onclick="toggleVisibility('password', 'eye-icon-reg')" id="eye-icon-reg">🙉</button>
                </div>
                
                <div class="strength-meter" id="strengthBox">
                    <span class="strength-badge" id="strengthBadge">KOSONG</span>
                    <div class="recommendation" id="recommendation">Wajib: 8 Karakter, Huruf Besar, Angka, dan Simbol (!@#$).</div>
                </div>
            </div>

            <button type="submit" name="register" id="btn-submit" class="btn-submit" disabled>Daftar Sekarang</button>
        </form>

        <div class="footer-link">Sudah punya akun? <a href="login.php">Masuk di sini</a></div>
    </div>

    <script>
        // BLOKIR TOMBOL SUBMIT JIKA PASSWORD LEMAH (Tanpa ngasih Hint spesifik!)
        function checkPasswordStrength() {
            const val = document.getElementById("password").value;
            const strengthBadge = document.getElementById("strengthBadge");
            const strengthBox = document.getElementById("strengthBox");
            const recommendation = document.getElementById("recommendation");
            const btnSubmit = document.getElementById("btn-submit");

            let strength = 0;

            // Cek kriteria secara rahasia (tidak dimasukkan ke array 'missing' lagi)
            if (val.length >= 8) strength += 1; 
            if (val.match(/[A-Z]/)) strength += 1; 
            if (val.match(/[0-9]/)) strength += 1; 
            if (val.match(/[^a-zA-Z0-9]/)) strength += 1; 

            const syaratMutlak = "Syarat wajib: Min. 8 Karakter, Huruf Besar, Angka, dan Simbol (!@#$).";

            if (val.length === 0) {
                strengthBadge.textContent = "KOSONG";
                strengthBadge.style.backgroundColor = "#535353";
                strengthBox.style.borderLeftColor = "#535353";
                recommendation.textContent = syaratMutlak;
                recommendation.style.color = "#475569";
                btnSubmit.disabled = true;
            } else if (strength < 4) {
                // Kalau ada 1 aja yang kurang, langsung pukul rata dibilang "TIDAK AMAN" tanpa kasih tahu kurang apa
                strengthBadge.textContent = "BELUM AMAN";
                strengthBadge.style.backgroundColor = "#e91429";
                strengthBox.style.borderLeftColor = "#e91429";
                recommendation.innerHTML = "<b>Password ditolak.</b> Pastikan memenuhi semua " + syaratMutlak;
                recommendation.style.color = "#e91429";
                btnSubmit.disabled = true; // Kunci tombol
            } else if (strength === 4) {
                strengthBadge.textContent = "SANGAT KUAT";
                strengthBadge.style.backgroundColor = "#10b981";
                strengthBox.style.borderLeftColor = "#10b981";
                recommendation.textContent = "Sempurna! Password memenuhi standar keamanan.";
                recommendation.style.color = "#10b981";
                btnSubmit.disabled = false; // BUKA KUNCI TOMBOL!
            }
        }

        function toggleVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === "password") { input.type = "text"; icon.textContent = "🙈"; } 
            else { input.type = "password"; icon.textContent = "🙉"; }
        }
    </script>
</body>
</html>