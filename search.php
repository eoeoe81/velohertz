<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
include 'koneksi.php';

$username = $_SESSION['username'];
$pesan_aksi = "";

$sql_user = "SELECT uid FROM User WHERE uname='$username'";
$uid = $conn->query($sql_user)->fetch_assoc()['uid'];

$playlists = [];
$result_pl = $conn->query("SELECT pid, ptitle FROM playlist WHERE uid='$uid'");
if ($result_pl) { while($row = $result_pl->fetch_assoc()) { $playlists[] = $row; } }

if (isset($_POST['tambah_lagu'])) {
    $tid = $conn->real_escape_string($_POST['tid']);
    $pid = $conn->real_escape_string($_POST['pid']);
    $cek_lagu = $conn->query("SELECT * FROM playlistcontain WHERE pid='$pid' AND tid='$tid'");
    
    if ($cek_lagu->num_rows > 0) {
        $pesan_aksi = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Lagu sudah ada di playlist tersebut!</div>";
    } else {
        if ($conn->query("INSERT INTO playlistcontain (pid, tid) VALUES ('$pid', '$tid')") === TRUE) {
            $pesan_aksi = "<div class='success-msg'><i class='fa-solid fa-circle-check'></i> Berhasil ditambahkan ke playlist!</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cari Lagu - Velohertz</title>
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
        margin: 0; 
        font-family: 'Poppins', sans-serif; 
        background-color: var(--app-bg-color);
        background-image: 
            radial-gradient(at 0% 0%, rgba(59, 113, 202, 0.15) 0px, transparent 50%),
            radial-gradient(at 100% 100%, rgba(116, 185, 255, 0.1) 0px, transparent 50%);
        background-attachment: fixed; 
        color: var(--text-main); 
        display: flex; 
        overflow-x: hidden; 
    }

    .sidebar { 
        width: 260px; 
        background: rgba(20, 25, 35, 0.4); 
        backdrop-filter: blur(20px); 
        padding: 32px 20px; 
        height: 100vh; 
        position: fixed; 
        left: 0; top: 0; z-index: 1000; 
        border-right: 1px solid var(--glass-border); 
        display: flex; 
        flex-direction: column; 
        transition: transform 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .sidebar.hidden { transform: translateX(-100%); }
    
    .sidebar h2 { 
        font-family: 'Outfit', sans-serif; 
        background: var(--primary-grad);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent; 
        margin: 0 0 40px 0; 
        font-size: 28px; 
        font-weight: 800; 
        text-align: center; 
        line-height: 45px; 
    }
    
    .sidebar a { display: flex; align-items: center; color: var(--text-main); text-decoration: none; margin: 8px 0; font-weight: 600; transition: 0.3s; padding: 12px 15px; border-radius: 16px; }
    .sidebar a i { margin-right: 15px; font-size: 18px; opacity: 0.7; }
    .sidebar a:hover, .sidebar a.active { background: var(--glass-bg); color: var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
    .logout-btn { margin-top: auto; color: #ff6b81 !important; background: rgba(255, 71, 87, 0.1) !important;}

    .hamburger-menu {
        position: fixed; top: 32px; left: 25px; z-index: 1100;
        background: var(--primary-grad); color: white; border: none;
        width: 45px; height: 45px; border-radius: 12px; cursor: pointer;
        display: flex; align-items: center; justify-content: center; font-size: 20px;
        box-shadow: 0 4px 15px rgba(162, 155, 254, 0.3);
        transition: 0.3s;
    }
    .hamburger-menu:hover { transform: scale(1.05); filter: brightness(1.1); }

    .main-content { 
        margin-left: 260px; padding: 40px; width: 100%;
        transition: all 0.4s ease; box-sizing: border-box; min-height: 100vh;
    }
    .main-content.full-width { margin-left: 0; padding-left: 90px; }
    .content-container { max-width: 1100px; margin: 0 auto; }
    
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; margin-top: 10px; }
    .header h2 { font-family: 'Outfit', sans-serif; font-size: 30px; }

    .form-box { 
        background: var(--glass-bg); 
        backdrop-filter: blur(12px); 
        padding: 30px; 
        border-radius: 24px; 
        border: 1px solid var(--glass-border); 
        box-shadow: 0 20px 40px rgba(0,0,0,0.4); 
        margin-bottom: 40px; 
    }
    .form-box h3 { font-family: 'Outfit', sans-serif; margin-top: 0; color: var(--text-main); margin-bottom: 20px; font-size: 20px; }
    
    .search-wrapper { display: flex; gap: 10px; flex-wrap: wrap; }
    
    .search-box { 
        flex-grow: 1; 
        min-width: 250px; 
        padding: 15px 25px; 
        border-radius: 16px; 
        border: 1px solid rgba(255, 255, 255, 0.1); 
        background: rgba(255, 255, 255, 0.03); 
        color: #fff;
        font-size: 15px; 
        outline: none; 
        transition: 0.3s; 
    }
    .search-box::placeholder { color: rgba(255, 255, 255, 0.4); }
    .search-box:focus { border-color: var(--primary); background: rgba(255, 255, 255, 0.08); box-shadow: 0 0 15px rgba(116, 185, 255, 0.1); }
    
    .btn-search { 
        background: var(--primary-grad); 
        color: white; 
        border: none; 
        padding: 15px 30px; 
        border-radius: 16px; 
        font-weight: 600; 
        cursor: pointer; 
        transition: 0.3s; 
        display: flex; 
        align-items: center; 
        gap: 10px; 
        letter-spacing: 0.5px;
    }
    .btn-search:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(162, 155, 254, 0.3); }

    table { width: 100%; border-collapse: separate; border-spacing: 0 10px; margin-bottom: 50px;}
    th { text-align: left; padding: 10px 20px; color: var(--text-muted); font-size: 13px; text-transform: uppercase; }
    td { padding: 15px 20px; background: var(--glass-bg); border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); font-size: 14px;}
    td:first-child { border-left: 1px solid var(--glass-border); border-radius: 15px 0 0 15px; }
    td:last-child { border-right: 1px solid var(--glass-border); border-radius: 0 15px 15px 0; }
    tr:hover td { background: rgba(255, 255, 255, 0.1); }

    .play-icon-btn { 
        background: var(--primary-grad); 
        color: white; 
        border: none; 
        width: 38px; 
        height: 38px; 
        border-radius: 50%; 
        cursor: pointer; 
        transition: 0.3s; 
    }
    .play-icon-btn:hover { transform: scale(1.1); box-shadow: 0 4px 10px rgba(162, 155, 254, 0.3); }

    .btn-add { 
        background: var(--emerald); 
        color: #000; 
        border: none; 
        padding: 10px 15px; 
        border-radius: 12px; 
        font-weight: 600; 
        cursor: pointer; 
        transition: 0.2s; 
        margin-left: 8px; 
        font-size: 13px;
    }
    .btn-add:hover { filter: brightness(1.1); transform: scale(1.05); }

    select[name='pid'] { 
        padding: 10px; 
        border-radius: 12px; 
        border: 1px solid rgba(255, 255, 255, 0.2); 
        background: rgba(255, 255, 255, 0.05); 
        outline: none; 
        font-size: 13px; 
        color: #fff; 
    }
    select[name='pid'] option {
        background: #0b0f19;
        color: #fff;
    }

    .error-msg { background: rgba(255, 71, 87, 0.1); color: #ff6b81; padding: 15px; border-radius: 16px; margin-bottom: 25px; border: 1px solid rgba(255, 71, 87, 0.3); display: flex; align-items: center; gap: 10px; font-weight: 500; backdrop-filter: blur(5px); }
    .success-msg { background: rgba(0, 206, 201, 0.1); color: var(--emerald); padding: 15px; border-radius: 16px; margin-bottom: 25px; border: 1px solid rgba(0, 206, 201, 0.3); display: flex; align-items: center; gap: 10px; font-weight: 500; backdrop-filter: blur(5px); }

    @media (max-width: 768px) {
        html, body { overflow-x: hidden !important; width: 100vw !important; max-width: 100%; }
        *, *::before, *::after { box-sizing: border-box !important; }
        
        .sidebar { width: 260px; box-shadow: 5px 0 15px rgba(0,0,0,0.5); }
        
        .main-content, .main-content.full-width { 
            margin-left: 0 !important; 
            padding: 80px 15px 120px 15px !important; 
            width: 100% !important; 
        }
        
        .hamburger-menu { top: 15px; left: 15px; width: 40px; height: 40px; }
        
        table { 
            display: block; 
            width: 100%; 
            overflow-x: auto; 
            white-space: nowrap; 
            -webkit-overflow-scrolling: touch; 
        }
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
        <a href="search.php" class="active"><i class="fa-solid fa-magnifying-glass"></i> Cari</a>
        <a href="library.php"><i class="fa-solid fa-book"></i> Koleksi</a>
        <a href="profile.php"><i class="fa-solid fa-user"></i> Profil</a>
        <?php if($username == 'admin'): ?>
            <a href="admin.php" style="color: var(--primary);"><i class="fa-solid fa-shield-halved"></i> Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin mau keluar?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="content-container">
            <div class="header">
                <h2>Cari Musik</h2>
                <a href="profile.php" style="display:flex; align-items:center; background:var(--glass-bg); padding:10px 20px; border-radius:20px; text-decoration:none; color:var(--text-main); font-weight:700; border:1px solid var(--glass-border);">
                    <i class="fa-solid fa-circle-user" style="margin-right: 10px; font-size: 20px; color: var(--primary);"></i> 
                    <?php echo htmlspecialchars($username); ?>
                </a>
            </div>

            <?php echo $pesan_aksi; ?>

            <div class="form-box">
                <h3>Apa yang ingin kamu dengarkan?</h3>
                <form action="" method="GET">
                    <div class="search-wrapper">
                        <input type="text" name="keyword" class="search-box" placeholder="Cari judul lagu, artis, atau album..." value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                        <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i> Cari Lagu</button>
                    </div>
                </form>
            </div>
            
            <div class="search-results">
                <?php
                if (isset($_GET['keyword']) && trim($_GET['keyword']) != '') {
                    $keyword = $conn->real_escape_string(trim($_GET['keyword']));
                    $sql_search = "SELECT t.*, a.atitle FROM Track t LEFT JOIN Album a ON t.alid = a.alid 
                                   WHERE t.ttitle LIKE '%$keyword%' OR t.aname LIKE '%$keyword%' OR a.atitle LIKE '%$keyword%'";
                    $result_search = $conn->query($sql_search);
                    
                    if ($result_search && $result_search->num_rows > 0) {
                        echo "<table>";
                        echo "<thead><tr><th>#</th><th>Lagu</th><th>Artis</th><th>Album</th><th style='text-align:center;'>Putar</th><th style='text-align:right;'>Tambah ke Playlist</th></tr></thead>";
                        echo "<tbody>";
                        $no = 1;
                        while($row = $result_search->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td style='width:30px; font-weight:bold; color:var(--text-muted);'>" . $no++ . "</td>";
                            echo "<td><strong>" . htmlspecialchars($row["ttitle"]) . "</strong></td>";
                            echo "<td>" . htmlspecialchars($row["aname"]) . "</td>";
                            echo "<td style='color: var(--primary); font-weight: 600;'>" . htmlspecialchars($row["atitle"] ?? 'Single') . "</td>";
                            echo "<td style='text-align: center;'><button class='play-icon-btn'><i class='fa-solid fa-play' style='margin-left:2px;'></i></button></td>";
                            echo "<td style='text-align: right;'>";
                            if (count($playlists) > 0) {
                                echo "<form action='' method='POST' style='display: flex; align-items: center; justify-content: flex-end;'>";
                                echo "<input type='hidden' name='tid' value='" . $row["tid"] . "'>";
                                echo "<input type='hidden' name='keyword' value='" . htmlspecialchars($_GET['keyword']) . "'>";
                                echo "<select name='pid' required><option value=''>Pilih Playlist...</option>";
                                foreach ($playlists as $pl) { echo "<option value='" . $pl['pid'] . "'>" . htmlspecialchars($pl['ptitle']) . "</option>"; }
                                echo "</select><button type='submit' name='tambah_lagu' class='btn-add'><i class='fa-solid fa-plus'></i></button></form>";
                            } else {
                                echo "<span style='color: var(--text-muted); font-size: 13px;'>Buat playlist dulu</span>";
                            }
                            echo "</td></tr>";
                        }
                        echo "</tbody></table>";
                    } else {
                        echo "<div style='background: var(--glass-bg); padding: 40px; border-radius: 24px; text-align: center; color: var(--text-muted); border: 1px solid var(--glass-border);'>
                                <i class='fa-solid fa-face-frown' style='font-size: 40px; margin-bottom: 15px;'></i><br>
                                <strong>Maaf, musik yang kamu cari tidak ada.</strong>
                              </div>";
                    }
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
            
            localStorage.setItem('sidebarState', sidebar.classList.contains('hidden') ? 'hidden' : 'visible');
        }
    </script>
</body>
</html>