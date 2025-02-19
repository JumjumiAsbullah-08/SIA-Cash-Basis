<?php
session_start();
include_once __DIR__ . './../config/database.php';

if (!isset($_GET['transaction_id'])) {
    echo "Transaction ID tidak diberikan.";
    exit;
}

$transaction_id = $_GET['transaction_id'];

// Ambil data transaksi
$sql = "SELECT * FROM transactions WHERE id = '$transaction_id'";
$result = $conn->query($sql);
if (!$result || $result->num_rows <= 0) {
    echo "Transaksi tidak ditemukan.";
    exit;
}
$transaction = $result->fetch_assoc();

// Ambil item transaksi
$sqlItems = "SELECT ti.*, i.item_name FROM transaction_items ti 
             LEFT JOIN items i ON ti.item_id = i.id 
             WHERE ti.transaction_id = '$transaction_id'";
$resultItems = $conn->query($sqlItems);

// Tampilkan detail transaksi
echo '<h5>Invoice: ' . $transaction['invoice_number'] . '</h5>';
echo '<p><strong>Nama Pembeli:</strong> ' . $transaction['buyer_name'] . '</p>';
echo '<p><strong>Tanggal Transaksi:</strong> ' . $transaction['transaction_date'] . '</p>';

echo '<table class="table table-bordered">';
echo '<thead>';
echo '<tr>
        <th>No</th>
        <th>Barang</th>
        <th>Harga</th>
        <th>Quantity</th>
        <th>Total</th>
      </tr>';
echo '</thead>';
echo '<tbody>';
$i = 1;
while ($item = $resultItems->fetch_assoc()) {
    echo '<tr>';
    echo '<td>' . $i++ . '</td>';
    echo '<td>' . $item['item_name'] . '</td>';
    echo '<td>' . number_format($item['price'], 2) . '</td>';
    echo '<td>' . $item['quantity'] . '</td>';
    echo '<td>' . number_format($item['total'], 2) . '</td>';
    echo '</tr>';
}
echo '</tbody>';
echo '</table>';
echo '<p><strong>Total Amount:</strong> ' . number_format($transaction['total_amount'], 2) . '</p>';
?>
