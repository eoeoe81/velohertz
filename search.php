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
        $pesan_aksi = "<div style='background: rgba(254,226,226,0.8); border: 1px solid #fecaca; padding: 15px; border-radius: 12px; color: #e53e3e; margin-bottom: 25px; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'><i class='fa-solid fa-circle-exclamation'></i> Lagu sudah ada di playlist tersebut!</div>";
    } else {
        if ($conn->query("INSERT INTO playlistcontain (pid, tid) VALUES ('$pid', '$tid')") === TRUE) {
            $pesan_aksi = "<div style='background: rgba(209,250,229,0.8); border: 1px solid #a7f3d0; padding: 15px; border-radius: 12px; color: #10b981; margin-bottom: 25px; font-weight: 600; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'><i class='fa-solid fa-circle-check'></i> Berhasil menambahkan lagu ke playlist!</div>";
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
        .sidebar h2 { color: var(--primary); margin-bottom: 30px; font-style: italic; font-size: 26px; margin-top: 0; padding-left: 10px; text-shadow: 0 2px 4px rgba(255,255,255,0.4); }
        .sidebar a { display: flex; align-items: center; color: var(--text-muted); text-decoration: none; margin: 5px 0; font-weight: 600; transition: 0.3s; padding: 12px 15px; border-radius: 12px; border: 1px solid transparent; }
        .sidebar a i { margin-right: 12px; font-size: 18px; transition: 0.3s; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255, 255, 255, 0.5); color: var(--primary); transform: translateX(4px); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(255, 255, 255, 0.6); }
        .sidebar a:hover i, .sidebar a.active i { color: var(--primary); }
        .logout-btn { margin-top: auto; background: rgba(255, 255, 255, 0.3); color: #e53e3e !important; border: 1px solid rgba(254, 215, 215, 0.6); justify-content: center; }
        .logout-btn:hover { background: #e53e3e !important; color: white !important; transform: none; border-color: #e53e3e; }

        .main-content { margin-left: 240px; padding: 40px; width: calc(100% - 240px); margin-bottom: 120px; box-sizing: border-box; }
        
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
        .header h2 { margin: 0; color: var(--text-main); font-size: 28px; font-weight: 800;}
        .profile-badge { display: flex; align-items: center; background: rgba(255,255,255,0.8); padding: 8px 20px; border-radius: 30px; text-decoration: none; color: var(--text-main); border: 1px solid var(--glass-border); box-shadow: 0 4px 10px rgba(0,0,0,0.05); transition: 0.3s; font-weight: 700; }
        .profile-badge:hover { background: #ffffff; border-color: var(--primary); }

        /* FORM PENCARIAN EFEK KACA */
        .form-box { background: var(--glass-bg); backdrop-filter: blur(12px); padding: 30px; border-radius: 16px; border: 1px solid var(--glass-border); box-shadow: 0 8px 32px rgba(0,0,0,0.05); margin-bottom: 40px; display: inline-block; width: 100%; box-sizing: border-box; }
        .form-box h3 { margin-top: 0; color: var(--text-main); margin-bottom: 20px; font-size: 20px; }
        .search-box { width: 100%; max-width: 400px; padding: 14px 20px; border-radius: 30px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.7); font-size: 15px; outline: none; transition: 0.3s; font-family: inherit; color: var(--text-main); box-sizing: border-box; }
        .search-box:focus { background: #ffffff; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(15,82,186,0.1); }
        .btn-search { background: var(--primary); color: white; border: none; padding: 14px 28px; border-radius: 30px; font-weight: 600; cursor: pointer; transition: 0.3s; margin-left: 10px; font-family: inherit; font-size: 15px; box-shadow: 0 4px 10px rgba(15,82,186,0.2); }
        .btn-search:hover { background: #0c4399; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(15,82,186,0.3); }

        /* TABEL EFEK KACA */
        table { width: 100%; border-collapse: separate; border-spacing: 0; background: var(--glass-bg); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-radius: 16px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.05); border: 1px solid var(--glass-border); margin-bottom: 20px;}
        th { text-align: left; padding: 18px 20px; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--glass-border); font-weight: 700; background: rgba(255,255,255,0.4); }
        td { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.3); font-size: 14px; vertical-align: middle; color: var(--text-main); transition: 0.2s; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: rgba(255,255,255,0.8); }
        
        .play-icon-btn { background: rgba(15,82,186,0.1); color: var(--primary); border: none; width: 36px; height: 36px; border-radius: 50%; font-size: 14px; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; }
        .play-icon-btn:hover { background: var(--primary); color: white; transform: scale(1.1); box-shadow: 0 4px 10px rgba(15,82,186,0.3); }
        .btn-add { background: #10b981; color: white; border: none; padding: 8px 12px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: 0.2s; margin-left: 10px; display: inline-flex; align-items: center; justify-content: center; }
        .btn-add:hover { background: #059669; }
        
        select[name='pid'] { padding: 10px 15px; border-radius: 10px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.8); outline: none; font-family: inherit; font-size: 13px; color: var(--text-main); transition: 0.3s; }
        select[name='pid']:focus { border-color: var(--primary); background: #fff; }

        @media screen and (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; flex-direction: row; align-items: center; padding: 15px 20px; border-right: none; border-bottom: 1px solid var(--glass-border); overflow-x: auto; }
            .sidebar h2 { margin: 0 20px 0 0; font-size: 20px; }
            .sidebar a { margin: 0 10px 0 0; white-space: nowrap; }
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
            .form-box form { display: flex; flex-direction: column; gap: 15px; align-items: stretch; }
            .btn-search { margin-left: 0; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>🎵 Velohertz</h2>
        <a href="index.php"><i class="fa-solid fa-house" style="width: 25px;"></i> Beranda</a>
        <a href="search.php" class="active"><i class="fa-solid fa-magnifying-glass" style="width: 25px;"></i> Cari</a>
        <a href="library.php"><i class="fa-solid fa-book" style="width: 25px;"></i> Koleksi Kamu</a>
        <a href="profile.php"><i class="fa-solid fa-user" style="width: 25px;"></i> Profil</a>
        <?php if($_SESSION['username'] == 'admin'): ?>
            <a href="admin.php" style="color: #e53e3e;"><i class="fa-solid fa-shield-halved" style="width: 25px;"></i> Admin Panel</a>
        <?php endif; ?>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Apakah kamu yakin ingin keluar?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Cari Lagu Kesukaanmu 🔍</h2>
            <a href="profile.php" class="profile-badge">
                <i class="fa-solid fa-circle-user" style="margin-right: 8px; font-size: 18px; color: var(--primary);"></i> 
                <?php echo htmlspecialchars($username); ?>
            </a>
        </div>

        <?php echo $pesan_aksi; ?>

        <div class="form-box">
            <h3>Jelajahi Database Velohertz</h3>
            <form action="" method="GET" style="display: flex; align-items: center; flex-wrap: wrap;">
                <input type="text" name="keyword" class="search-box" placeholder="Ketik judul lagu, artis, atau album..." value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>">
                <button type="submit" class="btn-search"><i class="fa-solid fa-magnifying-glass"></i> Cari Lagu</button>
            </form>
        </div>
        
        <div class="search-results">
            <?php
            if (isset($_GET['keyword']) && trim($_GET['keyword']) != '') {
                $keyword = $conn->real_escape_string(trim($_GET['keyword']));
                
                // Kueri gabungan Track dan Album (Bisa search nama album juga)
                $sql_search = "SELECT t.*, a.atitle FROM Track t LEFT JOIN Album a ON t.alid = a.alid 
                               WHERE t.ttitle LIKE '%$keyword%' OR t.aname LIKE '%$keyword%' OR a.atitle LIKE '%$keyword%'";
                $result_search = $conn->query($sql_search);
                
                if ($result_search && $result_search->num_rows > 0) {
                    echo "<table>";
                    echo "<tr><th style='width: 40px;'>#</th><th>Judul Lagu</th><th>Artis</th><th>Album</th><th style='text-align: center;'>Putar</th><th style='text-align: right; padding-right: 20px;'>Aksi</th></tr>";
                    
                    $no = 1;
                    while($row = $result_search->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><span style='color: var(--text-muted); font-weight: bold;'>" . $no++ . "</span></td>";
                        echo "<td><strong>" . htmlspecialchars($row["ttitle"]) . "</strong></td>";
                        echo "<td style='color: var(--text-muted);'>" . htmlspecialchars($row["aname"]) . "</td>";
                        echo "<td style='color: var(--primary); font-weight: 600;'>" . htmlspecialchars($row["atitle"] ?? 'Tanpa Album') . "</td>";
                        
                        echo "<td style='text-align: center;'><button class='play-icon-btn'><i class='fa-solid fa-play' style='margin-left: 2px;'></i></button></td>";
                        
                        echo "<td style='text-align: right; padding-right: 20px;'>";
                        if (count($playlists) > 0) {
                            echo "<form action='' method='POST' style='display: flex; align-items: center; justify-content: flex-end;'>";
                            echo "<input type='hidden' name='tid' value='" . $row["tid"] . "'>";
                            echo "<input type='hidden' name='keyword' value='" . htmlspecialchars($_GET['keyword']) . "'>";
                            echo "<select name='pid' required><option value=''>-- Pilih Playlist --</option>";
                            foreach ($playlists as $pl) { echo "<option value='" . $pl['pid'] . "'>" . htmlspecialchars($pl['ptitle']) . "</option>"; }
                            echo "</select><button type='submit' name='tambah_lagu' class='btn-add' title='Tambahkan'><i class='fa-solid fa-plus'></i> Tambah</button></form>";
                        } else {
                            echo "<span style='color: var(--text-muted); font-size: 13px; font-weight: 600;'>Buat playlist dulu</span>";
                        }
                        echo "</td></tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<div style='background: rgba(255,255,255,0.7); backdrop-filter: blur(10px); padding: 30px; border-radius: 16px; border: 1px solid var(--glass-border); text-align: center; color: var(--text-muted); font-weight: 600; box-shadow: 0 4px 15px rgba(0,0,0,0.02);'>Maaf, lagu, artis, atau album tidak ditemukan. 😢</div>";
                }
            }
            ?>
        </div>
    </div>
</body>
</html>