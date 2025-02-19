<?php
session_start();
include_once __DIR__ . '/../config/database.php'; // Perbaiki include path
header('Content-Type: application/json');

$id        = $_POST['id'] ?? '';
$branch_id = $_POST['branch_id'] ?? '';
$username  = trim($_POST['username'] ?? '');
$password  = $_POST['password'] ?? '';
$name      = $_POST['name'] ?? '';
$email     = trim($_POST['email'] ?? '');
$role      = $_POST['role'] ?? '';

// Validasi: Semua field kecuali password harus diisi
if(empty($id) || empty($branch_id) || empty($username) || empty($name) || empty($email) || empty($role)){
  echo json_encode(["status" => "error", "debug" => "Semua field kecuali password harus diisi."]);
  exit;
}

// Validasi Username: tidak boleh mengandung huruf besar atau spasi
if(preg_match('/[A-Z\s]/', $username)){
  echo json_encode(["status" => "error", "debug" => "Username tidak boleh mengandung huruf besar atau spasi."]);
  exit;
}

// Validasi: Email harus unik (selain user ini)
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
if(!$stmt){
  echo json_encode(["status" => "error", "debug" => $conn->error]);
  exit;
}
$stmt->bind_param("si", $email, $id);
$stmt->execute();
$stmt->store_result();
if($stmt->num_rows > 0){
  echo json_encode(["status" => "error", "debug" => "Email sudah terdaftar untuk user lain."]);
  $stmt->close();
  exit;
}
$stmt->close();

// Proses Upload Foto (jika ada)
// Pastikan form edit menggunakan enctype="multipart/form-data" dan dikirim dengan FormData di JS
$photo = '';
if(isset($_FILES['photo']) && $_FILES['photo']['error'] == 0){
    $uploadDir = __DIR__ . '/../uploads/users/';
    if(!is_dir($uploadDir)) {
        if(!mkdir($uploadDir, 0755, true)){
            echo json_encode(["status" => "error", "debug" => "Gagal membuat direktori upload: " . $uploadDir]);
            exit;
        }
    }
    $filename = time() . '_' . basename($_FILES['photo']['name']);
    $targetFile = $uploadDir . $filename;
    if(move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)){
        $photo = $filename;
    } else {
        echo json_encode(["status" => "error", "debug" => "Gagal mengupload file foto."]);
        exit;
    }
}

// Logika update: Jika password diisi, update dengan MD5; jika tidak, tidak mengubah password.
if(!empty($password)){
  $hashedPassword = md5($password);
  if(!empty($photo)){
      // Update termasuk foto dan password
      $stmt = $conn->prepare("UPDATE users SET branch_id=?, username=?, password=?, role=?, name=?, email=?, photo=?, updated_at=NOW() WHERE id=?");
      if(!$stmt){
          echo json_encode(["status" => "error", "debug" => "Prepare failed: " . $conn->error]);
          exit;
      }
      $stmt->bind_param("issssssi", $branch_id, $username, $hashedPassword, $role, $name, $email, $photo, $id);
  } else {
      // Update tanpa foto, tapi dengan password
      $stmt = $conn->prepare("UPDATE users SET branch_id=?, username=?, password=?, role=?, name=?, email=?, updated_at=NOW() WHERE id=?");
      if(!$stmt){
          echo json_encode(["status" => "error", "debug" => "Prepare failed: " . $conn->error]);
          exit;
      }
      $stmt->bind_param("isssssi", $branch_id, $username, $hashedPassword, $role, $name, $email, $id);
  }
} else {
  // Password tidak diubah
  if(!empty($photo)){
      // Update termasuk foto
      $stmt = $conn->prepare("UPDATE users SET branch_id=?, username=?, role=?, name=?, email=?, photo=?, updated_at=NOW() WHERE id=?");
      if(!$stmt){
          echo json_encode(["status" => "error", "debug" => "Prepare failed: " . $conn->error]);
          exit;
      }
      $stmt->bind_param("isssssi", $branch_id, $username, $role, $name, $email, $photo, $id);
  } else {
      // Update tanpa foto dan tanpa password baru
      $stmt = $conn->prepare("UPDATE users SET branch_id=?, username=?, role=?, name=?, email=?, updated_at=NOW() WHERE id=?");
      if(!$stmt){
          echo json_encode(["status" => "error", "debug" => "Prepare failed: " . $conn->error]);
          exit;
      }
      $stmt->bind_param("issssi", $branch_id, $username, $role, $name, $email, $id);
  }
}

if($stmt->execute()){
    echo json_encode(["status" => "success", "debug" => "Update executed successfully."]);
} else {
    echo json_encode(["status" => "error", "debug" => "Execute failed: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>
