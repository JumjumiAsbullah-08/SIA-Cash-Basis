<?php
// pages/owner/neraca_saldo.php
include_once __DIR__ . '/../../config/database.php';

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}
?>
<div class="container mt-4">
  <h2>Neraca Saldo</h2>
  
  <!-- Form Pencarian Periode -->
  <div class="row mb-3">
    <div class="col-md-12 text-end">
      <div class="input-group" style="width:300px; margin-left:auto;">
        <input type="month" id="searchMonth" class="form-control" placeholder="Bulan & Tahun">
        <button id="btnSearch" class="btn btn-secondary" type="button">Cari</button>
      </div>
    </div>
  </div>
  
  <!-- Tabel Summary Neraca Saldo -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 fw-bold text-primary">Summary Neraca Saldo</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
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
              // Query untuk summary data (dari journal_entries)
              $query = "SELECT je.branch_id, b.branch_name,
                               DATE_FORMAT(je.entry_date, '%Y-%m') AS period,
                               DATE_FORMAT(je.entry_date, '%M %Y') AS month_year,
                               SUM(je.debit) AS total_debit,
                               SUM(je.credit) AS total_credit
                        FROM journal_entries je
                        LEFT JOIN branches b ON je.branch_id = b.id
                        GROUP BY je.branch_id, DATE_FORMAT(je.entry_date, '%Y-%m')
                        ORDER BY je.entry_date DESC";
              $result = $conn->query($query);
              $i = 1;
              if($result && $result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                  $net = $row['total_debit'] - $row['total_credit'];
                  $color = ($net > 0) ? 'green' : (($net < 0) ? 'red' : 'black');
                  echo "<tr>";
                  echo "<td>{$i}</td>";
                  echo "<td>" . htmlspecialchars($row['branch_name']) . "</td>";
                  echo "<td>" . htmlspecialchars($row['month_year']) . "<br><small style='color:{$color};'>Net: " . formatRupiah($net) . "</small></td>";
                  echo "<td><button class='btn btn-info btn-sm btn-view-trial' 
                                data-branch_id='{$row['branch_id']}'
                                data-period='{$row['period']}'
                                data-branch_name='" . htmlspecialchars($row['branch_name']) . "'
                                data-month_year='" . htmlspecialchars($row['month_year']) . "'
                                title='Lihat Neraca Saldo'>
                                <i class='fas fa-eye'></i> Lihat Neraca Saldo</button></td>";
                  echo "</tr>";
                  $i++;
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

<!-- Modal Detail Neraca Saldo -->
<div class="modal fade" id="trialModal" tabindex="-1" aria-labelledby="trialModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
         <h5 class="modal-title" id="trialModalLabel">Detail Neraca Saldo - <span id="modalBranch"></span> (<span id="modalMonth"></span>)</h5>
         <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
         <div id="trialDetailsContainer">
           <!-- Tabel detail akan dimuat via AJAX -->
         </div>
      </div>
      <div class="modal-footer">
         <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- CDN: jQuery, Bootstrap Bundle, SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
  // Pencarian summary neraca saldo
  $("#btnSearch").on("click", function(){
    var period = $("#searchMonth").val(); // Format: YYYY-MM
    $.ajax({
      url: 'process/search_trial_balance.php',
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
  
  // Ketika tombol "Lihat Neraca Saldo" diklik
  $(document).on("click", ".btn-view-trial", function(){
    var branchId = $(this).data("branch_id");
    var period = $(this).data("period");
    var branchName = $(this).data("branch_name");
    var monthYear = $(this).data("month_year");
    
    $("#modalBranch").text(branchName);
    $("#modalMonth").text(monthYear);
    
    $.ajax({
      url: 'process/get_trial_balance_details.php',
      method: 'POST',
      data: { branch_id: branchId, period: period },
      dataType: 'json',
      success: function(response){
        if(response.status === "success"){
          $("#trialDetailsContainer").html(response.data);
          $("#trialModal").modal("show");
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
          text: 'Terjadi kesalahan saat mengambil detail neraca saldo.'
        });
      }
    });
  });
});
</script>