<?php
session_start();
include_once __DIR__ . './../config/database.php';

$user_branch_id = $_SESSION['branch_id'] ?? 0;
$description   = $_POST['description'] ?? '';
$amount        = $_POST['amount'] ?? '';
$date          = $_POST['date'] ?? '';

// Validasi data
if (empty($description) || empty($amount) || empty($date)) {
    http_response_code(400);
    echo 'Data tidak lengkap.';
    exit;
}

// Hitung total pemasukan
$totalIncomeQuery = "SELECT SUM(total_amount) AS total_income FROM transactions WHERE branch_id = '$user_branch_id'";
$totalIncomeResult = $conn->query($totalIncomeQuery);
$totalIncome = 0;
if ($totalIncomeResult && $row = $totalIncomeResult->fetch_assoc()) {
    $totalIncome = $row['total_income'];
}

// Hitung total pengeluaran saat ini
$totalExpenseQuery = "SELECT SUM(amount) AS total_expense FROM finance WHERE branch_id = '$user_branch_id' AND type='expense'";
$totalExpenseResult = $conn->query($totalExpenseQuery);
$totalExpense = 0;
if ($totalExpenseResult && $row = $totalExpenseResult->fetch_assoc()) {
    $totalExpense = $row['total_expense'];
}

$saldo = $totalIncome - $totalExpense;
if($amount > $saldo){
    http_response_code(400);
    echo "Saldo tidak cukup. Saldo saat ini: Rp " . number_format($saldo,2);
    exit;
}

// Simpan data expense menggunakan prepared statement
$stmt = $conn->prepare("INSERT INTO finance (branch_id, type, amount, description, date) VALUES (?, 'expense', ?, ?, ?)");
$stmt->bind_param("idss", $user_branch_id, $amount, $description, $date);

if ($stmt->execute()) {
    echo "Pengeluaran berhasil disimpan.";
} else {
    http_response_code(500);
    echo "Gagal menyimpan data.";
}
$stmt->close();
$conn->close();
?>
