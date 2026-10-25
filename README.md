# Vakano Toserba 🛒

Sistem informasi manajemen toko serba ada (toserba) berbasis web yang dirancang untuk mengintegrasikan operasional kasir, manajemen inventaris, pengelolaan sumber daya manusia, dan pemantauan analitik bisnis secara efisien.

## 🚀 Teknologi yang Digunakan
* **Backend:** PHP Native (PHP 8.x)
* **Database:** MySQL / MariaDB
* **Environment:** Laragon / XAMPP
* **Frontend:** HTML, CSS, JavaScript

## 👥 Manajemen Hak Akses (Role-Based Access)
Aplikasi ini memiliki sistem *multi-user* dengan pembagian tugas yang spesifik:
* **Admin:** Mengelola konfigurasi sistem, data master, dan akses CRUD dinamis untuk seluruh tabel *database*.
* **Staff Gudang:** Bertanggung jawab atas *inventory*, mutasi stok, pencatatan barang masuk dari *supplier*, dan retur.
* **Kasir:** Menangani antarmuka *Point of Sale* (POS), pemrosesan transaksi pelanggan, dan pencetakan struk.
* **HRD:** Mengelola data kepegawaian, pencatatan absensi, dan struktur penggajian karyawan.
* **CEO:** Memiliki akses ke dasbor eksekutif (*Read-Only*) untuk memantau laporan pendapatan, arus kas, dan barang terlaris.

## 🛠️ Panduan Instalasi (Lokal)
1. **Siapkan Server Lokal:** Pastikan *service* Apache dan MySQL/MariaDB sudah berjalan di Laragon atau XAMPP.
2. **Penempatan Direktori:** Pindahkan folder proyek `vakano_toserba` ke dalam *document root*:
   * Jika menggunakan Laragon: `C:\laragon\www\vakano_toserba`
   * Jika menggunakan XAMPP: `C:\xampp\htdocs\vakano_toserba`
3. **Konfigurasi Database:**
   * Buka antarmuka manajemen *database* (seperti phpMyAdmin atau HeidiSQL).
   * Buat *database* baru dengan nama `vakano_toserba`.
   * Lakukan *import* struktur tabel dari file `.sql` yang tersedia di dalam folder proyek.
4. **Kredensial Koneksi:** Pastikan file koneksi PHP (misal: `koneksi.php`) sudah sesuai dengan pengaturan lokalmu:
   ```php
   $koneksi = mysqli_connect("localhost", "root", "", "vakano_toserba");
