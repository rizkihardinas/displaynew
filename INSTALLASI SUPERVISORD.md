# Instalasi Supervisord di Raspberry Pi (untuk Laravel Echo Server)

Panduan ini menjelaskan langkah instalasi `supervisord` di Raspberry Pi (Raspberry Pi OS / Debian-based) sampai menjalankan program `laravel-echo-server` secara otomatis dan tetap hidup (auto-restart).

## 1. Update Sistem

```bash
sudo apt update
sudo apt upgrade -y
```

## 2. Install Supervisor

```bash
sudo apt install supervisor -y
```

Cek apakah service berjalan:

```bash
sudo systemctl status supervisor
```

Jika belum aktif, jalankan dan aktifkan agar otomatis start saat boot:

```bash
sudo systemctl enable supervisor
sudo systemctl start supervisor
```

## 3. Pastikan Laravel Echo Server Terinstall

Program `laravel-echo-server` harus sudah terinstall global dan bisa dipanggil lewat path berikut:

```
/usr/local/bin/laravel-echo-server
```

Cek dengan:

```bash
which laravel-echo-server
```

Jika belum ada, install dulu (butuh Node.js & npm):

```bash
sudo npm install -g laravel-echo-server
```

Pastikan hasil `which laravel-echo-server` menunjuk ke `/usr/local/bin/laravel-echo-server`. Jika path berbeda, sesuaikan `command=` pada konfigurasi di bawah.

## 4. Buat File Konfigurasi Supervisor

Buat file konfigurasi baru di direktori `conf.d`:

```bash
sudo nano /etc/supervisor/conf.d/laravel-echo-server.conf
```

Isi dengan konfigurasi berikut:

```ini
[program:laravel-echo-server]
process_name=%(program_name)s
directory=/var/www/html
command=/usr/local/bin/laravel-echo-server start
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/log/laravel-echo-server.log
stopwaitsecs=3600
```

Simpan file (`Ctrl+O`, `Enter`, lalu `Ctrl+X` untuk keluar dari nano).

### Penjelasan Singkat Konfigurasi

| Opsi | Fungsi |
|---|---|
| `directory` | Folder kerja saat proses dijalankan (sesuaikan dengan lokasi project Laravel) |
| `command` | Perintah untuk menjalankan Laravel Echo Server |
| `autostart` | Otomatis dijalankan saat supervisord start |
| `autorestart` | Otomatis restart jika proses mati/crash |
| `stopasgroup` & `killasgroup` | Memastikan seluruh proses (termasuk child process) ikut dihentikan saat stop |
| `user` | User yang menjalankan proses (`www-data` — pastikan user ini punya akses ke folder & Node) |
| `stdout_logfile` | Lokasi file log |
| `stopwaitsecs` | Waktu tunggu maksimal (detik) sebelum proses dipaksa kill saat stop |

## 5. Siapkan File Log

Pastikan file log bisa ditulis oleh user `www-data`:

```bash
sudo touch /var/log/laravel-echo-server.log
sudo chown www-data:www-data /var/log/laravel-echo-server.log
```

## 6. Terapkan Konfigurasi

Setelah file konfigurasi dibuat, reload supervisor agar konfigurasi baru terbaca:

```bash
sudo supervisorctl reread
sudo supervisorctl update
```

## 7. Jalankan / Cek Status Program

Jalankan program (jika belum otomatis start):

```bash
sudo supervisorctl start laravel-echo-server
```

Cek status:

```bash
sudo supervisorctl status laravel-echo-server
```

Output yang diharapkan:

```
laravel-echo-server              RUNNING   pid 1234, uptime 0:00:05
```

## 8. Melihat Log

```bash
tail -f /var/log/laravel-echo-server.log
```

## 9. Perintah Supervisorctl yang Berguna

```bash
sudo supervisorctl status              # cek status semua program
sudo supervisorctl restart laravel-echo-server
sudo supervisorctl stop laravel-echo-server
sudo supervisorctl start laravel-echo-server
sudo supervisorctl reread              # baca ulang perubahan config
sudo supervisorctl update              # terapkan perubahan config
```

## 10. Troubleshooting

- **Status `FATAL` atau `BACKOFF`**: cek isi log di `/var/log/laravel-echo-server.log`, biasanya karena path `command` salah atau dependency belum lengkap.
- **Permission denied**: pastikan user `www-data` punya akses baca/tulis ke folder `directory` dan file `laravel-echo-server`.
- **Command not found**: pastikan path binary sesuai hasil `which laravel-echo-server` — kadang di Raspberry Pi path npm global berbeda (misal `/usr/bin/laravel-echo-server` atau lewat nvm).
- Jika pakai **nvm**, service `www-data` biasanya tidak mengenali nvm, jadi sebaiknya install Node.js secara global (via apt/NodeSource) agar path konsisten di `/usr/local/bin` atau `/usr/bin`.