# SIA Cash Basis

SIA Cash Basis adalah sistem akuntansi berbasis kas yang dikembangkan untuk **CV. Tamora Electric**. Proyek ini dibangun menggunakan PHP, MySQL, dan mengadopsi tema [SB Admin 2](https://startbootstrap.com/theme/sb-admin-2) agar tampilan responsif dan modern. Selain itu, digunakan juga [Chart.js](https://www.chartjs.org/) untuk grafik interaktif dan [FontAwesome](https://fontawesome.com/) untuk ikon-ikon yang menarik.
## Teknologi yang Digunakan

![PHP](https://img.shields.io/badge/-PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/-MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/-Bootstrap-563D7C?style=flat-square&logo=bootstrap&logoColor=white)
![Chart.js](https://img.shields.io/badge/-Chart.js-FB8C00?style=flat-square&logo=chartdotjs&logoColor=white)
![FontAwesome](https://img.shields.io/badge/-FontAwesome-2E2E2E?style=flat-square&logo=fontawesome&logoColor=white)
![SB Admin 2](https://img.shields.io/badge/-SB%20Admin%202-4E73DF?style=flat-square)

## Fitur

- **Dashboard Berdasarkan Role:**
  - **Owner:** Menampilkan data personal (welcome message menggunakan nama user).
  - **Kasir:** Dashboard dan Financial Management Review yang difilter berdasarkan cabang.
  - **Pegawai:** Tampilan dashboard yang sederhana dengan 6 card yang menampilkan:
    - **Cabang:** Nama cabang.
    - **Total User:** Jumlah user di cabang.
    - **Kategori Biaya:** Jumlah kategori biaya di cabang.
    - **Transaksi:** Jumlah transaksi di cabang.
    - **Pengeluaran:** Total pengeluaran (finance dengan tipe *expense*).
    - **Pemasukan:** Total pemasukan (dari transaksi).
- **Real-time Update:**
  - Update data user status dan grafik dilakukan secara dinamis menggunakan AJAX.
- **Financial Management:**
  - Menampilkan data penjualan per barang (dengan progress bar berwarna-warni) jika diakses oleh role Kasir atau Owner.
- **Responsive & Modern:**
  - Menggunakan Bootstrap dan SB Admin 2 agar aplikasi mudah diakses di berbagai perangkat.

## Ikon yang Digunakan

Proyek ini menggunakan ikon dari [FontAwesome](https://fontawesome.com/) untuk mempercantik tampilan, di antaranya:
- **`fas fa-building`** untuk informasi cabang.
- **`fas fa-users`** untuk jumlah user.
- **`fas fa-tags`** untuk kategori biaya.
- **`fas fa-file-invoice-dollar`** untuk transaksi.
- **`fas fa-arrow-down`** untuk pengeluaran.
- **`fas fa-arrow-up`** untuk pemasukan.

## Cara Memulai

### Prasyarat
- **Web Server:** XAMPP, WAMP, atau sejenisnya.
- **Database:** MySQL.
- **PHP:** Versi 7.x atau lebih tinggi.
- **Git:** Untuk versi kontrol.

### Instalasi

## Clone Repository:

   ```bash
   git clone https://github.com/JumjumiAsbullah-08/SIA-Cash-Basis.git
   ```
## Import Database

Cari file SQL (misalnya di folder `database`) dan import ke MySQL menggunakan **phpMyAdmin** atau command line.

## Konfigurasi Database

Ubah konfigurasi di file `config/database.php` sesuai dengan kredensial MySQL Anda.

## Jalankan Aplikasi

- Tempatkan folder proyek ke direktori root web server (misalnya `htdocs` untuk XAMPP).
- Akses aplikasi melalui browser, misalnya: [http://localhost/SIA/](http://localhost/SIA/)

## Penggunaan

### Login

- Gunakan kredensial sesuai role (Owner, Kasir, atau Pegawai).
- **Owner** akan melihat welcome message dengan nama mereka, sedangkan **Kasir** dan **Pegawai** akan melihat nama cabang sesuai dengan data di database.

### Dashboard

- **Owner & Kasir:** Mendapatkan dashboard lengkap dengan grafik interaktif dan Financial Management Review.
- **Pegawai:** Mendapatkan tampilan sederhana berupa 6 card yang menampilkan data berdasarkan cabang.

## Kontribusi

Kontribusi sangat dipersilakan! Silakan fork repository ini dan kirimkan pull request jika ada perbaikan atau fitur baru yang ingin Anda tambahkan.

## Lisensi

Proyek ini dilisensikan di bawah **MIT License**.

## Kontak

Untuk pertanyaan atau dukungan, silakan hubungi jumjumiasbullah8@gmail.com atau kunjungi profil GitHub [@JumjumiAsbullah-08](https://github.com/JumjumiAsbullah-08).
