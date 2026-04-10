# Sistem Antrian Infinix Roxy Mas

Aplikasi antrian berbasis web menggunakan Laravel (arsitektur MVC).

## 1. Kebutuhan Sistem

Gunakan versi berikut agar setup paling aman dan minim error:

1. XAMPP 8.3.x (disarankan 8.3.12 atau lebih baru)
2. PHP 8.3.x
3. MySQL/MariaDB dari XAMPP
4. Composer 2.x
5. Git 2.x
6. Node.js 20.x atau 22.x (opsional, jika ingin jalankan Vite frontend)

Catatan:

1. Tidak wajib install Laravel global.
2. Laravel sudah ikut sebagai dependency project dan dijalankan via Composer.

## 2. Akses Aplikasi dan User Default

Setelah setup selesai, akses:

1. Halaman pelanggan: http://127.0.0.1:8000
2. Login admin: http://127.0.0.1:8000/admin/login

User admin default dari seeder:

1. Email: admin@infinix-roxy.local
2. Password: Admin12345!

## 3. Cara Install dari Repo (Clone)

Jalankan di terminal:

1. git clone https://github.com/Qhansa29/Tiketing-roxy.git
2. cd Tiketing-roxy/app
3. composer install
4. copy .env.example .env (Windows) atau cp .env.example .env (Linux/macOS)
5. php artisan key:generate

Lalu atur koneksi database di file .env:

1. DB_CONNECTION=mysql
2. DB_HOST=127.0.0.1
3. DB_PORT=3306
4. DB_DATABASE=db_antrian_infinix
5. DB_USERNAME=root
6. DB_PASSWORD=

## 4. Cara Buat DB dan Import SQL Export

Project sudah menyediakan dump database di:

1. database/exports/db_antrian_infinix.sql

Langkah import via phpMyAdmin:

1. Buat database bernama db_antrian_infinix
2. Buka phpMyAdmin
3. Pilih database db_antrian_infinix
4. Klik tab Import
5. Pilih file database/exports/db_antrian_infinix.sql
6. Klik Go/Import

Alternatif import via CLI:

1. mysql -h 127.0.0.1 -P 3306 -u root db_antrian_infinix < database/exports/db_antrian_infinix.sql

## 5. Jalankan Aplikasi

Pilihan A (disarankan):

1. cd app
2. php artisan serve
3. Buka URL yang muncul (default http://127.0.0.1:8000)

Pilihan B (Apache XAMPP):

1. Pastikan Apache aktif
2. Akses ke folder public app sesuai konfigurasi virtual host atau path lokal Anda

## 6. Opsional Frontend Dev Server

Jika ingin jalankan Vite hot reload:

1. npm install
2. npm run dev

## 7. Sinkronisasi Repo (Remote ke Local)

Ambil update terbaru dari GitHub:

1. git pull origin main

Kirim perubahan dari local ke GitHub:

1. git add .
2. git commit -m "pesan perubahan"
3. git push origin main

## 8. Catatan Penting

1. File .env tidak ikut ke Git, jadi wajib set ulang di setiap laptop.
2. Jika config tidak terbaca setelah ubah .env, jalankan: php artisan config:clear
3. Jika ada perubahan struktur DB, jalankan migration terbaru: php artisan migrate
