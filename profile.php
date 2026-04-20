<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
include 'koneksi.php';

$username = $_SESSION['username'];
$pesan = "";

$target_dir = "profiles/";
$foto_profil = $target_dir . $username . ".jpg";
$foto_default = "https://ui-avatars.com/api/?name=" . urlencode($username) . "&background=0f52ba&color=fff&size=200";

// 1. LOGIKA HAPUS FOTO
if (isset($_POST['hapus_foto'])) {
    if (file_exists($foto_profil)) {
        unlink($foto_profil); 
        $pesan = "<div style='color: #10b981; background: rgba(209,250,229,0.8); padding: 15px; border-radius: 12px; border: 1px solid #a7f3d0; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-check'></i> Foto profil berhasil dihapus! Kembali ke avatar default.</div>";
    } else {
        $pesan = "<div style='color: #e53e3e; background: rgba(254,226,226,0.8); padding: 15px; border-radius: 12px; border: 1px solid #fecaca; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> Kamu belum mengatur foto profil kustom.</div>";
    }
}

// 2. LOGIKA UPLOAD FOTO
if (isset($_POST['upload_foto'])) {
    $tipe_file = strtolower(pathinfo($_FILES["fileToUpload"]["name"], PATHINFO_EXTENSION));
    $ukuran_file = $_FILES["fileToUpload"]["size"];
    
    if($tipe_file != "jpg" && $tipe_file != "png" && $tipe_file != "jpeg") {
        $pesan = "<div style='color: #e53e3e; background: rgba(254,226,226,0.8); padding: 15px; border-radius: 12px; border: 1px solid #fecaca; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> Maaf, hanya file JPG, JPEG & PNG yang diizinkan.</div>";
    } elseif ($ukuran_file > 2000000) { 
        $pesan = "<div style='color: #e53e3e; background: rgba(254,226,226,0.8); padding: 15px; border-radius: 12px; border: 1px solid #fecaca; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> Maaf, ukuran foto terlalu besar (Maksimal 2MB).</div>";
    } else {
        if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); } 
        
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $foto_profil)) {
            $pesan = "<div style='color: #10b981; background: rgba(209,250,229,0.8); padding: 15px; border-radius: 12px; border: 1px solid #a7f3d0; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-check'></i> Foto profil berhasil diperbarui!</div>";
        } else {
            $pesan = "<div style='color: #e53e3e; background: rgba(254,226,226,0.8); padding: 15px; border-radius: 12px; border: 1px solid #fecaca; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> Terjadi kesalahan saat mengunggah foto.</div>";
        }
    }
}

// Cek foto saat ini
$img_src = file_exists($foto_profil) ? $foto_profil . "?t=" . time() : $foto_default;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil - Velohertz</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #0f52ba;
            --app-bg: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
            --glass-bg: rgba(255, 255, 255, 0.6);
            --glass-border: rgba(255, 255, 255, 0.5);
            --text-main: #0f172a;
            --text-muted: #475569;
        }
        body { margin: 0; font-family: 'Inter', sans-serif; background: var(--app-bg); background-attachment: fixed; color: var(--text-main); display: flex; overflow-x: hidden; }

        .sidebar { width: 240px; background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); padding: 30px 20px; height: 100vh; position: fixed; z-index: 100; box-sizing: border-box; border-right: 1px solid rgba(255, 255, 255, 0.4); display: flex; flex-direction: column; }
        .sidebar h2 { color: var(--primary); margin-bottom: 30px; font-style: italic; font-size: 26px; margin-top: 0; padding-left: 10px; }
        .sidebar a { display: flex; align-items: center; color: var(--text-muted); text-decoration: none; margin: 5px 0; font-weight: 600; transition: 0.3s; padding: 12px 15px; border-radius: 12px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255, 255, 255, 0.5); color: var(--primary); transform: translateX(4px); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .sidebar a i { margin-right: 12px; font-size: 18px; }
        .logout-btn { margin-top: auto; background: rgba(255, 255, 255, 0.3); color: #e53e3e !important; border: 1px solid rgba(254, 215, 215, 0.6); justify-content: center; }

        .main-content { margin-left: 240px; padding: 40px; width: calc(100% - 240px); box-sizing: border-box; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh;}
        
        .profile-card { background: var(--glass-bg); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border-radius: 24px; border: 1px solid var(--glass-border); box-shadow: 0 20px 40px rgba(0,0,0,0.05); padding: 50px; text-align: center; width: 100%; max-width: 500px; }
        .profile-pic { width: 150px; height: 150px; border-radius: 50%; object-fit: cover; border: 5px solid #ffffff; box-shadow: 0 10px 25px rgba(0,0,0,0.1); margin-bottom: 20px; background: #fff;}
        .profile-card h1 { margin: 0 0 5px 0; font-size: 28px; color: var(--text-main); font-weight: 800;}
        .profile-card p { margin: 0 0 30px 0; color: var(--text-muted); font-size: 15px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;}

        .btn-upload { background: var(--primary); color: white; border: none; padding: 12px 25px; border-radius: 30px; font-weight: 600; cursor: pointer; transition: 0.3s; font-family: inherit; font-size: 14px; box-shadow: 0 4px 10px rgba(15,82,186,0.2); width: 100%; max-width: 250px;}
        .btn-upload:hover { background: #0c4399; transform: translateY(-2px); }
        
        .btn-delete { background: rgba(229,62,62,0.1); color: #e53e3e; border: 1px solid #fecaca; padding: 12px 25px; border-radius: 30px; font-weight: 600; cursor: pointer; transition: 0.3s; font-family: inherit; font-size: 14px; width: 100%; max-width: 250px;}
        .btn-delete:hover { background: #e53e3e; color: white; }

        input[type="file"] { display: block; margin: 0 auto 15px auto; font-family: inherit; color: var(--text-muted); background: rgba(255,255,255,0.5); padding: 10px; border-radius: 12px; border: 1px solid var(--glass-border); width: 80%;}
        
        @media screen and (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; flex-direction: row; align-items: center; padding: 15px 20px; overflow-x: auto; }
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
            .profile-card { padding: 30px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>🎵 Velohertz</h2>
        <a href="index.php"><i class="fa-solid fa-house" style="width: 25px;"></i> Beranda</a>
        <a href="search.php"><i class="fa-solid fa-magnifying-glass" style="width: 25px;"></i> Cari</a>
        <a href="library.php"><i class="fa-solid fa-book" style="width: 25px;"></i> Koleksi Kamu</a>
        <a href="profile.php" class="active"><i class="fa-solid fa-user" style="width: 25px;"></i> Profil</a>
        
        <?php if($_SESSION['username'] == 'admin'): ?>
            <a href="admin.php" style="color: #e53e3e;"><i class="fa-solid fa-shield-halved" style="width: 25px;"></i> Admin Panel</a>
        <?php endif; ?>

        <a href="logout.php" class="logout-btn" onclick="return confirm('Apakah kamu yakin ingin keluar?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content">
        <?php echo $pesan; ?>
        
        <div class="profile-card">
            <img src="<?php echo $img_src; ?>" alt="Foto Profil" class="profile-pic">
            <h1><?php echo htmlspecialchars($username); ?></h1>
            <p>Member Velohertz</p>

            <form action="" method="POST" enctype="multipart/form-data" style="margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid rgba(0,0,0,0.05);">
                <input type="file" name="fileToUpload" id="fileToUpload" accept="image/jpeg, image/png, image/jpg" required>
                <button type="submit" name="upload_foto" class="btn-upload"><i class="fa-solid fa-cloud-arrow-up"></i> Ganti Foto</button>
            </form>

            <form action="" method="POST">
                <button type="submit" name="hapus_foto" class="btn-delete" onclick="return confirm('Yakin ingin menghapus foto profil?');"><i class="fa-solid fa-trash"></i> Hapus Foto Saat Ini</button>
            </form>
        </div>
    </div>

</body>
</html>