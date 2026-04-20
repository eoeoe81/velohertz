<?php
session_start();
if (!isset($_SESSION['username'])) { 
    header("Location: login.php"); 
    exit(); 
}
include 'koneksi.php';

$username = $_SESSION['username'];

// 1. AMBIL 10 LAGU TERBARU
$sql_tracks = "SELECT t.*, a.atitle FROM Track t LEFT JOIN Album a ON t.alid = a.alid ORDER BY t.tid DESC LIMIT 10";
$result_tracks = $conn->query($sql_tracks);

// 2. AMBIL 5 ALBUM ACAK 
$sql_albums = "SELECT * FROM Album ORDER BY RAND() LIMIT 5";
$result_albums = $conn->query($sql_albums);

$songs_array = [];
$no = 1;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - Velohertz</title>
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
        
        .banner { position: relative; background: linear-gradient(135deg, #0f52ba 0%, #8b5cf6 50%, #d946ef 100%); background-size: 200% 200%; animation: gradientMove 6s ease infinite; padding: 45px 50px; border-radius: 24px; display: flex; align-items: center; justify-content: space-between; margin-bottom: 40px; box-shadow: 0 20px 40px rgba(139, 92, 246, 0.25); border: 1px solid rgba(255, 255, 255, 0.2); overflow: hidden; color: #ffffff; }
        .banner::before { content: ''; position: absolute; top: -50px; left: -50px; width: 250px; height: 250px; background: rgba(255, 255, 255, 0.15); border-radius: 50%; filter: blur(40px); animation: floatShape 8s ease-in-out infinite; }
        .banner-text { position: relative; z-index: 2; }
        .banner-text h1 { margin: 0 0 10px 0; font-size: 40px; color: #ffffff; letter-spacing: -1px; font-weight: 800; text-shadow: 0 4px 15px rgba(0,0,0,0.15); }
        .banner-text p { margin: 0; color: rgba(255,255,255,0.9); font-size: 17px; font-weight: 500; }
        .banner-icon { font-size: 90px; color: #ffffff; opacity: 0.95; filter: drop-shadow(0 15px 25px rgba(0,0,0,0.2)); animation: floatIcon 4s ease-in-out infinite; position: relative; z-index: 2; }

        @keyframes gradientMove { 0% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } 100% { background-position: 0% 50%; } }
        @keyframes floatIcon { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-15px); } }
        @keyframes floatShape { 0%, 100% { transform: translate(0, 0); } 50% { transform: translate(60px, 30px) scale(1.1); } }

        h3 { color: var(--text-main); font-weight: 800; font-size: 22px; margin-bottom: 20px; letter-spacing: -0.5px;}

        .album-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 25px; margin-bottom: 50px; }
        .album-card { background: var(--glass-bg); backdrop-filter: blur(12px); padding: 15px; border-radius: 16px; border: 1px solid var(--glass-border); box-shadow: 0 8px 32px rgba(0,0,0,0.05); text-decoration: none; color: var(--text-main); transition: 0.3s; display: block; }
        .album-card:hover { transform: translateY(-8px); background: rgba(255,255,255,0.9); box-shadow: 0 15px 40px rgba(15,82,186,0.15); border-color: #ffffff; }
        .album-cover { width: 100%; height: 160px; object-fit: cover; border-radius: 12px; margin-bottom: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .album-title { font-size: 15px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin-bottom: 4px; }
        .album-date { font-size: 13px; color: var(--text-muted); font-weight: 600;}

        table { width: 100%; border-collapse: separate; border-spacing: 0; background: var(--glass-bg); backdrop-filter: blur(12px); border-radius: 16px; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,0.05); border: 1px solid var(--glass-border); margin-bottom: 50px;}
        th { text-align: left; padding: 18px 20px; color: var(--text-muted); font-size: 12px; text-transform: uppercase; letter-spacing: 1px; border-bottom: 1px solid var(--glass-border); font-weight: 700; background: rgba(255,255,255,0.4); }
        td { padding: 16px 20px; border-bottom: 1px solid rgba(255,255,255,0.3); font-size: 14px; vertical-align: middle; transition: 0.2s; }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background-color: rgba(255,255,255,0.8); }
        
        .play-icon-btn { background: rgba(15,82,186,0.1); color: var(--primary); border: none; width: 36px; height: 36px; border-radius: 50%; font-size: 14px; cursor: pointer; transition: 0.3s; display: inline-flex; align-items: center; justify-content: center; }
        .play-icon-btn:hover { background: var(--primary); color: white; transform: scale(1.1); box-shadow: 0 4px 10px rgba(15,82,186,0.3); }

        /* PLAYER BAR */
        .player-bar { position: fixed; bottom: 0; left: 0; width: 100%; height: 90px; background-color: rgba(255,255,255,0.85); backdrop-filter: blur(25px); -webkit-backdrop-filter: blur(25px); border-top: 1px solid var(--glass-border); box-shadow: 0 -10px 40px rgba(0,0,0,0.08); display: flex; justify-content: space-between; align-items: center; padding: 0 40px; box-sizing: border-box; z-index: 1000; }
        .player-left { width: 30%; display: flex; align-items: center; gap: 15px; }
        .now-playing-img { width: 60px; height: 60px; border-radius: 12px; object-fit: cover; box-shadow: 0 4px 15px rgba(0,0,0,0.1); background: #e0f2fe; display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 24px; overflow: hidden;}
        .now-playing-img img { width: 100%; height: 100%; object-fit: cover; display: none; } 
        .now-playing-info strong { font-size: 15px; color: var(--text-main); display: block; margin-bottom: 4px; font-weight: 800; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;}
        .now-playing-info span { font-size: 13px; color: var(--text-muted); display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px;}
        
        .player-center { width: 40%; display: flex; flex-direction: column; align-items: center; gap: 12px; }
        .controls { display: flex; align-items: center; gap: 20px; }
        .ctrl-btn { background: transparent; border: none; color: var(--text-muted); font-size: 18px; cursor: pointer; transition: 0.2s; }
        .ctrl-btn:hover { color: var(--primary); }
        .play-pause-btn { background: var(--primary); color: white; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 16px; cursor: pointer; transition: 0.3s; box-shadow: 0 4px 10px rgba(15,82,186,0.3); }
        .play-pause-btn:hover { transform: scale(1.05); background: #0c4399; }
        
        .progress-container { width: 100%; display: flex; align-items: center; gap: 10px; }
        .time-text { font-size: 12px; color: var(--text-muted); min-width: 40px; text-align: center; font-weight: 600; }
        
        /* =========================================
           PROGRESS BAR & VOLUME SLIDER (UPGRADE!)
           ========================================= */
        input[type=range] { 
            -webkit-appearance: none; 
            width: 100%; 
            height: 6px; 
            border-radius: 5px; 
            outline: none; 
            cursor: pointer; 
            /* Background transparan awal (diisi sama JS) */
            background: linear-gradient(to right, var(--primary) 0%, rgba(0,0,0,0.1) 0%); 
        }
        input[type=range]::-webkit-slider-thumb { 
            -webkit-appearance: none; 
            width: 14px; 
            height: 14px; 
            border-radius: 50%; 
            background: var(--primary); 
            cursor: pointer; 
            transition: 0.1s; 
            box-shadow: 0 2px 5px rgba(0,0,0,0.3); 
        }
        /* Buat Firefox biar gak kalah keren */
        input[type=range]::-moz-range-progress {
            background-color: var(--primary);
            border-radius: 5px;
            height: 6px;
        }

        .player-right { width: 30%; display: flex; justify-content: flex-end; align-items: center; gap: 15px; color: var(--text-muted); }
        .vol-slider { width: 100px !important; }

        @media screen and (max-width: 768px) {
            .sidebar { width: 100%; height: auto; position: relative; flex-direction: row; align-items: center; padding: 15px 20px; border-right: none; border-bottom: 1px solid var(--glass-border); overflow-x: auto; }
            .sidebar h2 { margin: 0 20px 0 0; font-size: 20px; }
            .sidebar a { margin: 0 10px 0 0; white-space: nowrap; }
            .main-content { margin-left: 0; width: 100%; padding: 20px; }
            .player-bar { padding: 15px; flex-direction: column; height: auto; border-radius: 20px 20px 0 0; }
            .player-left, .player-center { width: 100%; justify-content: center; text-align: center; }
            .player-right { display: none; }
            .banner { flex-direction: column; text-align: center; gap: 20px; }
        }
    </style>
</head>
<body>

    <div class="sidebar">
        <h2>🎵 Velohertz</h2>
        <a href="index.php" class="active"><i class="fa-solid fa-house" style="width: 25px;"></i> Beranda</a>
        <a href="search.php"><i class="fa-solid fa-magnifying-glass" style="width: 25px;"></i> Cari</a>
        <a href="library.php"><i class="fa-solid fa-book" style="width: 25px;"></i> Koleksi Kamu</a>
        <a href="profile.php"><i class="fa-solid fa-user" style="width: 25px;"></i> Profil</a>
        <?php if($username == 'admin'): ?>
            <a href="admin.php" style="color: #e53e3e;"><i class="fa-solid fa-shield-halved" style="width: 25px;"></i> Admin Panel</a>
        <?php endif; ?>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Apakah kamu yakin ingin keluar?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content">
        <div class="header">
            <h2>Beranda</h2>
            <a href="profile.php" class="profile-badge">
                <i class="fa-solid fa-circle-user" style="margin-right: 8px; font-size: 18px; color: var(--primary);"></i> 
                <?php echo htmlspecialchars($username); ?>
            </a>
        </div>

        <div class="banner">
            <div class="banner-text">
                <h1>Selamat Datang di Velohertz</h1>
                <p>Dengarkan 10 lagu terpopuler minggu ini tanpa gangguan.</p>
            </div>
            <div class="banner-icon">
                <i class="fa-solid fa-headphones-simple"></i>
            </div>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">Rekomendasi Album 💿</h3>
            <span style="color: var(--primary); font-size: 14px; font-weight: 600; cursor: pointer;">Lihat Semua</span>
        </div>

        <div class="album-grid">
            <?php
            if ($result_albums && $result_albums->num_rows > 0) {
                while($alb = $result_albums->fetch_assoc()) {
                    $album_cover = "https://picsum.photos/seed/" . urlencode($alb['atitle']) . "/200/200";
                    
                    echo "<a href='view_album.php?alid=" . $alb['alid'] . "' class='album-card'>";
                    echo "<img src='$album_cover' class='album-cover' alt='Cover Album'>";
                    echo "<div class='album-title'>" . htmlspecialchars($alb['atitle']) . "</div>";
                    echo "<div class='album-date'>Rilis: " . htmlspecialchars($alb['adate']) . "</div>";
                    echo "</a>";
                }
            } else {
                echo "<p style='color: var(--text-muted);'>Belum ada album yang tersedia.</p>";
            }
            ?>
        </div>

        <h3>Top 10 Lagu Terpopuler 🔥</h3>
        
        <table>
            <tr>
                <th>No</th>
                <th>Judul Lagu</th>
                <th>Artis</th>
                <th>Album</th>
                <th style="text-align: right;">Putar</th>
            </tr>
            <?php
            if ($result_tracks && $result_tracks->num_rows > 0) {
                $index_js = 0; 
                
                while($row = $result_tracks->fetch_assoc()) {
                    $gambar_cover = "https://picsum.photos/seed/" . urlencode($row['ttitle']) . "/100/100";

                    $songs_array[] = [
                        'title' => htmlspecialchars($row["ttitle"], ENT_QUOTES),
                        'artist' => htmlspecialchars($row["aname"], ENT_QUOTES),
                        'file' => 'music/' . $no . '.mp3',
                        'cover' => $gambar_cover 
                    ];

                    echo "<tr>";
                    echo "<td><span style='color: var(--text-muted); font-weight: bold;'>" . $no++ . "</span></td>";
                    
                    echo "<td><div style='display:flex; align-items:center; gap:12px;'>";
                    echo "<img src='$gambar_cover' style='width: 40px; height: 40px; border-radius: 8px; object-fit: cover;'>";
                    echo "<strong>" . htmlspecialchars($row["ttitle"]) . "</strong></div></td>";
                    
                    echo "<td style='color: var(--text-muted);'>" . htmlspecialchars($row["aname"]) . "</td>";
                    echo "<td style='color: var(--primary); font-weight: 600;'>" . htmlspecialchars($row["atitle"] ?? 'Single') . "</td>";
                    
                    echo "<td style='text-align: right;'>";
                    echo "<button class='play-icon-btn' onclick='playSong($index_js)'><i class='fa-solid fa-play'></i></button>";
                    echo "</td>";
                    echo "</tr>";

                    $index_js++;
                }
            } else {
                echo "<tr><td colspan='5' style='text-align:center;'>Belum ada lagu. Silakan tambah di menu Admin.</td></tr>";
            }
            ?>
        </table>

    </div>

    <div class="player-bar">
        <div class="player-left">
            <div class="now-playing-img" id="player-img-container">
                <i class="fa-solid fa-music" id="player-icon-default"></i>
                <img id="player-cover-img" src="" alt="Cover">
            </div>
            <div class="now-playing-info">
                <strong id="player-title">Pilih Lagu</strong>
                <span id="player-artist">Velohertz Player</span>
            </div>
        </div>
        
        <div class="player-center">
            <div class="controls">
                <button class="ctrl-btn" onclick="prevSong()"><i class="fa-solid fa-backward-step"></i></button>
                <button class="play-pause-btn" id="play-btn" onclick="togglePlay()"><i class="fa-solid fa-play"></i></button>
                <button class="ctrl-btn" onclick="nextSong()"><i class="fa-solid fa-forward-step"></i></button>
            </div>
            <div class="progress-container">
                <span class="time-text" id="curr-time">0:00</span>
                <input type="range" id="progress-bar" value="0" min="0" max="100">
                <span class="time-text" id="tot-time">0:00</span>
            </div>
        </div>
        
        <div class="player-right">
            <i class="fa-solid fa-volume-high"></i>
            <input type="range" class="vol-slider" id="volume-slider" min="0" max="100" value="100">
        </div>
    </div>

    <audio id="audio-player"></audio>

    <script>
        const songs = <?php echo json_encode($songs_array); ?>;
        let currentSongIndex = 0;
        let isPlaying = false;
        
        const audio = document.getElementById('audio-player');
        const playBtn = document.getElementById('play-btn');
        const playerTitle = document.getElementById('player-title');
        const playerArtist = document.getElementById('player-artist');
        const playerCoverImg = document.getElementById('player-cover-img');
        const playerIconDefault = document.getElementById('player-icon-default');
        
        const progressBar = document.getElementById('progress-bar');
        const currTimeText = document.getElementById('curr-time');
        const totTimeText = document.getElementById('tot-time');
        const volumeSlider = document.getElementById('volume-slider');

        // ==========================================
        // FUNGSI BARU: NGE-PAINT WARNA BAR (FILL)
        // ==========================================
        function updateSliderFill(slider, value) {
            slider.style.background = `linear-gradient(to right, var(--primary) ${value}%, rgba(0,0,0,0.1) ${value}%)`;
        }

        // Set volume bar fill ke 100% pas web baru dibuka
        updateSliderFill(volumeSlider, volumeSlider.value);

        function loadSong(song) {
            playerTitle.innerText = song.title;
            playerArtist.innerText = song.artist;
            audio.src = song.file;
            
            playerIconDefault.style.display = 'none';
            playerCoverImg.style.display = 'block';
            playerCoverImg.src = song.cover;
        }

        function playSong(index) {
            if (songs.length === 0) return;
            currentSongIndex = index;
            loadSong(songs[currentSongIndex]);
            audio.play();
            isPlaying = true;
            updatePlayIcon();
        }

        function togglePlay() {
            if (songs.length === 0) return;
            if (!audio.src) loadSong(songs[currentSongIndex]);

            if (isPlaying) {
                audio.pause();
            } else {
                audio.play();
            }
            isPlaying = !isPlaying;
            updatePlayIcon();
        }

        function updatePlayIcon() {
            if (isPlaying) {
                playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
            } else {
                playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
            }
        }

        function nextSong() {
            if (songs.length === 0) return;
            currentSongIndex = (currentSongIndex + 1) % songs.length;
            playSong(currentSongIndex);
        }

        function prevSong() {
            if (songs.length === 0) return;
            currentSongIndex = (currentSongIndex - 1 + songs.length) % songs.length;
            playSong(currentSongIndex);
        }

        audio.addEventListener('timeupdate', () => {
            const currentTime = audio.currentTime;
            const duration = audio.duration;
            
            if (!isNaN(duration)) {
                const percent = (currentTime / duration) * 100;
                progressBar.value = percent;
                updateSliderFill(progressBar, percent); // Update warna garisnya!
                
                let currMin = Math.floor(currentTime / 60);
                let currSec = Math.floor(currentTime % 60);
                if (currSec < 10) currSec = '0' + currSec;
                currTimeText.innerText = currMin + ':' + currSec;
                
                let totMin = Math.floor(duration / 60);
                let totSec = Math.floor(duration % 60);
                if (totSec < 10) totSec = '0' + totSec;
                totTimeText.innerText = totMin + ':' + totSec;
            }
        });

        // Waktu kita geser buletan musiknya manual
        progressBar.addEventListener('input', () => {
            updateSliderFill(progressBar, progressBar.value);
        });

        progressBar.addEventListener('change', () => {
            audio.currentTime = (progressBar.value / 100) * audio.duration;
        });

        // Waktu kita geser buletan volume
        volumeSlider.addEventListener('input', () => {
            audio.volume = volumeSlider.value / 100;
            updateSliderFill(volumeSlider, volumeSlider.value); // Update warna garis volume!
        });

        audio.addEventListener('ended', nextSong);
    </script>
</body>
</html>