<?php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

$id = $_POST['id'] ?? '';
$posting_date = $_POST['posting_date'] ?? '';
$account_id = $_POST['account_id'] ?? '';
$description = $_POST['description'] ?? '';
$debit = $_POST['debit'] ?? '0.00';
$credit = $_POST['credit'] ?? '0.00';
$reference = $_POST['reference'] ?? '';

if(empty($id) || empty($posting_date) || empty($account_id)){
  echo json_encode(["status" => "error", "debug" => "Tanggal posting dan akun harus diisi."]);
  exit;
}

// Catatan: Editing posting buku besar yang sudah di-posting seharusnya 
// memicu reposting untuk saldo berjalan. Di sini, kita hanya update data
// pada baris yang dipilih (tanpa recalculation saldo seluruh akun).

$stmt = $conn->prepare("UPDATE general_ledger SET posting_date = ?, account_id = ?, description = ?, debit = ?, credit = ?, reference = ?, updated_at = NOW() WHERE id = ?");
if(!$stmt){
  echo json_encode(["status" => "error", "debug" => $conn->error]);
  exit;
}
$stmt->bind_param("sisdssi", $posting_date, $account_id, $description, $debit, $credit, $reference, $id);
if($stmt->execute()){
    echo json_encode(["status" => "success", "debug" => "Posting buku besar berhasil diperbarui."]);
} else {
    echo json_encode(["status" => "error", "debug" => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
