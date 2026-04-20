<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
include 'koneksi.php';

$username = $_SESSION['username'];
$pesan = "";

// Ambil UID user yang sedang login
$sql_user = "SELECT uid FROM User WHERE uname='$username'";
$res_user = $conn->query($sql_user);
if ($res_user->num_rows > 0) {
    $uid = $res_user->fetch_assoc()['uid'];
} else {
    echo "User tidak valid!";
    exit();
}

// 1. LOGIKA BUAT PLAYLIST BARU
if (isset($_POST['buat_playlist'])) {
    $ptitle = $conn->real_escape_string(trim($_POST['nama_playlist']));
    $pdate = date('Y-m-d H:i:s'); 
    if (!empty($ptitle)) {
        $conn->query("INSERT INTO playlist (ptitle, pdate, pavailable, pdesc, uid) VALUES ('$ptitle', '$pdate', 1, 'Kumpulan melodi favoritku', '$uid')");
        $pesan = "<div style='color: #10b981; background: rgba(209,250,229,0.8); padding: 15px; border-radius: 12px; border: 1px solid #a7f3d0; margin-bottom: 20px; font-weight: bold; box-shadow: 0 4px 15px rgba(16,185,129,0.1);'><i class='fa-solid fa-circle-check'></i> Playlist '$ptitle' berhasil dibuat!</div>";
    }
}

// 2. LOGIKA HAPUS PLAYLIST
if (isset($_POST['hapus_playlist'])) {
    $pid_hapus = $conn->real_escape_string($_POST['pid_hapus']);
    $conn->query("DELETE FROM playlistcontain WHERE pid='$pid_hapus'");
    $conn->query("DELETE FROM playlist WHERE pid='$pid_hapus' AND uid='$uid'");
    $pesan = "<div style='color: #e53e3e; background: rgba(254,226,226,0.8); padding: 15px; border-radius: 12px; border: 1px solid #fecaca; margin-bottom: 20px; font-weight: bold; box-shadow: 0 4px 15px rgba(229,62,62,0.1);'><i class='fa-solid fa-trash'></i> Playlist telah dihapus.</div>";
}

// 3. AMBIL DAFTAR PLAYLIST MILIK USER INI
$sql_tampil = "SELECT p.*, (SELECT t.ttitle FROM playlistcontain pc JOIN Track t ON pc.tid = t.tid WHERE pc.pid = p.pid LIMIT 1) AS sample_track FROM playlist p WHERE p.uid='$uid' ORDER BY p.pdate DESC";
$result_playlist = $conn->query($sql_tampil);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koleksi Kamu - Velohertz</title>
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

        /* SIDEBAR KACA */
        .sidebar { width: 240px; background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); padding: 30px 20px; height: 100vh; position: fixed; z-index: 100; box-sizing: border-box; border-right: 1px solid rgba(255, 255, 255, 0.4); display: flex; flex-direction: column; box-shadow: 4px 0 30px rgba(0,0,0,0.03); }
        .sidebar h2 { color: var(--primary); margin-bottom: 30px; font-style: italic; font-size: 26px; margin-top: 0; padding-left: 10px; }
        .sidebar a { display: flex; align-items: center; color: var(--text-muted); text-decoration: none; margin: 5px 0; font-weight: 600; transition: 0.3s; padding: 12px 15px; border-radius: 12px; }
        .sidebar a i { margin-right: 12px; font-size: 18px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255, 255, 255, 0.5); color: var(--primary); transform: translateX(4px); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(255, 255, 255, 0.6); }
        
        .logout-btn { margin-top: auto; background: rgba(255, 255, 255, 0.3); color: #e53e3e !important; border: 1px solid rgba(254, 215, 215, 0.6); justify-content: center; }
        .logout-btn:hover { background: #e53e3e !important; color: white !important; }

        .main-content { margin-left: 240px; padding: 40px; width: calc(100% - 240px); margin-bottom: 120px; box-sizing: border-box; }
        
        .header { margin-bottom: 40px; }
        .header h2 { margin: 0; color: var(--text-main); font-size: 28px; font-weight: 800; letter-spacing: -0.5px;}

        /* BOX INPUT (Lebih Clean & Minimalis) */
        .form-box { 
            background: var(--glass-bg); 
            backdrop-filter: blur(15px); 
            padding: 30px; 
            border-radius: 20px; 
            border: 1px solid var(--glass-border); 
            box-shadow: 0 10px 30px rgba(0,0,0,0.05); 
            margin-bottom: 45px;
            max-width: 600px;
        }
        .form-box form {
            display: flex;
            gap: 15px;
            align-items: center;
        }
        .search-box { 
            flex: 1;
            padding: 14px 20px; 
            border-radius: 12px; 
            border: 1px solid var(--glass-border); 
            background: rgba(255,255,255,0.7); 
            font-size: 15px; 
            outline: none; 
            transition: 0.3s; 
            font-family: inherit;
            color: var(--text-main);
        }
        .search-box:focus { background: #ffffff; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(15,82,186,0.1); }
        .btn-submit { 
            background: var(--primary); 
            color: white; 
            border: none; 
            padding: 14px 30px; 
            border-radius: 12px; 
            font-weight: 600; 
            cursor: pointer; 
            transition: 0.3s; 
            font-family: inherit; 
            white-space: nowrap;
            box-shadow: 0 4px 15px rgba(15,82,186,0.2); 
            font-size: 15px;
        }
        .btn-submit:hover { background: #0c4399; transform: translateY(-2px); box-shadow: 0 8px 20px rgba(15,82,186,0.3); }

        /* GRID KARTU PLAYLIST PREMIUM (Tampilan Penuh ala Spotify) */
        .playlist-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 30px; }
        .playlist-wrapper { position: relative; display: block; }
        
        .playlist-card { 
            background: var(--glass-bg); 
            backdrop-filter: blur(15px); 
            border-radius: 20px; 
            border: 1px solid var(--glass-border); 
            transition: 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); 
            text-decoration: none; 
            color: var(--text-main); 
            display: flex; 
            flex-direction: column;
            overflow: hidden; /* Fotonya bakal full ngikutin lengkungan box */
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        }
        .playlist-card:hover { 
            transform: translateY(-10px); 
            background: rgba(255,255,255,0.9); 
            border-color: rgba(255,255,255,0.8); 
            box-shadow: 0 20px 40px rgba(15,82,186,0.15); 
        }
        
        .playlist-cover-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        .playlist-icon { 
            width: 100%; 
            height: 180px; 
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); 
            color: var(--primary); 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-size: 60px; 
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }
        
        .playlist-info { padding: 20px; }
        .playlist-title { font-size: 18px; font-weight: 800; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-main); }
        .playlist-desc { font-size: 14px; color: var(--text-muted); font-weight: 500; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;}

        /* TOMBOL HAPUS MELAYANG (Desain Halus) */
        .btn-delete-pl { 
            position: absolute; 
            top: 15px; 
            right: 15px; 
            background: rgba(255,255,255,0.95); 
            color: #e53e3e; 
            border: none; 
            border-radius: 50%; 
            width: 38px; 
            height: 38px; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            z-index: 10; 
            transition: 0.3s; 
            box-shadow: 0 5px 15px rgba(0,0,0,0.15); 
            font-size: 15px;
        }
        .btn-delete-pl:hover { background: #e53e3e; color: white; transform: scale(1.15); box-shadow: 0 8px 20px rgba(229,62,62,0.3); }

        @media screen and (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; flex-direction: row; align-items: center; padding: 15px 20px; overflow-x: auto; border-bottom: 1px solid var(--glass-border); border-right: none;}
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
            .form-box form { flex-direction: column; align-items: stretch; }
            .btn-submit { width: 100%; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>🎵 Velohertz</h2>
        <a href="index.php"><i class="fa-solid fa-house" style="width: 25px;"></i> Beranda</a>
        <a href="search.php"><i class="fa-solid fa-magnifying-glass" style="width: 25px;"></i> Cari</a>
        <a href="library.php" class="active"><i class="fa-solid fa-book" style="width: 25px;"></i> Koleksi Kamu</a>
        <a href="profile.php"><i class="fa-solid fa-user" style="width: 25px;"></i> Profil</a>
        
        <?php if($username == 'admin'): ?>
            <a href="admin.php" style="color: #e53e3e;"><i class="fa-solid fa-shield-halved" style="width: 25px;"></i> Admin Panel</a>
        <?php endif; ?>

        <a href="logout.php" class="logout-btn" onclick="return confirm('Apakah kamu yakin ingin keluar?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Koleksi Playlist Kamu</h2>
        </div>

        <?php echo $pesan; ?>

        <div class="form-box">
            <h3 style="margin-top: 0; margin-bottom: 20px; font-weight: 800; font-size: 20px;">Buat Playlist Baru ✨</h3>
            <form action="" method="POST">
                <input type="text" name="nama_playlist" class="search-box" placeholder="Beri nama playlistmu (Misal: Vibes Pagi)..." required>
                <button type="submit" name="buat_playlist" class="btn-submit"><i class="fa-solid fa-plus"></i> Buat</button>
            </form>
        </div>

        <div class="playlist-grid">
            <?php
            if ($result_playlist && $result_playlist->num_rows > 0) {
                while($row = $result_playlist->fetch_assoc()) {
                    echo "<div class='playlist-wrapper'>";
                    
                    // Tombol Hapus Playlist (Melayang di ujung kanan atas foto)
                    echo "<form action='' method='POST' style='margin:0;'>";
                    echo "<input type='hidden' name='pid_hapus' value='" . $row["pid"] . "'>";
                    echo "<button type='submit' name='hapus_playlist' class='btn-delete-pl' title='Hapus Playlist' onclick=\"return confirm('Yakin ingin menghapus playlist ini?');\"><i class='fa-solid fa-trash'></i></button>";
                    echo "</form>";

                    // Kartu Playlist
                    echo "<a href='view_playlist.php?pid=" . $row["pid"] . "' class='playlist-card'>";
                    
                    // Logic Gambar (Full width, nggak ada kotak putih di dalem kotak lagi)
                    if (!empty($row['sample_track'])) {
                        $pl_img = "https://picsum.photos/seed/" . urlencode($row['pid']) . "/300/300";
                        echo "<img src='$pl_img' class='playlist-cover-img'>";
                    } else {
                        echo "<div class='playlist-icon'><i class='fa-solid fa-music'></i></div>";
                    }
                    
                    // Box Teks yang rapi di bawah gambar
                    echo "<div class='playlist-info'>";
                    echo "<div class='playlist-title'>" . htmlspecialchars($row["ptitle"]) . "</div>";
                    echo "<div class='playlist-desc'>" . htmlspecialchars($row["pdesc"]) . "</div>";
                    echo "</div>";
                    
                    echo "</a></div>";
                }
            } else {
                echo "<div style='color: var(--text-muted); font-weight: 600; font-size: 15px; grid-column: 1 / -1;'>Kamu belum punya playlist. Yuk bikin koleksimu sekarang!</div>";
            }
            ?>
        </div>
    </div>

</body>
</html>