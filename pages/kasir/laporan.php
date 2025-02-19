<?php
// pages/owner/neraca_saldo.php
// session_start();
include_once __DIR__ . '/../../config/database.php';

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}

// Ambil data user dari session
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;
?>
<div class="container mt-4">
  <h2>Laporan</h2>
  
  <!-- Form Pencarian Periode -->
  <div class="row mb-3">
    <div class="col-md-12 text-end">
      <div class="input-group" style="width:300px; margin-left:auto;">
        <input type="month" id="searchMonth" class="form-control" placeholder="Bulan & Tahun">
        <button id="btnSearch" class="btn btn-secondary" type="button">Cari</button>
      </div>
    </div>
  </div>
  
  <!-- Tabel Summary Laporan -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 fw-bold text-primary">Laporan</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <?php
          // Query untuk summary data neraca saldo
          $query = "SELECT je.branch_id, b.branch_name,
                           DATE_FORMAT(je.entry_date, '%Y-%m') AS period,
                           DATE_FORMAT(je.entry_date, '%M %Y') AS month_year,
                           SUM(je.debit) AS total_debit,
                           SUM(je.credit) AS total_credit
                    FROM journal_entries je
                    LEFT JOIN branches b ON je.branch_id = b.id ";
          // Jika user adalah Kasir, tampilkan hanya data sesuai cabangnya
          if($role === 'Kasir'){
              $query .= "WHERE je.branch_id = '$user_branch_id' ";
          }
          $query .= "GROUP BY je.branch_id, DATE_FORMAT(je.entry_date, '%Y-%m')
                    ORDER BY je.entry_date DESC";
          $result = $conn->query($query);
          $counter = 1;
        ?>
        <table class="table table-bordered" id="summaryTable">
          <thead>
            <tr>
              <th>No</th>
              <th>Cabang</th>
              <th>Bulan & Tahun</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
              if($result && $result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                  $net = $row['total_debit'] - $row['total_credit'];
                  $color = ($net > 0) ? 'green' : (($net < 0) ? 'red' : 'black');
                  echo "<tr>";
                  echo "<td>{$counter}</td>";
                  echo "<td>" . htmlspecialchars($row['branch_name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['month_year']) . "<br><small style='color:{$color};'>Net: " . formatRupiah($net) . "</small></td>";
                  echo "<td><button class='btn btn-info btn-sm btn-download-report'
                                   data-branch_id='{$row['branch_id']}'
                                   data-period='{$row['period']}'
                                   data-branch_name='" . htmlspecialchars($row['branch_name']) . "'
                                   data-month_year='" . htmlspecialchars($row['month_year']) . "'
                                   title='Download Laporan'>
                                   <i class='fas fa-download'></i> Download Laporan</button></td>";
                  echo "</tr>";
                  $counter++;
                }
              } else {
                echo "<tr><td colspan='4' class='text-center'>Tidak ada data neraca saldo.</td></tr>";
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Download Laporan -->
<div class="modal fade" id="downloadModal" tabindex="-1" aria-labelledby="downloadModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="downloadForm">
      <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="downloadModalLabel">Download Laporan Neraca Saldo</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
            <p>Cabang: <span id="modalCabang"></span></p>
            <p>Periode: <span id="modalPeriode"></span></p>
            <div class="mb-3">
              <label for="reportFormat" class="form-label">Pilih Format Laporan</label>
              <select id="reportFormat" class="form-control" required>
                <option value="">-- Pilih --</option>
                <option value="pdf">PDF</option>
                <option value="excel">Excel</option>
              </select>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Download</button>
         </div>
      </div>
    </form>
  </div>
</div>

<!-- CDN: jQuery, Bootstrap Bundle, SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
  // Pencarian summary neraca saldo berdasarkan periode
  $("#btnSearch").on("click", function(){
    var period = $("#searchMonth").val(); // Format: YYYY-MM
    $.ajax({
      url: 'process_kasir/search_trial_balance_summary.php',
      method: 'POST',
      data: { period: period },
      dataType: 'json',
      success: function(response){
        if(response.status === "success"){
          $("#summaryTable tbody").html(response.data);
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Gagal',
            text: response.debug
          });
        }
      },
      error: function(){
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: 'Terjadi kesalahan saat pencarian.'
        });
      }
    });
  });
  
  // Ketika tombol "Download Laporan" diklik
  $(document).on("click", ".btn-download-report", function(){
    var branchId = $(this).data("branch_id");
    var period = $(this).data("period");
    var branchName = $(this).data("branch_name");
    var monthYear = $(this).data("month_year");
    
    // Set nilai di modal
    $("#modalCabang").text(branchName);
    $("#modalPeriode").text(monthYear);
    
    // Simpan data di form download (sebagai data attributes)
    $("#downloadForm").data("branch_id", branchId);
    $("#downloadForm").data("period", period);
    
    // Tampilkan modal
    $("#downloadModal").modal("show");
  });
  
  // Proses download laporan ketika form modal dikirim
  $("#downloadForm").on("submit", function(e){
    e.preventDefault();
    var branchId = $(this).data("branch_id");
    var period = $(this).data("period");
    var format = $("#reportFormat").val();
    if(!format){
      Swal.fire({
        icon: 'warning',
        title: 'Pilih Format',
        text: 'Silakan pilih format laporan (PDF atau Excel).'
      });
      return;
    }
    // Redirect ke file download_trial_balance.php dengan parameter branch_id, period, dan format
    window.location.href = "process/download_trial_balance.php?branch_id=" + branchId + "&period=" + period + "&format=" + format;
  });
});
</script>
