<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
include 'koneksi.php';

$username = $_SESSION['username'];
$pesan = "";

$target_dir = "profiles/";
$foto_profil = $target_dir . $username . ".jpg";
$foto_default = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=3b71ca&color=fff&size=200";

// 1. LOGIKA HAPUS FOTO (PHP ASLI)
if (isset($_POST['hapus_foto'])) {
    if (file_exists($foto_profil)) {
        unlink($foto_profil); 
        $pesan = "<div class='success-msg'><i class='fa-solid fa-circle-check'></i> Foto profil berhasil dihapus! Kembali ke avatar default.</div>";
    } else {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Kamu belum mengatur foto profil kustom.</div>";
    }
}

// 2. LOGIKA UPLOAD FOTO (PHP ASLI)
if (isset($_POST['upload_foto'])) {
    $tipe_file = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
    $ukuran_file = $_FILES["fileToUpload"]["size"];
    
    if($tipe_file != "jpg" && $tipe_file != "png" && $tipe_file != "jpeg") {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Maaf, hanya file JPG, JPEG & PNG yang diizinkan.</div>";
    } elseif ($ukuran_file > 2000000) { 
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Maaf, ukuran foto terlalu besar (Maksimal 2MB).</div>";
    } else {
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); } 
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $foto_profil)) {
            $pesan = "<div class='success-msg'><i class='fa-solid fa-circle-check'></i> Foto profil berhasil diperbarui!</div>";
        } else {
            $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Terjadi kesalahan saat mengunggah foto.</div>";
        }
    }
}

$img_src = file_exists($foto_profil) ? $foto_profil . "?t=" . time() : $foto_default;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Velohertz</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@800&family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3b71ca;
            --primary-dark: #2a5298;
            --app-bg: linear-gradient(135deg, #a1c4fd 0%, #c2e9fb 100%);
            --glass-bg: rgba(255, 255, 255, 0.7);
            --glass-border: rgba(255, 255, 255, 0.5);
            --text-main: #1e3c72;
            --text-muted: #64748b;
            --emerald: #10b981;
        }
        
        body { 
            margin: 0; 
            font-family: 'Poppins', sans-serif; 
            background: var(--app-bg); 
            background-attachment: fixed; 
            color: var(--text-main); 
            display: flex; 
            overflow-x: hidden; 
        }

        /* --- SIDEBAR (SINKRON 260PX) --- */
        .sidebar { 
            width: 260px; background: rgba(255, 255, 255, 0.4); backdrop-filter: blur(20px); 
            padding: 32px 20px; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; 
            border-right: 1px solid var(--glass-border); display: flex; flex-direction: column; 
            transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar.hidden { transform: translateX(-100%); }
        .sidebar h2 { font-family: 'Outfit', sans-serif; color: var(--primary); margin: 0 0 40px 0; font-size: 28px; font-weight: 800; text-align: center; line-height: 45px; }
        .sidebar a { display: flex; align-items: center; color: var(--text-main); text-decoration: none; margin: 8px 0; font-weight: 600; transition: 0.3s; padding: 12px 15px; border-radius: 16px; }
        .sidebar a i { margin-right: 15px; font-size: 18px; opacity: 0.7; }
        .sidebar a:hover, .sidebar a.active { background: var(--glass-bg); color: var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .logout-btn { margin-top: auto; color: #ff4757 !important; background: rgba(255, 71, 87, 0.1) !important;}

        /* --- HAMBURGER (SINKRON POSISI) --- */
        .hamburger-menu {
            position: fixed; top: 32px; left: 25px; z-index: 1100;
            background: var(--primary); color: white; border: none;
            width: 45px; height: 45px; border-radius: 12px; cursor: pointer;
            box-shadow: 0 4px 15px rgba(59, 113, 202, 0.3);
            display: flex; align-items: center; justify-content: center; font-size: 20px;
        }

        /* --- MAIN CONTENT (ANTI LENGKET) --- */
        .main-content { 
            margin-left: 260px; padding: 40px 60px; width: 100%;
            transition: all 0.4s ease; box-sizing: border-box; min-height: 100vh;
            display: flex; flex-direction: column; align-items: center; justify-content: center;
        }
        .main-content.full-width { margin-left: 0; padding-left: 90px; }

        /* --- PROFILE CARD --- */
        .profile-card { 
            background: var(--glass-bg); backdrop-filter: blur(15px); 
            border-radius: 30px; border: 1px solid var(--glass-border); 
            box-shadow: 0 20px 40px rgba(0,0,0,0.05); padding: 50px; 
            text-align: center; width: 100%; max-width: 500px; 
        }
        .profile-pic { 
            width: 160px; height: 160px; border-radius: 50%; object-fit: cover; 
            border: 6px solid #fff; box-shadow: 0 10px 25px rgba(0,0,0,0.1); 
            margin-bottom: 25px; background: #fff;
        }
        .profile-card h1 { font-family: 'Outfit', sans-serif; margin: 0 0 5px 0; font-size: 32px; color: var(--text-main); font-weight: 800; }
        .profile-card p.subtitle { margin: 0 0 30px 0; color: var(--text-muted); font-size: 14px; font-weight: 700; letter-spacing: 1.5px; text-transform: uppercase; }

        .btn-upload { 
            background: var(--primary); color: white; border: none; padding: 14px 30px; 
            border-radius: 16px; font-weight: 600; cursor: pointer; transition: 0.3s; 
            font-family: inherit; font-size: 14px; width: 100%; margin-bottom: 12px;
        }
        .btn-upload:hover { background: var(--primary-dark); transform: translateY(-2px); }
        
        .btn-delete { 
            background: rgba(255, 71, 87, 0.1); color: #ff4757; border: 1px solid rgba(255, 71, 87, 0.2); 
            padding: 14px 30px; border-radius: 16px; font-weight: 600; cursor: pointer; 
            transition: 0.3s; font-family: inherit; font-size: 14px; width: 100%;
        }
        .btn-delete:hover { background: #ff4757; color: white; }

        .custom-file-input { margin-bottom: 20px; width: 100%; }
        input[type="file"] { 
            background: rgba(255,255,255,0.5); padding: 12px; border-radius: 12px; 
            border: 1px solid var(--glass-border); width: 100%; font-size: 13px; color: var(--text-muted);
        }

        /* --- ALERTS --- */
        .success-msg { background: #e8fff3; color: var(--emerald); padding: 15px; border-radius: 16px; margin-bottom: 25px; border: 1px solid #c2f3d6; font-size: 14px; font-weight: 500; }
        .error-msg { background: #ffe8e8; color: #ff4757; padding: 15px; border-radius: 16px; margin-bottom: 25px; border: 1px solid #ffcccc; font-size: 14px; font-weight: 500; }

        @media (max-width: 768px) {
            .sidebar { width: 260px; box-shadow: 5px 0 15px rgba(0,0,0,0.3); }
            .main-content, .main-content.full-width { margin-left: 0 !important; padding: 80px 20px 120px 20px !important; width: 100vw !important; box-sizing: border-box; }
            .hamburger-menu { top: 15px; left: 15px; width: 40px; height: 40px; }
            .profile-card { padding: 30px; }
        }
    </style>
</head>
<body>

    <button class="hamburger-menu" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <h2>Velohertz</h2>
        <a href="index.php"><i class="fa-solid fa-house"></i> Beranda</a>
        <a href="search.php"><i class="fa-solid fa-magnifying-glass"></i> Cari</a>
        <a href="library.php"><i class="fa-solid fa-book"></i> Koleksi</a>
        <a href="profile.php" class="active"><i class="fa-solid fa-user"></i> Profil</a>
        <?php if($username == 'admin'): ?>
            <a href="admin.php" style="color: var(--primary);"><i class="fa-solid fa-shield-halved"></i> Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin mau keluar?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="profile-card">
            <?php echo $pesan; ?>
            
            <img src="<?php echo $img_src; ?>" alt="Foto Profil" class="profile-pic">
            <h1><?php echo htmlspecialchars($username); ?></h1>
            <p class="subtitle">Member Velohertz</p>

            <form action="" method="POST" enctype="multipart/form-data" style="margin-bottom: 15px; padding-bottom: 20px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <div class="custom-file-input">
                    <input type="file" name="fileToUpload" id="fileToUpload" accept="image/jpeg, image/png, image/jpg" required>
                </div>
                <button type="submit" name="upload_foto" class="btn-upload"><i class="fa-solid fa-cloud-arrow-up"></i> Ganti Foto Profil</button>
            </form>

            <form action="" method="POST">
                <button type="submit" name="hapus_foto" class="btn-delete" onclick="return confirm('Hapus foto profil kamu?');"><i class="fa-solid fa-trash"></i> Hapus Foto Saat Ini</button>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            const state = localStorage.getItem('sidebarState');
            if (window.innerWidth <= 768) {
                sidebar.classList.add('hidden');
                mainContent.classList.add('full-width');
            } else if (state === 'hidden') {
                sidebar.classList.add('hidden');
                mainContent.classList.add('full-width');
            }
        });

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const mainContent = document.getElementById('mainContent');
            sidebar.classList.toggle('hidden');
            mainContent.classList.toggle('full-width');
            localStorage.setItem('sidebarState', sidebar.classList.contains('hidden') ? 'hidden' : 'visible');
        }
    </script>
</body>
</html>