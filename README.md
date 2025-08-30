<p align="center">
  <img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo">
</p>
<br>

<div align="center">
  
# 🛒 Koperasi Digital SMK Informatika Utama

Koperasi SMKIUTAMA* adalah platform E-Commerce berbasis web yang dirancang untuk mendigitalisasi operasional koperasi di SMK Informatika Utama. Proyek ini bertujuan untuk menciptakan ekosistem di lingkungan sekolah melalui fitur pembayaran digital terintegrasi, manajemen saldo siswa, dan pelaporan keuangan *real-time*.

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
- [Panduan Instalasi](#-panduan-instalasi)
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

- **Backend**: **Laravel 11** (PHP)
- **Frontend**: **Blade Engine** dengan **Tailwind CSS**
- **Database**: **MySQL**
- **Server**: Localhost (Development) & Server Sekolah (Production)
- **Integrasi Pembayaran**: QRIS (OrderKuota)
- **Arsitektur**: Aplikasi Web Monolitik (Akses via PC, Smartphone, Dan Tablet)

---

## 📂 Struktur Proyek

Struktur direktori utama dari proyek ini mengikuti standar framework Laravel.

```
E-commerce/
├── app/                # Logika inti aplikasi (Models, Controllers, Providers)
├── config/             # File konfigurasi proyek
├── database/           # Migrasi, Seeder, dan Factory database
├── public/             # Aset publik (CSS, JS, gambar)
├── resources/
│   ├── css/
│   ├── js/
│   └── views/          # Template Blade untuk antarmuka pengguna (UI)
├── routes/             # Definisi rute (web.php, api.php)
├── .env                # File konfigurasi environment
└── README.md           # Dokumentasi proyek
```

---

## 🚀 Panduan Instalasi

Ikuti langkah-langkah berikut untuk menjalankan proyek ini di lingkungan lokal Anda.

### **1. Prasyarat**
Pastikan perangkat Anda telah terinstal:
- PHP (versi 8.2 atau lebih baru)
- Composer
- Node.js & NPM
- MySQL atau database sejenis

### **2. Langkah-langkah Instalasi**

1.  **Clone repository ini:**
    ```bash
    git clone [https://github.com/username/oscar-2.0.git](https://github.com/username/oscar-2.0.git)
    cd oscar-2.0
    ```

2.  **Instal dependensi PHP (Composer):**
    ```bash
    composer install
    ```

3.  **Instal dependensi JavaScript (NPM):**
    ```bash
    npm install
    ```

4.  **Buat file environment:**
    Salin file `.env.example` menjadi `.env`.
    ```bash
    cp .env.example .env
    ```

5.  **Generate application key:**
    ```bash
    php artisan key:generate
    ```

6.  **Konfigurasi database:**
    Buka file `.env` dan sesuaikan pengaturan database Anda (`DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`).

7.  **Jalankan migrasi dan seeder database:**
    Perintah ini akan membuat semua tabel dan mengisi data awal yang diperlukan.
    ```bash
    php artisan migrate --seed
    ```

8.  **Compile aset frontend:**
    ```bash
    npm run dev
    ```

9.  **Jalankan server development:**
    ```bash
    php artisan serve
    ```
    Aplikasi kini dapat diakses di `http://127.0.0.1:8000`.

---

## 🗺️ Roadmap Pengembangan

Berikut adalah rencana pengembangan fitur untuk versi selanjutnya.

- [ ] 🔔 **Notifikasi Real-Time** via WhatsApp untuk status pesanan dan top-up.
- [ ] 🎁 **Program Loyalitas** dengan sistem poin dan voucher belanja.
- [ ] 📱 **Aplikasi Mobile** pendamping untuk Android & iOS.
- [ ] 📈 **Analitik & Dasbor** yang lebih mendalam untuk pihak sekolah.
- [ ] 🌐 **Integrasi dengan Sistem Akademik** untuk sinkronisasi data siswa.

---

## 👨‍💻 Tim Pengembang

Proyek ini dikembangkan dan dibimbing oleh:

- **Sultan Nafis** - (Lead Developer / Backend)
- **Syawaludin Alhabsy** - (Frontend Developer)
- **Harnoko, S.Kom.** - (Pembimbing / Project Advisor)

---

## 📜 Lisensi

Proyek ini dibuat untuk keperluan Lomba Web Development OSCAR 2.0 dan penggunaan internal di lingkungan **SMK Informatika Utama**. Hak cipta dilindungi dan tidak untuk didistribusikan secara komersial tanpa izin.
