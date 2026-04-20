<?php
session_start();
// PROTEKSI HALAMAN: Cuma 'admin' yang boleh masuk!
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') { 
    header("Location: index.php"); 
    exit(); 
}
include 'koneksi.php';

$pesan = "";

// 1. LOGIKA TAMBAH ALBUM BARU
if (isset($_POST['tambah_album'])) {
    $alid = uniqid('alb_'); 
    $atitle = $conn->real_escape_string($_POST['atitle']);
    $adate = $conn->real_escape_string($_POST['adate']);

    try {
        if ($conn->query("INSERT INTO Album (alid, atitle, adate) VALUES ('$alid', '$atitle', '$adate')") === TRUE) {
            $pesan = "<div style='background: rgba(209,250,229,0.8); border: 1px solid #10b981; padding: 15px; border-radius: 10px; color: #047857; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-check'></i> Album '$atitle' berhasil ditambahkan!</div>";
        }
    } catch (mysqli_sql_exception $e) {
        $pesan = "<div style='background: rgba(254,226,226,0.8); border: 1px solid #e53e3e; padding: 15px; border-radius: 10px; color: #b91c1c; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> Gagal menambah album: " . $conn->error . "</div>";
    }
}

// 2. LOGIKA EDIT ALBUM (FITUR BARU)
if (isset($_POST['edit_album'])) {
    $edit_alid = $conn->real_escape_string($_POST['edit_alid']);
    $edit_atitle = $conn->real_escape_string($_POST['edit_atitle']);
    $edit_adate = $conn->real_escape_string($_POST['edit_adate']);

    try {
        if ($conn->query("UPDATE Album SET atitle='$edit_atitle', adate='$edit_adate' WHERE alid='$edit_alid'") === TRUE) {
            $pesan = "<div style='background: rgba(209,250,229,0.8); border: 1px solid #10b981; padding: 15px; border-radius: 10px; color: #047857; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-check'></i> Detail Album '$edit_atitle' berhasil diperbarui!</div>";
        }
    } catch (mysqli_sql_exception $e) {
        $pesan = "<div style='background: rgba(254,226,226,0.8); border: 1px solid #e53e3e; padding: 15px; border-radius: 10px; color: #b91c1c; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> Gagal mengedit album: " . $conn->error . "</div>";
    }
}

// 3. LOGIKA HAPUS ALBUM (FITUR BARU)
if (isset($_POST['hapus_album'])) {
    $hapus_alid = $conn->real_escape_string($_POST['hapus_alid']);
    try {
        $conn->query("DELETE FROM Album WHERE alid='$hapus_alid'");
        $pesan = "<div style='background: rgba(254,226,226,0.8); border: 1px solid #e53e3e; padding: 15px; border-radius: 10px; color: #b91c1c; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-trash'></i> Album berhasil dihapus dari sistem!</div>";
    } catch (mysqli_sql_exception $e) {
        $pesan = "<div style='background: rgba(254,226,226,0.8); border: 1px solid #e53e3e; padding: 15px; border-radius: 10px; color: #b91c1c; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> Gagal Menghapus Album! Pastikan album ini sudah tidak memiliki lagu di dalamnya.</div>";
    }
}

// 4. LOGIKA TAMBAH LAGU
if (isset($_POST['tambah_lagu'])) {
    $tid = uniqid('trk_'); 
    $ttitle = $conn->real_escape_string($_POST['ttitle']);
    $aname = $conn->real_escape_string($_POST['aname']);
    $alid = isset($_POST['alid']) ? $conn->real_escape_string($_POST['alid']) : ''; 
    $duration = 200000; 

    if (empty($alid)) {
        $pesan = "<div style='background: rgba(254,226,226,0.8); border: 1px solid #e53e3e; padding: 15px; border-radius: 10px; color: #b91c1c; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> Gagal: Silakan cari dan KLIK nama album dari daftar!</div>";
    } else {
        try {
            if ($conn->query("INSERT INTO Track (tid, ttitle, duration, aname, alid) VALUES ('$tid', '$ttitle', '$duration', '$aname', '$alid')") === TRUE) {
                $pesan = "<div style='background: rgba(209,250,229,0.8); border: 1px solid #10b981; padding: 15px; border-radius: 10px; color: #047857; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-check'></i> Lagu berhasil ditambahkan!</div>";
            }
        } catch (mysqli_sql_exception $e) {
            $pesan = "<div style='background: rgba(254,226,226,0.8); border: 1px solid #e53e3e; padding: 15px; border-radius: 10px; color: #b91c1c; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> Gagal: " . $conn->error . "</div>";
        }
    }
}

// 5. LOGIKA HAPUS LAGU
if (isset($_POST['hapus_lagu'])) {
    $tid_hapus = $conn->real_escape_string($_POST['tid_hapus']);
    try {
        $conn->query("DELETE FROM playlistcontain WHERE tid='$tid_hapus'");
        $conn->query("DELETE FROM Track WHERE tid='$tid_hapus'");
        $pesan = "<div style='background: rgba(254,226,226,0.8); border: 1px solid #e53e3e; padding: 15px; border-radius: 10px; color: #b91c1c; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-trash'></i> Lagu berhasil dihapus!</div>";
    } catch (mysqli_sql_exception $e) {
        $pesan = "<div style='background: rgba(254,226,226,0.8); border: 1px solid #e53e3e; padding: 15px; border-radius: 10px; color: #b91c1c; margin-bottom: 20px; font-weight: bold;'><i class='fa-solid fa-circle-exclamation'></i> Gagal Menghapus Lagu! Masih terkait data User.</div>";
    }
}

// AMBIL DATA STATISTIK
$jml_user = $conn->query("SELECT COUNT(*) as total FROM User")->fetch_assoc()['total'];
$jml_lagu = $conn->query("SELECT COUNT(*) as total FROM Track")->fetch_assoc()['total'];
$jml_album = $conn->query("SELECT COUNT(*) as total FROM Album")->fetch_assoc()['total'];

// AMBIL DATA TABEL
$result_tracks = $conn->query("SELECT DISTINCT t.tid, t.ttitle, t.aname, a.atitle FROM Track t LEFT JOIN Album a ON t.alid = a.alid ORDER BY t.tid DESC LIMIT 100");
$result_albums_table = $conn->query("SELECT * FROM Album ORDER BY adate DESC"); // Untuk tabel manajemen album
$result_albums_dropdown = $conn->query("SELECT alid, atitle FROM Album ORDER BY atitle ASC"); // Untuk form input lagu
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Velohertz</title>
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

        .sidebar { width: 240px; background: rgba(255, 255, 255, 0.25); backdrop-filter: blur(20px); padding: 30px 20px; height: 100vh; position: fixed; z-index: 100; box-sizing: border-box; border-right: 1px solid rgba(255, 255, 255, 0.4); display: flex; flex-direction: column; }
        .sidebar h2 { color: var(--primary); margin-bottom: 30px; font-style: italic; font-size: 26px; margin-top: 0; padding-left: 10px; }
        .sidebar a { display: flex; align-items: center; color: var(--text-muted); text-decoration: none; margin: 5px 0; font-weight: 600; transition: 0.3s; padding: 12px 15px; border-radius: 12px; }
        .sidebar a i { margin-right: 12px; font-size: 18px; }
        .sidebar a:hover, .sidebar a.active { background: rgba(255, 255, 255, 0.5); color: var(--primary); transform: translateX(4px); }
        .sidebar a:hover i, .sidebar a.active i { color: var(--primary); }
        .logout-btn { margin-top: auto; background: rgba(255, 255, 255, 0.3); color: #e53e3e !important; border: 1px solid rgba(254, 215, 215, 0.6); justify-content: center; }

        .main-content { margin-left: 240px; padding: 40px 50px; width: calc(100% - 240px); box-sizing: border-box; }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 1px solid var(--glass-border); }
        .header h1 { margin: 0; font-size: 28px; font-weight: 800; }
        .admin-badge { background: rgba(229,62,62,0.1); color: #e53e3e; padding: 8px 16px; border-radius: 30px; font-weight: 700; font-size: 13px; border: 1px solid #fecaca; }

        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
        .stat-card { background: var(--glass-bg); backdrop-filter: blur(12px); padding: 25px; border-radius: 16px; border: 1px solid var(--glass-border); border-bottom: 4px solid var(--primary); display: flex; align-items: center; gap: 20px; }
        .stat-icon { font-size: 40px; color: var(--primary); }
        .stat-info h3 { margin: 0; font-size: 32px; font-weight: 800; }
        .stat-info p { margin: 0; color: var(--text-muted); font-size: 13px; font-weight: 600; text-transform: uppercase; }

        .forms-container { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 40px; }
        .form-box { background: var(--glass-bg); backdrop-filter: blur(12px); padding: 30px; border-radius: 16px; border: 1px solid var(--glass-border); overflow: visible;}
        .form-box.lagu { border-left: 5px solid #10b981; }
        .form-box.album { border-left: 5px solid var(--primary); }
        .form-box h2 { font-size: 18px; margin-top: 0; margin-bottom: 20px; }
        
        .input-group { margin-bottom: 20px; }
        .input-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 14px; }
        .input-group input { width: 100%; padding: 14px; border-radius: 10px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.7); font-size: 14px; outline: none; transition: 0.3s; box-sizing: border-box; font-family: inherit; }
        .input-group input:focus { background: #fff; border-color: var(--primary); }
        
        .btn-submit { background: #10b981; color: white; border: none; padding: 14px 25px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.3s; font-family: inherit; font-size: 15px; width: 100%; }
        .btn-submit:hover { background: #059669; transform: translateY(-2px); }
        .btn-submit.album-btn { background: var(--primary); }
        .btn-submit.album-btn:hover { background: #0c4399; }

        .album-option { padding: 12px 15px; cursor: pointer; border-bottom: 1px solid rgba(0,0,0,0.05); font-size: 14px; transition: 0.2s; }
        .album-option:hover { background: var(--primary); color: white; }

        .search-box-admin { width: 100%; padding: 16px 25px; border-radius: 30px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.7); font-size: 15px; outline: none; transition: 0.3s; margin-bottom: 25px; box-sizing: border-box; font-family: inherit;}
        .search-box-admin:focus { background: #fff; border-color: var(--primary); }

        table { width: 100%; border-collapse: separate; border-spacing: 0; background: var(--glass-bg); backdrop-filter: blur(12px); border-radius: 16px; overflow: hidden; border: 1px solid var(--glass-border); margin-bottom: 40px;}
        th { text-align: left; padding: 18px 25px; color: var(--text-muted); font-size: 13px; text-transform: uppercase; font-weight: 700; background: rgba(255,255,255,0.4); border-bottom: 1px solid var(--glass-border); }
        td { padding: 18px 25px; border-bottom: 1px solid rgba(255,255,255,0.3); font-size: 15px; vertical-align: middle; }
        tr:hover td { background-color: rgba(255,255,255,0.8); }
        
        .btn-delete { background: rgba(229,62,62,0.1); color: #e53e3e; border: 1px solid #fecaca; padding: 8px 12px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; }
        .btn-delete:hover { background: #e53e3e; color: white; }
        .btn-edit { background: rgba(15,82,186,0.1); color: var(--primary); border: 1px solid rgba(15,82,186,0.3); padding: 8px 12px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; margin-right: 5px; }
        .btn-edit:hover { background: var(--primary); color: white; }

        /* MODAL EFEK KACA EMBUN */
        .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); z-index: 2000; justify-content: center; align-items: center; }
        .modal-box { background: rgba(255, 255, 255, 0.85); padding: 40px; border-radius: 20px; width: 400px; max-width: 90%; border: 1px solid #ffffff; box-shadow: 0 15px 50px rgba(0,0,0,0.1); position: relative; }
        .modal-box h2 { margin-top: 0; color: var(--primary); font-size: 22px; }
        .btn-close-modal { position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 20px; color: var(--text-muted); cursor: pointer; }
        .btn-close-modal:hover { color: #e53e3e; }

    </style>
</head>
<body>

    <div class="sidebar">
        <h2>🎵 Velohertz</h2>
        <a href="admin.php" class="active"><i class="fa-solid fa-gauge-high" style="width: 25px;"></i> Dashboard</a>
        <a href="index.php"><i class="fa-solid fa-house" style="width: 25px;"></i> Kembali ke Web</a>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Keluar dari mode Admin?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content">
        <div class="header">
            <div>
                <h1 style="margin: 0; font-size: 24px;">Admin Control Panel</h1>
                <p style="margin: 5px 0 0 0; color: var(--text-muted);">Kelola data pusat Velohertz</p>
            </div>
            <div class="admin-badge"><i class="fa-solid fa-shield-halved"></i> SUPERUSER</div>
        </div>

        <?php echo $pesan; ?>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-users"></i></div>
                <div class="stat-info">
                    <h3><?php echo $jml_user; ?></h3>
                    <p>Total Pengguna</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-music"></i></div>
                <div class="stat-info">
                    <h3><?php echo $jml_lagu; ?></h3>
                    <p>Total Lagu DB</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fa-solid fa-compact-disc"></i></div>
                <div class="stat-info">
                    <h3><?php echo $jml_album; ?></h3>
                    <p>Total Album DB</p>
                </div>
            </div>
        </div>

        <div class="forms-container">
            <div class="form-box album">
                <h2><i class="fa-solid fa-compact-disc"></i> Tambah Master Album Baru</h2>
                <form action="" method="POST">
                    <div class="input-group">
                        <label>Judul Album</label>
                        <input type="text" name="atitle" required placeholder="Contoh: Divide">
                    </div>
                    <div class="input-group">
                        <label>Tanggal Rilis</label>
                        <input type="date" name="adate" required>
                    </div>
                    <button type="submit" name="tambah_album" class="btn-submit album-btn">Simpan Album ke Database</button>
                </form>
            </div>

            <div class="form-box lagu">
                <h2><i class="fa-solid fa-music"></i> Tambah Master Lagu Baru</h2>
                <form action="" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="input-group">
                            <label>Judul Lagu</label>
                            <input type="text" name="ttitle" required placeholder="Contoh: Shape of You">
                        </div>
                        <div class="input-group">
                            <label>Nama Artis</label>
                            <input type="text" name="aname" required placeholder="Contoh: Ed Sheeran">
                        </div>
                    </div>
                    
                    <div class="input-group" style="position: relative;">
                        <label>Cari & Pilih Album (Wajib)</label>
                        <input type="hidden" name="alid" id="selected_alid">
                        <input type="text" id="album_search" placeholder="🔍 Ketik untuk mencari album..." autocomplete="off" required style="width: 100%; padding: 14px; border-radius: 10px; border: 1px solid var(--glass-border); background: rgba(255,255,255,0.7); font-size: 14px; outline: none;">
                        
                        <div id="album_dropdown" style="display: none; position: absolute; width: 100%; max-height: 200px; overflow-y: auto; background: rgba(255,255,255,0.95); backdrop-filter: blur(15px); border: 1px solid var(--glass-border); border-radius: 10px; margin-top: 5px; z-index: 1000; box-shadow: 0 8px 32px rgba(0,0,0,0.15);">
                            <?php
                            if ($result_albums_dropdown->num_rows > 0) {
                                // Reset pointer data album buat dropdown lagu
                                mysqli_data_seek($result_albums_dropdown, 0); 
                                while($alb = $result_albums_dropdown->fetch_assoc()) {
                                    echo "<div class='album-option' data-value='" . $alb['alid'] . "'>" . htmlspecialchars($alb['atitle']) . "</div>";
                                }
                            } else {
                                echo "<div style='padding: 12px 15px; color: var(--text-muted); font-size: 13px;'>Belum ada album. Buat album dulu!</div>";
                            }
                            ?>
                        </div>
                    </div>
                    <button type="submit" name="tambah_lagu" class="btn-submit">Simpan Lagu ke Database</button>
                </form>
            </div>
        </div>

        <h2 style="font-size: 18px; margin-bottom: 10px;"><i class="fa-solid fa-folder-open"></i> Manajemen Data Album</h2>
        <div style="max-height: 350px; overflow-y: auto; border-radius: 16px; margin-bottom: 40px; box-shadow: 0 8px 32px rgba(0,0,0,0.05); border: 1px solid var(--glass-border);">
            <table style="margin-bottom: 0; box-shadow: none; border: none;">
                <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th>No</th>
                        <th>ID Album</th>
                        <th>Judul Album</th>
                        <th>Tanggal Rilis</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result_albums_table && $result_albums_table->num_rows > 0) {
                    $no = 1;
                    while($row = $result_albums_table->fetch_assoc()) {
                        $safe_alid = htmlspecialchars($row["alid"], ENT_QUOTES);
                        $safe_title = htmlspecialchars($row["atitle"], ENT_QUOTES);
                        $safe_date = htmlspecialchars($row["adate"], ENT_QUOTES);
                        
                        echo "<tr>";
                        echo "<td><span style='color: var(--text-muted);'>" . $no++ . "</span></td>";
                        echo "<td style='font-size: 12px; color: var(--text-muted);'>" . $safe_alid . "</td>";
                        echo "<td><strong>" . $safe_title . "</strong></td>";
                        echo "<td style='color: var(--text-muted);'>" . $safe_date . "</td>";
                        
                        echo "<td style='text-align: right; white-space: nowrap;'>";
                        // TOMBOL EDIT MEMANGGIL JAVASCRIPT
                        echo "<button type='button' class='btn-edit' onclick=\"bukaModalEdit('$safe_alid', '$safe_title', '$safe_date')\"><i class='fa-solid fa-pen'></i> Edit</button>";
                        
                        // TOMBOL HAPUS
                        echo "<form action='' method='POST' style='display: inline;'>";
                        echo "<input type='hidden' name='hapus_alid' value='" . $row["alid"] . "'>";
                        echo "<button type='submit' name='hapus_album' class='btn-delete' onclick=\"return confirm('Yakin ingin menghapus album ini?');\"><i class='fa-solid fa-trash'></i> Hapus</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center;'>Belum ada data album.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <h2 style="font-size: 18px; margin-bottom: 10px;"><i class="fa-solid fa-database"></i> Penelusuran Master Lagu 🔍</h2>
        <input type="text" id="searchInput" class="search-box-admin" onkeyup="searchTable()" placeholder="🔍 Ketik Judul Lagu, Nama Artis, atau NAMA ALBUM...">

        <table id="dataTable">
            <tr>
                <th>No</th>
                <th>Judul Lagu</th>
                <th>Artis</th>
                <th>Bagian Dari Album</th>
                <th style="text-align: right;">Aksi</th>
            </tr>
            <?php
            if ($result_tracks && $result_tracks->num_rows > 0) {
                $no = 1;
                while($row = $result_tracks->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td><span style='color: var(--text-muted);'>" . $no++ . "</span></td>";
                    echo "<td><strong>" . htmlspecialchars($row["ttitle"]) . "</strong></td>";
                    echo "<td style='color: var(--text-muted);'>" . htmlspecialchars($row["aname"]) . "</td>";
                    echo "<td style='color: var(--primary); font-weight: 600;'>" . htmlspecialchars($row["atitle"] ?? 'Tanpa Album') . "</td>";
                    
                    echo "<td style='text-align: right;'>";
                    echo "<form action='' method='POST' style='margin: 0;'>";
                    echo "<input type='hidden' name='tid_hapus' value='" . $row["tid"] . "'>";
                    echo "<button type='submit' name='hapus_lagu' class='btn-delete' onclick=\"return confirm('BAHAYA: Yakin ingin menghapus lagu ini secara permanen dari server?');\"><i class='fa-solid fa-trash'></i> Hapus</button>";
                    echo "</form>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>Belum ada data lagu.</td></tr>";
            }
            ?>
        </table>
    </div>

    <div class="modal-overlay" id="editModal">
        <div class="modal-box">
            <button class="btn-close-modal" onclick="tutupModalEdit()"><i class="fa-solid fa-xmark"></i></button>
            <h2>Edit Detail Album</h2>
            <form action="" method="POST">
                <input type="hidden" name="edit_alid" id="input_edit_alid">
                
                <div class="input-group">
                    <label>Judul Album</label>
                    <input type="text" name="edit_atitle" id="input_edit_atitle" required>
                </div>
                <div class="input-group">
                    <label>Tanggal Rilis</label>
                    <input type="date" name="edit_adate" id="input_edit_adate" required>
                </div>
                <button type="submit" name="edit_album" class="btn-submit album-btn">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script>
        // SCRIPT MODAL EDIT ALBUM
        function bukaModalEdit(alid, atitle, adate) {
            document.getElementById('input_edit_alid').value = alid;
            document.getElementById('input_edit_atitle').value = atitle;
            document.getElementById('input_edit_adate').value = adate;
            document.getElementById('editModal').style.display = 'flex';
        }

        function tutupModalEdit() {
            document.getElementById('editModal').style.display = 'none';
        }

        // SCRIPT SMART DROPDOWN ALBUM
        document.addEventListener('DOMContentLoaded', function() {
            const albumSearch = document.getElementById('album_search');
            const albumDropdown = document.getElementById('album_dropdown');
            const selectedAlid = document.getElementById('selected_alid');
            const albumOptions = document.querySelectorAll('.album-option');

            if(albumSearch) {
                albumSearch.addEventListener('focus', function() {
                    albumDropdown.style.display = 'block';
                    if (this.value === '') albumOptions.forEach(opt => opt.style.display = 'block');
                });

                albumSearch.addEventListener('input', function() {
                    const filter = this.value.toLowerCase();
                    let hasVisible = false;
                    albumOptions.forEach(option => {
                        if (option.textContent.toLowerCase().indexOf(filter) > -1) {
                            option.style.display = 'block';
                            hasVisible = true;
                        } else {
                            option.style.display = 'none';
                        }
                    });
                    albumDropdown.style.display = hasVisible ? 'block' : 'none';
                    selectedAlid.value = ''; 
                });

                albumOptions.forEach(option => {
                    option.addEventListener('click', function() {
                        albumSearch.value = this.textContent;
                        selectedAlid.value = this.getAttribute('data-value');
                        albumDropdown.style.display = 'none';
                    });
                });

                document.addEventListener('click', function(e) {
                    if (!albumSearch.contains(e.target) && !albumDropdown.contains(e.target)) {
                        albumDropdown.style.display = 'none';
                    }
                });
            }
        });

        // SCRIPT PENCARIAN TABEL LAGU BAWAH
        function searchTable() {
            var input, filter, table, tr, tdTitle, tdArtist, tdAlbum, i, txtTitle, txtArtist, txtAlbum;
            input = document.getElementById("searchInput");
            filter = input.value.toUpperCase();
            table = document.getElementById("dataTable");
            tr = table.getElementsByTagName("tr");

            for (i = 1; i < tr.length; i++) {
                tdTitle = tr[i].getElementsByTagName("td")[1]; 
                tdArtist = tr[i].getElementsByTagName("td")[2]; 
                tdAlbum = tr[i].getElementsByTagName("td")[3]; 
                
                if (tdTitle || tdArtist || tdAlbum) {
                    txtTitle = tdTitle.textContent || tdTitle.innerText;
                    txtArtist = tdArtist.textContent || tdArtist.innerText;
                    txtAlbum = tdAlbum.textContent || tdAlbum.innerText;
                    
                    if (txtTitle.toUpperCase().indexOf(filter) > -1 || 
                        txtArtist.toUpperCase().indexOf(filter) > -1 || 
                        txtAlbum.toUpperCase().indexOf(filter) > -1) {
                        tr[i].style.display = "";
                    } else {
                        tr[i].style.display = "none";
                    }
                }       
            }
        }
    </script>
</body>
</html>