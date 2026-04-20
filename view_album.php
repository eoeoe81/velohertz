<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
include 'koneksi.php';

if (!isset($_GET['alid'])) { header("Location: index.php"); exit(); }
$alid = $conn->real_escape_string($_GET['alid']);

$sql_album = "SELECT * FROM Album WHERE alid='$alid'";
$result_album = $conn->query($sql_album);
if ($result_album->num_rows == 0) { echo "Album tidak ditemukan!"; exit(); }
$album = $result_album->fetch_assoc();

// RADAR GAMBAR (Mencocokkan dengan Beranda / Picsum)
$sql_all = "SELECT DISTINCT a.alid FROM Album a INNER JOIN Track t ON a.alid = t.alid LIMIT 20";
$res_all = $conn->query($sql_all);
$cover_number = 1; $counter = 1;
if ($res_all) { while($r = $res_all->fetch_assoc()) { if($r['alid'] == $alid) { $cover_number = $counter; break; } $counter++; } }

$cover_path = "albums/" . $cover_number . ".jpg";
// Kalau nggak ada gambar lokal, pakai cover otomatis dari Picsum sesuai judul album
$img_src = file_exists($cover_path) ? $cover_path : "https://picsum.photos/seed/" . urlencode($album["atitle"]) . "/300/300";

$sql_tracks = "SELECT * FROM Track WHERE alid='$alid'";
$result_tracks = $conn->query($sql_tracks);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($album['atitle']); ?> - Velohertz</title>
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
        
        body { margin: 0; font-family: 'Inter', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: var(--app-bg); background-attachment: fixed; color: var(--text-main); display: flex; overflow-x: hidden; }

        /* SIDEBAR KACA */
        .sidebar { width: 240px; background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); padding: 30px 20px; height: 100vh; position: fixed; z-index: 100; box-sizing: border-box; border-right: 1px solid rgba(255, 255, 255, 0.4); display: flex; flex-direction: column; box-shadow: 4px 0 30px rgba(0,0,0,0.03); }
        .sidebar h2 { color: var(--primary); margin-bottom: 30px; font-style: italic; font-size: 26px; margin-top: 0; padding-left: 10px; }
        .sidebar a { display: flex; align-items: center; color: var(--text-muted); text-decoration: none; margin: 5px 0; font-weight: 600; transition: 0.3s; padding: 12px 15px; border-radius: 12px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255, 255, 255, 0.5); color: var(--primary); transform: translateX(4px); box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .sidebar a:hover i, .sidebar a.active i { color: var(--primary); }
        .logout-btn { margin-top: auto; background: rgba(255, 255, 255, 0.3); color: #e53e3e !important; border: 1px solid rgba(254, 215, 215, 0.6); justify-content: center; }

        .main-content { margin-left: 240px; padding: 40px; width: calc(100% - 240px); box-sizing: border-box; }
        
        .back-link { display: inline-block; margin-bottom: 20px; color: var(--text-muted); text-decoration: none; font-weight: 600; transition: 0.2s; }
        .back-link:hover { color: var(--primary); }

        /* HEADER ALBUM EFEK KACA */
        .album-header { background: var(--glass-bg); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-radius: 16px; border: 1px solid var(--glass-border); box-shadow: 0 8px 32px rgba(0,0,0,0.05); padding: 30px; display: flex; align-items: center; gap: 30px; margin-bottom: 40px; }
        .album-cover { width: 160px; height: 160px; border-radius: 12px; object-fit: cover; box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
        .album-info p { margin: 0; color: var(--text-muted); font-weight: 600; letter-spacing: 1px; font-size: 13px; }
        .album-info h1 { font-size: 42px; margin: 10px 0; color: var(--text-main); letter-spacing: -1px; font-weight: 800; }

        /* TABEL EFEK KACA */
        table { width: 100%; border-collapse: separate; border-spacing: 0; background: var(--glass-bg); backdrop-filter: blur(12px); border-radius: 16px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.05); border: 1px solid var(--glass-border); }
        th { text-align: left; padding: 18px 20px; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--glass-border); font-weight: 700; background: rgba(255,255,255,0.4); }
        td { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.3); font-size: 14px; vertical-align: middle; color: var(--text-main); transition: 0.2s; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: rgba(255,255,255,0.8); }
        
        .play-icon-btn { background: rgba(15,82,186,0.1); color: var(--primary); border: none; width: 36px; height: 36px; border-radius: 50%; font-size: 14px; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; }
        .play-icon-btn:hover { background: var(--primary); color: white; transform: scale(1.1); box-shadow: 0 4px 10px rgba(15,82,186,0.3); }

        @media screen and (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; flex-direction: row; align-items: center; padding: 15px 20px; overflow-x: auto; }
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
            .album-header { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>🎵 Velohertz</h2>
        <a href="index.php"><i class="fa-solid fa-house" style="width: 20px;"></i> Beranda</a>
        <a href="search.php"><i class="fa-solid fa-magnifying-glass" style="width: 20px;"></i> Cari</a>
        <a href="library.php" class="active"><i class="fa-solid fa-book" style="width: 20px;"></i> Koleksi Kamu</a>
        <a href="profile.php"><i class="fa-solid fa-user" style="width: 20px;"></i> Profil</a>
        
        <?php if($_SESSION['username'] == 'admin'): ?>
            <a href="admin.php" style="color: #e53e3e;"><i class="fa-solid fa-shield-halved" style="width: 20px;"></i> Admin Panel</a>
        <?php endif; ?>

        <a href="logout.php" class="logout-btn" onclick="return confirm('Apakah kamu yakin ingin keluar?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>
    
    <div class="main-content">
        <a href="index.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Kembali ke Beranda</a>
        
        <div class="album-header">
            <img src="<?php echo $img_src; ?>" class="album-cover">
            <div class="album-info">
                <p>ALBUM</p>
                <h1><?php echo htmlspecialchars($album['atitle']); ?></h1>
                <p>Dirilis pada: <?php echo htmlspecialchars($album['adate']); ?></p>
            </div>
        </div>
        
        <table>
            <tr><th style="width: 50px;">#</th><th>Judul Lagu</th><th>Artis</th><th style="text-align: right; padding-right: 30px;">Putar</th></tr>
            <?php
            if ($result_tracks && $result_tracks->num_rows > 0) {
                $no = 1;
                while($row = $result_tracks->fetch_assoc()) {
                    echo "<tr><td><span style='color: var(--text-muted); font-weight: bold;'>" . $no++ . "</span></td>";
                    
                    // FIX: Warna text diputihkan agar seragam dengan Glassmorphism
                    echo "<td><strong>" . htmlspecialchars($row["ttitle"]) . "</strong></td>";
                    echo "<td style='color: var(--text-muted);'>" . htmlspecialchars($row["aname"]) . "</td>";
                    echo "<td style='text-align: right; padding-right: 30px;'><button class='play-icon-btn'><i class='fa-solid fa-play'></i></button></td></tr>";
                }
            } else { 
                echo "<tr><td colspan='4' style='text-align: center; color: var(--text-muted); padding: 30px;'>Belum ada lagu di album ini.</td></tr>"; 
            }
            ?>
        </table>
    </div>
</body>
</html>