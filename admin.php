<?php
session_start();
if (!isset($_SESSION['username']) || $_SESSION['username'] !== 'admin') { 
    header("Location: index.php"); 
    exit(); 
}
include 'koneksi.php';

$pesan = "";

if (isset($_POST['upload_file_lagu'])) {
    $total_files = count($_FILES['lagu']['name']);
    $max_files = 10;
    
    $bulk_aname = $conn->real_escape_string($_POST['bulk_aname']);
    $bulk_alid = $conn->real_escape_string($_POST['bulk_alid']);
    $duration = 200000; 

    if ($total_files > $max_files) {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Gagal: Maksimal upload 10 lagu sekaligus!</div>";
    } else {
        $berhasil = 0;
        $gagal = 0;
        $folder_tujuan = "music/";

        if (!is_dir($folder_tujuan)) {
            mkdir($folder_tujuan, 0777, true);
        }

        for ($i = 0; $i < $total_files; $i++) {
            if ($_FILES['lagu']['error'][$i] === UPLOAD_ERR_OK) {
                $nama_file = basename($_FILES['lagu']['name'][$i]);
                $tmp_file = $_FILES['lagu']['tmp_name'][$i];
                
                if (move_uploaded_file($tmp_file, $folder_tujuan . $nama_file)) {
                    
                    $tid = uniqid('trk_'); 
                    $judul_lagu = $conn->real_escape_string(pathinfo($nama_file, PATHINFO_FILENAME)); 
                    
                    try {
                        $conn->query("INSERT INTO Track (tid, ttitle, duration, aname, alid) VALUES ('$tid', '$judul_lagu', '$duration', '$bulk_aname', '$bulk_alid')");
                        $berhasil++;
                    } catch (mysqli_sql_exception $e) {
                        $gagal++;
                    }
                } else {
                    $gagal++;
                }
            }
        }
        
        if ($berhasil > 0) {
            $pesan = "<div class='success-msg'><i class='fa-solid fa-circle-check'></i> Berhasil upload dan menambah $berhasil lagu ke database!</div>";
        } else {
            $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Gagal mengupload file ke database. Pastikan format benar.</div>";
        }
    }
}

if (isset($_POST['tambah_album'])) {
    $alid = uniqid('alb_'); 
    $atitle = $conn->real_escape_string($_POST['atitle']);
    $adate = $conn->real_escape_string($_POST['adate']);

    try {
        if ($conn->query("INSERT INTO Album (alid, atitle, adate) VALUES ('$alid', '$atitle', '$adate')") === TRUE) {
            $pesan = "<div class='success-msg'><i class='fa-solid fa-circle-check'></i> Album '$atitle' berhasil ditambahkan!</div>";
        }
    } catch (mysqli_sql_exception $e) {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Gagal menambah album: " . $conn->error . "</div>";
    }
}

if (isset($_POST['edit_album'])) {
    $edit_alid = $conn->real_escape_string($_POST['edit_alid']);
    $edit_atitle = $conn->real_escape_string($_POST['edit_atitle']);
    $edit_adate = $conn->real_escape_string($_POST['edit_adate']);

    try {
        if ($conn->query("UPDATE Album SET atitle='$edit_atitle', adate='$edit_adate' WHERE alid='$edit_alid'") === TRUE) {
            $pesan = "<div class='success-msg'><i class='fa-solid fa-circle-check'></i> Detail Album '$edit_atitle' berhasil diperbarui!</div>";
        }
    } catch (mysqli_sql_exception $e) {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Gagal mengedit album: " . $conn->error . "</div>";
    }
}

if (isset($_POST['edit_lagu'])) {
    $edit_tid = $conn->real_escape_string($_POST['edit_tid']);
    $edit_ttitle = $conn->real_escape_string($_POST['edit_ttitle']);
    $edit_aname = $conn->real_escape_string($_POST['edit_aname']);
    $edit_alid = $conn->real_escape_string($_POST['edit_alid']);

    try {
        if ($conn->query("UPDATE Track SET ttitle='$edit_ttitle', aname='$edit_aname', alid='$edit_alid' WHERE tid='$edit_tid'") === TRUE) {
            $pesan = "<div class='success-msg'><i class='fa-solid fa-circle-check'></i> Detail Lagu '$edit_ttitle' berhasil diperbarui!</div>";
        }
    } catch (mysqli_sql_exception $e) {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Gagal mengedit lagu: " . $conn->error . "</div>";
    }
}

if (isset($_POST['hapus_album'])) {
    $hapus_alid = $conn->real_escape_string($_POST['hapus_alid']);
    try {
        $conn->query("DELETE FROM Album WHERE alid='$hapus_alid'");
        $pesan = "<div class='error-msg'><i class='fa-solid fa-trash'></i> Album berhasil dihapus dari sistem!</div>";
    } catch (mysqli_sql_exception $e) {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Gagal Menghapus Album! Pastikan album ini sudah tidak memiliki lagu di dalamnya.</div>";
    }
}

if (isset($_POST['hapus_lagu'])) {
    $tid_hapus = $conn->real_escape_string($_POST['tid_hapus']);
    try {
        $conn->query("DELETE FROM playlistcontain WHERE tid='$tid_hapus'");
        $conn->query("DELETE FROM Track WHERE tid='$tid_hapus'");
        $pesan = "<div class='error-msg'><i class='fa-solid fa-trash'></i> Lagu berhasil dihapus!</div>";
    } catch (mysqli_sql_exception $e) {
        $pesan = "<div class='error-msg'><i class='fa-solid fa-circle-exclamation'></i> Gagal Menghapus Lagu! Masih terkait data User.</div>";
    }
}

$jml_user = $conn->query("SELECT COUNT(*) as total FROM User")->fetch_assoc()['total'];
$jml_lagu = $conn->query("SELECT COUNT(*) as total FROM Track")->fetch_assoc()['total'];
$jml_album = $conn->query("SELECT COUNT(*) as total FROM Album")->fetch_assoc()['total'];

$result_tracks = $conn->query("SELECT DISTINCT t.tid, t.ttitle, t.aname, t.alid, a.atitle FROM Track t LEFT JOIN Album a ON t.alid = a.alid ORDER BY t.tid DESC LIMIT 100");
$result_albums_table = $conn->query("SELECT * FROM Album ORDER BY adate DESC"); 
$result_albums_dropdown = $conn->query("SELECT alid, atitle FROM Album ORDER BY atitle ASC"); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Velohertz</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@800&family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
    :root {
        --primary: #74b9ff;
        --primary-dark: #a29bfe;
        --primary-grad: linear-gradient(135deg, #3b71ca 0%, #a29bfe 100%);
        --emerald: #00cec9;
        --danger: #ff6b81;
        --app-bg-color: #0b0f19;
        --glass-bg: rgba(20, 25, 35, 0.6);
        --glass-border: rgba(255, 255, 255, 0.08);
        --text-main: #ffffff;
        --text-muted: rgba(255, 255, 255, 0.5);
    }
    
    *, *::before, *::after { box-sizing: border-box; }

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
        width: 300px; background: rgba(20, 25, 35, 0.4); backdrop-filter: blur(20px); 
        padding: 32px 20px; height: 100vh; position: fixed; left: 0; top: 0; z-index: 1000; 
        border-right: 1px solid var(--glass-border); display: flex; flex-direction: column; 
        transition: transform 0.4s ease; 
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
    .logout-btn { margin-top: auto; color: var(--danger) !important; background: rgba(255, 71, 87, 0.1) !important;}

    .hamburger-menu {
        position: fixed; top: 32px; left: 25px; z-index: 1100;
        background: var(--primary-grad); color: white; border: none;
        width: 45px; height: 45px; border-radius: 12px; cursor: pointer;
        box-shadow: 0 4px 15px rgba(162, 155, 254, 0.3);
        display: flex; align-items: center; justify-content: center; font-size: 20px;
        transition: 0.3s;
    }
    .hamburger-menu:hover { transform: scale(1.05); filter: brightness(1.1); }

    .main-content { margin-left: 300px; padding: 40px 60px; width: 100%; transition: all 0.4s ease; min-height: 100vh; }
    .main-content.full-width { margin-left: 0; padding-left: 90px; }
    
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; padding-bottom: 20px; border-bottom: 1px solid var(--glass-border); }
    .header h1 { font-family: 'Outfit', sans-serif; margin: 0; font-size: 32px; font-weight: 800; }
    .header p { margin: 5px 0 0 0; color: var(--text-muted); font-size: 15px; }
    .admin-badge {
        background: var(--primary-grad); color: white; padding: 10px 22px; border-radius: 30px;
        font-weight: 800; font-size: 13px; box-shadow: 0 6px 20px rgba(162, 155, 254, 0.3);
        display: flex; align-items: center; gap: 10px; border: none; letter-spacing: 1px;
    }

    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 40px; }
    
    .stat-card { 
        background: var(--glass-bg); backdrop-filter: blur(20px); -webkit-backdrop-filter: blur(20px); 
        padding: 25px; border-radius: 20px; 
        border: 1px solid var(--glass-border); 
        display: flex; align-items: center; gap: 25px; box-shadow: 0 20px 40px rgba(0,0,0,0.4); 
        transition: all 0.3s ease; 
    }
    
    .stat-card:hover {
        transform: translateY(-8px);
        border-color: var(--primary);
        box-shadow: 0 25px 50px rgba(116, 185, 255, 0.15);
    }
    
    .stat-icon { font-size: 45px; color: var(--primary); opacity: 0.9; }
    .stat-info h3 { font-family: 'Outfit', sans-serif; margin: 0; font-size: 36px; font-weight: 800; line-height: 1; }
    .stat-info p { margin: 5px 0 0 0; color: var(--text-muted); font-size: 13px; font-weight: 600; text-transform: uppercase; letter-spacing: 1px; }

    .forms-container { display: grid; grid-template-columns: 1fr; gap: 25px; margin-bottom: 40px; }
    
    .form-box { 
        background: var(--glass-bg); padding: 30px; border-radius: 24px; 
        border: 1px solid var(--glass-border); box-shadow: 0 20px 40px rgba(0,0,0,0.4); 
        backdrop-filter: blur(12px); 
        transition: all 0.3s ease; 
    }
    
    .form-box:hover {
        transform: translateY(-5px);
        border-color: rgba(255, 255, 255, 0.2);
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
    }
    
    .form-box h2 { font-family: 'Outfit', sans-serif; font-size: 20px; margin-top: 0; margin-bottom: 25px; display: flex; align-items: center; gap: 10px; color: var(--text-main);}
    
    .input-group { margin-bottom: 18px; position: relative; }
    .input-group label { display: block; margin-bottom: 8px; font-weight: 600; font-size: 13px; color: var(--text-muted); }
    .input-group input, .input-group select { width: 100%; padding: 14px 18px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.03); color: #fff; font-size: 14px; outline: none; transition: 0.3s; font-family: inherit; }
    .input-group input::placeholder { color: rgba(255, 255, 255, 0.3); }
    .input-group input:focus, .input-group select:focus { border-color: var(--primary); background: rgba(255, 255, 255, 0.08); box-shadow: 0 0 15px rgba(116, 185, 255, 0.1); }
    .input-group select option { background: #0b0f19; color: #fff; }
    
    .btn-submit { background: var(--emerald); color: #000; border: none; padding: 14px 25px; border-radius: 12px; font-weight: 600; cursor: pointer; transition: 0.3s; font-family: inherit; font-size: 15px; width: 100%; margin-top: 10px;}
    .btn-submit:hover { filter: brightness(1.1); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0, 206, 201, 0.3); }
    .btn-submit.album-btn { background: var(--primary-grad); color: white; }
    .btn-submit.album-btn:hover { box-shadow: 0 10px 20px rgba(162, 155, 254, 0.3); }

    .search-box-admin { width: 100%; padding: 16px 25px; border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.1); background: rgba(255, 255, 255, 0.03); color: #fff; font-size: 15px; outline: none; transition: 0.3s; margin-bottom: 25px; font-family: inherit; box-shadow: 0 5px 15px rgba(0,0,0,0.2);}
    .search-box-admin::placeholder { color: rgba(255, 255, 255, 0.4); }
    .search-box-admin:focus { border-color: var(--primary); background: rgba(255, 255, 255, 0.08); box-shadow: 0 0 15px rgba(116, 185, 255, 0.1); }

    .table-container { background: var(--glass-bg); border-radius: 20px; border: 1px solid var(--glass-border); overflow: hidden; box-shadow: 0 20px 40px rgba(0,0,0,0.4); margin-bottom: 40px; }
    table { width: 100%; border-collapse: collapse; }
    th { 
        text-align: left; padding: 18px 25px; color: var(--text-muted); font-size: 13px; text-transform: uppercase; font-weight: 700; 
        background: rgba(20, 25, 35, 0.95); backdrop-filter: blur(10px); 
        border-bottom: 1px solid var(--glass-border); letter-spacing: 1px;
    }
    td { padding: 18px 25px; border-bottom: 1px solid var(--glass-border); font-size: 14px; vertical-align: middle; color: var(--text-main); }
    tr:hover td { background-color: rgba(255,255,255,0.05); }
    
    .btn-delete { background: rgba(255, 71, 87, 0.1); color: var(--danger); border: 1px solid rgba(255, 71, 87, 0.3); padding: 8px 14px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; font-family: inherit; backdrop-filter: blur(5px);}
    .btn-delete:hover { background: rgba(255, 71, 87, 0.2); color: #fff; border-color: transparent;}
    .btn-edit { background: rgba(116, 185, 255, 0.1); color: var(--primary); border: 1px solid rgba(116, 185, 255, 0.3); padding: 8px 14px; border-radius: 10px; font-weight: 600; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; gap: 8px; font-size: 13px; margin-right: 8px; font-family: inherit; backdrop-filter: blur(5px);}
    .btn-edit:hover { background: rgba(116, 185, 255, 0.2); color: #fff; border-color: transparent;}

    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); z-index: 2000; justify-content: center; align-items: center; }
    .modal-box { background: #0b0f19; border: 1px solid var(--glass-border); padding: 40px; border-radius: 24px; width: 450px; max-width: 90%; box-shadow: 0 20px 50px rgba(0,0,0,0.6); position: relative; }
    .modal-box h2 { margin-top: 0; color: var(--primary); font-size: 22px; font-family: 'Outfit', sans-serif; margin-bottom: 25px;}
    .btn-close-modal { position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 22px; color: var(--text-muted); cursor: pointer; transition: 0.3s;}
    .btn-close-modal:hover { color: var(--danger); transform: rotate(90deg);}

    .success-msg { background: rgba(0, 206, 201, 0.1); color: var(--emerald); padding: 15px; border-radius: 16px; margin-bottom: 25px; border: 1px solid rgba(0, 206, 201, 0.3); font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; backdrop-filter: blur(5px);}
    .error-msg { background: rgba(255, 71, 87, 0.1); color: var(--danger); padding: 15px; border-radius: 16px; margin-bottom: 25px; border: 1px solid rgba(255, 71, 87, 0.3); font-size: 14px; font-weight: 500; display: flex; align-items: center; gap: 10px; backdrop-filter: blur(5px);}

    @media (max-width: 768px) {
        html, body { overflow-x: hidden !important; width: 100vw !important; max-width: 100%; }
        *, *::before, *::after { box-sizing: border-box !important; }
        .sidebar { width: 300px; box-shadow: 5px 0 15px rgba(0,0,0,0.5); padding-top: 90px !important; }
        .sidebar h2 { margin-top: 15px !important; }
        .main-content, .main-content.full-width { margin-left: 0 !important; padding: 80px 15px 120px 15px !important; width: 100% !important; }
        .hamburger-menu { top: 15px; left: 15px; width: 40px; height: 40px; }
        .header { flex-direction: column; align-items: flex-start; gap: 15px; }
        .stats-grid { grid-template-columns: 1fr; }
        table { display: block; width: 100%; overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch; }
    }
</style>
</head>
<body>

    <button class="hamburger-menu" onclick="toggleSidebar()">
        <i class="fa-solid fa-bars"></i>
    </button>

    <div class="sidebar" id="sidebar">
        <h2>Velohertz</h2>
        <a href="admin.php" class="active"><i class="fa-solid fa-gauge-high"></i> Dashboard</a>
        <a href="index.php"><i class="fa-solid fa-house"></i> Beranda User</a>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Keluar dari mode Admin?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="header">
            <div>
                <h1>Admin Control Panel</h1>
                <p>Kelola data pusat Velohertz</p>
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
                <h2><i class="fa-solid fa-compact-disc"></i> Tambah Master Album</h2>
                <form action="" method="POST">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="input-group" style="margin-bottom: 0;">
                            <label>Judul Album</label>
                            <input type="text" name="atitle" required placeholder="Contoh: Divide">
                        </div>
                        <div class="input-group" style="margin-bottom: 0;">
                            <label>Tanggal Rilis</label>
                            <input type="date" name="adate" required>
                        </div>
                    </div>
                    <button type="submit" name="tambah_album" class="btn-submit album-btn">Simpan Album</button>
                </form>
            </div>

            <div class="form-box upload">
                <h2><i class="fa-solid fa-cloud-arrow-up"></i> Upload & Tambah Lagu Otomatis (.mp3)</h2>
                <p style="color: var(--text-muted); font-size: 13px; margin-top: -15px; margin-bottom: 20px;">
                    Upload file dan otomatis masuk database. Judul lagu akan diambil dari nama file. Maksimal 10 lagu sekaligus.
                </p>
                <form action="" method="POST" enctype="multipart/form-data">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <div class="input-group">
                            <label>Nama Artis (Untuk semua lagu ini)</label>
                            <input type="text" name="bulk_aname" required placeholder="Contoh: Ed Sheeran">
                        </div>
                        <div class="input-group">
                            <label>Pilih Album (Wajib)</label>
                            <select name="bulk_alid" required>
                                <option value="">-- Pilih Album --</option>
                                <?php
                                mysqli_data_seek($result_albums_dropdown, 0); 
                                while($alb = $result_albums_dropdown->fetch_assoc()) {
                                    echo "<option value='" . $alb['alid'] . "'>" . htmlspecialchars($alb['atitle']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="input-group" style="margin-bottom: 0;">
                        <label>Pilih File (Bisa diblok banyak)</label>
                        <input type="file" name="lagu[]" accept=".mp3,audio/*" multiple required style="padding: 11px 18px; background: rgba(255, 255, 255, 0.05); cursor: pointer;">
                    </div>
                    <button type="submit" name="upload_file_lagu" class="btn-submit" style="margin-top: 15px; padding: 13px 25px;">
                        <i class="fa-solid fa-upload"></i> Mulai Upload & Simpan
                    </button>
                </form>
            </div>
        </div>

        <h2 style="font-family: 'Outfit', sans-serif; font-size: 22px; margin-bottom: 15px;"><i class="fa-solid fa-folder-open"></i> Manajemen Data Album</h2>
        <div class="table-container" style="max-height: 400px; overflow-y: auto;">
            <table>
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
                        echo "<td><strong style='color: var(--text-muted);'>" . $no++ . "</strong></td>";
                        echo "<td style='font-size: 12px; color: var(--text-muted);'>" . $safe_alid . "</td>";
                        echo "<td><strong>" . $safe_title . "</strong></td>";
                        echo "<td style='color: var(--text-muted);'>" . $safe_date . "</td>";
                        
                        echo "<td style='text-align: right; white-space: nowrap;'>";
                        echo "<button type='button' class='btn-edit' onclick=\"bukaModalEdit('$safe_alid', '$safe_title', '$safe_date')\"><i class='fa-solid fa-pen'></i> Edit</button>";
                        
                        echo "<form action='' method='POST' style='display: inline;'>";
                        echo "<input type='hidden' name='hapus_alid' value='" . $row["alid"] . "'>";
                        echo "<button type='submit' name='hapus_album' class='btn-delete' onclick=\"return confirm('Yakin ingin menghapus album ini?');\"><i class='fa-solid fa-trash'></i> Hapus</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center; padding: 30px;'>Belum ada data album.</td></tr>";
                }
                ?>
                </tbody>
            </table>
        </div>

        <h2 style="font-family: 'Outfit', sans-serif; font-size: 22px; margin-bottom: 15px; margin-top: 50px;"><i class="fa-solid fa-database"></i> Penelusuran Master Lagu</h2>
        <input type="text" id="searchInput" class="search-box-admin" onkeyup="searchTable()" placeholder="🔍 Ketik Judul Lagu, Nama Artis, atau NAMA ALBUM untuk memfilter...">

        <div class="table-container" style="max-height: 400px; overflow-y: auto;">
            <table id="dataTable">
                <thead style="position: sticky; top: 0; z-index: 10;">
                    <tr>
                        <th>No</th>
                        <th>Judul Lagu</th>
                        <th>Artis</th>
                        <th>Bagian Dari Album</th>
                        <th style="text-align: right;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ($result_tracks && $result_tracks->num_rows > 0) {
                    $no = 1;
                    while($row = $result_tracks->fetch_assoc()) {
                        $safe_tid = htmlspecialchars($row["tid"], ENT_QUOTES);
                        $safe_ttitle = htmlspecialchars($row["ttitle"], ENT_QUOTES);
                        $safe_aname = htmlspecialchars($row["aname"], ENT_QUOTES);
                        $safe_alid = htmlspecialchars($row["alid"] ?? '', ENT_QUOTES);
                        
                        echo "<tr>";
                        echo "<td><strong style='color: var(--text-muted);'>" . $no++ . "</strong></td>";
                        echo "<td><strong>" . $safe_ttitle . "</strong></td>";
                        echo "<td style='color: var(--text-muted);'>" . $safe_aname . "</td>";
                        echo "<td><span style='background: rgba(59,113,202,0.1); color: var(--primary); padding: 5px 10px; border-radius: 8px; font-size: 12px; font-weight: 600;'>" . htmlspecialchars($row["atitle"] ?? 'Tanpa Album') . "</span></td>";
                        
                        echo "<td style='text-align: right; white-space: nowrap;'>";
                        echo "<button type='button' class='btn-edit' onclick=\"bukaModalEditLagu('$safe_tid', '$safe_ttitle', '$safe_aname', '$safe_alid')\"><i class='fa-solid fa-pen'></i> Edit</button>";
                        
                        echo "<form action='' method='POST' style='display: inline;'>";
                        echo "<input type='hidden' name='tid_hapus' value='" . $row["tid"] . "'>";
                        echo "<button type='submit' name='hapus_lagu' class='btn-delete' onclick=\"return confirm('BAHAYA: Yakin ingin menghapus lagu ini secara permanen dari server?');\"><i class='fa-solid fa-trash'></i> Hapus</button>";
                        echo "</form>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5' style='text-align:center; padding: 30px;'>Belum ada data lagu.</td></tr>";
                }
                ?>
            </tbody>
                </table>
            </div>
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

    <div class="modal-overlay" id="editModalLagu">
        <div class="modal-box">
            <button class="btn-close-modal" onclick="tutupModalEditLagu()"><i class="fa-solid fa-xmark"></i></button>
            <h2>Edit Detail Lagu</h2>
            <form action="" method="POST">
                <input type="hidden" name="edit_tid" id="input_edit_tid">
                
                <div class="input-group">
                    <label>Judul Lagu</label>
                    <input type="text" name="edit_ttitle" id="input_edit_ttitle" required>
                </div>
                <div class="input-group">
                    <label>Nama Artis</label>
                    <input type="text" name="edit_aname" id="input_edit_aname" required>
                </div>
                <div class="input-group">
                    <label>Pindah ke Album Lain</label>
                    <select name="edit_alid" id="input_edit_alid_lagu" required>
                        <option value="">-- Pilih Album --</option>
                        <?php
                        mysqli_data_seek($result_albums_dropdown, 0); 
                        while($alb = $result_albums_dropdown->fetch_assoc()) {
                            echo "<option value='" . $alb['alid'] . "'>" . htmlspecialchars($alb['atitle']) . "</option>";
                        }
                        ?>
                    </select>
                </div>
                <button type="submit" name="edit_lagu" class="btn-submit">Simpan Perubahan Lagu</button>
            </form>
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

        function bukaModalEdit(alid, atitle, adate) {
            document.getElementById('input_edit_alid').value = alid;
            document.getElementById('input_edit_atitle').value = atitle;
            document.getElementById('input_edit_adate').value = adate;
            document.getElementById('editModal').style.display = 'flex';
        }

        function tutupModalEdit() {
            document.getElementById('editModal').style.display = 'none';
        }

        function bukaModalEditLagu(tid, ttitle, aname, alid) {
            document.getElementById('input_edit_tid').value = tid;
            document.getElementById('input_edit_ttitle').value = ttitle;
            document.getElementById('input_edit_aname').value = aname;
            document.getElementById('input_edit_alid_lagu').value = alid;
            document.getElementById('editModalLagu').style.display = 'flex';
        }

        function tutupModalEditLagu() {
            document.getElementById('editModalLagu').style.display = 'none';
        }

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