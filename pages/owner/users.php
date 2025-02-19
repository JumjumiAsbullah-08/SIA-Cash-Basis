<?php
// pages/users.php
include_once __DIR__ . '/../../config/database.php';
?>
<div class="container mt-4">
  <h2>Data Users</h2>
  <!-- Tombol untuk membuka modal tambah user -->
  <button type="button" class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addUserModal" title="Tambah User">
    <i class="fas fa-plus"></i> Tambah User
  </button>
  
  <!-- Card Table untuk Data Users -->
  <div class="card shadow mb-4">
    <div class="card-header py-3">
      <h6 class="m-0 font-weight-bold text-primary">Data Users</h6>
    </div>
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-bordered" id="dataUserTable" width="100%" cellspacing="0">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Cabang</th>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
                </thead>
                <tfoot>
                <tr>
                    <th>No</th>
                    <th>Foto</th>
                    <th>Cabang</th>
                    <th>Username</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Aksi</th>
                </tr>
                </tfoot>
            <tbody>
                <?php
                // Query: hanya ambil user dengan role Kasir dan Pegawai,
                // join ke tabel branches untuk mendapatkan nama cabang.
                $query = "SELECT users.*, branches.branch_name 
                            FROM users 
                            LEFT JOIN branches ON users.branch_id = branches.id 
                            WHERE users.role IN ('Kasir','Pegawai')
                            ORDER BY users.id";
                $result = $conn->query($query);
                $counter = 1;
                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                    // Jika ada foto, gunakan path uploads/users/, jika tidak, gunakan placeholder
                    $photoPath = !empty($row['photo']) ? 'uploads/users/' . $row['photo'] : 'https://via.placeholder.com/50';
                    ?>
                    <tr>
                        <td><?php echo $counter++; ?></td>
                        <td>
                        <img src="<?php echo $photoPath; ?>" alt="Foto Profil" width="50" height="50" style="object-fit:cover;">
                        </td>
                        <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['name']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo htmlspecialchars($row['role']); ?></td>
                        <td>
                        <!-- Tombol Lihat -->
                        <button class="btn btn-info btn-sm btn-view-user" 
                                data-id="<?php echo $row['id']; ?>" 
                                data-branch-id="<?php echo $row['branch_id']; ?>" 
                                data-branch-name="<?php echo htmlspecialchars($row['branch_name']); ?>" 
                                data-username="<?php echo htmlspecialchars($row['username']); ?>" 
                                data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                                data-email="<?php echo htmlspecialchars($row['email']); ?>" 
                                data-role="<?php echo htmlspecialchars($row['role']); ?>"
                                data-password="<?php echo htmlspecialchars($row['password']); ?>"
                                data-photo="<?php echo htmlspecialchars($row['photo']); ?>"
                                title="Lihat">
                            <i class="fas fa-eye"></i>
                        </button>
                        <!-- Tombol Edit -->
                        <button class="btn btn-warning btn-sm btn-edit-user" 
                                data-id="<?php echo $row['id']; ?>" 
                                data-branch-id="<?php echo $row['branch_id']; ?>" 
                                data-username="<?php echo htmlspecialchars($row['username']); ?>" 
                                data-name="<?php echo htmlspecialchars($row['name']); ?>" 
                                data-email="<?php echo htmlspecialchars($row['email']); ?>" 
                                data-role="<?php echo htmlspecialchars($row['role']); ?>"
                                title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <!-- Tombol Hapus -->
                        <button class="btn btn-danger btn-sm btn-delete-user" 
                                data-id="<?php echo $row['id']; ?>" 
                                data-username="<?php echo htmlspecialchars($row['username']); ?>"
                                title="Hapus">
                            <i class="fas fa-trash"></i>
                        </button>
                        </td>
                    </tr>
                    <?php
                    }
                } else {
                    echo "<tr><td colspan='8' class='text-center'>Belum ada data user.</td></tr>";
                }
                ?>
            </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah User -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="addUserForm" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="addUserModalLabel">Tambah User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <!-- Pilih Cabang -->
          <div class="mb-3">
            <label for="addUserBranch" class="form-label">Cabang</label>
            <select class="form-control" id="addUserBranch" name="branch_id" required>
              <option value="">Pilih Cabang</option>
              <?php
              $branchQuery = "SELECT * FROM branches ORDER BY branch_name";
              $branchResult = $conn->query($branchQuery);
              if ($branchResult->num_rows > 0) {
                while ($branch = $branchResult->fetch_assoc()) {
                  echo '<option value="'. $branch['id'] .'">'. htmlspecialchars($branch['branch_name']) .'</option>';
                }
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="addUsername" class="form-label">Username</label>
            <input type="text" class="form-control" id="addUsername" name="username" required>
          </div>
          <div class="mb-3">
            <label for="addPassword" class="form-label">Password</label>
            <input type="password" class="form-control" id="addPassword" name="password" required>
          </div>
          <div class="mb-3">
            <label for="addName" class="form-label">Nama</label>
            <input type="text" class="form-control" id="addName" name="name" required>
          </div>
          <div class="mb-3">
            <label for="addEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="addEmail" name="email" required>
          </div>
          <div class="mb-3">
            <label for="addRole" class="form-label">Role</label>
            <select class="form-control" id="addRole" name="role" required>
              <option value="">Pilih Role</option>
              <option value="Kasir">Kasir</option>
              <option value="Pegawai">Pegawai</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="addUserPhoto" class="form-label">Foto Profil</label>
            <input type="file" class="form-control" id="addUserPhoto" name="photo" accept="image/*">
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

<!-- Modal Edit User -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="editUserForm" enctype="multipart/form-data">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editUserModalLabel">Edit User</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="editUserId" name="id">
          <!-- Pilih Cabang -->
          <div class="mb-3">
            <label for="editUserBranch" class="form-label">Cabang</label>
            <select class="form-control" id="editUserBranch" name="branch_id" required>
              <option value="">Pilih Cabang</option>
              <?php
              $branchResult2 = $conn->query($branchQuery);
              if ($branchResult2->num_rows > 0) {
                while ($branch = $branchResult2->fetch_assoc()) {
                  echo '<option value="'. $branch['id'] .'">'. htmlspecialchars($branch['branch_name']) .'</option>';
                }
              }
              ?>
            </select>
          </div>
          <div class="mb-3">
            <label for="editUsername" class="form-label">Username</label>
            <input type="text" class="form-control" id="editUsername" name="username" required>
          </div>
          <div class="mb-3">
            <label for="editPassword" class="form-label">Password (kosongkan jika tidak diubah)</label>
            <input type="password" class="form-control" id="editPassword" name="password">
          </div>
          <div class="mb-3">
            <label for="editName" class="form-label">Nama</label>
            <input type="text" class="form-control" id="editName" name="name" required>
          </div>
          <div class="mb-3">
            <label for="editEmail" class="form-label">Email</label>
            <input type="email" class="form-control" id="editEmail" name="email" required>
          </div>
          <div class="mb-3">
            <label for="editRole" class="form-label">Role</label>
            <select class="form-control" id="editRole" name="role" required>
              <option value="">Pilih Role</option>
              <option value="Kasir">Kasir</option>
              <option value="Pegawai">Pegawai</option>
            </select>
          </div>
          <div class="mb-3">
            <label for="editUserPhoto" class="form-label">Foto Profil (kosongkan jika tidak diubah)</label>
            <input type="file" class="form-control" id="editUserPhoto" name="photo" accept="image/*">
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
<!-- Modal Lihat User -->
<div class="modal fade" id="viewUserModal" tabindex="-1" aria-labelledby="viewUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="viewUserModalLabel">Detail User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" id="viewUserId">
        <div class="mb-3">
          <label for="viewUserBranch" class="form-label">Cabang</label>
          <input type="text" class="form-control" id="viewUserBranch" readonly>
        </div>
        <div class="mb-3">
          <label for="viewUsername" class="form-label">Username</label>
          <input type="text" class="form-control" id="viewUsername" readonly>
        </div>
        <div class="mb-3">
          <label for="viewName" class="form-label">Nama</label>
          <input type="text" class="form-control" id="viewName" readonly>
        </div>
        <div class="mb-3">
          <label for="viewEmail" class="form-label">Email</label>
          <input type="text" class="form-control" id="viewEmail" readonly>
        </div>
        <div class="mb-3">
          <label for="viewRole" class="form-label">Role</label>
          <input type="text" class="form-control" id="viewRole" readonly>
        </div>
        <div class="mb-3">
          <label for="viewPhoto" class="form-label">Foto Profil</label>
          <div id="viewPhotoContainer"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>

<!-- jQuery, Bootstrap 5 Bundle JS, dan SweetAlert2 (pastikan CDN ini sudah ada di layout kamu) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Script untuk handle modal dan AJAX -->
<script>
$(document).ready(function(){
  // Tambah User
  $("#addUserForm").on("submit", function(e){
  e.preventDefault();
  // Buat objek FormData dari form
  var formData = new FormData(this);
  
  $.ajax({
    url: 'process/process_add_user.php',
    method: 'POST',
    data: formData,
    processData: false,  // Jangan ubah data menjadi string
    contentType: false,  // Jangan set contentType, biarkan FormData mengaturnya
    dataType: "json",
    success: function(response){
      if(response.status === "success"){
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: 'User berhasil ditambahkan!',
          timer: 2000,
          showConfirmButton: false
        }).then(() => {
          $("#addUserModal").modal("hide");
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
        text: 'Terjadi kesalahan saat menambahkan user.'
      });
    }
  });
});


  // Lihat User
$(".btn-view-user").on("click", function(){
    var id = $(this).data("id");
    var branch_name = $(this).data("branch-name");
    var username = $(this).data("username");
    var name = $(this).data("name");
    var email = $(this).data("email");
    var role = $(this).data("role");
    var photo = $(this).data("photo"); // pastikan data-photo dikirim

    $("#viewUserId").val(id);
    $("#viewUserBranch").val(branch_name);
    $("#viewUsername").val(username);
    $("#viewName").val(name);
    $("#viewEmail").val(email);
    $("#viewRole").val(role);
    
    // Tampilkan foto jika ada, jika tidak tampilkan placeholder
    var photoPath = photo ? 'uploads/users/' + photo : 'https://via.placeholder.com/150';
    $("#viewPhotoContainer").html('<img src="'+photoPath+'" alt="Foto Profil" class="img-fluid" width="50" height="50"> ');

    $("#viewUserModal").modal("show");
});


  // Edit User: Isi modal dengan data user yang akan diedit
  $(".btn-edit-user").on("click", function(){
    var id = $(this).data("id");
    var branch_id = $(this).data("branch-id");
    var username = $(this).data("username");
    var name = $(this).data("name");
    var email = $(this).data("email");
    var role = $(this).data("role");

    $("#editUserId").val(id);
    $("#editUserBranch").val(branch_id);
    $("#editUsername").val(username);
    $("#editName").val(name);
    $("#editEmail").val(email);
    $("#editRole").val(role);

    $("#editUserModal").modal("show");
  });

  // Submit Edit User
  $("#editUserForm").on("submit", function(e){
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({
        url: 'process/process_edit_user.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: "json",
        success: function(response){
        if(response.status === "success"){
          Swal.fire({
            icon: 'success',
            title: 'Berhasil',
            text: 'User berhasil diperbarui!',
            timer: 2000,
            showConfirmButton: false
          }).then(() => {
            $("#editUserModal").modal("hide");
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
          text: 'Terjadi kesalahan saat memperbarui user.'
        });
      }
    });
  });

  // Hapus User
  $(".btn-delete-user").on("click", function(){
    var id = $(this).data("id");
    var username = $(this).data("username");
    Swal.fire({
      title: 'Apakah Anda yakin?',
      text: "User '" + username + "' akan dihapus!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Ya, hapus!',
      cancelButtonText: 'Batal'
    }).then((result) => {
      if(result.isConfirmed){
        $.ajax({
          url: 'process/process_delete_user.php',
          method: 'POST',
          data: { id: id },
          dataType: "json",
          success: function(response){
            if(response.status === "success"){
              Swal.fire({
                icon: 'success',
                title: 'Berhasil',
                text: 'User berhasil dihapus!',
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
              text: 'Terjadi kesalahan saat menghapus user.'
            });
          }
        });
      }
    });
  });
});
</script>
