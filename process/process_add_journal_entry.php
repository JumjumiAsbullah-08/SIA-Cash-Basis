<?php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

$entry_date = trim($_POST['entry_date'] ?? '');
$branch_id = trim($_POST['branch_id'] ?? '');
$cost_category_id = trim($_POST['cost_category_id'] ?? '');
$ref_number = trim($_POST['ref_number'] ?? '');
$description = trim($_POST['description'] ?? '');
// Ambil nilai debit dan kredit (tidak ada transaction_type lagi)
$debit = trim($_POST['debit'] ?? '0.00');
$credit = trim($_POST['credit'] ?? '0.00');

if(empty($entry_date) || empty($branch_id) || empty($cost_category_id) || empty($ref_number)){
  echo json_encode(["status" => "error", "debug" => "Tanggal, Cabang, Kategori, dan Nomor Ref harus diisi."]);
  exit;
}

// Jika tipe transaksi adalah debit, maka pastikan credit = 0, jika tidak, debit = 0
// Namun di sini, asumsi pengguna telah memilih input yang tepat.
if($debit > 0){
  $credit = 0.00;
} else {
  $debit = 0.00;
}

$stmt = $conn->prepare("INSERT INTO journal_entries (entry_date, branch_id, cost_category_id, ref_number, description, debit, credit, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, NOW())");
if(!$stmt){
  echo json_encode(["status" => "error", "debug" => $conn->error]);
  exit;
}
$stmt->bind_param("siissdd", $entry_date, $branch_id, $cost_category_id, $ref_number, $description, $debit, $credit);
if($stmt->execute()){
    echo json_encode(["status" => "success", "debug" => "Jurnal berhasil ditambahkan."]);
} else {
    echo json_encode(["status" => "error", "debug" => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
