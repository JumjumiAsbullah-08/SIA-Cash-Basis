<?php
session_start();
include_once __DIR__ . './../config/database.php';

$id = $_POST['id'] ?? 0;
$branch_name = $_POST['branch_name'] ?? '';
$address = $_POST['address'] ?? '';

$stmt = $conn->prepare("UPDATE branches SET branch_name = ?, address = ? WHERE id = ?");
$stmt->bind_param("ssi", $branch_name, $address, $id);
if($stmt->execute()){
    echo "success";
} else {
    http_response_code(500);
    echo "error";
}
$stmt->close();
$conn->close();
?>
