<?php
// pages/kasir/items.php
// session_start();
include_once __DIR__ . '/../../config/database.php';

// Ambil informasi user dari session
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;

// Query: hanya menampilkan data barang dari cabang user (jika Kasir)
$query = "SELECT i.*, b.branch_name 
          FROM items i
          LEFT JOIN branches b ON i.branch_id = b.id ";
if ($role === 'Kasir') {
    $query .= "WHERE i.branch_id = '$user_branch_id' ";
}
$query .= "ORDER BY i.id";
$result = $conn->query($query);
// Fungsi format Rupiah (jika diperlukan)
function formatRupiah($angka) {
    return "Rp. " . number_format($angka, 2, ',', '.');
}
?>

<div class="container mt-4">
  <h2>Data Barang</h2>
  <!-- Tombol Tambah Barang -->
  <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addItemModal" title="Tambah Barang">
    <i class="fas fa-plus"></i> Tambah Barang
  </button>
  
  <!-- Tabel Barang -->
    <div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 fw-bold text-primary">Daftar Barang</h6>
    </div>
    <div class="card-body">
        <div class="table-responsive">
        <table class="table table-bordered" id="itemsTable" width="100%" cellspacing="0">
            <thead>
            <tr>
                <th>No</th>
                <th>Cabang</th>
                <th>Nama Barang</th>
                <th>Kode Barang</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Restock</th>
                <th>Harga</th>
                <th>Aksi</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th>No</th>
                <th>Cabang</th>
                <th>Nama Barang</th>
                <th>Kode Barang</th>
                <th>Kategori</th>
                <th>Stok</th>
                <th>Restock</th>
                <th>Harga</th>
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
                    <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['item_code']); ?></td>
                    <td><?php echo htmlspecialchars($row['category']); ?></td>
                    <td><?php echo htmlspecialchars($row['stock']); ?></td>
                    <td>
                    <?php if ($row['stock'] < 3): ?>
                        <input type="number" min="1" class="form-control form-control-sm restock-qty" style="width:80px; display:inline-block;" placeholder="Qty">
                        <button class="btn btn-sm btn-outline-danger btn-restock" data-id="<?php echo $row['id']; ?>">Restock</button>
                    <?php else: ?>
                        <span>-</span>
                    <?php endif; ?>
                    </td>
                    <td><?php echo formatRupiah($row['price']); ?></td>
                    <td>
                    <!-- Tombol Lihat Barang -->  
                    <button class="btn btn-info btn-sm btn-view-item" 
                        data-id="<?php echo $row['id']; ?>"
                        data-branch_id="<?php echo $row['branch_id']; ?>"
                        data-branch_name="<?php echo htmlspecialchars($row['branch_name']); ?>"
                        data-item_name="<?php echo htmlspecialchars($row['item_name']); ?>"
                        data-item_code="<?php echo htmlspecialchars($row['item_code']); ?>"
                        data-category="<?php echo htmlspecialchars($row['category']); ?>"
                        data-stock="<?php echo htmlspecialchars($row['stock']); ?>"
                        data-price="<?php echo htmlspecialchars($row['price']); ?>"
                        data-created_at="<?php echo htmlspecialchars($row['created_at']); ?>"
                        title="Lihat">
                        <i class="fas fa-eye"></i>
                    </button>
                    <!-- Tombol Edit Barang -->  
                    <button class="btn btn-warning btn-sm btn-edit-item" 
                        data-id="<?php echo $row['id']; ?>"
                        data-branch_id="<?php echo $row['branch_id']; ?>"
                        data-item_name="<?php echo htmlspecialchars($row['item_name']); ?>"
                        data-item_code="<?php echo htmlspecialchars($row['item_code']); ?>"
                        data-category="<?php echo htmlspecialchars($row['category']); ?>"
                        data-stock="<?php echo htmlspecialchars($row['stock']); ?>"
                        data-price="<?php echo htmlspecialchars($row['price']); ?>"
                        title="Edit">
                        <i class="fas fa-edit"></i>
                    </button>
                    <!-- Tombol Hapus Barang -->  
                    <button class="btn btn-danger btn-sm btn-delete-item" 
                        data-id="<?php echo $row['id']; ?>"
                        data-item_name="<?php echo htmlspecialchars($row['item_name']); ?>"
                        title="Hapus">
                        <i class="fas fa-trash"></i>
                    </button>
                    </td>
                </tr>
            <?php
                }
            } else {
                echo "<tr><td colspan='9' class='text-center'>Belum ada data barang.</td></tr>";
            }
            ?>
            </tbody>
        </table>
        </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Barang -->
<div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addItemForm">
      <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="addItemModalLabel">Tambah Barang</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
            <!-- Field Cabang: Jika Kasir, set otomatis sesuai session -->  
            <?php if ($role === 'Kasir'): 
                $branchQuery = "SELECT branch_name FROM branches WHERE id = '$user_branch_id'";
                $branchRes = $conn->query($branchQuery);
                $branchRow = $branchRes->fetch_assoc();
                $branchName = $branchRow['branch_name'] ?? '';
            ?>
              <div class="mb-3">
                <label for="itemBranch" class="form-label">Cabang</label>
                <input type="hidden" name="branch_id" value="<?php echo $user_branch_id; ?>">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($branchName); ?>" readonly>
              </div>
            <?php else: ?>
              <div class="mb-3">
                <label for="itemBranch" class="form-label">Cabang</label>
                <select class="form-control" id="itemBranch" name="branch_id" required>
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
              <label for="itemName" class="form-label">Nama Barang</label>
              <input type="text" class="form-control" id="itemName" name="item_name" required>
            </div>
            <!-- Kode Barang akan di-generate otomatis, jadi tampil readonly -->
            <div class="mb-3">
              <label for="itemCode" class="form-label">Kode Barang</label>
              <input type="text" class="form-control" id="itemCode" name="item_code" readonly placeholder="Akan digenerate otomatis">
            </div>
            <div class="mb-3">
              <label for="category" class="form-label">Kategori</label>
              <input type="text" class="form-control" id="category" name="category" required>
            </div>
            <div class="mb-3">
              <label for="stock" class="form-label">Stok</label>
              <input type="number" class="form-control" id="stock" name="stock" required>
            </div>
            <div class="mb-3">
              <label for="price" class="form-label">Harga</label>
              <input type="number" class="form-control" id="price" name="price" required>
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

<!-- Modal Edit Barang -->
<div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editItemForm">
      <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="editItemModalLabel">Edit Barang</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
            <input type="hidden" id="editItemId" name="id">
            <!-- Field Cabang: Jika Kasir, set otomatis -->  
            <?php if ($role === 'Kasir'): ?>
              <div class="mb-3">
                <label for="editItemBranch" class="form-label">Cabang</label>
                <input type="hidden" name="branch_id" id="editItemBranch" value="<?php echo $user_branch_id; ?>">
                <input type="text" class="form-control" value="<?php echo htmlspecialchars($branchName); ?>" readonly>
              </div>
            <?php else: ?>
              <div class="mb-3">
                <label for="editItemBranch" class="form-label">Cabang</label>
                <select class="form-control" id="editItemBranch" name="branch_id" required>
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
              <label for="editItemName" class="form-label">Nama Barang</label>
              <input type="text" class="form-control" id="editItemName" name="item_name" required>
            </div>
            <!-- Kode Barang ditampilkan readonly karena tidak boleh diubah -->  
            <div class="mb-3">
              <label for="editItemCode" class="form-label">Kode Barang</label>
              <input type="text" class="form-control" id="editItemCode" name="item_code" readonly>
            </div>
            <div class="mb-3">
              <label for="editCategory" class="form-label">Kategori</label>
              <input type="text" class="form-control" id="editCategory" name="category" required>
            </div>
            <div class="mb-3">
              <label for="editStock" class="form-label">Stok</label>
              <input type="number" class="form-control" id="editStock" name="stock" required>
            </div>
            <div class="mb-3">
              <label for="editPrice" class="form-label">Harga</label>
              <input type="number" class="form-control" id="editPrice" name="price" required>
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

<!-- Modal Lihat Barang -->
<div class="modal fade" id="viewItemModal" tabindex="-1" aria-labelledby="viewItemModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
         <div class="modal-header">
             <h5 class="modal-title" id="viewItemModalLabel">Detail Barang</h5>
             <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
         </div>
         <div class="modal-body">
             <div class="mb-3">
                <label for="viewItemBranch" class="form-label">Cabang</label>
                <input type="text" class="form-control" id="viewItemBranch" readonly>
             </div>
             <div class="mb-3">
                <label for="viewItemName" class="form-label">Nama Barang</label>
                <input type="text" class="form-control" id="viewItemName" readonly>
             </div>
             <div class="mb-3">
                <label for="viewItemCode" class="form-label">Kode Barang</label>
                <input type="text" class="form-control" id="viewItemCode" readonly>
             </div>
             <div class="mb-3">
                <label for="viewCategory" class="form-label">Kategori</label>
                <input type="text" class="form-control" id="viewCategory" readonly>
             </div>
             <div class="mb-3">
                <label for="viewStock" class="form-label">Stok</label>
                <input type="number" class="form-control" id="viewStock" readonly>
             </div>
             <div class="mb-3">
                <label for="viewPrice" class="form-label">Harga</label>
                <input type="number" class="form-control" id="viewPrice" readonly>
             </div>
             <div class="mb-3">
                <label for="viewItemCreatedAt" class="form-label">Dibuat</label>
                <input type="text" class="form-control" id="viewItemCreatedAt" readonly>
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
    // Modal View: Tampilkan data barang
    $(document).on("click", ".btn-view-item", function(){
      $("#viewItemModal").modal("show");
      $("#viewItemBranch").val($(this).data("branch_name"));
      $("#viewItemName").val($(this).data("item_name"));
      $("#viewItemCode").val($(this).data("item_code"));
      $("#viewCategory").val($(this).data("category"));
      $("#viewStock").val($(this).data("stock"));
      $("#viewPrice").val($(this).data("price"));
      $("#viewItemCreatedAt").val($(this).data("created_at"));
    });
    
    // Modal Edit: Isi data dari tombol edit
    $(document).on("click", ".btn-edit-item", function(){
      $("#editItemModal").modal("show");
      $("#editItemId").val($(this).data("id"));
      $("#editItemBranch").val($(this).data("branch_id"));
      $("#editItemName").val($(this).data("item_name"));
      $("#editItemCode").val($(this).data("item_code"));
      $("#editCategory").val($(this).data("category"));
      $("#editStock").val($(this).data("stock"));
      $("#editPrice").val($(this).data("price"));
    });
    
    // Proses tambah barang
    $("#addItemForm").on("submit", function(e){
      e.preventDefault();
      var formData = $(this).serialize();
      $.ajax({
         url: 'process_kasir/process_add_item.php',
         method: 'POST',
         data: formData,
         dataType: 'json',
         success: function(response){
           if(response.status === 'success'){
             Swal.fire({
               icon: 'success',
               title: 'Berhasil',
               text: 'Barang berhasil ditambahkan!',
               timer: 2000,
               showConfirmButton: false
             }).then(() => {
               $("#addItemModal").modal("hide");
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
             text: 'Terjadi kesalahan saat menambahkan barang.'
           });
         }
      });
    });
    
    // Proses edit barang
    $("#editItemForm").on("submit", function(e){
      e.preventDefault();
      var formData = $(this).serialize();
      $.ajax({
         url: 'process_kasir/process_edit_item.php',
         method: 'POST',
         data: formData,
         dataType: 'json',
         success: function(response){
           if(response.status === 'success'){
             Swal.fire({
               icon: 'success',
               title: 'Berhasil',
               text: 'Barang berhasil diperbarui!',
               timer: 2000,
               showConfirmButton: false
             }).then(() => {
               $("#editItemModal").modal("hide");
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
             text: 'Terjadi kesalahan saat memperbarui barang.'
           });
         }
      });
    });
    
    // Proses hapus barang
    $(document).on("click", ".btn-delete-item", function(){
      var id = $(this).data("id");
      var itemName = $(this).data("item_name");
      Swal.fire({
        title: 'Apakah Anda yakin?',
        text: "Barang '" + itemName + "' akan dihapus!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, hapus!',
        cancelButtonText: 'Batal'
      }).then((result) => {
        if(result.isConfirmed){
          $.ajax({
             url: 'process_kasir/process_delete_item.php',
             method: 'POST',
             data: { id: id },
             dataType: 'json',
             success: function(response){
               if(response.status === 'success'){
                 Swal.fire({
                   icon: 'success',
                   title: 'Berhasil',
                   text: 'Barang berhasil dihapus!',
                   timer: 2000,
                   showConfirmButton: false
                 }).then(() => {
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
                 text: 'Terjadi kesalahan saat menghapus barang.'
               });
             }
          });
        }
      });
    });
    
    // Proses Restock: Ambil nilai dari textbox di kolom Restock
    $(document).on("click", ".btn-restock", function(){
      var id = $(this).data("id");
      // Ambil nilai dari input restock pada baris yang sama
      var qty = $(this).siblings(".restock-qty").val();
      if(!qty || qty <= 0){
        Swal.fire({
          icon: 'warning',
          title: 'Peringatan',
          text: 'Masukkan jumlah restock yang valid!'
        });
        return;
      }
      $.ajax({
         url: 'process_kasir/process_restock_item.php',
         method: 'POST',
         data: { id: id, qty: qty },
         dataType: 'json',
         success: function(response){
           if(response.status === 'success'){
             Swal.fire({
               icon: 'success',
               title: 'Berhasil',
               text: 'Barang berhasil direstock!',
               timer: 2000,
               showConfirmButton: false
             }).then(() => {
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
             text: 'Terjadi kesalahan saat restock barang.'
           });
         }
      });
    });
});
</script>
