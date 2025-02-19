<?php
session_start();
include_once __DIR__ . '/../config/database.php';

$id = $_POST['id'] ?? 0;
if ($id > 0) {
    $stmt = $conn->prepare("DELETE FROM branches WHERE id = ?");
    $stmt->bind_param("i", $id);
    if ($stmt->execute()) {
        echo "success";
    } else {
        http_response_code(500);
        echo "error";
    }
    $stmt->close();
} else {
    http_response_code(400);
    echo "error";
}
$conn->close();
?>
