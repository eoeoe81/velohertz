<?php
session_start();
if (!isset($_SESSION['username'])) { 
    header("Location: login.php"); 
    exit(); 
}
include 'koneksi.php';

$username = $_SESSION['username'];

$sql_tracks = "SELECT t.*, a.atitle FROM Track t LEFT JOIN Album a ON t.alid = a.alid ORDER BY t.tid DESC LIMIT 10";
$result_tracks = $conn->query($sql_tracks);

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
        position: fixed; 
        top: 32px; 
        left: 25px; 
        z-index: 1100;
        background: var(--primary-grad); 
        color: white; 
        border: none;
        width: 45px; 
        height: 45px; 
        border-radius: 12px; 
        cursor: pointer;
        box-shadow: 0 4px 15px rgba(162, 155, 254, 0.3);
        display: flex; 
        align-items: center; 
        justify-content: center; 
        font-size: 20px;
        transition: 0.3s;
    }
    .hamburger-menu:hover { transform: scale(1.05); filter: brightness(1.1); }

    .main-content { 
        margin-left: 300px; 
        padding: 40px; 
        width: 100%;
        transition: all 0.4s ease; 
        box-sizing: border-box;
        min-height: 100vh;
        margin-bottom: 120px;
    }

    .content-container { max-width: 1100px; margin: 0 auto; }
    .main-content.full-width { margin-left: 0; padding-left: 90px; }
    .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; margin-top: 10px; }
    
    .banner { 
        background: var(--primary-grad); 
        padding: 40px 50px; border-radius: 30px; display: flex; align-items: center; 
        justify-content: space-between; margin-bottom: 40px; color: white; box-shadow: 0 10px 30px rgba(162, 155, 254, 0.2);
    }
    
    .album-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px, 1fr)); gap: 25px; margin-bottom: 50px; }
    .album-card { background: var(--glass-bg); padding: 15px; border-radius: 20px; border: 1px solid var(--glass-border); text-decoration: none; color: var(--text-main); transition: 0.3s; }
    .album-card:hover { transform: translateY(-8px); background: rgba(255, 255, 255, 0.1); border-color: rgba(255, 255, 255, 0.2); }
    .album-cover { width: 100%; height: 160px; object-fit: cover; border-radius: 15px; margin-bottom: 12px; }

    table { width: 100%; border-collapse: separate; border-spacing: 0 10px; margin-bottom: 50px;}
    td { padding: 15px 20px; background: var(--glass-bg); border-top: 1px solid var(--glass-border); border-bottom: 1px solid var(--glass-border); vertical-align: middle;}
    td:first-child { border-left: 1px solid var(--glass-border); border-radius: 15px 0 0 15px; }
    td:last-child { border-right: 1px solid var(--glass-border); border-radius: 0 15px 15px 0; }
    tr:hover td { background: rgba(255, 255, 255, 0.1); }

    .btn-play-table { 
        background: var(--primary-grad); color: white; border: none; 
        width: 36px; height: 36px; border-radius: 50%; cursor: pointer; 
        display: inline-flex; align-items: center; justify-content: center; 
        transition: 0.3s;
    }
    .btn-play-table i { margin-left: 2px; }
    .btn-play-table:hover { transform: scale(1.1); box-shadow: 0 4px 10px rgba(162, 155, 254, 0.3); }

    .player-bar { 
        position: fixed; bottom: 0; left: 0; width: 100%; height: 100px; 
        background: rgba(11, 15, 25, 0.85); 
        backdrop-filter: blur(20px); 
        -webkit-backdrop-filter: blur(20px);
        border-top: 1px solid var(--glass-border); display: flex; 
        justify-content: space-between; align-items: center; padding: 0 40px; z-index: 2000; 
        box-sizing: border-box;
    }

    .player-left { width: 30%; display: flex; align-items: center; gap: 15px; overflow: hidden; }
    .now-playing-img { width: 55px; height: 55px; border-radius: 12px; background: rgba(255, 255, 255, 0.05); display: flex; align-items: center; justify-content: center; overflow: hidden; flex-shrink: 0; }
    .now-playing-img img { width: 100%; height: 100%; object-fit: cover; display: none; }
    .now-playing-info { overflow: hidden; }
    .now-playing-info strong { display: block; font-size: 14px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; color: var(--text-main); }
    .now-playing-info span { font-size: 12px; color: var(--text-muted); }

    .player-center { width: 40%; display: flex; flex-direction: column; align-items: center; gap: 8px; }
    .controls { display: flex; align-items: center; gap: 20px; }
    .play-pause-btn { background: var(--primary-grad); color: white; width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; font-size: 18px; box-shadow: 0 4px 15px rgba(162, 155, 254, 0.2); }

    .progress-container { width: 100%; display: flex; align-items: center; gap: 10px; }
    .time-text { font-size: 11px; color: var(--text-muted); min-width: 35px; font-weight: 600; }

    input[type=range] { -webkit-appearance: none; background: rgba(255, 255, 255, 0.2); border-radius: 5px; height: 5px; width: 100%; outline: none; cursor: pointer; }
    input[type=range]::-webkit-slider-thumb { -webkit-appearance: none; width: 12px; height: 12px; border-radius: 50%; background: #ffffff; cursor: pointer; box-shadow: 0 0 5px rgba(0,0,0,0.5); } 

    .player-right { width: 30%; display: flex; justify-content: flex-end; align-items: center; gap: 12px; }
    .vol-slider { width: 90px !important; }

    @media (max-width: 768px) {
        .sidebar { width: 260px; box-shadow: 5px 0 15px rgba(0,0,0,0.5); }
        .main-content, .main-content.full-width { margin-left: 0 !important; padding: 80px 20px 120px 20px !important; width: 100vw !important; box-sizing: border-box; }
        .hamburger-menu { top: 15px; left: 15px; width: 40px; height: 40px; }
        .hero-section, .album-hero { flex-direction: column; align-items: center; text-align: center; }
        .banner { flex-direction: column; text-align: center; padding: 30px 20px; }
        .banner i { display: none; } 
        .item-grid, .album-grid, .playlist-grid { grid-template-columns: repeat(2, 1fr) !important; gap: 15px; }
        .item-cover, .album-cover, .playlist-img, .playlist-cover-img { height: 130px !important; }
        .player-bar { padding: 0 15px !important; }
        .player-right { display: none !important; }
        .player-left { width: 50% !important; }
        .player-center { width: 50% !important; }
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
        <?php if($username == 'admin'): ?>
            <a href="admin.php" style="color: var(--primary);"><i class="fa-solid fa-shield-halved"></i> Admin</a>
        <?php endif; ?>
        <a href="logout.php" class="logout-btn" onclick="return confirm('Yakin mau keluar?');"><i class="fa-solid fa-right-from-bracket"></i> Keluar</a>
    </div>

    <div class="main-content" id="mainContent">
        <div class="content-container">
            <div class="header">
                <h2 style="font-family: 'Outfit', sans-serif;">Beranda</h2>
                <a href="profile.php" style="display:flex; align-items:center; background:var(--glass-bg); padding:10px 20px; border-radius:20px; text-decoration:none; color:var(--text-main); font-weight:700; border:1px solid var(--glass-border);">
                    <i class="fa-solid fa-circle-user" style="margin-right: 10px; font-size: 20px; color: var(--primary);"></i> 
                    <?php echo htmlspecialchars($username); ?>
                </a>
            </div>

            <div class="banner">
                <div class="banner-text">
                    <h1 style="font-family: 'Outfit', sans-serif; font-size: 36px; margin:0;">Halo, <?php echo htmlspecialchars($username); ?>!</h1>
                    <p style="font-size: 16px; opacity: 0.9;">Ayo dengerin lagu favoritmu hari ini.</p>
                </div>
                <i class="fa-solid fa-compact-disc fa-spin" style="font-size: 60px; opacity: 0.5;"></i>
            </div>

            <h3 style="font-family: 'Outfit', sans-serif; font-size: 22px; margin-bottom: 20px;">Rekomendasi Album 💿</h3>

            <div class="album-grid">
            <?php
            if ($result_albums && $result_albums->num_rows > 0) {
                while($alb = $result_albums->fetch_assoc()) {
                    $album_cover = "https://picsum.photos/seed/album_" . $alb['alid'] . "/200/200";
                    echo "<a href='view_album.php?alid=" . $alb['alid'] . "' class='album-card'>";
                    echo "<img src='$album_cover' class='album-cover'>";
                    echo "<div style='font-weight:700; font-size:14px; margin-bottom:5px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;'>" . htmlspecialchars($alb['atitle']) . "</div>";
                    echo "<div style='font-size:12px; color:var(--text-muted);'>Rilis: " . htmlspecialchars($alb['adate']) . "</div>";
                    echo "</a>";
                }
            }
            ?>
            </div>

            <h3 style="font-family: 'Outfit', sans-serif; font-size: 22px;">Top 10 Lagu Terpopuler 🔥</h3>
            <table>
                <tbody>
                    <?php
                    if ($result_tracks && $result_tracks->num_rows > 0) {
                        $index_js = 0; 
                        while($row = $result_tracks->fetch_assoc()) {
                            $gambar_cover = "https://picsum.photos/seed/" . urlencode($row['ttitle']) . "/100/100";
                            
                            $songs_array[] = [
                                'title' => htmlspecialchars($row["ttitle"], ENT_QUOTES),
                                'artist' => htmlspecialchars($row["aname"] ?? 'Artis', ENT_QUOTES),
                                'file' => 'music/' . $row["ttitle"] . '.mp3', 
                                'cover' => $gambar_cover 
                            ];
                            
                            echo "<tr>";
                            echo "<td style='width:30px; font-weight:bold; color:var(--text-muted);'>" . $no++ . "</td>";
                            echo "<td><div style='display:flex; align-items:center; gap:12px;'>";
                            echo "<img src='$gambar_cover' style='width: 40px; height: 40px; border-radius: 8px;'>";
                            echo "<strong>" . htmlspecialchars($row["ttitle"]) . "</strong></div></td>";
                            echo "<td>" . htmlspecialchars($row["aname"] ?? '-') . "</td>";
                            echo "<td>" . htmlspecialchars($row["atitle"] ?? 'Single') . "</td>";
                            echo "<td style='text-align: right;'><button onclick='playSong($index_js)' class='btn-play-table'><i class='fa-solid fa-play'></i></button></td>";
                            echo "</tr>";
                            $index_js++;
                        }
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="player-bar">
        <div class="player-left">
            <div class="now-playing-img" id="player-img-container">
                <i class="fa-solid fa-music" id="player-icon-default" style="font-size: 20px; color: var(--primary);"></i>
                <img id="player-cover-img" src="" alt="Cover">
            </div>
            <div class="now-playing-info">
                <strong id="player-title">Pilih Lagu</strong>
                <span id="player-artist">Velohertz Player</span>
            </div>
        </div>
        
        <div class="player-center">
            <div class="controls">
                <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="prevSong()"><i class="fa-solid fa-backward-step"></i></button>
                <div class="play-pause-btn" id="play-btn" onclick="togglePlay()"><i class="fa-solid fa-play"></i></div>
                <button style="background:none; border:none; cursor:pointer; color:var(--text-muted);" onclick="nextSong()"><i class="fa-solid fa-forward-step"></i></button>
            </div>
            <div class="progress-container">
                <span class="time-text" id="curr-time">0:00</span>
                <input type="range" id="progress-bar" value="0" min="0" max="100">
                <span class="time-text" id="tot-time">0:00</span>
            </div>
        </div>
        
        <div class="player-right">
            <i class="fa-solid fa-volume-high" style="font-size: 13px; color: var(--text-muted);"></i>
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
        const progressBar = document.getElementById('progress-bar');
        const volumeSlider = document.getElementById('volume-slider');

        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('hidden');
            document.getElementById('mainContent').classList.toggle('full-width');
        }

        function updateSliderFill(slider) {
            const percent = ((slider.value - slider.min) / (slider.max - slider.min)) * 100;
            slider.style.background = `linear-gradient(to right, var(--primary) ${percent}%, rgba(255, 255, 255, 0.2) ${percent}%)`;
        }

        function loadSong(song) {
            document.getElementById('player-title').innerText = song.title;
            document.getElementById('player-artist').innerText = song.artist;
            document.getElementById('player-cover-img').src = song.cover;
            document.getElementById('player-cover-img').style.display = 'block';
            document.getElementById('player-icon-default').style.display = 'none';
            audio.src = song.file;
        }

        function playSong(index) {
            currentSongIndex = index;
            loadSong(songs[index]);
            audio.play();
            isPlaying = true;
            updatePlayIcon();
        }

        function togglePlay() {
            if (!audio.src) loadSong(songs[currentSongIndex]);
            if (isPlaying) { audio.pause(); } else { audio.play(); }
            isPlaying = !isPlaying;
            updatePlayIcon();
        }

        function updatePlayIcon() {
            playBtn.innerHTML = isPlaying ? '<i class="fa-solid fa-pause"></i>' : '<i class="fa-solid fa-play"></i>';
        }

        function nextSong() {
            currentSongIndex = (currentSongIndex + 1) % songs.length;
            playSong(currentSongIndex);
        }

        function prevSong() {
            currentSongIndex = (currentSongIndex - 1 + songs.length) % songs.length;
            playSong(currentSongIndex);
        }

        audio.addEventListener('timeupdate', () => {
            if (!isNaN(audio.duration)) {
                const percent = (audio.currentTime / audio.duration) * 100;
                progressBar.value = percent;
                updateSliderFill(progressBar);
                
                let currMin = Math.floor(audio.currentTime / 60);
                let currSec = Math.floor(audio.currentTime % 60);
                document.getElementById('curr-time').innerText = `${currMin}:${currSec < 10 ? '0' : ''}${currSec}`;
                
                let totMin = Math.floor(audio.duration / 60);
                let totSec = Math.floor(audio.duration % 60);
                document.getElementById('tot-time').innerText = `${totMin}:${totSec < 10 ? '0' : ''}${totSec}`;
            }
        });

        progressBar.addEventListener('input', () => updateSliderFill(progressBar));
        progressBar.addEventListener('change', () => audio.currentTime = (progressBar.value / 100) * audio.duration);
        volumeSlider.addEventListener('input', () => {
            audio.volume = volumeSlider.value / 100;
            updateSliderFill(volumeSlider);
        });

        updateSliderFill(progressBar);
        updateSliderFill(volumeSlider);
        audio.addEventListener('ended', nextSong);

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