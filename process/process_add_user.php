<?php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

// Ambil data dari form
$branch_id = $_POST['branch_id'] ?? '';
$username  = trim($_POST['username'] ?? '');
$password  = $_POST['password'] ?? '';
$name      = $_POST['name'] ?? '';
$email     = trim($_POST['email'] ?? '');
$role      = $_POST['role'] ?? '';

// Validasi field wajib
if(empty($branch_id) || empty($username) || empty($password) || empty($name) || empty($email) || empty($role)){
  echo json_encode(["status" => "error", "debug" => "Semua field harus diisi."]);
  exit;
}

// Debug: Tampilkan path upload yang dihitung
$uploadDir = __DIR__ . '/../uploads/users/';
error_log("Current __DIR__: " . __DIR__);
error_log("Calculated uploadDir: " . $uploadDir);

// Proses Upload Foto (jika ada)
$photo = '';
if(isset($_FILES['photo'])) {
    // Cek error upload
    if ($_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode([
            "status" => "error",
            "debug" => "Upload error code: " . $_FILES['photo']['error'] . ". " . file_upload_error_message($_FILES['photo']['error'])
        ]);
        exit;
    }
    
    // Pastikan folder upload ada
    if (!is_dir($uploadDir)) {
        if(!mkdir($uploadDir, 0777, true)){
            echo json_encode([
                "status" => "error",
                "debug" => "Gagal membuat direktori upload: " . $uploadDir
            ]);
            exit;
        }
    }
    
    // Cek apakah direktori writable
    if(!is_writable($uploadDir)){
        echo json_encode([
            "status" => "error",
            "debug" => "Direktori upload tidak writable: " . $uploadDir
        ]);
        exit;
    }
    
    $filename = time() . '_' . basename($_FILES['photo']['name']);
    $targetFile = $uploadDir . $filename;
    
    if(move_uploaded_file($_FILES['photo']['tmp_name'], $targetFile)){
        $photo = $filename;
    } else {
        $lastError = error_get_last();
        echo json_encode([
            "status" => "error",
            "debug" => "Gagal mengupload file foto. Target file: $targetFile. Last error: " . json_encode($lastError)
        ]);
        exit;
    }
}

// Fungsi untuk mengubah kode error upload menjadi pesan
function file_upload_error_message($error_code) {
    switch ($error_code) {
        case UPLOAD_ERR_INI_SIZE:
            return "Ukuran file melebihi batas upload yang diizinkan.";
        case UPLOAD_ERR_FORM_SIZE:
            return "Ukuran file melebihi batas yang diizinkan oleh form.";
        case UPLOAD_ERR_PARTIAL:
            return "File hanya terupload sebagian.";
        case UPLOAD_ERR_NO_FILE:
            return "Tidak ada file yang diupload.";
        case UPLOAD_ERR_NO_TMP_DIR:
            return "Folder temporary tidak tersedia.";
        case UPLOAD_ERR_CANT_WRITE:
            return "Gagal menulis file ke disk.";
        case UPLOAD_ERR_EXTENSION:
            return "Upload diblokir oleh ekstensi PHP.";
        default:
            return "Unknown upload error.";
    }
}

// Gunakan MD5 untuk password
$hashedPassword = md5($password);

// Insert data user dengan foto
$stmt = $conn->prepare("INSERT INTO users (branch_id, username, password, role, name, email, photo, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), NOW())");
if(!$stmt){
  echo json_encode(["status" => "error", "debug" => "Prepare failed: " . $conn->error]);
  exit;
}
$stmt->bind_param("issssss", $branch_id, $username, $hashedPassword, $role, $name, $email, $photo);
if($stmt->execute()){
    echo json_encode(["status" => "success", "debug" => "Insert executed successfully."]);
} else {
    echo json_encode(["status" => "error", "debug" => "Execute failed: " . $stmt->error]);
}
$stmt->close();
$conn->close();
?>
