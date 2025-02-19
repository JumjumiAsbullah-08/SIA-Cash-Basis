<?php
session_start();
include_once './../config/database.php';
require_once './../vendor/phpqrcode/qrlib.php'; // Pastikan path ini benar sesuai instalasi Anda

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

$userId = $_SESSION['user']['id'];
$ttd_text = trim($_POST['ttd_text'] ?? '');

if (empty($ttd_text)) {
    $_SESSION['error'] = "Tanda tangan tidak boleh kosong.";
    header("Location: ../index.php?page=profile");
    exit;
}

// Folder untuk menyimpan QR Code
$uploadDir = '../uploads/signatures/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}
$qrFileName = 'signature_' . $userId . '_' . time() . '.png';
$qrFilePath = $uploadDir . $qrFileName;

// Generate QR Code
QRcode::png($ttd_text, $qrFilePath, QR_ECLEVEL_L, 4);

// Cek apakah data tanda tangan sudah ada
$stmt = $conn->prepare("SELECT id FROM signatures WHERE user_id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$result = $stmt->get_result();
$exists = $result->num_rows > 0;
$stmt->close();

if ($exists) {
    $stmt = $conn->prepare("UPDATE signatures SET ttd_text = ?, qr_code_image = ?, updated_at = NOW() WHERE user_id = ?");
    $stmt->bind_param("ssi", $ttd_text, $qrFileName, $userId);
} else {
    $stmt = $conn->prepare("INSERT INTO signatures (user_id, ttd_text, qr_code_image) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $ttd_text, $qrFileName);
}

if ($stmt->execute()) {
    $_SESSION['success'] = "Tanda tangan berhasil diperbarui.";
} else {
    $_SESSION['error'] = "Gagal memperbarui tanda tangan.";
}
$stmt->close();
header("Location: ../index.php?page=profile");
exit;
?>
