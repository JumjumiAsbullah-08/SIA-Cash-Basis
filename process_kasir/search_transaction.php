<?php
// search_transactions.php

session_start();
include_once __DIR__ . './../config/database.php';

// Dapatkan informasi user dari session
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;

// Ambil input pencarian
$search = $_GET['search'] ?? '';

// Buat filter berdasarkan cabang user (jika bukan Admin, tampilkan transaksi hanya dari cabang user)
if ($role !== 'Admin') {
    $queryFilter = "WHERE branch_id = '$user_branch_id'";
} else {
    $queryFilter = "WHERE 1";
}

// Jika ada input pencarian, tambahkan kondisi filter untuk nomor invoice atau nama pembeli
if (!empty($search)) {
    // Menggunakan real_escape_string untuk mencegah SQL Injection
    $search = $conn->real_escape_string($search);
    $queryFilter .= " AND (invoice_number LIKE '%$search%' OR buyer_name LIKE '%$search%')";
}

$sql = "SELECT * FROM transactions $queryFilter ORDER BY transaction_date DESC";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $i = 1;
    while ($transaction = $result->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $i++ . '</td>';
        echo '<td>' . $transaction['invoice_number'] . '</td>';
        echo '<td>' . $transaction['buyer_name'] . '</td>';
        echo '<td>' . number_format($transaction['total_amount'], 2) . '</td>';
        echo '<td>' . $transaction['transaction_date'] . '</td>';
        echo '<td>
                <button class="btn btn-info btn-sm btn-detail" data-id="' . $transaction['id'] . '">
                  <i class="fas fa-eye"></i> Detail
                </button>
              </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="6" class="text-center">Tidak ada data transaksi.</td></tr>';
}
?>
