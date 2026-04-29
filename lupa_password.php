<?php
session_start();
include 'koneksi.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; 

$pesan = "";
$step = isset($_SESSION['reset_step']) ? $_SESSION['reset_step'] : 1;

// ==========================================
// TAHAP 1: REQUEST OTP
// ==========================================
if (isset($_POST['kirim_otp'])) {
    $email = trim($_POST['email']);
    
    $stmt = $conn->prepare("SELECT uid FROM user WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $otp = rand(100000, 999999);
        $_SESSION['reset_email'] = $email;
        $_SESSION['reset_otp'] = $otp;
        
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'jessileoo64@gmail.com';       // <-- Ganti email aslimu
            $mail->Password   = 'yakqjewmnaciynob';   // <-- Ganti 16 huruf App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            $mail->setFrom('no-reply@velohertz.com', 'Velohertz Security');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Kode OTP Reset Password Velohertz';
            $mail->Body    = "<h3>Permintaan Reset Password</h3>
                              <p>Kode OTP Anda adalah: <b style='font-size: 18px; letter-spacing: 2px;'>$otp</b></p>
                              <p>Jangan berikan kode ini kepada siapapun demi keamanan akun Anda.</p>";

            $mail->send();
            
            $_SESSION['reset_step'] = 2;
            $step = 2;
            $pesan = "<div class='success-msg'><i class='fa-solid fa-envelope'></i> Kode OTP telah dikirim ke email Anda!</div>";
        } catch (Exception $e) {
            $pesan = "<div class='error-msg'>Gagal mengirim email. Error: {$mail->ErrorInfo}</div>";
        }
    } else {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Email tidak ditemukan di sistem!</div>";
    }
}

// ==========================================
// TAHAP 2: VERIFIKASI OTP
// ==========================================
if (isset($_POST['verifikasi_otp'])) {
    $input_otp = trim($_POST['otp']);
    
    if ($input_otp == $_SESSION['reset_otp']) {
        $_SESSION['reset_step'] = 3;
        $step = 3;
        $pesan = "<div class='success-msg'><i class='fa-solid fa-unlock'></i> OTP Valid! Silakan buat password baru.</div>";
    } else {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-xmark'></i> Kode OTP salah!</div>";
    }
}

// ==========================================
// TAHAP 3: UPDATE PASSWORD BARU (DENGAN KONFIRMASI)
// ==========================================
if (isset($_POST['reset_password'])) {
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];
    $email = $_SESSION['reset_email'];

    // 1. Cek apakah password dan konfirmasi sama
    if ($new_pass !== $confirm_pass) {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Konfirmasi password tidak cocok!</div>";
    } else {
        // 2. Validasi Regex Server (Anti bypass JS)
        $uppercase = preg_match('@[A-Z]@', $new_pass);
        $lowercase = preg_match('@[a-z]@', $new_pass);
        $number    = preg_match('@[0-9]@', $new_pass);
        $special   = preg_match('@[^\w]@', $new_pass);

        if (!$uppercase || !$lowercase || !$number || !$special || strlen($new_pass) < 8) {
            $pesan = "<div class='error-msg'><i class='fa-solid fa-shield'></i> Password Terlalu Lemah atau gagal memenuhi standar keamanan server!</div>";
        } else {
            // 3. Hashing & Update DB
            $hashed_password = password_hash($new_pass, PASSWORD_BCRYPT);
            
            $stmt_update = $conn->prepare("UPDATE user SET upassword = ? WHERE email = ?");
            $stmt_update->bind_param("ss", $hashed_password, $email);
            
            if ($stmt_update->execute()) {
                session_destroy();
                echo "<script>alert('Password berhasil diubah! Silakan Login.'); window.location='login.php';</script>";
                exit();
            } else {
                $pesan = "<div class='error-msg'>Gagal update database.</div>";
            }
        }
    }
}

// Batal / Reset Ulang
if (isset($_GET['cancel'])) {
    session_destroy();
    header("Location: lupa_password.php");
    exit();
}
?>

<!doctype html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Velohertz</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #3b71ca;
            --primary-dark: #2a5298;
            --emerald: #10b981;
            --danger: #ff4757;
            --text-main: #1e3c72;
            --text-muted: #64748b;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%); 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            min-height: 100vh; 
            color: var(--text-main); 
        }

        .container { 
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(20px); 
            padding: 40px; 
            border-radius: 30px; 
            box-shadow: 0 20px 40px rgba(0,0,0,0.05); 
            width: 100%; 
            max-width: 400px; 
            text-align: center; 
            border: 1px solid rgba(255,255,255,0.5); 
        }

        h2 { 
            font-family: 'Outfit', sans-serif;
            font-size: 32px; 
            margin-bottom: 25px; 
            margin-top: 0; 
            color: var(--primary); 
            letter-spacing: -1px;
        }

        .subtitle { font-size: 14px; color: var(--text-muted); margin-bottom: 25px; line-height: 1.6; }

        .input-group { margin-bottom: 20px; text-align: left; position: relative; }
        .input-group label { display: block; font-size: 13px; margin-bottom: 8px; font-weight: 600; color: var(--text-main); }
        
        .input-group input { 
            width: 100%; padding: 14px 18px; border-radius: 16px; 
            border: 2px solid #e1e5ee; background: #fff; 
            outline: none; transition: 0.3s; font-size: 14px; font-family: inherit; 
        }
        .input-group input:focus { 
            border-color: var(--primary); 
            box-shadow: 0 0 0 4px rgba(59, 113, 202, 0.1); 
        }
        
        .password-wrapper { position: relative; }
        .toggle-password { 
            position: absolute; right: 15px; top: 50%; transform: translateY(-50%); 
            background: none; border: none; font-size: 18px; cursor: pointer; color: var(--text-muted); 
        }
        
        /* CSS INDIKATOR PASSWORD */
        .strength-meter { 
            margin-top: 12px; padding: 15px; background: rgba(255,255,255,0.8); 
            border-left: 4px solid #94a3b8; border-radius: 12px; text-align: left;
        }
        .strength-badge { 
            display: inline-block; padding: 4px 10px; border-radius: 8px; 
            font-size: 11px; font-weight: 700; color: white; margin-bottom: 8px; 
            background: #94a3b8; letter-spacing: 1px;
        }
        .recommendation { font-size: 12px; color: var(--text-muted); line-height: 1.5; }

        .btn-submit { 
            width: 100%; padding: 15px; background: var(--primary); 
            color: white; border: none; border-radius: 16px; 
            font-weight: 600; font-size: 15px; cursor: pointer; 
            transition: 0.3s; margin-top: 10px; font-family: inherit;
        }
        .btn-submit:hover:not(:disabled) { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 10px 20px rgba(59, 113, 202, 0.2); }
        .btn-submit:disabled { background: #cbd5e1; cursor: not-allowed; }
        
        .btn-cancel { 
            display: inline-block; font-size: 14px; color: var(--text-muted); 
            text-decoration: underline; font-weight: 600; margin-top: 20px; transition: 0.3s;
        }
        .btn-cancel:hover { color: var(--danger); }

        .error-msg { background: #ffe8e8; color: var(--danger); font-size: 13px; padding: 15px; margin-bottom: 20px; border-radius: 16px; border: 1px solid #ffcccc; text-align: left; display: flex; align-items: center; gap: 10px; font-weight: 500;}
        .success-msg { background: #e8fff3; color: var(--emerald); font-size: 13px; padding: 15px; margin-bottom: 20px; border-radius: 16px; border: 1px solid #c2f3d6; text-align: left; display: flex; align-items: center; gap: 10px; font-weight: 500;}
    </style>
</head>
<body>
    <div class="container">
        <h2><i class="fa-solid fa-shield-halved"></i> Pemulihan</h2>
        
        <?php echo $pesan; ?>

        <?php if ($step == 1): ?>
            <p class="subtitle">Masukkan email akun Anda. Kami akan mengirimkan 6-digit OTP ke email tersebut.</p>
            <form action="" method="POST">
                <div class="input-group">
                    <label>Email Terdaftar</label>
                    <input type="email" name="email" required placeholder="email@contoh.com" />
                </div>
                <button type="submit" name="kirim_otp" class="btn-submit">Kirim OTP Via Email</button>
            </form>
            <a href="login.php" class="btn-cancel">Kembali ke Login</a>

        <?php elseif ($step == 2): ?>
            <p class="subtitle">Masukkan OTP yang telah dikirim ke <br><strong style="color: var(--primary);"><?php echo $_SESSION['reset_email']; ?></strong></p>
            <form action="" method="POST">
                <div class="input-group">
                    <label>Kode OTP (6 Angka)</label>
                    <input type="number" name="otp" required placeholder="Contoh: 123456" style="text-align: center; font-size: 24px; letter-spacing: 8px; font-weight: 600;" />
                </div>
                <button type="submit" name="verifikasi_otp" class="btn-submit">Verifikasi OTP</button>
            </form>
            <a href="?cancel=true" class="btn-cancel">Ganti Email</a>

        <?php elseif ($step == 3): ?>
            <p class="subtitle">Buat password baru yang kuat dan aman.</p>
            <form action="" method="POST">
                
                <div class="input-group">
                    <label>Password Baru</label>
                    <div class="password-wrapper">
                        <input type="password" id="new_password" name="new_password" required placeholder="Buat password kuat" onkeyup="checkPasswordStrength()" />
                        <button type="button" class="toggle-password" onclick="toggleVisibility('new_password', 'eye-icon-new')" id="eye-icon-new">🙉</button>
                    </div>
                    
                    <div class="strength-meter" id="strengthBox">
                        <span class="strength-badge" id="strengthBadge">KOSONG</span>
                        <div class="recommendation" id="recommendation">Syarat wajib: Min. 8 Karakter, Huruf Besar, Angka, dan Simbol (!@#$).</div>
                    </div>
                </div>

                <div class="input-group">
                    <label>Konfirmasi Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="confirm_password" name="confirm_password" required placeholder="Ketik ulang password" />
                        <button type="button" class="toggle-password" onclick="toggleVisibility('confirm_password', 'eye-icon-confirm')" id="eye-icon-confirm">🙉</button>
                    </div>
                </div>

                <button type="submit" name="reset_password" id="btn-submit" class="btn-submit" disabled>Simpan Password Baru</button>
            </form>
            <a href="?cancel=true" class="btn-cancel" style="color: var(--danger);">Batalkan Proses</a>
        <?php endif; ?>
    </div>

    <script>
        // JS RAHASIA (Sama Persis Kayak Register)
        function checkPasswordStrength() {
            const val = document.getElementById("new_password").value;
            const strengthBadge = document.getElementById("strengthBadge");
            const strengthBox = document.getElementById("strengthBox");
            const recommendation = document.getElementById("recommendation");
            const btnSubmit = document.getElementById("btn-submit");

            let strength = 0;

            if (val.length >= 8) strength += 1; 
            if (val.match(/[A-Z]/)) strength += 1; 
            if (val.match(/[0-9]/)) strength += 1; 
            if (val.match(/[^a-zA-Z0-9]/)) strength += 1; 

            const syaratMutlak = "Syarat wajib: Min. 8 Karakter, Huruf Besar, Angka, dan Simbol (!@#$).";

            if (val.length === 0) {
                strengthBadge.textContent = "KOSONG";
                strengthBadge.style.backgroundColor = "#94a3b8";
                strengthBox.style.borderLeftColor = "#94a3b8";
                recommendation.textContent = syaratMutlak;
                recommendation.style.color = "#64748b";
                btnSubmit.disabled = true;
            } else if (strength < 4) {
                strengthBadge.textContent = "BELUM AMAN";
                strengthBadge.style.backgroundColor = "#ff4757";
                strengthBox.style.borderLeftColor = "#ff4757";
                recommendation.innerHTML = "<b>Password ditolak.</b> Pastikan memenuhi semua " + syaratMutlak;
                recommendation.style.color = "#ff4757";
                btnSubmit.disabled = true; 
            } else if (strength === 4) {
                strengthBadge.textContent = "SANGAT KUAT";
                strengthBadge.style.backgroundColor = "#10b981";
                strengthBox.style.borderLeftColor = "#10b981";
                recommendation.textContent = "Sempurna! Password memenuhi standar keamanan.";
                recommendation.style.color = "#10b981";
                btnSubmit.disabled = false; 
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