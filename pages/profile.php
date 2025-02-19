<?php
// session_start();
include_once 'config/database.php';

// Pastikan user sudah login
if (!isset($_SESSION['user'])) {
    header('Location: index.php?page=auth/login');
    exit;
}

$userId = $_SESSION['user']['id'];

// Inisialisasi variabel pesan
$errors = [];
$success = "";

// Proses update profil jika form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $name  = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    // Validasi
    if (empty($name)) {
        $errors[] = "Nama tidak boleh kosong.";
    }
    if (empty($email)) {
        $errors[] = "Email tidak boleh kosong.";
    }
    
    // Foto: jika user mengupload file baru
    $photoPath = $_SESSION['user']['photo'] ?? '';
    if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($_FILES['photo']['type'], $allowedTypes)) {
            $errors[] = "Jenis file tidak valid. Hanya JPEG, PNG, atau GIF yang diperbolehkan.";
        } else {
            $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
            $newFileName = 'user_' . $userId . '_' . time() . '.' . $ext;
            $destination = 'uploads/users/' . $newFileName;
            if (move_uploaded_file($_FILES['photo']['tmp_name'], $destination)) {
                $photoPath = $newFileName;
            } else {
                $errors[] = "Gagal mengupload foto.";
            }
        }
    }
    
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, photo = ?, updated_at = NOW() WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $photoPath, $userId);
        if ($stmt->execute()) {
            $success = "Profil berhasil diperbarui.";
            $_SESSION['user']['name'] = $name;
            $_SESSION['user']['email'] = $email;
            $_SESSION['user']['photo'] = $photoPath;
        } else {
            $errors[] = "Gagal memperbarui profil.";
        }
        $stmt->close();
    }
}

// Ambil data terbaru user
$stmt = $conn->prepare("SELECT name, email, photo FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
$stmt->close();

// Ambil data tanda tangan
$stmt = $conn->prepare("SELECT * FROM signatures WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$resultSign = $stmt->get_result();
$signatureData = $resultSign->fetch_assoc();
$stmt->close();

$currentTtdText = $signatureData['ttd_text'] ?? '';
$currentQrCode = isset($signatureData['qr_code_image']) && !empty($signatureData['qr_code_image'])
    ? 'uploads/signatures/' . $signatureData['qr_code_image']
    : 'img/default-signature.png';
?>
    <style>
      .profile-img {
          width: 150px;
          height: 150px;
          object-fit: cover;
      }
    </style>
<div class="container mt-5">
    <h2 class="mb-4">Profil & Tanda Tangan Saya</h2>
    <?php if(!empty($errors)): ?>
      <div class="alert alert-danger">
        <?php foreach($errors as $error): ?>
          <p class="mb-1"><?php echo $error; ?></p>
        <?php endforeach; ?>
      </div>
    <?php elseif($success): ?>
      <div class="alert alert-success">
         <?php echo $success; ?>
      </div>
    <?php endif; ?>

    <div class="row">
      <!-- Kolom Profil -->
      <div class="col-md-6">
        <div class="card shadow mb-4">
          <div class="card-header bg-primary text-white">
              Profil Saya
          </div>
          <div class="card-body">
              <form method="POST" enctype="multipart/form-data">
                  <div class="text-center mb-3">
                      <img src="<?php echo isset($userData['photo']) && !empty($userData['photo']) ? 'uploads/users/' . htmlspecialchars($userData['photo']) : 'img/default-profile.png'; ?>" alt="Foto Profil" class="img-thumbnail profile-img rounded-circle">
                  </div>
                  <div class="mb-3">
                      <label for="name" class="form-label">Nama</label>
                      <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($userData['name']); ?>" required>
                  </div>
                  <div class="mb-3">
                      <label for="email" class="form-label">Email</label>
                      <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($userData['email']); ?>" required>
                  </div>
                  <div class="mb-3">
                      <label for="photo" class="form-label">Ganti Foto Profil</label>
                      <input type="file" class="form-control" id="photo" name="photo">
                  </div>
                  <div class="d-grid">
                      <button type="submit" name="update_profile" class="btn btn-primary">Perbarui Profil</button>
                  </div>
              </form>
          </div>
        </div>
      </div>
      
      <!-- Kolom Tanda Tangan -->
      <div class="col-md-6">
        <div class="card shadow mb-4">
          <div class="card-header bg-danger text-white">
              Atur Tanda Tangan
          </div>
          <div class="card-body">
              <form method="POST" action="process/process_update_signature.php">
                  <div class="mb-3">
                      <label for="ttd_text" class="form-label">Tanda Tangan (Teks)</label>
                      <input type="text" class="form-control" id="ttd_text" name="ttd_text" value="<?php echo htmlspecialchars($currentTtdText); ?>" required>
                      <div class="form-text">Masukkan teks tanda tangan Anda, yang akan di-generate menjadi QR Code.</div>
                  </div>
                  <div class="mb-3 text-center">
                      <img src="<?php echo $currentQrCode; ?>" alt="QR Code Tanda Tangan" class="img-fluid" style="max-width:200px;">
                  </div>
                  <div class="d-grid">
                      <button type="submit" class="btn btn-danger">Perbarui Tanda Tangan</button>
                  </div>
              </form>
          </div>
        </div>
      </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>