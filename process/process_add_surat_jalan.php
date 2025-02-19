<?php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

$user_branch_id = $_SESSION['branch_id'] ?? 0;
$transaction_id = $_POST['transaction_id'] ?? '';
$surat_jalan_number = $_POST['surat_jalan_number'] ?? '';
$sender_name = $_POST['sender_name'] ?? '';

// Pastikan semua field yang diperlukan telah diisi
if(empty($transaction_id) || empty($surat_jalan_number) || empty($sender_name)){
    echo json_encode(["status" => "error", "message" => "Semua field harus diisi."]);
    exit;
}

// Set status langsung menjadi 'sent' (berarti "Berhasil")
$status = 'sent';

$stmt = $conn->prepare("INSERT INTO surat_jalan (transaction_id, branch_id, surat_jalan_number, sender_name, status) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("iisss", $transaction_id, $user_branch_id, $surat_jalan_number, $sender_name, $status);

if($stmt->execute()){
    echo json_encode(["status" => "success", "message" => "Surat jalan berhasil dibuat."]);
} else {
    echo json_encode(["status" => "error", "message" => "Gagal membuat surat jalan."]);
}

$stmt->close();
$conn->close();
?>
