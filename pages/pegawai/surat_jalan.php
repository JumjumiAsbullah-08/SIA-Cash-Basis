<?php
// session_start();
include_once __DIR__ . '/../../config/database.php';

// Hanya untuk role Pegawai
if ($_SESSION['role'] !== 'Pegawai') {
    header('Location: no_access.php');
    exit;
}

$user_branch_id = $_SESSION['branch_id'] ?? 0;
?>
<!-- Pastikan CSS SB Admin 2 termuat -->
<link href="/../../themes/css/sb-admin-2.min.css" rel="stylesheet">

<div class="container mt-4">
  <h2><i class="fas fa-truck"></i> Surat Jalan</h2>
  <p>Pilih transaksi untuk membuat atau melihat surat jalan.</p>
  <!-- Search Input -->
  <div class="mb-3">
    <input type="text" class="form-control" id="searchInput" placeholder="Cari berdasarkan Invoice atau Nama Pembeli...">
  </div>
  <!-- Tempat untuk menampilkan tabel hasil pencarian -->
  <div id="tableContainer"></div>
</div>

<!-- Modal Buat Surat Jalan (Bootstrap 4 version) -->
<div class="modal fade" id="createSuratModal" tabindex="-1" role="dialog" aria-labelledby="createSuratModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form id="createSuratForm">
        <div class="modal-header">
          <h5 class="modal-title" id="createSuratModalLabel"><i class="fas fa-file-alt"></i> Buat Surat Jalan</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <!-- Hidden field untuk menyimpan transaction_id -->
          <input type="hidden" name="transaction_id" id="transaction_id">
          <div class="form-group">
            <label for="suratJalanNumber">Nomor Surat Jalan</label>
            <input type="text" class="form-control" id="suratJalanNumber" name="surat_jalan_number" required>
          </div>
          <div class="form-group">
            <label for="senderName">Nama Pengirim</label>
            <input type="text" class="form-control" id="senderName" name="sender_name" required>
          </div>
          <!-- Status default -->
          <input type="hidden" name="status" value="Berhasil buat surat jalan">
        </div>
        <div class="modal-footer">
          <!-- Tombol "Batal" -->
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan Surat Jalan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Modal Detail Surat Jalan (Bootstrap 4 version) -->
<div class="modal fade" id="detailSuratModal" tabindex="-1" role="dialog" aria-labelledby="detailSuratModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header bg-info text-white">
        <h5 class="modal-title" id="detailSuratModalLabel"><i class="fas fa-info-circle"></i> Detail Surat Jalan</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table table-sm">
          <tr>
            <th>Nomor Surat Jalan</th>
            <td id="detailSuratNumber"></td>
          </tr>
          <tr>
            <th>Nama Pengirim</th>
            <td id="detailSenderName"></td>
          </tr>
          <tr>
            <th>Status</th>
            <td id="detailStatus">Berhasil buat surat jalan</td>
          </tr>
          <tr>
            <th>Tanggal Dibuat</th>
            <td id="detailDateCreated"></td>
          </tr>
        </table>
      </div>
      <div class="modal-footer">
        <!-- Tombol "Batal" pada modal detail -->
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Batal
        </button>
      </div>
    </div>
  </div>
</div>

<!-- jQuery, Bootstrap 4 JS, dan SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Gunakan Bootstrap 4 (misal versi 4.6) -->
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.6.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
  // Fungsi untuk memuat data tabel via AJAX
  function loadTable(query = '') {
    $.ajax({
      url: 'process/search_surat.php',
      type: 'GET',
      data: { q: query },
      success: function(data) {
        $('#tableContainer').html(data);
      },
      error: function() {
        console.error('Error loading data');
      }
    });
  }

  $(document).ready(function(){
    // Muat data tabel awal
    loadTable();

    // Pencarian dinamis
    $('#searchInput').on('keyup', function(){
      var query = $(this).val();
      loadTable(query);
    });

    // Buka modal "Buat Surat" ketika tombol diklik
    $(document).on('click', '.btn-create-surat', function(){
      var transactionId = $(this).attr('data-transaction-id');
      var invoice = $(this).attr('data-invoice');
      // Auto-generate nomor surat
      $('#suratJalanNumber').val("SJ-" + invoice);
      $('#transaction_id').val(transactionId);
      $('#createSuratModal').modal('show');
    });

    // Buka modal "Detail" ketika tombol diklik
    $(document).on('click', '.btn-detail-surat', function(){
      var suratNumber = $(this).attr('data-surat-number');
      var senderName  = $(this).attr('data-sender');
      var dateCreated = $(this).attr('data-date');
      $('#detailSuratNumber').text(suratNumber);
      $('#detailSenderName').text(senderName);
      $('#detailStatus').text("Berhasil");
      $('#detailDateCreated').text(dateCreated);
      $('#detailSuratModal').modal('show');
    });

    // Proses submit form "Buat Surat Jalan"
    $(document).on('submit', '#createSuratForm', function(e){
      e.preventDefault();
      $.ajax({
        url: 'process/process_add_surat_jalan.php',
        type: 'POST',
        data: $(this).serialize(),
        dataType: 'json',
        success: function(response){
          if(response.status === 'success'){
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: response.message,
              timer: 2000,
              showConfirmButton: false
            }).then(function(){
              // Reload data tabel sesuai query saat ini
              loadTable($('#searchInput').val());
              // Tutup modal "Buat Surat"
              $('#createSuratModal').modal('hide');
            });
          } else {
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: response.message
            });
          }
        },
        error: function(){
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: 'Terjadi kesalahan saat membuat surat jalan.'
          });
        }
      });
    });
  });
</script>
