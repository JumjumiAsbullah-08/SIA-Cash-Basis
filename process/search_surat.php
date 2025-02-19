<?php
session_start();
include_once __DIR__ . './../config/database.php';

// Hanya untuk role Pegawai
if ($_SESSION['role'] !== 'Pegawai') {
    header('Location: no_access.php');
    exit;
}

$user_branch_id = $_SESSION['branch_id'] ?? 0;

// Dapatkan parameter pencarian
$query = isset($_GET['q']) ? $conn->real_escape_string($_GET['q']) : '';

// Query transaksi
$sql = "SELECT * FROM transactions WHERE branch_id = '$user_branch_id'";
if ($query !== '') {
    $sql .= " AND (invoice_number LIKE '%$query%' OR buyer_name LIKE '%$query%')";
}
$sql .= " ORDER BY transaction_date DESC";
$resultTrans = $conn->query($sql);

// Query data surat jalan
$sqlSJ = "SELECT * FROM surat_jalan WHERE branch_id = '$user_branch_id'";
$resultSJ = $conn->query($sqlSJ);
$suratJalanMap = [];
if ($resultSJ) {
    while ($row = $resultSJ->fetch_assoc()) {
        $suratJalanMap[$row['transaction_id']] = $row;
    }
}
?>
<table class="table table-bordered table-hover">
  <thead class="table-dark">
    <tr>
      <th>No</th>
      <th>Invoice</th>
      <th>Nama Pembeli</th>
      <th>Total Amount</th>
      <th>Tanggal Transaksi</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php if ($resultTrans && $resultTrans->num_rows > 0): ?>
      <?php $i = 1; ?>
      <?php while ($trans = $resultTrans->fetch_assoc()): ?>
        <tr>
          <td><?= $i++ ?></td>
          <td><?= htmlspecialchars($trans['invoice_number']) ?></td>
          <td><?= htmlspecialchars($trans['buyer_name']) ?></td>
          <td><?= number_format($trans['total_amount'], 2) ?></td>
          <td><?= htmlspecialchars($trans['transaction_date']) ?></td>
          <td>
            <?php if (isset($suratJalanMap[$trans['id']])): 
                    $sj = $suratJalanMap[$trans['id']];
                  ?>
              <button class="btn btn-info btn-sm btn-detail-surat" 
                      data-surat-number="<?= htmlspecialchars($sj['surat_jalan_number']) ?>"
                      data-sender="<?= htmlspecialchars($sj['sender_name']) ?>"
                      data-date="<?= $sj['date_created'] ?>">
                <i class="fas fa-info-circle"></i> Detail
              </button>
              <a href="process/download_surat_jalan.php?sj_id=<?= $sj['id'] ?>" class="btn btn-success btn-sm" target="_blank">
                <i class="fas fa-download"></i> Download
              </a>
            <?php else: ?>
              <button class="btn btn-primary btn-sm btn-create-surat" 
                      data-transaction-id="<?= $trans['id'] ?>"
                      data-invoice="<?= htmlspecialchars($trans['invoice_number']) ?>">
                <i class="fas fa-file-alt"></i> Buat Surat Jalan
              </button>
            <?php endif; ?>
          </td>
        </tr>
      <?php endwhile; ?>
    <?php else: ?>
      <tr>
        <td colspan="6" class="text-center">Tidak ada transaksi.</td>
      </tr>
    <?php endif; ?>
  </tbody>
</table>
