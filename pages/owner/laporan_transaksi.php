<?php 
// session_start();
include_once __DIR__ . '/../../config/database.php';

// Ambil informasi user dari session
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;

// Jika user bukan Owner, filter berdasarkan cabang user, jika Owner tampilkan semua
if ($role !== 'Owner') {
    $queryFilter = "WHERE t.branch_id = '$user_branch_id'";
} else {
    $queryFilter = ""; // Owner melihat semua data
}

// Query menampilkan transaksi dengan informasi cabang dan status surat jalan (jika ada)
// Asumsi: tiap transaksi hanya memiliki 1 data surat_jalan (atau NULL jika belum dibuat)
$sql = "SELECT t.*, b.branch_name, sj.status AS surat_status 
        FROM transactions t
        LEFT JOIN branches b ON t.branch_id = b.id
        LEFT JOIN surat_jalan sj ON t.id = sj.transaction_id
        $queryFilter
        ORDER BY t.transaction_date DESC";

$result = $conn->query($sql);
?>
<div class="container mt-4">
  <h2>Laporan Transaksi</h2>
  
  <!-- Search textbox dan tombol refresh -->
  <div class="row mb-3">
    <div class="col-md-4">
      <input type="text" class="form-control" id="searchInput" placeholder="Cari nomor invoice atau nama pembeli...">
    </div>
    <div class="col-md-2">
      <button id="refreshBtn" class="btn btn-secondary">
        <i class="fas fa-sync-alt"></i> Refresh
      </button>
    </div>
  </div>
  
  <!-- Tabel daftar transaksi -->
  <table class="table table-bordered">
    <thead>
      <tr>
        <th>No</th>
        <th>Cabang</th>
        <th>Invoice Number</th>
        <th>Nama Pembeli</th>
        <th>Total Amount</th>
        <th>Tanggal Transaksi</th>
        <th>Status Surat Jalan</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody id="transactionsTable">
      <?php
      if ($result && $result->num_rows > 0) {
        $i = 1;
        while ($transaction = $result->fetch_assoc()) {
          // Tentukan badge status surat jalan
          if (empty($transaction['surat_status'])) {
              $statusBadge = '<span class="badge bg-secondary" style="color:#fff !important;">Belum Dibuat</span>';
          } else {
              switch($transaction['surat_status']){
                  case 'pending':
                      $statusBadge = '<span class="badge bg-warning">Pending</span>';
                      break;
                  case 'sent':
                      $statusBadge = '<span class="badge bg-success" style="color:#fff !important;">Berhasil</span>';
                      break;
                  case 'delivered':
                      $statusBadge = '<span class="badge bg-info">Delivered</span>';
                      break;
                  case 'canceled':
                      $statusBadge = '<span class="badge bg-danger">Dibatalkan</span>';
                      break;
                  default:
                      $statusBadge = '<span class="badge bg-secondary">'.htmlspecialchars($transaction['surat_status']).'</span>';
                      break;
              }
          }

          echo '<tr>';
          echo '<td>' . $i++ . '</td>';
          echo '<td>' . ($transaction['branch_name'] ?? '-') . '</td>';
          echo '<td>' . $transaction['invoice_number'] . '</td>';
          echo '<td>' . $transaction['buyer_name'] . '</td>';
          echo '<td>' . number_format($transaction['total_amount'], 2) . '</td>';
          echo '<td>' . date('d/m/Y', strtotime($transaction['transaction_date'])) . '</td>';
          echo '<td>' . $statusBadge . '</td>';
          echo '<td>
                  <button class="btn btn-info btn-sm btn-detail" data-id="' . $transaction['id'] . '">
                    <i class="fas fa-eye"></i> Detail
                  </button>
                </td>';
          echo '</tr>';
        }
      } else {
        echo '<tr><td colspan="8" class="text-center">Tidak ada data transaksi.</td></tr>';
      }
      ?>
    </tbody>
  </table>
</div>

<!-- Modal untuk detail transaksi & surat jalan -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
          <i class="fas fa-info-circle"></i> Detail Transaksi
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <div id="detailContent">Loading...</div>
      </div>
      <div class="modal-footer">
        <!-- Tombol download faktur -->
        <a href="#" id="downloadInvoiceBtn" class="btn btn-primary" target="_blank">
          <i class="fas fa-download"></i> Download Faktur
        </a>
        <!-- Tombol download surat jalan; akan diset secara dinamis jika ada surat jalan -->
        <a href="#" id="downloadSJBtn" class="btn btn-success" target="_blank" style="display:none;">
          <i class="fas fa-download"></i> Download Surat Jalan
        </a>
        <button type="button" class="btn btn-secondary" id="modalCloseBtn" data-bs-dismiss="modal">
          <i class="fas fa-times"></i> Batal
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Library JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  var currentModal = null;
  
  $(document).ready(function(){
    $(document).on('click', '.btn-detail', function(){
      var transactionId = $(this).data('id');
      // Set link download faktur
      $('#downloadInvoiceBtn').attr('href', 'process/download_invoice.php?transaction_id=' + transactionId);
      // Reset tombol download surat jalan (disembunyikan jika tidak ada)
      $('#downloadSJBtn').hide().attr('href', '#');
      
      currentModal = new bootstrap.Modal(document.getElementById('detailModal'));
      $('#detailContent').html('Loading...');
      currentModal.show();
      
      $.ajax({
        url: 'process/get_transaction_detail.php',
        type: 'GET',
        data: { transaction_id: transactionId },
        dataType: 'html',
        success: function(response){
          $('#detailContent').html(response);
          // Jika response menyertakan data surat jalan (misal ada elemen dengan id "sjDetail"), tampilkan tombol download surat jalan
          if ($('#sjDetail').length) {
            var downloadSJUrl = $('#sjDetail').data('download-url');
            $('#downloadSJBtn').show().attr('href', downloadSJUrl);
          }
        },
        error: function(){
          $('#detailContent').html('<div class="alert alert-danger">Gagal memuat detail transaksi.</div>');
        }
      });
    });
    
    $('#refreshBtn').on('click', function(){
      location.reload();
    });

    $('#searchInput').on('keyup', function(){
      var search = $(this).val();
      
      $.ajax({
        url: 'process/search_transaction.php',
        method: 'GET',
        data: { search: search },
        success: function(response) {
          $('#transactionsTable').html(response);
        },
        error: function() {
          $('#transactionsTable').html('<tr><td colspan="8" class="text-center text-danger">Error loading data</td></tr>');
        }
      });
    });

    $(document).on('click', '#modalCloseBtn', function(){
      if (currentModal) {
        currentModal.hide();
      }
    });
  });
</script>
