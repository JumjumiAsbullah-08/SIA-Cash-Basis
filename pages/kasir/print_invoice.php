<?php
// pages/kasir/print_invoice.php
session_start();
include_once __DIR__ . '/../../config/database.php';

$transaction_id = $_GET['transaction_id'] ?? '';

if(!$transaction_id){
   echo "Transaction ID tidak tersedia.";
   exit;
}

// Ambil data transaksi header
$stmt = $conn->prepare("SELECT t.*, b.branch_name FROM transactions t LEFT JOIN branches b ON t.branch_id = b.id WHERE t.id = ?");
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$transactionResult = $stmt->get_result();
$transaction = $transactionResult->fetch_assoc();

if(!$transaction){
   echo "Transaksi tidak ditemukan.";
   exit;
}

// Ambil data detail transaksi
$stmt2 = $conn->prepare("SELECT ti.*, i.item_name, i.item_code FROM transaction_items ti LEFT JOIN items i ON ti.item_id = i.id WHERE ti.transaction_id = ?");
$stmt2->bind_param("i", $transaction_id);
$stmt2->execute();
$detailResult = $stmt2->get_result();
?>
<html>
<head>
  <title>Invoice - <?php echo $transaction['invoice_number']; ?></title>
  <style>
    body { font-family: Arial, sans-serif; }
    .invoice-box { max-width: 800px; margin: auto; padding: 30px; border: 1px solid #eee; }
    table { width: 100%; border-collapse: collapse; }
    table, th, td { border: 1px solid #ddd; }
    th, td { padding: 8px; text-align: left; }
  </style>
</head>
<body onload="window.print();">
  <div class="invoice-box">
    <h2>Invoice</h2>
    <p><strong>Invoice Number:</strong> <?php echo $transaction['invoice_number']; ?></p>
    <p><strong>Nama Pembeli:</strong> <?php echo $transaction['buyer_name']; ?></p>
    <p><strong>Cabang:</strong> <?php echo $transaction['branch_name']; ?></p>
    <p><strong>Tanggal Transaksi:</strong> <?php echo $transaction['transaction_date']; ?></p>
    <table>
      <thead>
        <tr>
          <th>No</th>
          <th>Barang</th>
          <th>Kode</th>
          <th>Quantity</th>
          <th>Harga</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php $counter = 1; while($row = $detailResult->fetch_assoc()){ ?>
        <tr>
          <td><?php echo $counter++; ?></td>
          <td><?php echo $row['item_name']; ?></td>
          <td><?php echo $row['item_code']; ?></td>
          <td><?php echo $row['quantity']; ?></td>
          <td><?php echo number_format($row['price'],2); ?></td>
          <td><?php echo number_format($row['total'],2); ?></td>
        </tr>
        <?php } ?>
      </tbody>
    </table>
    <h3>Total: <?php echo number_format($transaction['total_amount'],2); ?></h3>
  </div>
</body>
</html>
