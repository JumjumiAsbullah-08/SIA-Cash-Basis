<?php
session_start();
include_once __DIR__ . './../config/database.php';

header('Content-Type: application/json');

// Pastikan request POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'debug' => 'Invalid request method']);
    exit;
}

// Ambil data dari form
$branch_id  = $_POST['branch_id']  ?? '';
$item_name  = $_POST['item_name']  ?? '';
// Kode barang akan digenerate otomatis
$category   = $_POST['category']   ?? '';
$stock      = $_POST['stock']      ?? '';
$price      = $_POST['price']      ?? '';

// Validasi input wajib
if (!$branch_id || !$item_name || !$category || $stock === '' || $price === '') {
    echo json_encode(['status' => 'error', 'debug' => 'Missing required fields']);
    exit;
}

// Jika user adalah Kasir, pastikan branch_id dari form sesuai dengan session
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;
if ($role === 'Kasir' && $branch_id != $user_branch_id) {
    echo json_encode(['status' => 'error', 'debug' => 'Invalid branch selection for Kasir']);
    exit;
}

// Generate kode barang secara otomatis: BRG-YYMMDD-RAND4
$item_code = 'BRG-' . date('ymd') . '-' . rand(1000, 9999);

// Siapkan prepared statement untuk insert data
$stmt = $conn->prepare("INSERT INTO items (branch_id, item_name, item_code, category, stock, price, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
if (!$stmt) {
    echo json_encode(['status' => 'error', 'debug' => 'Prepare Error: ' . $conn->error]);
    exit;
}
$stmt->bind_param("isssid", $branch_id, $item_name, $item_code, $category, $stock, $price);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'debug' => 'Execute Error: ' . $stmt->error]);
}
?>
