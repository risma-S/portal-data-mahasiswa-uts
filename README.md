# Sistem Manajemen Data Interaktif — Portal Data Mahasiswa

Aplikasi web dinamis berbasis **PHP**, **HTML5 Semantik**, **CSS Murni (Grid & Flexbox)**, dan **JavaScript** untuk mengelola data mahasiswa (tambah, ubah, hapus, filter/pencarian, statistik ringkasan, dan reset data sesi).

## Fitur Utama
- **CRUD Data Mahasiswa (`$_SESSION` & HTTP `POST` / `GET`):** Tambah data baru, edit data, hapus data, dan reset ke data awal.
- **Validasi Input:** Kolom NIM divalidasi agar hanya menerima angka (`pattern="[0-9]+"` di sisi klien dan `ctype_digit()` di sisi server PHP).
- **Pencarian & Filter:** Filter program studi dan kata kunci melalui HTTP `GET` serta filter langsung (*live search*) di sisi klien menggunakan JavaScript.
- **Tampilan Responsif:** Mendukung tampilan layar desktop maupun smartphone.

## Cara Menjalankan di Lokal (XAMPP)
1. Salin folder proyek ke `C:\xampp\htdocs\data_mahasiswa`.
2. Jalankan **Apache** pada **XAMPP Control Panel**.
3. Buka browser dan akses `http://localhost/data_mahasiswa`.
4. Untuk menguji di smartphone dalam jaringan yang sama, akses `http://<IPv4_LAPTOP>/data_mahasiswa`.

## Dokumentasi
Berkas laporan proyek tersedia di folder `docs/` dengan judul **Laporan Project [Nama Mahasiswa]**.