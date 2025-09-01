<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>
<br>

<div align="center">
  
# 🛒 Koperasi Digital SMK Informatika Utama

Koperasi **SMKIUTAMA** adalah platform E-Commerce berbasis web yang dirancang untuk mendigitalisasi operasional koperasi di SMK Informatika Utama. Proyek ini bertujuan untuk menciptakan ekosistem di lingkungan sekolah melalui fitur pembayaran digital terintegrasi, manajemen saldo siswa, dan pelaporan keuangan *real-time*.

<p align="center">
    <img src="https://img.shields.io/badge/PHP-8.2%2B-777BB4?style=for-the-badge&logo=php" alt="PHP Version">
    <img src="https://img.shields.io/badge/Laravel-11-FF2D20?style=for-the-badge&logo=laravel" alt="Laravel Version">
    <img src="https://img.shields.io/badge/Tailwind_CSS-3-38B2AC?style=for-the-badge&logo=tailwind-css" alt="Tailwind CSS">
    <img src="https://img.shields.io/badge/MySQL-8-4479A1?style=for-the-badge&logo=mysql" alt="MySQL">
</p>

</div>

---

## 📑 Daftar Isi
- [Fitur Unggulan](#-fitur-unggulan)
- [Teknologi yang Digunakan](#-teknologi-yang-digunakan)
- [Struktur Proyek](#-struktur-proyek)
- [Setup Laragon & Laravel](#-setup-laragon--laravel)
- [Panduan Instalasi](#-panduan-instalasi)
- [Setup Seeder](#-setup-seeder)
- [Roadmap Pengembangan](#-roadmap-pengembangan)
- [Tim Pengembang](#-tim-pengembang)
- [Lisensi](#-lisensi)

---

## ✨ Fitur Unggulan

Platform ini dirancang dengan fitur-fitur modern untuk memenuhi kebutuhan semua pihak di sekolah.

| Fitur | Untuk Siswa & Guru | Untuk Admin Koperasi | Untuk Pihak Sekolah |
| :--- | :---: | :---: | :---: |
| 🔐 **Autentikasi Aman** | ✅ | ✅ | ✅ |
| 💳 **Manajemen Saldo Digital** | ✅ | ✅ | - |
| 🛍️ **Pemesanan Online** | ✅ | ✅ | ✅ |
| 📲 **Pembayaran QR Code** | ✅ | ✅ | - |
| 📊 **Laporan Real-Time** | ✅ | ✅ | ✅ |
| 📦 **Manajemen Inventori** | - | ✅ | - |
| 💸 **Top-Up Saldo Multi-Channel**| ✅ | ✅ | - |

---

## 🛠️ Teknologi yang Digunakan

Proyek ini dibangun menggunakan tumpukan teknologi (tech stack) yang modern dan andal.

- **Backend**: **Laravel 11** (PHP 8.2+ Wajib)
- **Frontend**: **Blade Engine** dengan **Tailwind CSS**
- **Database**: **MySQL**
- **Server**: Laragon (Localhost) & Server Sekolah (Production)
- **Integrasi Pembayaran**: QRIS (OrderKuota)
- **Arsitektur**: Aplikasi Web Monolitik (Akses via PC, Smartphone, dan Tablet)

---

## 📂 Struktur Proyek

E-commerce/
├── app/ # Logika inti aplikasi (Models, Controllers, Providers)
├── config/ # File konfigurasi proyek
├── database/ # Migrasi, Seeder, dan Factory database
├── public/ # Aset publik (CSS, JS, gambar)
├── resources/
│ ├── css/
│ ├── js/
│ └── views/ # Template Blade untuk antarmuka pengguna (UI)
├── routes/ # Definisi rute (web.php, api.php)
├── .env # File konfigurasi environment
└── README.md # Dokumentasi proyek


---

## ⚙️ Setup Laragon

Laragon adalah server lokal yang ringan dan mendukung Laravel secara penuh. Berikut langkah-langkahnya:

### **1. Download & Install Laragon**
- Unduh Laragon dari [https://laragon.org/download/](https://laragon.org/download/)
- Pilih versi **Full** agar semua modul tersedia.
- Setelah instalasi, buka Laragon.

### **2. Pastikan PHP Versi 8.2+**
- Cek versi PHP di Laragon:
    ```
    php -v
    ```
- Jika belum 8.2, unduh PHP 8.2 dari [Laragon PHP Releases](https://github.com/laragon/php/releases) dan ekstrak ke folder:
    ```
    C:\laragon\bin\php\
    ```
- Pilih versi melalui:
    **Menu Laragon → PHP → Version → Pilih 8.2**

### **3. Aktifkan Composer**
- Laragon sudah mendukung Composer bawaan. Cek dengan:
    ```
    composer -V
    ```

### **4. Buat Project Laravel Baru di Laragon**
- Buka terminal Laragon (Cmder) dan jalankan:
    ```
    composer create-project laravel/laravel:^11.0 E-commerce_koperasi_oscar
    ```
- Setelah selesai, buka Laragon dan klik:
    **Menu → Quick App → Laravel → Isi nama proyek**  
    (Laragon akan otomatis membuat VirtualHost)

### **5. Akses Project**
- Aplikasi dapat diakses melalui:
    ```
    http://nama-projek.test
    ```

---

## 🚀 Panduan Instalasi

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di lingkungan lokal Anda.

### **1. Prasyarat**
- PHP (versi 8.2)
- Composer
- Node.js & NPM
- MySQL atau HeidiSQL
- Laragon (disarankan)

### **2. Langkah-langkah Instalasi**

1. **Clone repository ini:**
    ```bash
    git clone https://github.com/username/oscar-2.0.git
    cd oscar-2.0
    ```

2. **Instal dependensi PHP:**
    ```bash
    composer install
    ```

3. **Instal dependensi JavaScript:**
    ```bash
    npm install
    ```

4. **Buat file environment:**
    ```bash
    cp .env.example .env
    ```

5. **Generate application key:**
    ```bash
    php artisan key:generate
    ```

6. **Konfigurasi database di file `.env`**

7. **Jalankan migrasi & seeder:**
    ```bash
    php artisan migrate --seed
    ```

8. **Compile aset frontend:**
    ```bash
    npm run dev
    ```

9. **Jalankan server:**
    ```bash
    php artisan serve
    ```
    Atau akses via Laragon:
    ```
    http://E-commerce_koperasi_oscar.test
    ```

---

## 🌱 Setup Seeder

Seeder digunakan untuk mengisi data awal (default) pada database setelah migrasi.

### **Perintah Menjalankan Seeder**
```bash
php artisan migrate --seed

Seeder yang Disertakan

RoleSeeder → Menambahkan role pengguna (admin, guru, siswa)

UserSeeder → Membuat akun admin default:

Admin Koperasi:
Email: nunik@gmail.com
Password: nunik1234

Kepala Koperasi:
Email: harnoko@gmail.com
Password: harnoko1234

Siswa:
Email: sultannafis1324@gmail.com
Password: sultan1324

CategorySeeder → Menambahkan kategori produk awal

ProductSeeder → Menambahkan produk contoh

Menjalankan Seeder Tertentu
php artisan db:seed --class=NamaSeeder

Contoh:
php artisan db:seed --class=UserSeeder

---

---

## 🌍 Roadmap Pengembangan

- 🔔 Notifikasi Real-Time via WhatsApp
- 🎁 Program Loyalitas
- 📱 Aplikasi Mobile
- 🧾 Analitik & Dasbor

---

## 👨‍💻 Tim Pengembang

Sultan Nafis - (Lead Developer / Backend)

Syawaludin Alhabsy - (Frontend Developer)

Harnoko, S.Kom. - (Pembimbing / Project Advisor)

📜 Lisensi

Proyek ini dibuat untuk keperluan Lomba Web Development OSCAR 2.0 dan penggunaan internal di SMK Informatika Utama. Hak cipta dilindungi dan tidak untuk didistribusikan secara komersial tanpa izin.


---
