# SIA Cash Basis

SIA Cash Basis adalah sistem akuntansi berbasis kas yang dikembangkan untuk **CV. Tamora Electric**. Proyek ini dibangun menggunakan PHP, MySQL, dan mengadopsi tema [SB Admin 2](https://startbootstrap.com/theme/sb-admin-2) agar tampilan responsif dan modern. Selain itu, digunakan juga [Chart.js](https://www.chartjs.org/) untuk grafik interaktif dan [FontAwesome](https://fontawesome.com/) untuk ikon-ikon yang menarik.
## Teknologi yang Digunakan

Proyek ini dibangun dengan menggunakan teknologi dan framework berikut:
- **PHP**  
  ![PHP Logo](https://www.php.net/images/logos/new-php-logo.png)

- **MySQL**  
  ![MySQL Logo](https://upload.wikimedia.org/wikipedia/en/d/dd/MySQL_logo.svg)

- **Bootstrap**  
  ![Bootstrap Logo](https://getbootstrap.com/docs/5.0/assets/brand/bootstrap-logo-shadow.png)

- **Chart.js**  
  ![Chart.js Logo](https://www.chartjs.org/img/chartjs-logo.svg)

- **FontAwesome**  
  ![FontAwesome Logo](https://upload.wikimedia.org/wikipedia/commons/4/4e/Font_Awesome_5_logo.svg)

- **SB Admin 2**  
  ![SB Admin 2 Logo](https://startbootstrap.com/assets/img/logos/sb-admin-2.svg)

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
