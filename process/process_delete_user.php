<?php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? '';

if(empty($id)){
  echo json_encode(["status" => "error", "debug" => "ID is required."]);
  exit;
}

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
if(!$stmt){
  echo json_encode(["status" => "error", "debug" => $conn->error]);
  exit;
}
$stmt->bind_param("i", $id);

if($stmt->execute()){
    echo json_encode(["status" => "success", "debug" => "Delete executed successfully."]);
} else {
    echo json_encode(["status" => "error", "debug" => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
