<?php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? '';

if (empty($id)) {
    echo json_encode(["status" => "error", "debug" => "ID kategori tidak boleh kosong."]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM cost_categories WHERE id = ?");
if (!$stmt) {
    echo json_encode(["status" => "error", "debug" => "Gagal menyiapkan query."]);
    exit;
}

$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    echo json_encode(["status" => "success", "debug" => "Kategori biaya berhasil dihapus."]);
} else {
    $error = $stmt->error;
    // Cek jika error disebabkan oleh foreign key constraint
    if (strpos($error, 'Cannot delete or update a parent row') !== false) {
        $error = "Kategori ini tidak bisa dihapus karena masih digunakan dalam jurnal umum.";
    }
    echo json_encode(["status" => "error", "debug" => $error]);
}

$stmt->close();
$conn->close();
?>
