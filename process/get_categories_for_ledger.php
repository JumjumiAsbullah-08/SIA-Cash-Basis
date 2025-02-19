<?php
// process/get_categories_for_ledger.php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

$branch_id = $_POST['branch_id'] ?? '';
$period    = $_POST['period'] ?? '';

if(empty($branch_id) || empty($period)){
    echo json_encode(["status" => "error", "debug" => "Branch dan periode harus diisi."]);
    exit;
}

// Ambil DISTINCT kategori yang digunakan di jurnal
$query = "SELECT DISTINCT je.cost_category_id AS id, cc.category_name AS name
          FROM journal_entries je
          LEFT JOIN cost_categories cc ON je.cost_category_id = cc.id
          WHERE je.branch_id = ? 
            AND DATE_FORMAT(je.entry_date, '%Y-%m') = ?
          ORDER BY cc.category_name";

$stmt = $conn->prepare($query);
if(!$stmt){
    echo json_encode(["status" => "error", "debug" => $conn->error]);
    exit;
}
$stmt->bind_param("is", $branch_id, $period);
$stmt->execute();
$result = $stmt->get_result();

$data = [];
while($row = $result->fetch_assoc()){
    // Pastikan data id & name valid
    $data[] = [
        "id"   => $row['id'],
        "name" => $row['name']
    ];
}

$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "data" => $data]);
