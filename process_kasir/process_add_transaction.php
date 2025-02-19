<?php
session_start();
include_once __DIR__ . './../config/database.php';

header('Content-Type: application/json');

if($_SERVER['REQUEST_METHOD'] !== 'POST'){
    echo json_encode(['status'=>'error','debug'=>'Invalid request method']);
    exit;
}

// Ambil data header transaksi
$invoice_number   = $_POST['invoice_number'] ?? '';
$buyer_name       = $_POST['buyer_name'] ?? '';
$branch_id        = $_POST['branch_id'] ?? ''; // dari hidden input
$grand_total      = $_POST['grand_total'] ?? '';
$transaction_date = $_POST['transaction_date'] ?? date('Y-m-d H:i:s');

// Ambil data detail transaksi (sebagai array)
$item_ids    = $_POST['item_id'] ?? [];
$prices      = $_POST['price'] ?? [];
$quantities  = $_POST['quantity'] ?? [];
$line_totals = $_POST['line_total'] ?? [];

// Validasi header dan minimal satu item
if(!$invoice_number || !$buyer_name || !$branch_id || !$grand_total || empty($item_ids)){
    echo json_encode(['status'=>'error','debug'=>'Missing required fields']);
    exit;
}

// Mulai transaksi database
$conn->begin_transaction();

// Simpan header transaksi
$stmt = $conn->prepare("INSERT INTO transactions (invoice_number, buyer_name, branch_id, total_amount, transaction_date) VALUES (?, ?, ?, ?, ?)");
if(!$stmt){
    echo json_encode(['status'=>'error','debug'=>'Prepare Error: '.$conn->error]);
    exit;
}
$stmt->bind_param("ssids", $invoice_number, $buyer_name, $branch_id, $grand_total, $transaction_date);
if(!$stmt->execute()){
    $conn->rollback();
    echo json_encode(['status'=>'error','debug'=>'Execute Error (transactions): '.$stmt->error]);
    exit;
}
$transaction_id = $conn->insert_id;
$stmt->close();

// Simpan tiap item transaksi dan update stok barang
$insertStmt = $conn->prepare("INSERT INTO transaction_items (transaction_id, item_id, quantity, price, total, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
if(!$insertStmt){
    $conn->rollback();
    echo json_encode(['status'=>'error','debug'=>'Prepare Error (transaction_items): '.$conn->error]);
    exit;
}

for($i = 0; $i < count($item_ids); $i++){
    $item_id = $item_ids[$i];
    $price = $prices[$i];
    $quantity = $quantities[$i];
    $total = $line_totals[$i];
    
    // Periksa stok yang tersedia
    $checkStmt = $conn->prepare("SELECT stock FROM items WHERE id = ?");
    $checkStmt->bind_param("i", $item_id);
    $checkStmt->execute();
    $stockResult = $checkStmt->get_result();
    if($stockRow = $stockResult->fetch_assoc()){
         if($quantity > $stockRow['stock']){
             $conn->rollback();
             echo json_encode(['status'=>'error','debug'=>'Quantity untuk item '.$item_id.' melebihi stok yang tersedia.']);
             exit;
         }
    }
    $checkStmt->close();
    
    // Simpan detail transaksi
    $insertStmt->bind_param("iiisd", $transaction_id, $item_id, $quantity, $price, $total);
    if(!$insertStmt->execute()){
        $conn->rollback();
        echo json_encode(['status'=>'error','debug'=>'Execute Error (transaction_items): '.$insertStmt->error]);
        exit;
    }
    
    // Update stok barang: kurangi dengan quantity yang dijual
    $updateStmt = $conn->prepare("UPDATE items SET stock = stock - ? WHERE id = ?");
    $updateStmt->bind_param("ii", $quantity, $item_id);
    if(!$updateStmt->execute()){
        $conn->rollback();
        echo json_encode(['status'=>'error','debug'=>'Execute Error (update stock): '.$updateStmt->error]);
        exit;
    }
    $updateStmt->close();
}
$insertStmt->close();

// Commit transaksi
$conn->commit();
echo json_encode(['status'=>'success', 'transaction_id' => $transaction_id]);
?>
