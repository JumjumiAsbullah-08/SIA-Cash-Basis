<?php
// session_start();
include_once __DIR__ . '/../../config/database.php';

// Ambil informasi user dari session
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;

// Fungsi untuk menampilkan status dengan badge
function formatStatusBadge($status) {
    return ($status == 1) 
            ? '<span class="badge bg-success" style="color:#fff;">Aktif</span>' 
            : '<span class="badge bg-danger" style="color:#fff;">Nonaktif</span>';
}

// Query hanya menampilkan data kategori biaya untuk cabang user jika role-nya Kasir
$query = "SELECT cc.*, b.branch_name 
          FROM cost_categories cc
          LEFT JOIN branches b ON cc.branch_id = b.id ";

if ($role === 'Kasir') {
    $query .= "WHERE cc.branch_id = '$user_branch_id' ";
}

$query .= "ORDER BY cc.id";

$result = $conn->query($query);
?>

<div class="container mt-4">
  <h2>Data Kategori Biaya</h2>
  <!-- Tombol Tambah Kategori -->
  <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addCategoryModal" title="Tambah Kategori Biaya">
    <i class="fas fa-plus"></i> Tambah Kategori
  </button>
  
  <!-- Tabel Kategori Biaya -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 fw-bold text-primary">Daftar Kategori Biaya</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="dataCostCategoryTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>No</th>
              <th>Cabang</th>
              <th>Ref. Number</th>
              <th>Nama Kategori</th>
              <th>Deskripsi</th>
              <th>Status</th>
              <th>Dibuat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>No</th>
              <th>Cabang</th>
              <th>Ref. Number</th>
              <th>Nama Kategori</th>
              <th>Deskripsi</th>
              <th>Status</th>
              <th>Dibuat</th>
              <th>Aksi</th>
            </tr>
          </tfoot>
          <tbody>
            <?php
            $counter = 1;
            if ($result && $result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
            ?>
                <tr>
                  <td><?php echo $counter++; ?></td>
                  <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['ref_number']); ?></td>
                  <td><?php echo htmlspecialchars($row['category_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['description']); ?></td>
                  <td><?php echo formatStatusBadge($row['status']); ?></td>
                  <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                  <td>
                    <!-- Tombol Lihat -->
                    <button class="btn btn-info btn-sm btn-view-category" 
                      data-id="<?php echo $row['id']; ?>"
                      data-branch_id="<?php echo $row['branch_id']; ?>"
                      data-branch_name="<?php echo htmlspecialchars($row['branch_name']); ?>"
                      data-ref_number="<?php echo htmlspecialchars($row['ref_number']); ?>"
                      data-category_name="<?php echo htmlspecialchars($row['category_name']); ?>"
                      data-description="<?php echo htmlspecialchars($row['description']); ?>"
                      data-status="<?php echo $row['status']; ?>"
                      data-created_at="<?php echo htmlspecialchars($row['created_at']); ?>"
                      title="Lihat">
                      <i class="fas fa-eye"></i>
                    </button>
                    <!-- Tombol Edit -->
                    <button class="btn btn-warning btn-sm btn-edit-category" 
                      data-id="<?php echo $row['id']; ?>"
                      data-branch_id="<?php echo $row['branch_id']; ?>"
                      data-ref_number="<?php echo htmlspecialchars($row['ref_number']); ?>"
                      data-category_name="<?php echo htmlspecialchars($row['category_name']); ?>"
                      data-description="<?php echo htmlspecialchars($row['description']); ?>"
                      data-status="<?php echo $row['status']; ?>"
                      title="Edit">
                      <i class="fas fa-edit"></i>
                    </button>
                    <!-- Tombol Hapus -->
                    <button class="btn btn-danger btn-sm btn-delete-category" 
                      data-id="<?php echo $row['id']; ?>"
                      data-category_name="<?php echo htmlspecialchars($row['category_name']); ?>"
                      title="Hapus">
                      <i class="fas fa-trash"></i>
                    </button>
                  </td>
                </tr>
            <?php
              }
            } else {
              // Jika tidak ada data (misal kasir belum menginput data kategori biaya)
              echo "<tr><td colspan='8' class='text-center'>Belum ada data kategori biaya.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Kategori Biaya -->
<div class="modal fade" id="addCategoryModal" tabindex="-1" aria-labelledby="addCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addCategoryForm">
      <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="addCategoryModalLabel">Tambah Kategori Biaya</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
            <!-- Field Cabang: Jika Kasir, set otomatis sesuai session -->
            <?php if ($role === 'Kasir'): 
                // Ambil nama cabang dari tabel branches
                $branchQuery = "SELECT branch_name FROM branches WHERE id = '$user_branch_id'";
                $branchRes = $conn->query($branchQuery);
                $branchRow = $branchRes->fetch_assoc();
                $branchName = $branchRow['branch_name'] ?? '';
            ?>
              <div class="mb-3">
                <label for="categoryBranch" class="form-label">Cabang</label>
                <input type="hidden" name="branch_id" value="<?php echo $user_branch_id; ?>">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($branchName); ?>" readonly>
              </div>
            <?php else: ?>
              <div class="mb-3">
                <label for="categoryBranch" class="form-label">Cabang</label>
                <select class="form-control" id="categoryBranch" name="branch_id" required>
                  <option value="">Pilih Cabang</option>
                  <?php
                  $branchQuery = "SELECT * FROM branches ORDER BY branch_name";
                  $branchResult = $conn->query($branchQuery);
                  if ($branchResult->num_rows > 0) {
                    while ($b = $branchResult->fetch_assoc()) {
                      echo '<option value="'. $b['id'] .'">'. htmlspecialchars($b['branch_name']) .'</option>';
                    }
                  }
                  ?>
                </select>
              </div>
            <?php endif; ?>

            <div class="mb-3">
              <label for="reffNumber" class="form-label">Nomor Ref.</label>
              <input type="text" class="form-control" id="reffNumber" name="ref_number" required>
            </div>
            <div class="mb-3">
              <label for="categoryName" class="form-label">Nama Kategori</label>
              <input type="text" class="form-control" id="categoryName" name="category_name" required>
            </div>
            <div class="mb-3">
              <label for="categoryDescription" class="form-label">Deskripsi</label>
              <textarea class="form-control" id="categoryDescription" name="description" required></textarea>
            </div>
            <div class="mb-3">
              <label for="categoryStatus" class="form-label">Status</label>
              <select class="form-control" id="categoryStatus" name="status" required>
                <option value="1" selected>Aktif</option>
                <option value="0">Nonaktif</option>
              </select>
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

<!-- Modal Edit Kategori Biaya -->
<div class="modal fade" id="editCategoryModal" tabindex="-1" aria-labelledby="editCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editCategoryForm">
      <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="editCategoryModalLabel">Edit Kategori Biaya</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
            <input type="hidden" id="editCategoryId" name="id">
            <!-- Field Cabang: Jika Kasir, set otomatis -->
            <?php if ($role === 'Kasir'): ?>
              <div class="mb-3">
                <label for="editCategoryBranch" class="form-label">Cabang</label>
                <input type="hidden" name="branch_id" id="editCategoryBranch" value="<?php echo $user_branch_id; ?>">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($branchName); ?>" readonly>
              </div>
            <?php else: ?>
              <div class="mb-3">
                <label for="editCategoryBranch" class="form-label">Cabang</label>
                <select class="form-control" id="editCategoryBranch" name="branch_id" required>
                  <option value="">Pilih Cabang</option>
                  <?php
                  $branchQuery2 = "SELECT * FROM branches ORDER BY branch_name";
                  $branchResult2 = $conn->query($branchQuery2);
                  if ($branchResult2->num_rows > 0) {
                    while ($b = $branchResult2->fetch_assoc()) {
                      echo '<option value="'. $b['id'] .'">'. htmlspecialchars($b['branch_name']) .'</option>';
                    }
                  }
                  ?>
                </select>
              </div>
            <?php endif; ?>

            <div class="mb-3">
              <label for="editReffNumber" class="form-label">Nomor Ref.</label>
              <input type="text" class="form-control" id="editReffNumber" name="ref_number" required>
            </div>
            <div class="mb-3">
              <label for="editCategoryName" class="form-label">Nama Kategori</label>
              <input type="text" class="form-control" id="editCategoryName" name="category_name" required>
            </div>
            <div class="mb-3">
              <label for="editCategoryDescription" class="form-label">Deskripsi</label>
              <textarea class="form-control" id="editCategoryDescription" name="description" required></textarea>
            </div>
            <div class="mb-3">
              <label for="editCategoryStatus" class="form-label">Status</label>
              <select class="form-control" id="editCategoryStatus" name="status" required>
                <option value="1">Aktif</option>
                <option value="0">Nonaktif</option>
              </select>
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

<!-- Modal Lihat Kategori Biaya -->
<div class="modal fade" id="viewCategoryModal" tabindex="-1" aria-labelledby="viewCategoryModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="viewCategoryModalLabel">Detail Kategori Biaya</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
             <div class="mb-3">
                <label for="viewCategoryBranch" class="form-label">Cabang</label>
                <input type="text" class="form-control" id="viewCategoryBranch" readonly>
             </div>
             <div class="mb-3">
                <label for="viewReffNumber" class="form-label">Nomor Ref.</label>
                <input type="text" class="form-control" id="viewReffNumber" readonly>
             </div>
             <div class="mb-3">
                <label for="viewCategoryName" class="form-label">Nama Kategori</label>
                <input type="text" class="form-control" id="viewCategoryName" readonly>
             </div>
             <div class="mb-3">
                <label for="viewCategoryDescription" class="form-label">Deskripsi</label>
                <textarea class="form-control" id="viewCategoryDescription" readonly></textarea>
             </div>
             <div class="mb-3">
                <label for="viewCategoryStatus" class="form-label">Status</label>
                <input type="text" class="form-control" id="viewCategoryStatus" readonly>
             </div>
             <div class="mb-3">
                <label for="viewCategoryCreatedAt" class="form-label">Dibuat</label>
                <input type="text" class="form-control" id="viewCategoryCreatedAt" readonly>
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
    // Modal View: Isi data dari tombol view
    $(".btn-view-category").on("click", function(){
      $("#viewCategoryModal").modal("show");
      $("#viewCategoryBranch").val($(this).data("branch_name"));
      $("#viewReffNumber").val($(this).data("ref_number"));
      $("#viewCategoryName").val($(this).data("category_name"));
      $("#viewCategoryDescription").val($(this).data("description"));
      var status = $(this).data("status");
      $("#viewCategoryStatus").val(status == 1 ? "Aktif" : "Nonaktif");
      $("#viewCategoryCreatedAt").val($(this).data("created_at"));
    });
    
    // Modal Edit: Isi data dari tombol edit
    $(".btn-edit-category").on("click", function(){
      $("#editCategoryModal").modal("show");
      $("#editCategoryId").val($(this).data("id"));
      $("#editCategoryBranch").val($(this).data("branch_id"));
      $("#editReffNumber").val($(this).data("ref_number"));
      $("#editCategoryName").val($(this).data("category_name"));
      $("#editCategoryDescription").val($(this).data("description"));
      $("#editCategoryStatus").val($(this).data("status"));
    });
    
    // Submit Tambah Kategori
    $("#addCategoryForm").on("submit", function(e){
      e.preventDefault();
      var formData = $(this).serialize();
      $.ajax({
         url: 'process/process_add_cost_category.php',
         method: 'POST',
         data: formData,
         dataType: "json",
         success: function(response){
           if(response.status === "success"){
             Swal.fire({
               icon: 'success',
               title: 'Berhasil',
               text: 'Kategori biaya berhasil ditambahkan!',
               timer: 2000,
               showConfirmButton: false
             }).then(() => {
               $("#addCategoryModal").modal("hide");
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
             text: 'Terjadi kesalahan saat menambahkan kategori biaya.'
           });
         }
      });
    });
    
    // Submit Edit Kategori
    $("#editCategoryForm").on("submit", function(e){
      e.preventDefault();
      var formData = $(this).serialize();
      $.ajax({
         url: 'process/process_edit_cost_category.php',
         method: 'POST',
         data: formData,
         dataType: "json",
         success: function(response){
           if(response.status === "success"){
             Swal.fire({
               icon: 'success',
               title: 'Berhasil',
               text: 'Kategori biaya berhasil diperbarui!',
               timer: 2000,
               showConfirmButton: false
             }).then(() => {
               $("#editCategoryModal").modal("hide");
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
             text: 'Terjadi kesalahan saat memperbarui kategori biaya.'
           });
         }
      });
    });
    
    // Hapus Kategori
    $(".btn-delete-category").on("click", function () {
      var id = $(this).data("id");
      var categoryName = $(this).data("category_name");

      Swal.fire({
        title: "Apakah Anda yakin?",
        text: "Kategori biaya '" + categoryName + "' akan dihapus!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Ya, hapus!",
        cancelButtonText: "Batal",
      }).then((result) => {
        if (result.isConfirmed) {
          $.ajax({
            url: "process/process_delete_cost_category.php",
            method: "POST",
            data: { id: id },
            dataType: "json",
            success: function (response) {
              if (response.status === "success") {
                Swal.fire({
                  icon: "success",
                  title: "Berhasil",
                  text: "Kategori biaya berhasil dihapus!",
                  timer: 2000,
                  showConfirmButton: false,
                }).then(() => {
                  location.reload();
                });
              } else {
                Swal.fire({
                  icon: "error",
                  title: "Gagal",
                  text:
                    response.debug.includes("jurnal umum") // Jika pesan error terkait jurnal umum
                      ? "Kategori ini masih digunakan dalam jurnal umum dan tidak bisa dihapus."
                      : "Kategori ini masih digunakan dalam jurnal umum dan tidak bisa dihapus.",
                });
              }
            },
            error: function (xhr, status, error) {
              Swal.fire({
                icon: "error",
                title: "Gagal",
                text: "Terjadi kesalahan saat menghapus kategori biaya.",
              });
            },
          });
        }
      });
    });

});
</script>