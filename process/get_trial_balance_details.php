<?php
// process/get_trial_balance_details.php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}

$branch_id = $_POST['branch_id'] ?? '';
$period    = $_POST['period'] ?? ''; // Format: YYYY-MM

if(empty($branch_id) || empty($period)){
    echo json_encode(["status" => "error", "debug" => "Branch dan periode harus diisi."]);
    exit;
}

// Query untuk mengambil detail jurnal sesuai branch dan periode
$query = "SELECT je.ref_number, cc.category_name, je.debit, je.credit
          FROM journal_entries je
          LEFT JOIN cost_categories cc ON je.cost_category_id = cc.id
          WHERE je.branch_id = ? AND DATE_FORMAT(je.entry_date, '%Y-%m') = ?
          ORDER BY je.entry_date ASC, je.ref_number ASC";
$stmt = $conn->prepare($query);
if(!$stmt){
    echo json_encode(["status" => "error", "debug" => $conn->error]);
    exit;
}
$stmt->bind_param("is", $branch_id, $period);
$stmt->execute();
$result = $stmt->get_result();

$html = '<table class="table table-bordered table-sm">';
$html .= '<thead>';
$html .= '  <tr>';
$html .= '    <th>No</th>';
$html .= '    <th>Nomor Ref</th>';
$html .= '    <th>Nama Kategori</th>';
$html .= '    <th class="text-end">Debit</th>';
$html .= '    <th class="text-end">Kredit</th>';
$html .= '    <th class="text-end">Total</th>';
$html .= '  </tr>';
$html .= '</thead>';
$html .= '<tbody>';

$counter = 1;
$totalDebit = 0;
$totalCredit = 0;
$totalNet = 0;
while($row = $result->fetch_assoc()){
    $debit = floatval($row['debit']);
    $credit = floatval($row['credit']);
    $net = $debit - $credit; // Net per baris
    
    $totalDebit += $debit;
    $totalCredit += $credit;
    $totalNet += $net;
    
    // Format kolom Total per baris
    if($net > 0){
        $formattedNet = '<span class="fw-bold" style="color:green;">' . formatRupiah($net) . '</span>';
    } elseif($net < 0){
        $formattedNet = '<span class="fw-bold" style="color:red;">' . formatRupiah(abs($net)) . '</span>';
    } else {
        $formattedNet = '-';
    }
    
    $html .= '<tr>';
    $html .= '  <td>' . $counter++ . '</td>';
    $html .= '  <td>' . htmlspecialchars($row['ref_number']) . '</td>';
    $html .= '  <td>' . htmlspecialchars($row['category_name']) . '</td>';
    $html .= '  <td class="text-end">' . ($debit > 0 ? formatRupiah($debit) : '-') . '</td>';
    $html .= '  <td class="text-end">' . ($credit > 0 ? formatRupiah($credit) : '-') . '</td>';
    $html .= '  <td class="text-end">' . $formattedNet . '</td>';
    $html .= '</tr>';
}
$html .= '</tbody>';

$html .= '<tfoot>';
$html .= '  <tr>';
$html .= '    <td colspan="3" class="text-end fw-bold">Total</td>';
$html .= '    <td class="text-end fw-bold">' . ($totalDebit > 0 ? formatRupiah($totalDebit) : '-') . '</td>';
$html .= '    <td class="text-end fw-bold">' . ($totalCredit > 0 ? formatRupiah($totalCredit) : '-') . '</td>';
if($totalNet > 0){
    $formattedTotalNet = '<span class="fw-bold" style="color:green;">' . formatRupiah($totalNet) . '</span>';
} elseif($totalNet < 0){
    $formattedTotalNet = '<span class="fw-bold" style="color:red;">' . formatRupiah(abs($totalNet)) . '</span>';
} else {
    $formattedTotalNet = '-';
}
$html .= '    <td class="text-end fw-bold">' . $formattedTotalNet . '</td>';
$html .= '  </tr>';

// Baris status Neraca Saldo (SEIMBANG / TIDAK SEIMBANG)
if($totalNet == 0){
    $status = "SEIMBANG";
    $bgColor = "#28a745"; // hijau
} else {
    $status = "TIDAK SEIMBANG";
    $bgColor = "#dc3545"; // merah
}
$html .= '  <tr style="background-color:' . $bgColor . '; color:#fff;">';
$html .= '    <td colspan="6" class="text-center fw-bold">' . $status . '</td>';
$html .= '  </tr>';

$html .= '</tfoot>';
$html .= '</table>';

$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "data" => $html]);
