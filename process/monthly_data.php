<?php
// monthly_data.php
include_once __DIR__ . './../config/database.php';

// Ambil parameter tahun dan bulan dari GET; gunakan nilai default jika tidak ada
$year  = isset($_GET['year']) ? (int) $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int) $_GET['month'] : date('n');

// Pastikan koneksi berhasil
if (!$conn) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Koneksi database gagal.']);
    exit;
}

// Inisialisasi variabel metrik
$totalCabang        = 0;
$totalUser          = 0;
$totalKategoriBiaya = 0;
$totalTransaksi     = 0;
$totalPengeluaran   = 0;
$totalPemasukan     = 0;

// Query Total Cabang (tabel branches menggunakan kolom created_at)
$query  = "SELECT COUNT(*) AS total FROM branches WHERE YEAR(created_at) = $year AND MONTH(created_at) = $month";
$result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalCabang = $row['total'];
}

// Query Total User (tabel users menggunakan kolom created_at)
$query  = "SELECT COUNT(*) AS total FROM users WHERE YEAR(created_at) = $year AND MONTH(created_at) = $month";
$result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalUser = $row['total'];
}

// Query Total Kategori Biaya (tabel cost_categories menggunakan kolom created_at)
$query  = "SELECT COUNT(*) AS total FROM cost_categories WHERE YEAR(created_at) = $year AND MONTH(created_at) = $month";
$result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalKategoriBiaya = $row['total'];
}

// Query Total Transaksi (tabel transactions menggunakan kolom transaction_date)
$query  = "SELECT COUNT(*) AS total FROM transactions WHERE YEAR(transaction_date) = $year AND MONTH(transaction_date) = $month";
$result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalTransaksi = $row['total'];
}

// Query Total Pengeluaran (tabel finance dengan tipe 'expense' menggunakan kolom date)
$query  = "SELECT COUNT(*) AS total FROM finance WHERE type = 'expense' AND YEAR(date) = $year AND MONTH(date) = $month";
$result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalPengeluaran = $row['total'];
}

// Query Total Pemasukan (diambil dari tabel transactions karena data pemasukan berasal dari transaksi)
$query  = "SELECT COUNT(*) AS total FROM transactions WHERE YEAR(transaction_date) = $year AND MONTH(transaction_date) = $month";
$result = mysqli_query($conn, $query);
if ($result && $row = mysqli_fetch_assoc($result)) {
    $totalPemasukan = $row['total'];
}

// Kembalikan data dalam format JSON
header('Content-Type: application/json');
echo json_encode([
    'totalCabang'        => $totalCabang,
    'totalUser'          => $totalUser,
    'totalKategoriBiaya' => $totalKategoriBiaya,
    'totalTransaksi'     => $totalTransaksi,
    'totalPengeluaran'   => $totalPengeluaran,
    'totalPemasukan'     => $totalPemasukan
]);
?>
