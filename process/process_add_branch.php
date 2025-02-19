<?php
session_start();
include_once __DIR__ . './../config/database.php';

$branch_name = $_POST['branch_name'] ?? '';
$address = $_POST['address'] ?? '';

$stmt = $conn->prepare("INSERT INTO branches (branch_name, address) VALUES (?, ?)");
$stmt->bind_param("ss", $branch_name, $address);
if($stmt->execute()){
    echo "success";
} else {
    http_response_code(500);
    echo "error";
}
$stmt->close();
$conn->close();
?>
