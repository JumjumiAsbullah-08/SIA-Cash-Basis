<?php
session_start();
include_once __DIR__ . './../config/database.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'debug' => 'Invalid request method']);
    exit;
}

$id         = $_POST['id']         ?? '';
$branch_id  = $_POST['branch_id']  ?? '';
$item_name  = $_POST['item_name']  ?? '';
$category   = $_POST['category']   ?? '';
$stock      = $_POST['stock']      ?? '';
$price      = $_POST['price']      ?? '';

if (!$id || !$branch_id || !$item_name || !$category || $stock === '' || $price === '') {
    echo json_encode(['status' => 'error', 'debug' => 'Missing required fields']);
    exit;
}

$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;
// Jika user adalah Kasir, pastikan data yang diedit milik cabangnya
if ($role === 'Kasir') {
    if ($branch_id != $user_branch_id) {
        echo json_encode(['status' => 'error', 'debug' => 'Invalid branch selection for Kasir']);
        exit;
    }
    // Pastikan data yang diedit milik cabang user
    $checkStmt = $conn->prepare("SELECT id FROM items WHERE id = ? AND branch_id = ?");
    $checkStmt->bind_param("ii", $id, $user_branch_id);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if ($checkResult->num_rows == 0) {
        echo json_encode(['status' => 'error', 'debug' => 'Unauthorized action']);
        exit;
    }
}

// Perbaikan: Gunakan "issidi" karena parameter: branch_id (i), item_name (s), category (s), stock (i), price (d), id (i)
$stmt = $conn->prepare("UPDATE items SET branch_id = ?, item_name = ?, category = ?, stock = ?, price = ?, updated_at = NOW() WHERE id = ?");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'debug' => 'Prepare Error: ' . $conn->error]);
    exit;
}
$stmt->bind_param("issidi", $branch_id, $item_name, $category, $stock, $price, $id);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'debug' => 'Execute Error: ' . $stmt->error]);
}
?>
