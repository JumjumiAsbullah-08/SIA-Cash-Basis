<?php 
// session_start();
include_once __DIR__ . '/../../config/database.php';

// Ambil informasi user dari session
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;

// Buat filter berdasarkan cabang user (jika bukan Admin, tampilkan transaksi hanya dari cabang user)
if ($role !== 'Admin') {
    $queryFilter = "WHERE branch_id = '$user_branch_id'";
} else {
    $queryFilter = "WHERE 1";
}

$sql = "SELECT * FROM transactions $queryFilter ORDER BY transaction_date DESC";
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
        <th>Invoice Number</th>
        <th>Nama Pembeli</th>
        <th>Total Amount</th>
        <th>Tanggal Transaksi</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody id="transactionsTable">
      <?php
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
    </tbody>
  </table>
</div>

<!-- Modal untuk detail transaksi -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detail Transaksi</h5>
        <!-- Tombol close bawaan Bootstrap -->
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <!-- Konten detail akan dimuat melalui AJAX -->
        <div id="detailContent"></div>
      </div>
      <div class="modal-footer">
        <a href="#" id="downloadInvoiceBtn" class="btn btn-primary" target="_blank">
            <i class="fas fa-download"></i> Download Faktur
        </a>
        <!-- Tombol Batal dengan data-bs-dismiss -->
        <button type="button" class="btn btn-secondary" id="modalCloseBtn" data-bs-dismiss="modal">
            <i class="fas fa-times"></i> Batal
        </button>
      </div>
    </div>
  </div>
</div>

<!-- CDN: jQuery, Bootstrap Bundle, SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  // Variabel global untuk menyimpan instance modal
  var currentModal = null;
  
  $(document).ready(function(){
    // Gunakan event delegation untuk tombol detail
    $(document).on('click', '.btn-detail', function(){
      var transactionId = $(this).data('id');
      // Set href untuk tombol download faktur
      $('#downloadInvoiceBtn').attr('href', 'process_kasir/download_invoice.php?transaction_id=' + transactionId);
      
      // Buat atau peroleh instance modal dan simpan di variabel global
      currentModal = new bootstrap.Modal(document.getElementById('detailModal'));
      $('#detailContent').html('Loading...');
      currentModal.show();
      
      // Panggil AJAX untuk memuat detail transaksi
      $.ajax({
        url: 'process_kasir/get_transaction_detail.php',
        type: 'GET',
        data: { transaction_id: transactionId },
        success: function(response){
          $('#detailContent').html(response);
        },
        error: function(){
          $('#detailContent').html('<div class="alert alert-danger">Gagal memuat detail transaksi.</div>');
        }
      });
    });
    
    // Tombol refresh halaman
    $('#refreshBtn').on('click', function(){
      location.reload();
    });
    
    // Live search: setiap ketik langsung refresh tabel transaksi
    $('#searchInput').on('keyup', function(){
      var search = $(this).val();
      
      $.ajax({
        url: 'process_kasir/search_transaction.php',
        method: 'GET',
        data: { search: search },
        success: function(response) {
          $('#transactionsTable').html(response);
        },
        error: function() {
          $('#transactionsTable').html('<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>');
        }
      });
    });
    
    // Tombol modal close (jika diperlukan)
    $(document).on('click', '#modalCloseBtn', function(){
      if (currentModal) {
        currentModal.hide();
      }
    });
  });
    
//     // Live search: setiap ketik langsung refresh tabel transaksi
//     $('#searchInput').on('keyup', function(){
//       var search = $(this).val();
      
//       $.ajax({
//         url: 'process_kasir/search_transaction.php',
//         method: 'GET',
//         data: { search: search },
//         success: function(response) {
//           $('#transactionsTable').html(response);
//         },
//         error: function() {
//           $('#transactionsTable').html('<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>');
//         }
//       });
//     });

//     // Tombol refresh halaman
//     $('#refreshBtn').on('click', function(){
//       location.reload();
//     });
//   });
</script>
