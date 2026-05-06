<?php
session_start();
if (!isset($_SESSION['username'])) { 
    header("Location: login.php"); 
    exit(); 
}
include 'koneksi.php';

if (!isset($_GET['pid'])) { 
    header("Location: library.php"); 
    exit(); 
}

$pid = $conn->real_escape_string($_GET['pid']);
$username = $_SESSION['username'];
$pesan = "";

if (isset($_POST['edit_playlist'])) {
    $new_title = $conn->real_escape_string($_POST['new_title']);
    $new_desc = $conn->real_escape_string($_POST['new_desc']);
    if ($conn->query("UPDATE playlist SET ptitle='$new_title', pdesc='$new_desc' WHERE pid='$pid'")) {
        $pesan = "<div class='success-msg'><i class='fa-solid fa-circle-check'></i> Playlist diperbarui!</div>";
    }
}

if (isset($_POST['hapus_lagu'])) {
    $tid_hapus = $conn->real_escape_string($_POST['tid_hapus']);
    $conn->query("DELETE FROM playlistcontain WHERE pid='$pid' AND tid='$tid_hapus'");
    $pesan = "<div class='error-msg'><i class='fa-solid fa-trash'></i> Lagu dihapus.</div>";
}

if (isset($_POST['tambah_ke_playlist'])) {
    $tid_tambah = $conn->real_escape_string($_POST['tid_tambah']);
    $conn->query("INSERT INTO playlistcontain (pid, tid) VALUES ('$pid', '$tid_tambah')");
    $pesan = "<div class='success-msg'><i class='fa-solid fa-plus'></i> Lagu ditambahkan!</div>";
}

$playlist = $conn->query("SELECT * FROM playlist WHERE pid='$pid'")->fetch_assoc();
$result_tracks = $conn->query("SELECT t.*, a.atitle FROM Track t JOIN playlistcontain pc ON t.tid = pc.tid LEFT JOIN Album a ON t.alid = a.alid WHERE pc.pid = '$pid'");
$pl_img = "https://picsum.photos/seed/playlist_" . urlencode($pid) . "/400/400";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($playlist['ptitle']); ?> - Velohertz</title>
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
        margin: 0; font-family: 'Poppins', sans-serif; 
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
    .sidebar a { display: flex; align-items: center; color: var(--text-main); text-decoration: none; margin: 8px 0; font-weight: 600; transition: 0.3s; padding: 12px 15px; border-radius: 16px; }
    .sidebar a i { margin-right: 15px; font-size: 18px; opacity: 0.7; }
    .sidebar a:hover, .sidebar a.active { background: var(--glass-bg); color: var(--primary); box-shadow: 0 4px 15px rgba(0,0,0,0.2); }
    .logout-btn { margin-top: auto; color: #ff6b81 !important; background: rgba(255, 71, 87, 0.1) !important;}

    .hamburger-menu { 
        position: fixed; top: 32px; left: 25px; z-index: 1100; 
        background: var(--primary-grad); color: white; border: none; 
        width: 45px; height: 45px; border-radius: 12px; cursor: pointer; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 20px; box-shadow: 0 4px 15px rgba(162, 155, 254, 0.3);
        transition: 0.3s;
    }
    .hamburger-menu:hover { transform: scale(1.05); filter: brightness(1.1); }

    .main-content { 
        margin-left: 280px; padding: 40px 60px; width: 100%; 
        transition: all 0.4s ease; box-sizing: border-box; min-height: 100vh; 
    }
    .main-content.full-width { margin-left: 0; padding-left: 90px; }
    .content-container { max-width: 1100px; margin: 0 auto; }

    .back-link { display: inline-flex; align-items: center; gap: 10px; margin-top: 10px; margin-bottom: 30px; color: var(--primary); text-decoration: none; font-weight: 600; font-size: 14px; transition: 0.3s; opacity: 0.8;}
    .back-link:hover { opacity: 1; transform: translateX(-5px); }

    .hero-section { 
        display: flex; align-items: center; gap: 35px; margin-bottom: 40px; padding: 35px; 
        background: var(--glass-bg); backdrop-filter: blur(20px); border-radius: 30px; 
        border: 1px solid var(--glass-border); box-shadow: 0 20px 40px rgba(0,0,0,0.4); 
    }
    .hero-img { width: 180px; height: 180px; border-radius: 20px; object-fit: cover; box-shadow: 0 15px 35px rgba(0,0,0,0.5); flex-shrink: 0; }
    .hero-text h1 { font-family: 'Outfit', sans-serif; font-size: 46px; margin: 5px 0; line-height: 1.1; letter-spacing: -2px; color: var(--text-main); }
    
    .btn-edit-pl { background: rgba(255, 255, 255, 0.1); color: var(--text-main); border: 1px solid rgba(255, 255, 255, 0.2); padding: 12px 20px; border-radius: 12px; font-weight: 600; cursor: pointer; font-size: 13px; transition: 0.3s; }
    .btn-edit-pl:hover { background: rgba(255, 255, 255, 0.2); }

    table { width: 100%; border-collapse: separate; border-spacing: 0 10px; margin-bottom: 80px; }
    td { padding: 15px 20px; background: var(--glass-bg); border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); vertical-align: middle; color: var(--text-main); }
    td:first-child { border-left: 1px solid var(--glass-border); border-radius: 15px 0 0 15px; width: 45px; font-weight: bold; color: var(--text-muted); }
    td:last-child { border-right: 1px solid var(--glass-border); border-radius: 0 15px 15px 0; text-align: right; }
    tr:hover td { background: rgba(255, 255, 255, 0.1); }

    .btn-play-small { background: var(--primary-grad); color: white; border: none; width: 38px; height: 38px; border-radius: 50%; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s; }
    .btn-play-small:hover { transform: scale(1.1); box-shadow: 0 4px 10px rgba(162, 155, 254, 0.3); }
    .btn-play-small i { margin-left: 2px; }

    .success-msg { background: rgba(0, 206, 201, 0.1); color: var(--emerald); padding: 15px; border-radius: 16px; margin-bottom: 25px; border: 1px solid rgba(0, 206, 201, 0.3); display: flex; align-items: center; gap: 10px; font-weight: 500; backdrop-filter: blur(5px); }
    .error-msg { background: rgba(255, 71, 87, 0.1); color: #ff6b81; padding: 15px; border-radius: 16px; margin-bottom: 25px; border: 1px solid rgba(255, 71, 87, 0.3); display: flex; align-items: center; gap: 10px; font-weight: 500; backdrop-filter: blur(5px); }

    #edit_form textarea {
        background: rgba(0, 0, 0, 0.2);
        color: var(--text-main);
        border: 1px solid var(--glass-border);
    }
    #edit_form textarea:focus {
        outline: none;
        border-color: var(--primary);
    }

    @media (max-width: 768px) {
        .sidebar { width: 260px; box-shadow: 5px 0 15px rgba(0,0,0,0.5); }
        .main-content { margin-left: 0 !important; padding: 80px 20px 120px 20px !important; width: 100%; }
        .hamburger-menu { top: 15px; left: 15px; width: 40px; height: 40px; }
        .hero-section { flex-direction: column; align-items: center; text-align: center; }
    }
</style>
</head>
<body>
    <button class="hamburger-menu" onclick="toggleSidebar()"><i class="fa-solid fa-bars"></i></button>

    <div class="sidebar" id="sidebar">
        <h2>Velohertz</h2>
        <a href="index.php"><i class="fa-solid fa-house"></i> Beranda</a>
        <a href="search.php"><i class="fa-solid fa-magnifying-glass"></i> Cari</a>
        <a href="library.php" class="active"><i class="fa-solid fa-book"></i> Koleksi</a>
        <a href="profile.php"><i class="fa-solid fa-user"></i> Profil</a>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin mau keluar?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="content-container">
            <a href="library.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Kembali ke Koleksi</a>

            <div class="hero-section">
                <img src="<?php echo $pl_img; ?>" class="hero-img">
                <div class="hero-text">
                    <span style="font-weight: 800; font-size: 11px; letter-spacing: 2px; color: var(--primary);">PLAYLIST</span>
                    <div id="display_section">
                        <h1><?php echo htmlspecialchars($playlist['ptitle']); ?></h1>
                        <p style="color: var(--text-muted); margin-bottom: 20px;"><?php echo htmlspecialchars($playlist['pdesc']); ?></p>
                        <button onclick="toggleEdit()" class="btn-edit-pl"><i class="fa-solid fa-pen"></i> Edit Info</button>
                    </div>

                    <form id="edit_form" method="POST" style="display: none;">
                        <input type="text" name="new_title" value="<?php echo htmlspecialchars($playlist['ptitle']); ?>" style="display:block; font-family:'Outfit'; font-size:32px; font-weight:800; border:none; border-bottom:2px solid var(--primary); background:transparent; width:100%; color:var(--text-main); margin-bottom:10px; outline:none;" required>
                        <textarea name="new_desc" style="display:block; width:100%; height:60px; border:1px solid #ddd; border-radius:10px; padding:10px; font-family:inherit; resize:none; margin-bottom:15px;"><?php echo htmlspecialchars($playlist['pdesc']); ?></textarea>
                        <div style="display: flex; gap: 10px;">
                            <button type="submit" name="edit_playlist" class="btn-edit-pl" style="background:var(--emerald);">Simpan</button>
                            <button type="button" onclick="toggleEdit()" style="background:none; border:none; color:red; cursor:pointer; font-weight:600;">Batal</button>
                        </div>
                    </form>
                </div>
            </div>

            <?php echo $pesan; ?>

            <h3 style="font-family: 'Outfit', sans-serif; font-size: 24px; margin-bottom: 20px;">Daftar Lagu</h3>
            <table>
                <tbody>
                    <?php $n=1; while($tr = $result_tracks->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $n++; ?></td>
                            <td><strong><?php echo htmlspecialchars($tr['ttitle']); ?></strong><br><small><?php echo htmlspecialchars($tr['aname']); ?></small></td>
                            <td><?php echo htmlspecialchars($tr['atitle'] ?? 'Single'); ?></td>
                            <td>
                                <div style="display:inline-flex; align-items:center; gap:15px;">
                                    <button class="btn-play-small"><i class="fa-solid fa-play"></i></button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Hapus lagu ini dari playlist? 🥺');">
    <input type="hidden" name="tid_hapus" value="<?php echo $tr['tid']; ?>">
    <button type="submit" name="hapus_lagu" style="color:#ff6b81; border:none; background:none; cursor:pointer; font-size:18px; transition:0.2s;" onmouseover="this.style.transform='scale(1.2)'" onmouseout="this.style.transform='scale(1)'"><i class="fa-solid fa-circle-xmark"></i></button>
</form>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
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

        function toggleEdit() {
            var d = document.getElementById('display_section'), f = document.getElementById('edit_form');
            if(f.style.display === 'none'){ f.style.display='block'; d.style.display='none'; }
            else { f.style.display='none'; d.style.display='block'; }
        }
    </script>
</body>
</html>