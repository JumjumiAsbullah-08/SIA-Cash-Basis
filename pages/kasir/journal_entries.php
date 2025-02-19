<?php
// pages/owner/journal_entries.php
// session_start();
include_once __DIR__ . '/../../config/database.php';

// Ambil informasi user dari session
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;

// Fungsi format Rupiah (jika diperlukan)
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}
?>
<div class="container mt-4">
  <h2>Journal Umum</h2>
  
  <!-- Row: Tombol Tambah & Pencarian Bulan & Tahun -->
  <div class="row mb-3">
    <div class="col-md-6">
      <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addJournalEntryModal" title="Tambah Jurnal Umum">
        <i class="fas fa-plus"></i> Tambah Jurnal Umum
      </button>
    </div>
    <div class="col-md-6 text-end">
      <div class="input-group" style="width: 300px; margin-left:auto;">
        <input type="month" class="form-control" id="searchMonthYear" placeholder="Bulan & Tahun">
        <button class="btn btn-secondary" type="button" id="btnSearch">Cari</button>
      </div>
    </div>
  </div>
  
  <!-- Summary Table -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 fw-bold text-primary">Summary Journal Umum</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="dataJournalSummary">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama Cabang</th>
              <th>Bulan & Tahun</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
              // Query untuk menampilkan summary jurnal
              $query = "SELECT je.branch_id, DATE_FORMAT(je.entry_date, '%Y-%m') AS period, 
                        DATE_FORMAT(je.entry_date, '%M %Y') AS bulan_tahun, b.branch_name 
                        FROM journal_entries je 
                        LEFT JOIN branches b ON je.branch_id = b.id ";
              // Jika user adalah Kasir, tampilkan data hanya untuk cabangnya sendiri
              if($role === 'Kasir'){
                  $query .= "WHERE je.branch_id = '$user_branch_id' ";
              }
              $query .= "GROUP BY je.branch_id, DATE_FORMAT(je.entry_date, '%Y-%m')
                        ORDER BY je.entry_date DESC";
              $result = $conn->query($query);
              $counter = 1;
              if($result && $result->num_rows > 0){
                while($row = $result->fetch_assoc()){
                  ?>
                  <tr>
                    <td><?php echo $counter++; ?></td>
                    <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['bulan_tahun']); ?></td>
                    <td>
                      <button class="btn btn-info btn-sm btn-view-journal-summary" 
                              data-branch_id="<?php echo $row['branch_id']; ?>"
                              data-period="<?php echo $row['period']; ?>"
                              title="Lihat Journal">
                        <i class="fas fa-eye"></i> Lihat Journal
                      </button>
                    </td>
                  </tr>
                  <?php
                }
              } else {
                echo "<tr><td colspan='4' class='text-center'>Belum ada data jurnal.</td></tr>";
              }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Journal Entry -->
<div class="modal fade" id="addJournalEntryModal" tabindex="-1" aria-labelledby="addJournalEntryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addJournalEntryForm">
      <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="addJournalEntryModalLabel">Tambah Jurnal Umum</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
            <!-- Tanggal Transaksi -->
            <div class="mb-3">
              <label for="entryDate" class="form-label">Tanggal Transaksi</label>
              <input type="date" class="form-control" id="entryDate" name="entry_date" required>
            </div>
            <!-- Cabang -->
            <?php if ($role === 'Kasir'):
                // Ambil nama cabang user
                $branchQuery = "SELECT branch_name FROM branches WHERE id = '$user_branch_id'";
                $branchRes = $conn->query($branchQuery);
                $branchRow = $branchRes->fetch_assoc();
                $branchName = $branchRow['branch_name'] ?? '';
            ?>
              <div class="mb-3">
                <label for="journalBranch" class="form-label">Cabang</label>
                <input type="hidden" name="branch_id" value="<?php echo $user_branch_id; ?>">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($branchName); ?>" readonly>
              </div>
            <?php else: ?>
              <div class="mb-3">
                <label for="journalBranch" class="form-label">Cabang</label>
                <select class="form-control" id="journalBranch" name="branch_id" required>
                  <option value="">Pilih Cabang</option>
                  <?php
                    $branchQuery = "SELECT * FROM branches ORDER BY branch_name";
                    $branchResult = $conn->query($branchQuery);
                    if($branchResult->num_rows > 0){
                      while($b = $branchResult->fetch_assoc()){
                        echo '<option value="'.$b['id'].'">'.htmlspecialchars($b['branch_name']).'</option>';
                      }
                    }
                  ?>
                </select>
              </div>
            <?php endif; ?>

            <!-- Nama Kategori (load via AJAX) -->
            <div class="mb-3">
              <label for="journalCategory" class="form-label">Nama Kategori</label>
              <select class="form-control" id="journalCategory" name="cost_category_id" required>
                <option value="">Pilih Kategori</option>
              </select>
            </div>
            <!-- Nomor Ref: otomatis diisi berdasarkan data kategori -->
            <div class="mb-3">
              <label for="journalRefNumber" class="form-label">Nomor Ref.</label>
              <input type="text" class="form-control" id="journalRefNumber" name="ref_number" readonly required>
            </div>
            <!-- Deskripsi -->
            <div class="mb-3">
              <label for="journalDescription" class="form-label">Deskripsi</label>
              <textarea class="form-control" id="journalDescription" name="description" required></textarea>
            </div>
            <!-- Tipe Transaksi -->
            <div class="mb-3">
              <label for="journalType" class="form-label">Tipe Transaksi</label>
              <select class="form-control" id="journalType" name="transaction_type" required>
                <option value="debit" selected>Debit</option>
                <option value="kredit">Kredit</option>
              </select>
            </div>
            <!-- Input untuk Debit dan Kredit (ditoggle) -->
            <div class="mb-3" id="debitInputDiv">
              <label for="journalDebit" class="form-label">Debit</label>
              <input type="number" step="0.01" class="form-control" id="journalDebit" name="debit" value="0.00" required>
            </div>
            <div class="mb-3" id="creditInputDiv" style="display:none;">
              <label for="journalCredit" class="form-label">Kredit</label>
              <input type="number" step="0.01" class="form-control" id="journalCredit" name="credit" value="0.00" required>
            </div>
         </div>
         <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
            <button type="submit" class="btn btn-primary">Simpan</button>
         </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Lihat Detail Journal -->
<div class="modal fade" id="viewJournalModal" tabindex="-1" aria-labelledby="viewJournalModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="viewJournalModalLabel">Detail Journal Umum</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
             <div id="journalDetailsContent">
               <!-- Detail jurnal akan dimuat melalui AJAX -->
             </div>
         </div>
         <div class="modal-footer">
             <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
         </div>
    </div>
  </div>
</div>

<!-- Modal Edit Journal Entry -->
<div class="modal fade" id="editJournalEntryModal" tabindex="-1" aria-labelledby="editJournalEntryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editJournalEntryForm">
      <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="editJournalEntryModalLabel">Edit Journal Entry</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
           <input type="hidden" id="editJournalId" name="id">
           <!-- Tanggal Transaksi -->
           <div class="mb-3">
             <label for="editEntryDate" class="form-label">Tanggal Transaksi</label>
             <input type="date" class="form-control" id="editEntryDate" name="entry_date" required>
           </div>
           <!-- Cabang -->
           <?php if ($role === 'Kasir'):
                // Gunakan data cabang user
                $branchQuery = "SELECT branch_name FROM branches WHERE id = '$user_branch_id'";
                $branchRes = $conn->query($branchQuery);
                $branchRow = $branchRes->fetch_assoc();
                $branchName = $branchRow['branch_name'] ?? '';
            ?>
              <div class="mb-3">
                <label for="editJournalBranch" class="form-label">Cabang</label>
                <input type="hidden" name="branch_id" id="editJournalBranch" value="<?php echo $user_branch_id; ?>">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($branchName); ?>" readonly>
              </div>
            <?php else: ?>
              <div class="mb-3">
                <label for="editJournalBranch" class="form-label">Cabang</label>
                <select class="form-control" id="editJournalBranch" name="branch_id" required>
                  <option value="">Pilih Cabang</option>
                  <?php
                    $branchResult = $conn->query("SELECT * FROM branches ORDER BY branch_name");
                    if($branchResult->num_rows > 0){
                      while($b = $branchResult->fetch_assoc()){
                        echo '<option value="'.$b['id'].'">'.htmlspecialchars($b['branch_name']).'</option>';
                      }
                    }
                  ?>
                </select>
              </div>
            <?php endif; ?>
           <!-- Nama Kategori (load via AJAX) -->
           <div class="mb-3">
             <label for="editJournalCategory" class="form-label">Nama Kategori</label>
             <select class="form-control" id="editJournalCategory" name="cost_category_id" required>
               <option value="">Pilih Kategori</option>
             </select>
           </div>
           <!-- Nomor Ref -->
           <div class="mb-3">
             <label for="editJournalRefNumber" class="form-label">Nomor Ref.</label>
             <input type="text" class="form-control" id="editJournalRefNumber" name="ref_number" readonly required>
           </div>
           <!-- Deskripsi -->
           <div class="mb-3">
             <label for="editJournalDescription" class="form-label">Deskripsi</label>
             <textarea class="form-control" id="editJournalDescription" name="description" required></textarea>
           </div>
           <!-- Tipe Transaksi -->
           <div class="mb-3">
             <label for="editJournalType" class="form-label">Tipe Transaksi</label>
             <select class="form-control" id="editJournalType" name="transaction_type" required>
               <option value="debit">Debit</option>
               <option value="kredit">Kredit</option>
             </select>
           </div>
           <!-- Input untuk Debit dan Kredit (ditoggle) -->
           <div class="mb-3" id="editDebitInputDiv">
             <label for="editJournalDebit" class="form-label">Debit</label>
             <input type="number" step="0.01" class="form-control" id="editJournalDebit" name="debit" value="0.00" required>
           </div>
           <div class="mb-3" id="editCreditInputDiv" style="display:none;">
             <label for="editJournalCredit" class="form-label">Kredit</label>
             <input type="number" step="0.01" class="form-control" id="editJournalCredit" name="credit" value="0.00" required>
           </div>
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
           <button type="submit" class="btn btn-primary">Perbarui</button>
         </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal Delete Journal Entry -->
<div class="modal fade" id="deleteJournalEntryModal" tabindex="-1" aria-labelledby="deleteJournalEntryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="deleteJournalEntryForm">
      <div class="modal-content">
         <div class="modal-header">
           <h5 class="modal-title" id="deleteJournalEntryModalLabel">Hapus Journal Entry</h5>
           <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
           <p>Apakah Anda yakin ingin menghapus jurnal ini?</p>
           <input type="hidden" id="deleteJournalId" name="id">
         </div>
         <div class="modal-footer">
           <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
           <button type="submit" class="btn btn-danger">Hapus</button>
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
// Simpan variabel role dan branch id dari PHP ke JS
var userRole = "<?php echo $role; ?>";
var userBranchId = "<?php echo $user_branch_id; ?>";

// Fungsi untuk load kategori berdasarkan branch (untuk modal tambah dan edit)
function loadCategoriesByBranch(branchId, targetSelect, callback) {
  $.ajax({
    url: 'process_kasir/get_cost_categories_by_branch.php',
    method: 'POST',
    data: { branch_id: branchId },
    dataType: 'json',
    success: function(response) {
      $(targetSelect).empty().append('<option value="">Pilih Kategori</option>');
      if(response.status === "success"){
        $.each(response.data, function(i, cat){
          $(targetSelect).append('<option value="'+ cat.id +'" data-ref="'+ cat.ref_number +'">'+ cat.category_name +'</option>');
        });
      }
      if(callback) callback();
    },
    error: function(xhr, status, error){
      console.error("Error loading categories:", error);
    }
  });
}

// Fungsi untuk update nomor ref berdasarkan kategori (modal tambah)
function updateRefNumberFromCategory() {
  var ref = $("#journalCategory option:selected").data("ref");
  $("#journalRefNumber").val(ref ? ref : "");
}

// Fungsi update nomor ref di modal edit
function updateEditRefNumberFromCategory() {
  var ref = $("#editJournalCategory option:selected").data("ref");
  $("#editJournalRefNumber").val(ref ? ref : "");
}

$(document).ready(function(){
  // --- Modal Tambah Journal Entry ---
  // Jika user Kasir, langsung load kategori berdasarkan branch miliknya saat modal tampil
  $('#addJournalEntryModal').on('shown.bs.modal', function(){
    if(userRole === 'Kasir'){
        loadCategoriesByBranch(userBranchId, "#journalCategory", function(){
            // Jika terdapat data kategori (selain opsi default), otomatis pilih opsi pertama
            var $options = $("#journalCategory").children("option");
            if($options.length > 1){
                $("#journalCategory").val($options.eq(1).val());
                updateRefNumberFromCategory();
            }
        });
    }
  });
  
  // Untuk user non-Kasir, load kategori saat cabang dipilih
  $("#journalBranch").on("change", function(){
    var branchId = $(this).val();
    loadCategoriesByBranch(branchId, "#journalCategory");
    $("#journalRefNumber").val("");
  });
  
  $("#journalCategory").on("change", function(){
    updateRefNumberFromCategory();
  });
  
  $("#journalType").on("change", function(){
    var type = $(this).val();
    if(type === "debit"){
      var creditValue = parseFloat($("#journalCredit").val());
      if(creditValue > 0){
          $("#journalDebit").val(creditValue.toFixed(2));
      }
      $("#journalCredit").val("0.00");
      $("#debitInputDiv").show();
      $("#creditInputDiv").hide();
      $("#journalDebit").prop("required", true);
      $("#journalCredit").prop("required", false);
    } else {
      var debitValue = parseFloat($("#journalDebit").val());
      if(debitValue > 0){
          $("#journalCredit").val(debitValue.toFixed(2));
      }
      $("#journalDebit").val("0.00");
      $("#debitInputDiv").hide();
      $("#creditInputDiv").show();
      $("#journalCredit").prop("required", true);
      $("#journalDebit").prop("required", false);
    }
  });
  
  $("#addJournalEntryForm").on("submit", function(e){
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
      url: 'process_kasir/process_add_journal_entry.php',
      method: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response){
        if(response.status === "success"){
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Jurnal berhasil ditambahkan!',
            timer: 2000,
            showConfirmButton: false
          }).then(function(){
            $("#addJournalEntryModal").modal("hide");
            location.reload();
          });
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
          text: 'Terjadi kesalahan saat menambahkan jurnal.'
        });
      }
    });
  });
  
  // Tampilkan detail journal summary melalui AJAX
  $(document).on("click", ".btn-view-journal-summary", function(){
    var branchId = $(this).data("branch_id");
    var period = $(this).data("period");
    $.ajax({
      url: 'process_kasir/get_journal_details.php',
      method: 'POST',
      data: { branch_id: branchId, period: period },
      dataType: 'json',
      success: function(response) {
        if(response.status === "success"){
          $("#journalDetailsContent").html(response.data);
          $("#viewJournalModal").modal("show");
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
          text: 'Terjadi kesalahan saat mengambil detail jurnal.'
        });
      }
    });
  });
  
  // --- Event delegation untuk tombol edit pada tabel detail jurnal (modal view) ---
  $("#journalDetailsContent").on("click", ".btn-edit-journal", function(){
    var journalId = $(this).data("id");
    var entryDate = $(this).data("entry_date");
    var branchId = $(this).data("branch_id");
    var costCategoryId = $(this).data("cost_category_id");
    var refNumber = $(this).data("ref_number");
    var description = $(this).data("description");
    var debit = $(this).data("debit");
    var credit = $(this).data("credit");
    var transType = parseFloat(debit) > 0 ? "debit" : "kredit";
    
    $("#editJournalId").val(journalId);
    $("#editEntryDate").val(entryDate);
    $("#editJournalBranch").val(branchId);
    
    loadCategoriesByBranch(branchId, "#editJournalCategory", function(){
        $("#editJournalCategory").val(costCategoryId);
        var selectedRef = $("#editJournalCategory option:selected").data("ref");
        $("#editJournalRefNumber").val(selectedRef ? selectedRef : refNumber);
    });
    
    $("#editJournalDescription").val(description);
    $("#editJournalType").val(transType);
    
    if(transType === "debit"){
      $("#editDebitInputDiv").show();
      $("#editCreditInputDiv").hide();
      $("#editJournalDebit").val(debit);
      $("#editJournalCredit").val("0.00");
    } else {
      $("#editDebitInputDiv").hide();
      $("#editCreditInputDiv").show();
      $("#editJournalCredit").val(credit);
      $("#editJournalDebit").val("0.00");
    }
    
    $("#editJournalEntryModal").modal("show");
  });
  
  // --- Handle branch change di modal edit untuk load kategori ---
  $("#editJournalBranch").on("change", function(){
    var branchId = $(this).val();
    loadCategoriesByBranch(branchId, "#editJournalCategory", function(){
      $("#editJournalRefNumber").val("");
    });
  });
  
  // --- Handle category change di modal edit untuk update nomor ref ---
  $("#editJournalCategory").on("change", function(){
    updateEditRefNumberFromCategory();
  });
  
  // --- Event handler untuk tipe transaksi di modal edit ---
  $("#editJournalType").on("change", function(){
    var type = $(this).val();
    if(type === "debit"){
      var creditValue = parseFloat($("#editJournalCredit").val());
      if(creditValue > 0){
          $("#editJournalDebit").val(creditValue.toFixed(2));
      }
      $("#editJournalCredit").val("0.00");
      $("#editDebitInputDiv").show();
      $("#editCreditInputDiv").hide();
      $("#editJournalDebit").prop("required", true);
      $("#editJournalCredit").prop("required", false);
    } else {
      var debitValue = parseFloat($("#editJournalDebit").val());
      if(debitValue > 0){
          $("#editJournalCredit").val(debitValue.toFixed(2));
      }
      $("#editJournalDebit").val("0.00");
      $("#editDebitInputDiv").hide();
      $("#editCreditInputDiv").show();
      $("#editJournalCredit").prop("required", true);
      $("#editJournalDebit").prop("required", false);
    }
  });
  
  // --- Event handler untuk tombol delete pada tabel detail jurnal (modal view) ---
  $("#journalDetailsContent").on("click", ".btn-delete-journal", function(){
    var journalId = $(this).data("id");
    $("#deleteJournalId").val(journalId);
    $("#deleteJournalEntryModal").modal("show");
  });
  
  // Submit Edit Journal Entry
  $("#editJournalEntryForm").on("submit", function(e){
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
      url: 'process_kasir/process_edit_journal_entry.php',
      method: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response){
        if(response.status === "success"){
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Journal entry berhasil diperbarui!',
            timer: 2000,
            showConfirmButton: false
          }).then(function(){
            $("#editJournalEntryModal").modal("hide");
            location.reload();
          });
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
          text: 'Terjadi kesalahan saat memperbarui journal entry.'
        });
      }
    });
  });
  
  // Submit Delete Journal Entry
  $("#deleteJournalEntryForm").on("submit", function(e){
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
      url: 'process_kasir/process_delete_journal_entry.php',
      method: 'POST',
      data: formData,
      dataType: 'json',
      success: function(response){
        if(response.status === "success"){
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'Journal entry berhasil dihapus!',
            timer: 2000,
            showConfirmButton: false
          }).then(function(){
            $("#deleteJournalEntryModal").modal("hide");
            location.reload();
          });
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
          text: 'Terjadi kesalahan saat menghapus journal entry.'
        });
      }
    });
  });
  
  // --- Pencarian Summary Berdasarkan Bulan & Tahun ---
  $("#btnSearch").on("click", function(){
    var period = $("#searchMonthYear").val(); // format YYYY-MM
    $.ajax({
      url: 'process_kasir/search_journal_summary.php',
      method: 'POST',
      data: { period: period },
      dataType: 'json',
      success: function(response){
        if(response.status === "success"){
          $("#dataJournalSummary tbody").html(response.data);
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
});
</script>
