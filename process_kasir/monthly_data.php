<?php
session_start();
include_once __DIR__ . './../config/database.php';

// Pastikan user sudah login dan branch_id tersedia
if (!isset($_SESSION['branch_id'])) {
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$branch_id = $_SESSION['branch_id'];

// Ambil parameter tahun dan bulan dari GET, gunakan default tahun dan bulan saat ini jika tidak ada
$year  = isset($_GET['year']) ? (int) $_GET['year'] : date('Y');
$month = isset($_GET['month']) ? (int) $_GET['month'] : date('n');

// Query untuk total user (dengan filter berdasarkan tanggal pembuatan) pada cabang tersebut
$query = "SELECT COUNT(*) AS total 
          FROM users 
          WHERE branch_id = $branch_id 
            AND YEAR(created_at) = $year 
            AND MONTH(created_at) = $month";
$result = mysqli_query($conn, $query);
$totalUser = ($result && $row = mysqli_fetch_assoc($result)) ? $row['total'] : 0;

// Query untuk total kategori biaya pada cabang tersebut
$query = "SELECT COUNT(*) AS total 
          FROM cost_categories 
          WHERE branch_id = $branch_id 
            AND YEAR(created_at) = $year 
            AND MONTH(created_at) = $month";
$result = mysqli_query($conn, $query);
$totalKategoriBiaya = ($result && $row = mysqli_fetch_assoc($result)) ? $row['total'] : 0;

// Query untuk total transaksi pada cabang tersebut (menggunakan kolom transaction_date)
$query = "SELECT COUNT(*) AS total 
          FROM transactions 
          WHERE branch_id = $branch_id 
            AND YEAR(transaction_date) = $year 
            AND MONTH(transaction_date) = $month";
$result = mysqli_query($conn, $query);
$totalTransaksi = ($result && $row = mysqli_fetch_assoc($result)) ? $row['total'] : 0;

// Query untuk total pengeluaran pada cabang tersebut (dari tabel finance dengan type 'expense')
$query = "SELECT COUNT(*) AS total 
          FROM finance 
          WHERE branch_id = $branch_id 
            AND type = 'expense' 
            AND YEAR(date) = $year 
            AND MONTH(date) = $month";
$result = mysqli_query($conn, $query);
$totalPengeluaran = ($result && $row = mysqli_fetch_assoc($result)) ? $row['total'] : 0;

// Query untuk total pemasukan pada cabang tersebut (dari transaksi)
$query = "SELECT COUNT(*) AS total 
          FROM transactions 
          WHERE branch_id = $branch_id 
            AND YEAR(transaction_date) = $year 
            AND MONTH(transaction_date) = $month";
$result = mysqli_query($conn, $query);
$totalPemasukan = ($result && $row = mysqli_fetch_assoc($result)) ? $row['total'] : 0;

// Kembalikan data dalam format JSON
header('Content-Type: application/json');
echo json_encode([
    'totalUser'          => $totalUser,
    'totalKategoriBiaya' => $totalKategoriBiaya,
    'totalTransaksi'     => $totalTransaksi,
    'totalPengeluaran'   => $totalPengeluaran,
    'totalPemasukan'     => $totalPemasukan
]);
?>
