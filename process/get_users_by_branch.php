<?php
session_start();
include_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$branch_id = $_POST['branch_id'] ?? '';

if(empty($branch_id)){
  echo json_encode(["status" => "error", "debug" => "Branch ID tidak diberikan."]);
  exit;
}

$stmt = $conn->prepare("SELECT id, name, username FROM users WHERE branch_id = ?");
if(!$stmt){
  echo json_encode(["status" => "error", "debug" => $conn->error]);
  exit;
}
$stmt->bind_param("i", $branch_id);
$stmt->execute();
$result = $stmt->get_result();
$users = [];
while($row = $result->fetch_assoc()){
  $users[] = $row;
}
$stmt->close();
$conn->close();
echo json_encode(["status" => "success", "data" => $users]);
?>
