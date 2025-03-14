# Sistem Informasi Koperasi Simpan Pinjam Sido Manunggal

![Version](https://img.shields.io/badge/version-1.0.0-blue)
![PHP](https://img.shields.io/badge/PHP-8.1+-green)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-4.3.1-red)
![License](https://img.shields.io/badge/license-MIT-yellow)

Sistem Informasi Koperasi Simpan Pinjam berbasis web yang dibangun menggunakan framework CodeIgniter 4 untuk membantu pengelolaan administrasi dan keuangan Koperasi Kredit Sido Manunggal.

## 📑 Deskripsi Proyek

Proyek ini merupakan implementasi sistem informasi untuk mengatasi permasalahan pencatatan manual pada Koperasi Kredit Sido Manunggal. Sistem ini dikembangkan sebagai bagian dari penelitian skripsi dengan tujuan meningkatkan efisiensi, akurasi, dan kemudahan akses dalam pengelolaan data simpanan dan pinjaman anggota koperasi.

### Latar Belakang

Koperasi Kredit Sido Manunggal menghadapi kendala dalam pengelolaan administrasi simpan pinjam yang masih dilakukan secara manual menggunakan buku catatan dan spreadsheet sederhana. Hal ini menyebabkan:

- Keterlambatan dalam pelaporan keuangan
- Kesulitan dalam pencarian dan penelusuran data anggota
- Potensi kesalahan input data yang tinggi
- Inefisiensi dalam proses administrasi

Sistem informasi ini dikembangkan untuk mengatasi permasalahan tersebut dengan menghadirkan solusi digital yang terintegrasi.

## 🚀 Fitur Utama

- **Coming Soon**

## 💻 Teknologi yang Digunakan

- **Backend Framework**: CodeIgniter 4
- **Database**: MySQL
- **Frontend**: Bootstrap 5, jQuery, Chart.js
- **Authentication**: CodeIgniter Shield
- **Reporting**: DOMPDF
- **Version Control**: Git

## 🛠️ Instalasi

### Persyaratan Sistem

- PHP 8.2 atau lebih tinggi
- MySQL 5.7 atau lebih tinggi
- Composer
- Web server (Apache/Nginx)

### Langkah Instalasi

1. Clone repositori ini

   ```bash
   git clone https://github.com/username/koperasi-sido-manunggal.git
   cd koperasi-sido-manunggal
   ```

2. Instal dependensi menggunakan Composer

   ```bash
   composer install
   ```

3. Salin file `.env.example` menjadi `.env` dan sesuaikan konfigurasi database

   ```bash
   cp .env.example .env
   ```

4. Sesuaikan konfigurasi database pada file `.env`

   ```
   database.default.hostname = localhost
   database.default.database = koperasi_sidomanunggal
   database.default.username = root
   database.default.password = password
   database.default.DBDriver = MySQLi
   ```

5. Jalankan migrasi database

   ```bash
   php spark migrate
   ```

6. Jalankan seeder untuk data awal

   ```bash
   php spark db:seed UserSeeder
   ```

7. Jalankan server pengembangan

   ```bash
   php spark serve
   ```

8. Akses aplikasi melalui browser
   ```
   http://localhost:8080
   ```

## 👥 Kontribusi

Proyek ini merupakan bagian dari penelitian skripsi, namun kontribusi untuk perbaikan dan pengembangan sangat diterima. Silakan ikuti langkah-langkah berikut untuk berkontribusi:

1. Fork repositori
2. Buat branch fitur baru (`git checkout -b feature/fitur-baru`)
3. Commit perubahan (`git commit -m 'Menambahkan fitur baru'`)
4. Push ke branch (`git push origin feature/fitur-baru`)
5. Buat Pull Request

## 📝 Lisensi

Proyek ini dilisensikan di bawah [MIT License](LICENSE).

## 🙏 Acknowledgments

- Koperasi Kredit Sido Manunggal yang telah bersedia menjadi objek penelitian
- Dosen pembimbing yang telah memberikan arahan selama penelitian
- CodeIgniter Community untuk framework yang luar biasa
- Semua pihak yang telah membantu dalam penyelesaian proyek

---

⭐ Dikembangkan sebagai bagian dari penelitian skripsi di Universitas Bina Sarana Informatika © 2025
