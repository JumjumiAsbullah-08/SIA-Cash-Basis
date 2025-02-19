<?php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

$id            = $_POST['id'] ?? '';
$branch_id     = $_POST['branch_id'] ?? '';
$ref_number   = $_POST['ref_number'] ?? '';
$category_name = $_POST['category_name'] ?? '';
$description   = $_POST['description'] ?? '';
$status        = $_POST['status'] ?? '';

if(empty($id) || empty($branch_id) || empty($ref_number) || empty($category_name)){
  echo json_encode(["status" => "error", "debug" => "ID, Cabang, Nomor Ref., dan Nama Kategori harus diisi."]);
  exit;
}

$stmt = $conn->prepare("UPDATE cost_categories SET branch_id = ?, ref_number = ?, category_name = ?, description = ?, status = ?, updated_at = NOW() WHERE id = ?");
if(!$stmt){
  echo json_encode(["status" => "error", "debug" => $conn->error]);
  exit;
}
$stmt->bind_param("issssi", $branch_id, $ref_number, $category_name, $description, $status, $id);
if($stmt->execute()){
    echo json_encode(["status" => "success", "debug" => "Kategori biaya berhasil diperbarui."]);
} else {
    echo json_encode(["status" => "error", "debug" => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
