<?php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

// Ambil dan trim input dari form edit
$id               = trim($_POST['id'] ?? '');
$entry_date       = trim($_POST['entry_date'] ?? '');
$branch_id        = trim($_POST['branch_id'] ?? '');
$cost_category_id = trim($_POST['cost_category_id'] ?? '');
$ref_number       = trim($_POST['ref_number'] ?? '');
$description      = trim($_POST['description'] ?? '');
$transaction_type = trim($_POST['transaction_type'] ?? 'debit'); // 'debit' atau 'kredit'
$debit_input      = trim($_POST['debit'] ?? '0.00');
$credit_input     = trim($_POST['credit'] ?? '0.00');

// Debug: log nilai awal
error_log("DEBUG: id=$id, entry_date=$entry_date, branch_id=$branch_id, cost_category_id=$cost_category_id, ref_number=$ref_number, description=$description, transaction_type=$transaction_type, debit_input=$debit_input, credit_input=$credit_input");

// Validasi: Pastikan field wajib diisi
if (empty($id) || empty($entry_date) || empty($branch_id) || empty($cost_category_id) || empty($ref_number)) {
    echo json_encode(["status" => "error", "debug" => "Field wajib harus diisi (ID, Tanggal, Cabang, Kategori, Nomor Ref)."]);
    exit;
}

// Berdasarkan transaction_type, tentukan nilai debit dan credit.
// Jika transaction_type adalah "debit", maka nilai kredit harus di-set 0.
// Jika "kredit", nilai debit di-set 0.
if ($transaction_type === 'debit') {
    $debit = floatval($debit_input);
    $credit = 0.00;
} elseif ($transaction_type === 'kredit') {
    $credit = floatval($credit_input);
    $debit = 0.00;
} else {
    echo json_encode(["status" => "error", "debug" => "Invalid transaction type: $transaction_type"]);
    exit;
}

// Debug: log nilai final yang akan disimpan
error_log("DEBUG FINAL: debit=$debit, credit=$credit");

$sql = "UPDATE journal_entries 
        SET entry_date = ?, 
            branch_id = ?, 
            cost_category_id = ?, 
            ref_number = ?, 
            description = ?, 
            debit = ?, 
            credit = ?, 
            updated_at = NOW() 
        WHERE id = ?";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    error_log("Prepare Error: " . $conn->error);
    echo json_encode(["status" => "error", "debug" => $conn->error]);
    exit;
}

$stmt->bind_param("siissddi", $entry_date, $branch_id, $cost_category_id, $ref_number, $description, $debit, $credit, $id);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "debug" => "Journal entry berhasil diperbarui."]);
} else {
    error_log("Execute Error: " . $stmt->error);
    echo json_encode(["status" => "error", "debug" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
