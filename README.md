# VeloHertz

VeloHertz adalah platform streaming dan manajemen musik digital berbasis web yang dikembangkan menggunakan arsitektur native. Proyek ini dirancang secara khusus untuk memenuhi standar penilaian akademik Program Studi Sistem Informasi Universitas Pelita Harapan (UPH) Medan, dengan fokus utama pada integritas data relasional, efisiensi manajemen media, dan keamanan berlapis pada akun publik.


## Fitur Utama Sistem

**Autentikasi & Keamanan Berlapis:** Sistem login multi-level (Admin & User) yang diamankan menggunakan enkripsi sandi *BCRYPT* serta verifikasi akun menggunakan kode OTP (One-Time Password) yang dikirim otomatis melalui email.
**Arsitektur Database Relasional:** Penerapan struktur database teroptimasi dengan relasi *One-to-Many* (Album ke Track) dan *Many-to-Many* menggunakan *Junction/Bridge Table* (`playlistcontain`) untuk mengelola daftar putar kustom tanpa redudansi data.
**Dashboard Admin CRUD Instan:** Manajemen data terpusat bagi administrator untuk menambah, memperbarui, dan menghapus data Master Album serta Master Lagu. Dilengkapi fitur *Multiple Upload* hingga 10 file audio `.mp3` sekaligus secara langsung ke server.
**Pemutar Musik Interaktif:** Antarmuka pemutar musik *front-end* yang sinkron secara *real-time* dengan kontrol audio dinamis seperti *Play*, *Pause*, *Volume Adjustment*, dan *Progress Bar tracking*.

## Spesifikasi Teknologi (Tech Stack)
**Back-end:** PHP (Native)
**Front-end:** HTML5, CSS3 (Custom Responsive UI), JavaScript (ES6 Native)
**Database Management:** MySQL / MariaDB
**Ekstensi & Library:** PHPMailer (Modul Pengiriman Email OTP)

## Catatan Evaluasi (Developer Notes)

Sebagai bentuk kepatuhan terhadap praktik keamanan perangkat lunak, terdapat beberapa penyesuaian pada repositori ini:

1. **Pengosongan App Password (SMTP)**
   Kredensial pengiriman email otomatis pada file konfigurasi (seperti `register.php`) telah dikosongkan. Untuk menguji fungsionalitas OTP, mohon masukkan 16-digit *Google App Password* Anda sendiri pada variabel yang tersedia.

2. **Skema Database**
   File `project1.sql` yang disertakan sudah berisi struktur tabel final beserta data *dumping*. Cukup lakukan *import* pada basis data bernama `project1`.

3. **Limitasi File Server**
   Dashboard Admin dilengkapi fitur *Multiple Upload* (.mp3). Untuk pengujian fitur ini secara maksimal, pastikan parameter `upload_max_filesize` dan `post_max_size` pada `php.ini` diatur minimal 50M.
