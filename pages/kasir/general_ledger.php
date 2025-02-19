<?php
// session_start();
include_once __DIR__ . '/../../config/database.php';

// Ambil informasi user dari session
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;

// Fungsi format Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}
?>
<div class="container mt-4">
  <h2>Buku Besar</h2>

  <!-- Input Pencarian Bulan & Tahun -->
  <div class="row mb-3">
    <div class="col-md-12 text-end">
      <div class="input-group" style="width: 300px; margin-left:auto;">
        <input type="month" class="form-control" id="searchMonthYear" placeholder="Bulan & Tahun">
        <button class="btn btn-secondary" type="button" id="btnSearch">Cari</button>
      </div>
    </div>
  </div>

  <!-- Tabel Summary Buku Besar -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 fw-bold text-primary">Daftar Buku Besar (Summary)</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
      <?php
        // Query summary per cabang & periode
        $query = "SELECT je.branch_id, b.branch_name,
                         DATE_FORMAT(je.entry_date, '%Y-%m') AS period,
                         DATE_FORMAT(je.entry_date, '%M %Y') AS month_year,
                         SUM(je.debit) AS total_debit,
                         SUM(je.credit) AS total_credit
                  FROM journal_entries je
                  LEFT JOIN branches b ON je.branch_id = b.id ";
        // Jika user adalah Kasir, tampilkan data hanya untuk cabangnya sendiri
        if ($role === 'Kasir') {
            $query .= "WHERE je.branch_id = '$user_branch_id' ";
        }
        $query .= "GROUP BY je.branch_id, DATE_FORMAT(je.entry_date, '%Y-%m')
                  ORDER BY je.entry_date DESC";
        $result = $conn->query($query);
      ?>
        <table class="table table-bordered" id="dataGeneralLedgerTable" width="100%" cellspacing="0">
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
            $counter = 1;
            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                $net_total = $row['total_debit'] - $row['total_credit'];
                $color = ($net_total > 0) ? 'green' : (($net_total < 0) ? 'red' : 'black');
                ?>
                <tr>
                  <td><?php echo $counter++; ?></td>
                  <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                  <td>
                    <?php echo htmlspecialchars($row['month_year']); ?><br>
                    <small style="color:<?php echo $color; ?>">
                      Net: <?php echo formatRupiah($net_total); ?>
                    </small>
                  </td>
                  <td>
                    <button class="btn btn-info btn-sm btn-view-ledger"
                            data-branch_id="<?php echo $row['branch_id']; ?>"
                            data-period="<?php echo $row['period']; ?>"
                            data-branch_name="<?php echo htmlspecialchars($row['branch_name']); ?>"
                            data-month_year="<?php echo htmlspecialchars($row['month_year']); ?>"
                            title="Lihat Buku Besar">
                      <i class="fas fa-eye"></i> Lihat Buku Besar
                    </button>
                  </td>
                </tr>
                <?php
              }
            } else {
              echo "<tr><td colspan='4' class='text-center'>Belum ada data buku besar.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Pilih Kategori & Tampilkan Detail Buku Besar -->
<div class="modal fade" id="viewLedgerModal" tabindex="-1" aria-labelledby="viewLedgerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="viewLedgerModalLabel">Pilih Kategori - <span id="modalBranchName"></span> (<span id="modalMonthYear"></span>)</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
            <!-- Pilih Kategori -->
            <div class="mb-3">
              <label for="ledgerCategorySelect" class="form-label">Pilih Kategori</label>
              <select class="form-control" id="ledgerCategorySelect">
                <option value="">-- Pilih --</option>
              </select>
            </div>
            <button class="btn btn-primary mb-3" id="btnShowLedgerDetail">Lanjutkan</button>
            
            <!-- Kontainer untuk menampilkan tabel detail buku besar -->
            <div id="ledgerDetailsContainer"></div>
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
let globalBranchId = null;
let globalPeriod = null;

// Event delegation: Ketika tombol Lihat Buku Besar diklik
$(document).on("click", ".btn-view-ledger", function(){
  globalBranchId = $(this).data("branch_id");
  globalPeriod   = $(this).data("period");
  
  $("#modalBranchName").text($(this).data("branch_name"));
  $("#modalMonthYear").text($(this).data("month_year"));
  
  $("#ledgerCategorySelect").empty().append('<option value="">-- Pilih --</option>');
  $("#ledgerDetailsContainer").html("");
  
  // Panggil AJAX untuk mendapatkan daftar kategori berdasarkan branch & periode
  $.ajax({
    url: 'process/get_categories_for_ledger.php',
    method: 'POST',
    data: { branch_id: globalBranchId, period: globalPeriod },
    dataType: 'json',
    success: function(response){
      if(response.status === "success"){
        $.each(response.data, function(i, cat){
          $("#ledgerCategorySelect").append('<option value="'+cat.id+'">'+cat.name+'</option>');
        });
        $("#viewLedgerModal").modal("show");
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: response.debug
        });
      }
    },
    error: function(xhr, status, error){
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: 'Terjadi kesalahan saat memuat daftar kategori.'
      });
    }
  });
});

// Ketika tombol Lanjutkan di modal diklik (setelah memilih kategori)
$("#btnShowLedgerDetail").on("click", function(){
  const categoryId = $("#ledgerCategorySelect").val();
  if(!categoryId){
    Swal.fire({
      icon: 'warning',
      title: 'Pilih Kategori',
      text: 'Silakan pilih kategori terlebih dahulu.'
    });
    return;
  }
  
  // Panggil AJAX untuk mendapatkan detail buku besar berdasarkan kategori yang dipilih
  $.ajax({
    url: 'process/get_general_ledger_details.php',
    method: 'POST',
    data: {
      branch_id: globalBranchId,
      period: globalPeriod,
      category_id: categoryId
    },
    dataType: 'json',
    success: function(response){
      if(response.status === "success"){
        $("#ledgerDetailsContainer").html(response.data);
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: response.debug
        });
      }
    },
    error: function(xhr, status, error){
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: 'Terjadi kesalahan saat memuat detail buku besar.'
      });
    }
  });
});

// Pencarian Buku Besar berdasarkan Bulan & Tahun
$("#btnSearch").on("click", function(){
  const period = $("#searchMonthYear").val(); // Format: YYYY-MM
  $.ajax({
    url: 'process_kasir/search_general_ledger.php',
    method: 'POST',
    data: { period: period },
    dataType: 'json',
    success: function(response){
      if(response.status === "success"){
        $("#dataGeneralLedgerTable tbody").html(response.data);
      } else {
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: response.debug
        });
      }
    },
    error: function(xhr, status, error){
      Swal.fire({
        icon: 'error',
        title: 'Gagal',
        text: 'Terjadi kesalahan saat pencarian.'
      });
    }
  });
});
</script>
