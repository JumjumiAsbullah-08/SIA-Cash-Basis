<?php
session_start();
include_once __DIR__ . './../config/database.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode(['status' => 'error', 'debug' => 'Invalid request method']);
    exit;
}

$id = $_POST['id'] ?? '';
$qty = $_POST['qty'] ?? '';
if(!$id || !$qty || $qty <= 0){
    echo json_encode(['status' => 'error', 'debug' => 'Missing or invalid parameters']);
    exit;
}

$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;
if($role === 'Kasir'){
    $checkStmt = $conn->prepare("SELECT id FROM items WHERE id = ? AND branch_id = ?");
    $checkStmt->bind_param("ii", $id, $user_branch_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if($checkResult->num_rows == 0){
        echo json_encode(['status' => 'error', 'debug' => 'Unauthorized action']);
        exit;
    }
}

$stmt = $conn->prepare("UPDATE items SET stock = stock + ?, updated_at = NOW() WHERE id = ?");
if(!$stmt){
    echo json_encode(['status' => 'error', 'debug' => 'Prepare Error: ' . $conn->error]);
    exit;
}
$stmt->bind_param("ii", $qty, $id);
if($stmt->execute()){
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'debug' => 'Execute Error: ' . $stmt->error]);
}
?>
