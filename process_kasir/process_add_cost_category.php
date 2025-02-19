<?php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

$branch_id     = $_POST['branch_id'] ?? '';
$ref_number   = $_POST['ref_number'] ?? '';
$category_name = $_POST['category_name'] ?? '';
$description   = $_POST['description'] ?? '';
$status        = $_POST['status'] ?? '1'; // default aktif

if(empty($branch_id) || empty($ref_number) || empty($category_name)){
  echo json_encode(["status" => "error", "debug" => "Cabang, Nomor Ref., dan Nama Kategori harus diisi."]);
  exit;
}

$stmt = $conn->prepare("INSERT INTO cost_categories (branch_id, ref_number, category_name, description, status, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
if(!$stmt){
  echo json_encode(["status" => "error", "debug" => $conn->error]);
  exit;
}
$stmt->bind_param("isssi", $branch_id, $ref_number, $category_name, $description, $status);
if($stmt->execute()){
    echo json_encode(["status" => "success", "debug" => "Kategori biaya berhasil ditambahkan."]);
} else {
    echo json_encode(["status" => "error", "debug" => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
