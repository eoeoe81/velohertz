<?php
session_start();
if (!isset($_SESSION['username'])) { 
    header("Location: login.php"); 
    exit(); 
}
include 'koneksi.php';

if (!isset($_GET['alid'])) { 
    header("Location: index.php"); 
    exit(); 
}

$alid = $conn->real_escape_string($_GET['alid']);
$username = $_SESSION['username'];

$sql_album = "SELECT * FROM Album WHERE alid='$alid'";
$result_album = $conn->query($sql_album);
if ($result_album->num_rows == 0) { 
    echo "Album tidak ditemukan!"; 
    exit(); 
}
$album = $result_album->fetch_assoc();

$sql_all = "SELECT DISTINCT a.alid FROM Album a INNER JOIN Track t ON a.alid = t.alid LIMIT 20";
$res_all = $conn->query($sql_all);
$cover_number = 1; 
$counter = 1;
if ($res_all) { 
    while($r = $res_all->fetch_assoc()) { 
        if($r['alid'] == $alid) { $cover_number = $counter; break; } 
        $counter++; 
    } 
}

$cover_path = "albums/" . $cover_number . ".jpg";
$img_src = file_exists($cover_path) ? $cover_path : "https://picsum.photos/seed/album_" . $alid . "/400/400";

$sql_tracks = "SELECT * FROM Track WHERE alid='$alid'";
$result_tracks = $conn->query($sql_tracks);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($album['atitle']); ?> - Velohertz</title>
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
        background-attachment: fixed; color: var(--text-main); display: flex; overflow-x: hidden; 
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
        margin: 0 0 40px 0; font-size: 28px; font-weight: 800; text-align: center; line-height: 45px; 
    }
    .sidebar a { display: flex; align-items: center; color: var(--text-main); text-decoration: none; margin: 8px 0; font-weight: 600; transition: 0.3s; padding: 12px 15px; border-radius: 16px; }
    .sidebar a i { margin-right: 15px; font-size: 18px; opacity: 0.7; }
    .sidebar a:hover, .sidebar a.active { background: var(--glass-bg); color: var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
    .logout-btn { margin-top: auto; color: #ff6b81 !important; background: rgba(255, 71, 87, 0.1) !important;}

    .hamburger-menu { 
        position: fixed; top: 32px; left: 25px; z-index: 1100; 
        background: var(--primary-grad); color: white; border: none; 
        width: 45px; height: 45px; border-radius: 12px; cursor: pointer; 
        box-shadow: 0 4px 15px rgba(162, 155, 254, 0.3); display: flex; 
        align-items: center; justify-content: center; font-size: 20px; transition: 0.3s; 
    }
    .hamburger-menu:hover { transform: scale(1.05); filter: brightness(1.1); }

    .main-content { margin-left: 280px; padding: 40px 60px; width: 100%; transition: all 0.4s ease; box-sizing: border-box; min-height: 100vh; }
    .main-content.full-width { margin-left: 0; padding-left: 90px; }
    .content-container { max-width: 1100px; margin: 0 auto; }
    
    .back-link { display: inline-flex; align-items: center; gap: 10px; margin-top: 10px; margin-bottom: 25px; color: var(--primary); text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.3s; opacity: 0.8; }
    .back-link:hover { opacity: 1; transform: translateX(-5px); }

    .album-hero {
        display: flex; 
        align-items: center; 
        gap: 30px; 
        margin-bottom: 45px;
        padding: 30px; 
        background: var(--glass-bg); 
        backdrop-filter: blur(20px);
        border-radius: 30px;
        border: 1px solid var(--glass-border); 
        box-shadow: 0 20px 40px rgba(0,0,0,0.4);
    }
    .album-hero-img { 
        width: 180px; 
        height: 180px; 
        border-radius: 20px; 
        object-fit: cover; 
        box-shadow: 0 15px 35px rgba(0,0,0,0.5); 
        flex-shrink: 0; 
    }
    
    .album-hero-text { flex-grow: 1; }
    .album-hero-text span { font-weight: 800; font-size: 11px; letter-spacing: 2px; color: var(--primary); text-transform: uppercase; margin-bottom: 5px; display: block; }
    .album-hero-text h1 { font-family: 'Outfit', sans-serif; font-size: 42px; margin: 0; line-height: 1.1; letter-spacing: -1.5px; color: var(--text-main); }
    .album-hero-text p { color: var(--text-muted); font-size: 15px; margin-top: 8px; }

    table { width: 100%; border-collapse: separate; border-spacing: 0 10px; margin-bottom: 100px; }
    th { text-align: left; padding: 10px 20px; color: var(--text-muted); font-size: 13px; text-transform: uppercase; font-weight: 700; }
    td { padding: 15px 20px; background: var(--glass-bg); border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); font-size: 15px; color: var(--text-main); }
    td:first-child { border-left: 1px solid var(--glass-border); border-radius: 15px 0 0 15px; width: 45px; font-weight: bold; color: var(--text-muted); }
    td:last-child { border-right: 1px solid var(--glass-border); border-radius: 0 15px 15px 0; text-align: right; }
    tr:hover td { background: rgba(255, 255, 255, 0.1); }

    .btn-play-row { background: var(--primary-grad); color: white; border: none; width: 38px; height: 38px; border-radius: 50%; cursor: pointer; transition: 0.3s; }
    .btn-play-row:hover { transform: scale(1.1); box-shadow: 0 4px 10px rgba(162, 155, 254, 0.3); }
    .btn-play-row i { margin-left: 2px; }

    @media (max-width: 768px) {
        .sidebar { width: 260px; box-shadow: 5px 0 15px rgba(0,0,0,0.5); }
        .main-content, .main-content.full-width { margin-left: 0 !important; padding: 80px 20px 120px 20px !important; width: 100vw !important; box-sizing: border-box; }
        .hamburger-menu { top: 15px; left: 15px; width: 40px; height: 40px; }
        .album-hero { flex-direction: column; text-align: center; }
    }
</style>
</head>
<body>

    <button class="hamburger-menu" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <h2>Velohertz</h2>
        <a href="index.php" class="active"><i class="fa-solid fa-house"></i> Beranda</a>
        <a href="search.php"><i class="fa-solid fa-magnifying-glass"></i> Cari</a>
        <a href="library.php"><i class="fa-solid fa-book"></i> Koleksi</a>
        <a href="profile.php"><i class="fa-solid fa-user"></i> Profil</a>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Apakah kamu yakin ingin keluar?');">
            <i class="fa-solid fa-right-from-bracket"></i> Keluar
        </a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="content-container">
            <a href="index.php" class="back-link">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda
            </a>

            <div class="album-hero">
                <img src="<?php echo $img_src; ?>" class="album-hero-img" alt="Album Cover">
                <div class="album-hero-text">
                    <span>Album Musik</span>
                    <h1><?php echo htmlspecialchars($album['atitle']); ?></h1>
                    <p>Rilis: <strong><?php echo date('d M Y', strtotime($album['adate'])); ?></strong></p>
                </div>
            </div>

            <h3 style="font-family: 'Outfit', sans-serif; font-size: 24px; margin-bottom: 20px;">Daftar Lagu</h3>
            
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Judul Musik</th>
                        <th>Artis</th>
                        <th style="text-align:right;">Putar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($result_tracks && $result_tracks->num_rows > 0) {
                        $no = 1;
                        while($row = $result_tracks->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><strong><?php echo htmlspecialchars($row['ttitle']); ?></strong></td>
                                <td><?php echo htmlspecialchars($row['aname'] ?? 'Artis'); ?></td>
                                <td>
                                    <button class="btn-play-row">
                                        <i class="fa-solid fa-play"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile;
                    } else {
                        echo "<tr><td colspan='4' style='text-align:center; padding:50px;'>Belum ada lagu.</td></tr>";
                    } ?>
                </tbody>
            </table>
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