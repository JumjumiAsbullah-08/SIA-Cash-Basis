<?php
session_start();
include_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$posting_date = $_POST['posting_date'] ?? '';
$account_id   = $_POST['account_id'] ?? '';
$description  = $_POST['description'] ?? '';
$debit        = $_POST['debit'] ?? '0.00';
$credit       = $_POST['credit'] ?? '0.00';
$reference    = $_POST['reference'] ?? '';

// Validasi: posting_date dan account_id wajib
if(empty($posting_date) || empty($account_id)){
  echo json_encode(["status" => "error", "debug" => "Tanggal posting dan akun harus diisi."]);
  exit;
}

// Dapatkan account_name dari tabel cost_categories
$account_name = "";
$stmt = $conn->prepare("SELECT category_name FROM cost_categories WHERE id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$stmt->bind_result($account_name);
$stmt->fetch();
$stmt->close();

// Hitung saldo berjalan terakhir untuk akun tersebut
$lastBalance = 0.00;
$stmt = $conn->prepare("SELECT running_balance FROM general_ledger WHERE account_id = ? ORDER BY posting_date DESC, id DESC LIMIT 1");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$stmt->bind_result($lastBalance);
$stmt->fetch();
$stmt->close();

// Hitung saldo baru
$newBalance = $lastBalance + ($debit - $credit);

// Karena posting manual ini tidak berasal dari jurnal_entries, kita set journal_entry_id ke NULL
$journal_entry_id = null;

// Siapkan query INSERT
$sql = "INSERT INTO general_ledger 
(posting_date, account_id, account_name, journal_entry_id, description, debit, credit, running_balance, reference, created_at) 
VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
$stmt = $conn->prepare($sql);
if(!$stmt){
  echo json_encode(["status" => "error", "debug" => $conn->error]);
  exit;
}

// Format binding string: 
// posting_date (s), account_id (i), account_name (s), journal_entry_id (i) -> null, description (s), debit (d), credit (d), running_balance (d), reference (s)
// Format: "sisisddds" (9 parameter)
$stmt->bind_param("sisisddds", $posting_date, $account_id, $account_name, $journal_entry_id, $description, $debit, $credit, $newBalance, $reference);

if($stmt->execute()){
    echo json_encode(["status" => "success", "debug" => "Posting buku besar berhasil ditambahkan."]);
} else {
    echo json_encode(["status" => "error", "debug" => $stmt->error]);
}
$stmt->close();
$conn->close();
?>
