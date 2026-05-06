<?php
session_start();
if (!isset($_SESSION['username'])) { 
    header("Location: login.php"); 
    exit(); 
}
include 'koneksi.php';

$username = $_SESSION['username'];
$pesan = "";

$sql_user = "SELECT uid FROM User WHERE uname='$username'";
$res_user = $conn->query($sql_user);
if ($res_user && $res_user->num_rows > 0) {
    $uid = $res_user->fetch_assoc()['uid'];
} else {
    echo "User tidak ditemukan di database!";
    exit();
}

if (isset($_POST['buat_playlist'])) {
    $ptitle = $conn->real_escape_string(trim($_POST['nama_playlist']));
    $pdate = date('Y-m-d H:i:s'); 
    if (!empty($ptitle)) {
        $insert_pl = "INSERT INTO playlist (ptitle, pdate, pavailable, pdesc, uid) 
                      VALUES ('$ptitle', '$pdate', 1, 'Kumpulan musik favorit saya', '$uid')";
        if ($conn->query($insert_pl)) {
            $pesan = "<div class='success-msg'><i class='fa-solid fa-circle-check'></i> Playlist '$ptitle' berhasil dibuat!</div>";
        }
    }
}

if (isset($_POST['hapus_playlist'])) {
    $pid_hapus = $conn->real_escape_string($_POST['pid_hapus']);
    $conn->query("DELETE FROM playlistcontain WHERE pid='$pid_hapus'");
    $conn->query("DELETE FROM playlist WHERE pid='$pid_hapus' AND uid='$uid'");
    $pesan = "<div class='error-msg'><i class='fa-solid fa-trash'></i> Playlist telah dihapus dari koleksi.</div>";
}

$sql_tampil = "SELECT p.*, 
              (SELECT t.ttitle FROM playlistcontain pc 
               JOIN Track t ON pc.tid = t.tid 
               WHERE pc.pid = p.pid LIMIT 1) AS sample_track 
               FROM playlist p WHERE p.uid='$uid' ORDER BY p.pdate DESC";
$result_playlist = $conn->query($sql_tampil);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koleksi Kamu - Velohertz</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
    :root {
        --primary: #74b9ff;
        --primary-grad: linear-gradient(135deg, #3b71ca 0%, #a29bfe 100%);
        --app-bg-color: #0b0f19;
        --glass-bg: rgba(20, 25, 35, 0.6);
        --glass-border: rgba(255, 255, 255, 0.08);
        --text-main: #ffffff;
        --text-muted: rgba(255, 255, 255, 0.5);
        --emerald: #00cec9;
    }
    
    body { 
        margin: 0; padding: 0; font-family: 'Poppins', sans-serif; 
        background-color: var(--app-bg-color);
        background-image: 
            radial-gradient(at 0% 0%, rgba(59, 113, 202, 0.15) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(116, 185, 255, 0.1) 0px, transparent 50%);
        background-attachment: fixed; 
        color: var(--text-main); display: flex; overflow-x: hidden; 
    }

    .sidebar { 
        width: 260px; background: rgba(20, 25, 35, 0.4); backdrop-filter: blur(20px); 
        padding: 32px 20px; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; 
        border-right: 1px solid var(--glass-border); display: flex; flex-direction: column; 
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .sidebar.hidden { transform: translateX(-100%); }
    
    .sidebar h2 { 
        font-family: 'Outfit', sans-serif; 
        background: var(--primary-grad);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent; 
        margin: 0 0 40px 0; font-size: 28px; font-weight: 800; 
        text-align: center; line-height: 45px; 
    }

    .sidebar a { 
        display: flex; align-items: center; color: var(--text-main); 
        text-decoration: none; margin: 8px 0; font-weight: 600; 
        transition: 0.3s; padding: 12px 15px; border-radius: 16px; 
    }

    .sidebar a i { margin-right: 15px; font-size: 18px; opacity: 0.7; }
    .sidebar a:hover, .sidebar a.active { background: var(--glass-bg); color: var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }

    .logout-btn { margin-top: auto; color: #ff6b81 !important; background: rgba(255, 71, 87, 0.1) !important;}

    .hamburger-menu {
        position: fixed; top: 32px; left: 25px; z-index: 1100;
        background: var(--primary-grad); color: white; border: none;
        width: 45px; height: 45px; border-radius: 12px; cursor: pointer;
        box-shadow: 0 4px 15px rgba(162, 155, 254, 0.3);
        display: flex; align-items: center; justify-content: center; font-size: 20px;
        transition: 0.3s;
    }
    .hamburger-menu:hover { transform: scale(1.05); filter: brightness(1.1); }

    .main-content { 
        margin-left: 280px; padding: 40px 60px; width: 100%;
        transition: all 0.4s ease; box-sizing: border-box; min-height: 100vh;
    }

    .main-content.full-width { margin-left: 0; padding-left: 90px; }
    .content-container { max-width: 1100px; margin: 0 auto; }
    
    .header { margin-bottom: 30px; margin-top: 10px; }
    .header h2 { font-family: 'Outfit', sans-serif; font-size: 32px; font-weight: 800; }

    .form-box { 
        background: var(--glass-bg); padding: 30px; border-radius: 24px; 
        border: 1px solid var(--glass-border); box-shadow: 0 20px 40px rgba(0,0,0,0.4); 
        margin-bottom: 45px; max-width: 600px; backdrop-filter: blur(12px);
    }
    .form-box h3 { font-family: 'Outfit', sans-serif; margin-top: 0; margin-bottom: 20px; font-size: 20px; font-weight: 800; color: var(--text-main); }
    
    .input-playlist { 
        flex: 1; padding: 14px 20px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); 
        background: rgba(255, 255, 255, 0.03); color: #fff; font-size: 15px; outline: none; transition: 0.3s; font-family: inherit;
    }
    .input-playlist::placeholder { color: rgba(255, 255, 255, 0.4); }
    .input-playlist:focus { border-color: var(--primary); background: rgba(255, 255, 255, 0.08); box-shadow: 0 0 15px rgba(116, 185, 255, 0.1); }
    
    .btn-create { 
        background: var(--emerald); color: #000; border: none; padding: 14px 30px; 
        border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.3s; font-size: 15px;
    }
    .btn-create:hover { filter: brightness(1.1); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 206, 201, 0.3); }

    .playlist-grid { 
        display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); 
        gap: 30px; margin-bottom: 100px;
    }
    .playlist-wrapper { position: relative; }
    
    .playlist-card { 
        background: var(--glass-bg); border-radius: 20px; border: 1px solid var(--glass-border); 
        transition: 0.3s; text-decoration: none; color: var(--text-main); display: flex; 
        flex-direction: column; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    }
    .playlist-card:hover { transform: translateY(-8px); background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.2); }
    
    .playlist-img { width: 100%; height: 200px; object-fit: cover; }
    
    .playlist-icon-empty { 
        width: 100%; height: 200px; background: rgba(255, 255, 255, 0.05); color: rgba(255, 255, 255, 0.3); 
        display: flex; align-items: center; justify-content: center; font-size: 60px; 
    }
    
    .playlist-info { padding: 20px; }
    .playlist-title { font-size: 18px; font-weight: 800; margin-bottom: 6px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .playlist-desc { font-size: 13px; color: var(--text-muted); line-height: 1.4; }

    .btn-delete-pl { 
        position: absolute; top: 15px; right: 15px; background: rgba(0, 0, 0, 0.6); 
        color: #ff6b81; border: 1px solid rgba(255, 71, 87, 0.3); border-radius: 50%; width: 35px; height: 35px; 
        cursor: pointer; z-index: 10; transition: 0.3s; box-shadow: 0 4px 10px rgba(0,0,0,0.3); backdrop-filter: blur(5px);
    }
    .btn-delete-pl:hover { background: #ff6b81; color: white; transform: scale(1.1); border-color: transparent; }

    .success-msg { background: rgba(0, 206, 201, 0.1); color: var(--emerald); padding: 15px; border-radius: 16px; margin-bottom: 25px; border: 1px solid rgba(0, 206, 201, 0.3); display: flex; align-items: center; gap: 10px; font-weight: 500; backdrop-filter: blur(5px); }
    .error-msg { background: rgba(255, 71, 87, 0.1); color: #ff6b81; padding: 15px; border-radius: 16px; margin-bottom: 25px; border: 1px solid rgba(255, 71, 87, 0.3); display: flex; align-items: center; gap: 10px; font-weight: 500; backdrop-filter: blur(5px); }

    @media (max-width: 768px) {
        .sidebar { width: 220px; }
        .main-content { margin-left: 0; padding: 20px; padding-top: 80px; }
        .main-content.full-width { padding-left: 20px; }
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
        <a href="library.php" class="active"><i class="fa-solid fa-book"></i> Koleksi</a>
        <a href="profile.php"><i class="fa-solid fa-user"></i> Profil</a>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Apakah kamu yakin ingin keluar?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="content-container">
            <div class="header">
                <h2>Koleksi Kamu</h2>
            </div>

            <?php echo $pesan; ?>

            <div class="form-box">
                <h3>Buat Playlist Baru</h3>
                <form action="" method="POST" style="display: flex; gap: 12px;">
                    <input type="text" name="nama_playlist" class="input-playlist" placeholder="Nama koleksi kamu..." required>
                    <button type="submit" name="buat_playlist" class="btn-create">Buat Playlist</button>
                </form>
            </div>

            <div class="playlist-grid">
                <?php
                if ($result_playlist && $result_playlist->num_rows > 0) {
                    while($row = $result_playlist->fetch_assoc()) {
                        echo "<div class='playlist-wrapper'>";
                        
                        echo "<form action='' method='POST' style='margin:0;'>";
                        echo "<input type='hidden' name='pid_hapus' value='" . $row["pid"] . "'>";
                        echo "<button type='submit' name='hapus_playlist' class='btn-delete-pl' onclick=\"return confirm('Hapus playlist ini?');\"><i class='fa-solid fa-trash'></i></button>";
                        echo "</form>";

                        echo "<a href='view_playlist.php?pid=" . $row["pid"] . "' class='playlist-card'>";
                        
                        if (!empty($row['sample_track'])) {
                            echo "<img src='https://picsum.photos/seed/playlist_" . $row['pid'] . "/300/300' class='playlist-img' alt='Cover'>";
                        } else {
                            echo "<div class='playlist-icon-empty'><i class='fa-solid fa-music'></i></div>";
                        }
                        
                        echo "<div class='playlist-info'>";
                        echo "<div class='playlist-title'>" . htmlspecialchars($row["ptitle"]) . "</div>";
                        echo "<div class='playlist-desc'>" . htmlspecialchars($row["pdesc"]) . "</div>";
                        echo "</div>";
                        
                        echo "</a></div>";
                    }
                } else {
                    echo "<p style='color: var(--text-muted); font-weight: 500; grid-column: 1/-1;'>Kamu belum punya koleksi. Yuk buat playlist pertama kamu!</p>";
                }
                ?>
            </div>
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
    } 
    else if (state === 'hidden') {
        sidebar.classList.add('hidden');
        mainContent.classList.add('full-width');
    }
});

function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    sidebar.classList.toggle('hidden');
    mainContent.classList.toggle('full-width');
    
    if (sidebar.classList.contains('hidden')) {
        localStorage.setItem('sidebarState', 'hidden');
    } else {
        localStorage.setItem('sidebarState', 'visible');
    }
}
    </script>
</body>
</html>