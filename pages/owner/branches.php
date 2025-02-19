<?php
// pages/branches.php
include_once __DIR__ . '/../../config/database.php';
?>
<div class="container mt-4">
  <h2>Data Cabang</h2>
  <!-- Tombol untuk membuka modal tambah -->
  <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addBranchModal" title="Tambah Data Cabang">
    <i class="fas fa-plus"></i> Tambah Data Cabang
  </button>
  
  <!-- Card Table for Data Cabang -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Data Cabang</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
          <thead>
            <tr>
              <th>ID</th>
              <th>Nama Cabang</th>
              <th>Alamat</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tfoot>
            <tr>
              <th>ID</th>
              <th>Nama Cabang</th>
              <th>Alamat</th>
              <th>Aksi</th>
            </tr>
          </tfoot>
          <tbody>
            <?php
            // Query untuk mengambil data cabang
            $query = "SELECT * FROM branches ORDER BY id";
            $result = $conn->query($query);
            
            // Mulai penomoran dari 1
            $counter = 1;
            
            if ($result->num_rows > 0) {
              while ($row = $result->fetch_assoc()) {
                ?>
                <tr>
                  <!-- Ganti ID dengan penomoran yang di increment -->
                  <td><?php echo $counter++; ?></td>
                  <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                  <td><?php echo htmlspecialchars($row['address']); ?></td>
                  <td>
                    <!-- Tombol Lihat -->
                    <button class="btn btn-info btn-sm btn-view" 
                            data-id="<?php echo $row['id']; ?>" 
                            data-name="<?php echo htmlspecialchars($row['branch_name']); ?>" 
                            data-address="<?php echo htmlspecialchars($row['address']); ?>"
                            title="Lihat Detail">
                      <i class="fas fa-eye"></i> Lihat
                    </button>
                    <!-- Tombol Edit -->
                    <button class="btn btn-warning btn-sm btn-edit" 
                            data-id="<?php echo $row['id']; ?>" 
                            data-name="<?php echo htmlspecialchars($row['branch_name']); ?>" 
                            data-address="<?php echo htmlspecialchars($row['address']); ?>"
                            title="Edit Data">
                      <i class="fas fa-edit"></i> Edit
                    </button>
                    <!-- Tombol Hapus -->
                    <button class="btn btn-danger btn-sm btn-delete" 
                            data-id="<?php echo $row['id']; ?>" 
                            data-name="<?php echo htmlspecialchars($row['branch_name']); ?>"
                            title="Hapus Data">
                      <i class="fas fa-trash"></i> Hapus
                    </button>
                  </td>
                </tr>
                <?php
              }
            } else {
              echo "<tr><td colspan='4' class='text-center'>Belum ada data cabang.</td></tr>";
            }
            ?>
          </tbody>

        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Data Cabang -->
<div class="modal fade" id="addBranchModal" tabindex="-1" aria-labelledby="addBranchModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addBranchForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addBranchModalLabel">Tambah Data Cabang</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="branchName" class="form-label">Nama Cabang</label>
            <input type="text" class="form-control" id="branchName" name="branch_name" required>
          </div>
          <div class="mb-3">
            <label for="branchAddress" class="form-label">Alamat</label>
            <textarea class="form-control" id="branchAddress" name="address" required></textarea>
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

<!-- Modal Edit Data Cabang -->
<div class="modal fade" id="editBranchModal" tabindex="-1" aria-labelledby="editBranchModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editBranchForm">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editBranchModalLabel">Edit Data Cabang</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <!-- Field hidden untuk ID -->
          <input type="hidden" name="id" id="editBranchId">
          <div class="mb-3">
            <label for="editBranchName" class="form-label">Nama Cabang</label>
            <input type="text" class="form-control" id="editBranchName" name="branch_name" required>
          </div>
          <div class="mb-3">
            <label for="editBranchAddress" class="form-label">Alamat</label>
            <textarea class="form-control" id="editBranchAddress" name="address" required></textarea>
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

<!-- Modal Lihat Data Cabang -->
<div class="modal fade" id="viewBranchModal" tabindex="-1" aria-labelledby="viewBranchModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewBranchModalLabel">Detail Data Cabang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id" id="viewBranchId">
        <div class="mb-3">
          <label for="viewBranchName" class="form-label">Nama Cabang</label>
          <input type="text" class="form-control" id="viewBranchName" name="branch_name" readonly>
        </div>
        <div class="mb-3">
          <label for="viewBranchAddress" class="form-label">Alamat</label>
          <input type="text" class="form-control" id="viewBranchAddress" name="address" readonly>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>


<!-- CDN JS -->
<!-- jQuery (required for our custom jQuery code) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Bootstrap 5 Bundle JS (includes Popper) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- Sertakan jQuery dan Bootstrap JS (pastikan sudah ada di layout kamu) -->
<script>
$(document).ready(function(){
  // Fungsi tambah data cabang
  $("#addBranchForm").on("submit", function(e){
    e.preventDefault();
    $.ajax({
      url: 'process/process_add_branch.php',
      method: 'POST',
      data: $(this).serialize(),
      success: function(response){
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Data cabang berhasil ditambahkan!',
          timer: 2000,
          showConfirmButton: false
        }).then(() => {
          $("#addBranchModal").modal("hide");
          location.reload();
        });
      },
      error: function(xhr, status, error){
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: 'Terjadi kesalahan saat menambahkan data cabang.'
        });
      }
    });
  });

  // Fungsi edit data cabang
  $(".btn-edit").on("click", function(){
    var id = $(this).data("id");
    var name = $(this).data("name");
    var address = $(this).data("address");
    $("#editBranchId").val(id);
    $("#editBranchName").val(name);
    $("#editBranchAddress").val(address);
    $("#editBranchModal").modal("show");
  });

  $("#editBranchForm").on("submit", function(e){
    e.preventDefault();
    $.ajax({
      url: 'process/process_edit_branch.php',
      method: 'POST',
      data: $(this).serialize(),
      success: function(response){
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'Data cabang berhasil diperbarui!',
          timer: 2000,
          showConfirmButton: false
        }).then(() => {
          $("#editBranchModal").modal("hide");
          location.reload();
        });
      },
      error: function(xhr, status, error){
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: 'Terjadi kesalahan saat memperbarui data cabang.'
        });
      }
    });
  });

  // Fungsi lihat data cabang
  $(".btn-view").on("click", function(){
    var id = $(this).data("id");
    var name = $(this).data("name");
    var address = $(this).data("address");
    // Gunakan .val() untuk mengisi nilai input
    $("#viewBranchId").val(id);
    $("#viewBranchName").val(name);
    $("#viewBranchAddress").val(address);
    $("#viewBranchModal").modal("show");
  });

  // Fungsi hapus data cabang
  $(".btn-delete").on("click", function(){
    var id = $(this).data("id");
    var name = $(this).data("name");
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "Data cabang '" + name + "' akan dihapus!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: 'process/process_delete_branch.php',
          method: 'POST',
          data: { id: id },
          success: function(response){
            Swal.fire({
              icon: 'success',
              title: 'Berhasil',
              text: 'Data cabang berhasil dihapus!',
              timer: 2000,
              showConfirmButton: false
            }).then(() => {
              location.reload();
            });
          },
          error: function(xhr, status, error){
            Swal.fire({
              icon: 'error',
              title: 'Gagal',
              text: 'Terjadi kesalahan saat menghapus data cabang.'
            });
          }
        });
      }
    });
  });
});
</script>
