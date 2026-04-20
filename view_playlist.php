<?php
session_start();
if (!isset($_SESSION['username'])) { header("Location: login.php"); exit(); }
include 'koneksi.php';

if (!isset($_GET['pid'])) { header("Location: library.php"); exit(); }
$pid = $conn->real_escape_string($_GET['pid']);
$username = $_SESSION['username'];

$pesan = "";

// 1. LOGIKA EDIT PLAYLIST (Nama & Bio)
if (isset($_POST['edit_playlist'])) {
    $new_title = $conn->real_escape_string($_POST['new_title']);
    $new_desc = $conn->real_escape_string($_POST['new_desc']);
    
    if ($conn->query("UPDATE playlist SET ptitle='$new_title', pdesc='$new_desc' WHERE pid='$pid'")) {
        $pesan = "<div style='color: #10b981; background: rgba(209,250,229,0.8); padding: 15px; border-radius: 12px; border: 1px solid #a7f3d0; margin-bottom: 20px; font-weight: bold; box-shadow: 0 4px 15px rgba(16,185,129,0.1);'><i class='fa-solid fa-circle-check'></i> Detail playlist berhasil diperbarui!</div>";
    }
}

// 2. LOGIKA HAPUS LAGU
if (isset($_POST['hapus_lagu'])) {
    $tid_hapus = $conn->real_escape_string($_POST['tid_hapus']);
    $conn->query("DELETE FROM playlistcontain WHERE pid='$pid' AND tid='$tid_hapus'");
    $pesan = "<div style='color: #e53e3e; background: rgba(254,226,226,0.8); padding: 15px; border-radius: 12px; border: 1px solid #fecaca; margin-bottom: 20px; font-weight: bold; box-shadow: 0 4px 15px rgba(229,62,62,0.1);'><i class='fa-solid fa-trash'></i> Lagu dikeluarkan dari playlist.</div>";
}

// 3. LOGIKA TAMBAH LAGU
if (isset($_POST['tambah_ke_playlist'])) {
    $tid_tambah = $conn->real_escape_string($_POST['tid_tambah']);
    $cek = $conn->query("SELECT * FROM playlistcontain WHERE pid='$pid' AND tid='$tid_tambah'");
    if ($cek->num_rows > 0) {
        $pesan = "<div style='color: #e53e3e; background: rgba(254,226,226,0.8); padding: 15px; border-radius: 12px; border: 1px solid #fecaca; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> Lagu sudah ada di dalam playlist!</div>";
    } else {
        $conn->query("INSERT INTO playlistcontain (pid, tid) VALUES ('$pid', '$tid_tambah')");
        $pesan = "<div style='color: #10b981; background: rgba(209,250,229,0.8); padding: 15px; border-radius: 12px; border: 1px solid #a7f3d0; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-check'></i> Lagu berhasil ditambahkan!</div>";
    }
}

// AMBIL INFO PLAYLIST
$sql_info = "SELECT * FROM playlist WHERE pid='$pid'";
$result_info = $conn->query($sql_info);
if ($result_info->num_rows == 0) { echo "Playlist tidak ditemukan!"; exit(); }
$playlist = $result_info->fetch_assoc();

// AMBIL DAFTAR LAGU DALAM PLAYLIST
$sql_tracks = "SELECT t.*, a.atitle FROM Track t JOIN playlistcontain pc ON t.tid = pc.tid LEFT JOIN Album a ON t.alid = a.alid WHERE pc.pid = '$pid'";
$result_tracks = $conn->query($sql_tracks);

// GAMBAR PLAYLIST (Otomatis pakai Picsum biar konsisten sama halaman Library)
$pl_img = "https://picsum.photos/seed/" . urlencode($pid) . "/300/300";
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($playlist['ptitle']); ?> - Velohertz</title>
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
        
        body { margin: 0; font-family: 'Inter', 'Segoe UI', sans-serif; background: var(--app-bg); background-attachment: fixed; color: var(--text-main); display: flex; overflow-x: hidden; }

        /* SIDEBAR KACA (Konsisten!) */
        .sidebar { width: 240px; background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); padding: 30px 20px; height: 100vh; position: fixed; z-index: 100; box-sizing: border-box; border-right: 1px solid rgba(255, 255, 255, 0.4); display: flex; flex-direction: column; box-shadow: 4px 0 30px rgba(0,0,0,0.03); }
        .sidebar h2 { color: var(--primary); margin-bottom: 30px; font-style: italic; font-size: 26px; margin-top: 0; padding-left: 10px; }
        .sidebar a { display: flex; align-items: center; color: var(--text-muted); text-decoration: none; margin: 5px 0; font-weight: 600; transition: 0.3s; padding: 12px 15px; border-radius: 12px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255, 255, 255, 0.5); color: var(--primary); transform: translateX(4px); box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(255, 255, 255, 0.6); }
        .sidebar a i { margin-right: 12px; font-size: 18px; }
        .logout-btn { margin-top: auto; background: rgba(255, 255, 255, 0.3); color: #e53e3e !important; border: 1px solid rgba(254, 215, 215, 0.6); justify-content: center; }
        .logout-btn:hover { background: #e53e3e !important; color: white !important; }

        .main-content { margin-left: 240px; padding: 40px; width: calc(100% - 240px); box-sizing: border-box; margin-bottom: 100px; }
        
        .back-link { display: inline-flex; align-items: center; gap: 8px; margin-bottom: 25px; color: var(--text-muted); text-decoration: none; font-weight: 600; transition: 0.2s; background: rgba(255,255,255,0.5); padding: 10px 20px; border-radius: 30px; border: 1px solid var(--glass-border); }
        .back-link:hover { color: var(--primary); background: #fff; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }

        /* HEADER PLAYLIST EFEK KACA MAHAL */
        .playlist-header { background: var(--glass-bg); backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px); border-radius: 24px; border: 1px solid var(--glass-border); box-shadow: 0 10px 30px rgba(0,0,0,0.05); padding: 40px; display: flex; align-items: center; gap: 35px; margin-bottom: 40px; }
        .playlist-cover-img { width: 180px; height: 180px; border-radius: 16px; object-fit: cover; box-shadow: 0 10px 25px rgba(0,0,0,0.1); flex-shrink: 0;}
        
        .playlist-info { width: 100%; }
        .playlist-info p.label { font-weight: 700; color: var(--text-muted); margin:0; letter-spacing: 1px; font-size: 13px; text-transform: uppercase;}
        .playlist-info h1 { font-size: 46px; margin: 5px 0 10px 0; color: var(--text-main); letter-spacing: -1px; font-weight: 800; word-break: break-word; }
        .playlist-info p.desc { color: var(--text-muted); margin: 0 0 20px 0; max-width: 600px; line-height: 1.6; font-size: 15px; font-weight: 500;}

        /* TOMBOL EDIT */
        .btn-edit { background: rgba(255, 255, 255, 0.7); color: var(--text-main); border: 1px solid var(--glass-border); padding: 10px 20px; border-radius: 30px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 4px 10px rgba(0,0,0,0.02);}
        .btn-edit:hover { background: #ffffff; color: var(--primary); box-shadow: 0 6px 15px rgba(15,82,186,0.1); transform: translateY(-2px);}
        .btn-batal { background: rgba(229,62,62,0.1); color: #e53e3e; border: 1px solid #fecaca; padding: 12px 25px; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer; transition: 0.3s; font-family: inherit;}
        .btn-batal:hover { background: #e53e3e; color: white; }

        /* FORM KOTAK KACA (Konsisten) */
        .form-box { background: var(--glass-bg); backdrop-filter: blur(15px); padding: 30px; border-radius: 20px; border: 1px solid var(--glass-border); box-shadow: 0 10px 30px rgba(0,0,0,0.05); margin-bottom: 40px; }
        .form-box h3 { margin-top: 0; margin-bottom: 20px; color: var(--text-main); font-size: 20px; font-weight: 800;}
        .search-box { width: 100%; max-width: 350px; padding: 14px 20px; border-radius: 12px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.7); font-size: 15px; outline: none; transition: 0.3s; font-family: inherit; color: var(--text-main); box-sizing: border-box;}
        .search-box:focus { background: #ffffff; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(15,82,186,0.1); }
        .btn-search { background: var(--primary); color: white; border: none; padding: 14px 28px; border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.3s; font-family: inherit; font-size: 15px; box-shadow: 0 4px 10px rgba(15,82,186,0.2); }
        .btn-search:hover { background: #0c4399; transform: translateY(-2px); }
        .btn-add { background: #10b981; color: white; border: none; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-family: inherit; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; font-size: 13px;}
        .btn-add:hover { background: #059669; }

        /* TABEL KONSISTEN */
        table { width: 100%; border-collapse: separate; border-spacing: 0; background: var(--glass-bg); backdrop-filter: blur(12px); border-radius: 16px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.05); border: 1px solid var(--glass-border); margin-bottom: 20px;}
        th { text-align: left; padding: 18px 20px; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--glass-border); font-weight: 700; background: rgba(255,255,255,0.4); }
        td { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.3); font-size: 14px; vertical-align: middle; color: var(--text-main); transition: 0.2s; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: rgba(255,255,255,0.8); }
        
        .play-icon-btn { background: rgba(15,82,186,0.1); color: var(--primary); border: none; width: 36px; height: 36px; border-radius: 50%; font-size: 14px; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; }
        .play-icon-btn:hover { background: var(--primary); color: white; transform: scale(1.1); box-shadow: 0 4px 10px rgba(15,82,186,0.3); }
        .delete-icon-btn { background: rgba(229,62,62,0.1); color: #e53e3e; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; transition: 0.2s; display: inline-flex; align-items: center; gap: 6px; font-family: inherit; font-size: 13px; font-weight: 600;}
        .delete-icon-btn:hover { background: #e53e3e; color: white; }

        @media screen and (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; flex-direction: row; align-items: center; padding: 15px 20px; border-bottom: 1px solid var(--glass-border); border-right: none; overflow-x: auto; }
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
            .playlist-header { flex-direction: column; text-align: center; padding: 30px; }
            .form-box form { display: flex; flex-direction: column; gap: 15px; }
            .search-box, .btn-search { width: 100%; max-width: 100%; margin: 0; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
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
        <a href="library.php" class="back-link"><i class="fa-solid fa-arrow-left"></i> Kembali ke Koleksi</a>
        
        <div class="playlist-header">
            <img src="<?php echo $pl_img; ?>" class="playlist-cover-img" alt="Cover Playlist">
            
            <div class="playlist-info">
                <p class="label">PLAYLIST</p>
                
                <div id="display_section">
                    <h1><?php echo htmlspecialchars($playlist['ptitle']); ?></h1>
                    <p class="desc"><?php echo htmlspecialchars($playlist['pdesc']); ?></p>
                    <button onclick="toggleEdit()" class="btn-edit"><i class="fa-solid fa-pen"></i> Edit Detail Playlist</button>
                </div>

                <form id="edit_form" method="POST" style="display: none; background: rgba(255,255,255,0.6); padding: 25px; border-radius: 16px; border: 1px solid var(--glass-border); margin-top: 15px; max-width: 500px; box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                    <label style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 8px;">Nama Playlist</label>
                    <input type="text" name="new_title" value="<?php echo htmlspecialchars($playlist['ptitle']); ?>" class="search-box" style="margin-bottom: 15px; width: 100%; max-width: 100%;" required>
                    
                    <label style="font-size: 13px; font-weight: 700; color: var(--text-main); display: block; margin-bottom: 8px;">Bio / Deskripsi</label>
                    <textarea name="new_desc" class="search-box" style="width: 100%; max-width: 100%; height: 90px; resize: none; margin-bottom: 20px; border-radius: 12px;" placeholder="Tulis deskripsi playlistmu..."><?php echo htmlspecialchars($playlist['pdesc']); ?></textarea>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="edit_playlist" class="btn-search" style="margin: 0; padding: 12px 25px;">Simpan Perubahan</button>
                        <button type="button" onclick="toggleEdit()" class="btn-batal">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        <?php echo $pesan; ?>

        <div class="form-box">
            <h3>Cari & Tambah Lagu Baru</h3>
            <form action="" method="GET" style="display: flex; align-items: center; gap: 10px;">
                <input type="hidden" name="pid" value="<?php echo htmlspecialchars($pid); ?>">
                <input type="text" name="cari_lagu" class="search-box" placeholder="Ketik judul lagu atau artis..." value="<?php echo isset($_GET['cari_lagu']) ? htmlspecialchars($_GET['cari_lagu']) : ''; ?>" required>
                <button type="submit" class="btn-search" style="margin: 0;"><i class="fa-solid fa-magnifying-glass"></i> Cari</button>
            </form>

            <?php
            if (isset($_GET['cari_lagu']) && trim($_GET['cari_lagu']) != '') {
                $keyword = $conn->real_escape_string(trim($_GET['cari_lagu']));
                $res_cari = $conn->query("SELECT * FROM Track WHERE ttitle LIKE '%$keyword%' OR aname LIKE '%$keyword%' LIMIT 5");
                
                if ($res_cari && $res_cari->num_rows > 0) {
                    echo "<table style='margin-top: 25px; margin-bottom: 0;'>";
                    echo "<tr><th>Judul Lagu</th><th>Artis</th><th style='text-align: right;'>Aksi</th></tr>";
                    while($rc = $res_cari->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td><strong>" . htmlspecialchars($rc['ttitle']) . "</strong></td>";
                        echo "<td>" . htmlspecialchars($rc['aname']) . "</td>";
                        echo "<td style='text-align:right;'>
                                <form method='POST' style='margin:0;'>
                                    <input type='hidden' name='tid_tambah' value='".$rc['tid']."'>
                                    <button type='submit' name='tambah_ke_playlist' class='btn-add'><i class='fa-solid fa-plus'></i> Tambah</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                    echo "</table>";
                } else {
                    echo "<p style='color: #e53e3e; margin-top: 20px; font-weight: 600;'><i class='fa-solid fa-circle-exclamation'></i> Lagu tidak ditemukan di database.</p>";
                }
            }
            ?>
        </div>

        <h3 style="margin-bottom: 20px; color: var(--text-main); font-size: 20px; font-weight: 800;">Daftar Lagu (<?php echo $result_tracks->num_rows; ?>)</h3>
        <table>
            <tr><th style="width: 50px;">#</th><th>Judul Lagu</th><th>Artis</th><th>Album</th><th style="text-align: right; padding-right: 20px;">Aksi</th></tr>
            <?php
            if ($result_tracks && $result_tracks->num_rows > 0) {
                $no = 1;
                while($row = $result_tracks->fetch_assoc()) {
                    echo "<tr><td><span style='color: var(--text-muted); font-weight: bold;'>" . $no++ . "</span></td>";
                    echo "<td><strong>" . htmlspecialchars($row["ttitle"]) . "</strong></td>";
                    echo "<td style='color: var(--text-muted);'>" . htmlspecialchars($row["aname"]) . "</td>";
                    echo "<td style='color: var(--primary); font-weight: 600;'>" . htmlspecialchars($row["atitle"] ?? 'Tanpa Album') . "</td>";
                    
                    echo "<td style='text-align: right; padding-right: 20px; white-space: nowrap;'>";
                    echo "<button class='play-icon-btn' style='margin-right: 8px;'><i class='fa-solid fa-play'></i></button>";
                    echo "<form action='' method='POST' style='display:inline;'>
                            <input type='hidden' name='tid_hapus' value='" . $row["tid"] . "'>
                            <button type='submit' name='hapus_lagu' class='delete-icon-btn' onclick=\"return confirm('Keluarkan lagu ini dari playlist?');\"><i class='fa-solid fa-trash'></i> Hapus</button>
                          </form>";
                    echo "</td></tr>";
                }
            } else { 
                echo "<tr><td colspan='5' style='text-align:center; color: var(--text-muted); padding: 40px; font-weight: 500;'>Playlist ini masih kosong. Silakan cari dan tambah lagu di atas.</td></tr>"; 
            }
            ?>
        </table>
    </div>

    <script>
        function toggleEdit() {
            var displaySection = document.getElementById('display_section');
            var editForm = document.getElementById('edit_form');

            if (editForm.style.display === 'none' || editForm.style.display === '') {
                editForm.style.display = 'block';
                displaySection.style.display = 'none';
            } else {
                editForm.style.display = 'none';
                displaySection.style.display = 'block';
            }
        }
    </script>
</body>
</html>