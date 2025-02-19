<?php
session_start();
include_once __DIR__ . '/../config/database.php';

if (!isset($_GET['transaction_id'])) {
    echo "<div class='alert alert-danger'>Transaction ID tidak diberikan.</div>";
    exit;
}

$transaction_id = (int) $_GET['transaction_id'];

// Ambil detail transaksi beserta nama cabang
$sql = "SELECT t.*, b.branch_name 
        FROM transactions t
        LEFT JOIN branches b ON t.branch_id = b.id
        WHERE t.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $transaction_id);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Transaksi tidak ditemukan.</div>";
    exit;
}
$transaction = $result->fetch_assoc();

// Ambil item transaksi
$sqlItems = "SELECT ti.*, i.item_name 
             FROM transaction_items ti 
             LEFT JOIN items i ON ti.item_id = i.id 
             WHERE ti.transaction_id = ?";
$stmtItems = $conn->prepare($sqlItems);
$stmtItems->bind_param("i", $transaction_id);
$stmtItems->execute();
$resultItems = $stmtItems->get_result();

?>

<!-- Detail Transaksi -->
<h5>Invoice: <strong><?= htmlspecialchars($transaction['invoice_number']) ?></strong></h5>
<p><strong>Cabang:</strong> <?= htmlspecialchars($transaction['branch_name'] ?? 'Tidak Diketahui') ?></p>
<p><strong>Nama Pembeli:</strong> <?= htmlspecialchars($transaction['buyer_name']) ?></p>
<p><strong>Tanggal Transaksi:</strong> <?= date('d/m/Y H:i', strtotime($transaction['transaction_date'])) ?></p>

<table class="table table-bordered">
  <thead>
    <tr>
      <th>No</th>
      <th>Barang</th>
      <th>Harga</th>
      <th>Quantity</th>
      <th>Total</th>
    </tr>
  </thead>
  <tbody>
    <?php 
    $i = 1;
    while ($item = $resultItems->fetch_assoc()) {
        echo '<tr>';
        echo '<td>' . $i++ . '</td>';
        echo '<td>' . htmlspecialchars($item['item_name']) . '</td>';
        echo '<td>' . number_format($item['price'], 2) . '</td>';
        echo '<td>' . (int)$item['quantity'] . '</td>';
        echo '<td>' . number_format($item['total'], 2) . '</td>';
        echo '</tr>';
    }
    ?>
  </tbody>
</table>
<p><strong>Total Amount:</strong> <?= number_format($transaction['total_amount'], 2) ?></p>

<?php
// Cek apakah ada data surat jalan untuk transaksi ini
$sqlSJ = "SELECT * FROM surat_jalan WHERE transaction_id = ?";
$stmtSJ = $conn->prepare($sqlSJ);
$stmtSJ->bind_param("i", $transaction_id);
$stmtSJ->execute();
$resultSJ = $stmtSJ->get_result();

if ($resultSJ && $resultSJ->num_rows > 0) {
    $sj = $resultSJ->fetch_assoc();
    // Tampilan detail surat jalan
    echo '<hr>';
    echo '<h5>Detail Surat Jalan</h5>';
    echo '<table class="table table-sm">';
    echo '<tr>
            <th>Nomor Surat Jalan</th>
            <td>' . htmlspecialchars($sj['surat_jalan_number']) . '</td>
          </tr>';
    echo '<tr>
            <th>Nama Pengirim</th>
            <td>' . htmlspecialchars($sj['sender_name']) . '</td>
          </tr>';
    echo '<tr>
            <th>Status</th>
            <td>' . htmlspecialchars($sj['status']) . '</td>
          </tr>';
    echo '<tr>
            <th>Tanggal Dibuat</th>
            <td>' . date('d/m/Y H:i', strtotime($sj['date_created'])) . '</td>
          </tr>';
    echo '</table>';
    // Sediakan elemen tersembunyi yang menyimpan URL download surat jalan, untuk diambil oleh JS
    echo '<div id="sjDetail" data-download-url="process/download_surat_jalan.php?sj_id=' . $sj['id'] . '" style="display:none;"></div>';
}
?>
