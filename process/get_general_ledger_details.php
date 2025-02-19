<?php
// process/get_general_ledger_details.php
session_start();
include_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

// Fungsi format Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}

$branch_id  = $_POST['branch_id'] ?? '';
$period     = $_POST['period'] ?? '';  // YYYY-MM
$category_id= $_POST['category_id'] ?? '';

if(empty($branch_id) || empty($period) || empty($category_id)){
    echo json_encode(["status" => "error", "debug" => "Branch, periode, dan category_id harus diisi."]);
    exit;
}

// Ambil data jurnal umum HANYA untuk 1 kategori
$query = "SELECT je.entry_date, cc.category_name, je.debit, je.credit
          FROM journal_entries je
          LEFT JOIN cost_categories cc ON je.cost_category_id = cc.id
          WHERE je.branch_id = ?
            AND DATE_FORMAT(je.entry_date, '%Y-%m') = ?
            AND je.cost_category_id = ?
          ORDER BY je.entry_date ASC, je.id ASC";
$stmt = $conn->prepare($query);
if(!$stmt){
    echo json_encode(["status" => "error", "debug" => $conn->error]);
    exit;
}
$stmt->bind_param("isi", $branch_id, $period, $category_id);
$stmt->execute();
$result = $stmt->get_result();

// Header tabel
$html = '<table class="table table-bordered table-sm">';
$html .= '  <thead>';
$html .= '    <tr>';
$html .= '      <th>No</th>';
$html .= '      <th>Tanggal</th>';
$html .= '      <th>Nama Kategori</th>';
$html .= '      <th class="text-end">Debit</th>';
$html .= '      <th class="text-end">Kredit</th>';
$html .= '      <th class="text-end">Saldo Debit</th>';
$html .= '      <th class="text-end">Saldo Kredit</th>';
$html .= '    </tr>';
$html .= '  </thead>';
$html .= '  <tbody>';

$counter = 1;
$running_balance = 0;
while($row = $result->fetch_assoc()){
    $debit  = floatval($row['debit']);
    $credit = floatval($row['credit']);
    // Hitung saldo berjalan
    $running_balance += ($debit - $credit);
    
    // Tentukan saldo di baris ini
    if($running_balance >= 0){
        // Saldo debit
        $saldo_debit  = $running_balance;
        $saldo_kredit = 0;
    } else {
        // Saldo kredit
        $saldo_debit  = 0;
        $saldo_kredit = abs($running_balance);
    }
    
    // Format tampilan saldo
    $formattedSaldoDebit  = ($saldo_debit > 0)
        ? '<span class="fw-bold" style="color:green;">'.formatRupiah($saldo_debit).'</span>'
        : '-';
    $formattedSaldoKredit = ($saldo_kredit > 0)
        ? '<span class="fw-bold" style="color:red;">'.formatRupiah($saldo_kredit).'</span>'
        : '-';
    
    $html .= '  <tr>';
    $html .= '    <td>' . $counter++ . '</td>';
    $html .= '    <td>' . htmlspecialchars($row['entry_date']) . '</td>';
    $html .= '    <td>' . htmlspecialchars($row['category_name']) . '</td>';
    $html .= '    <td class="text-end">' . formatRupiah($debit) . '</td>';
    $html .= '    <td class="text-end">' . formatRupiah($credit) . '</td>';
    $html .= '    <td class="text-end">' . $formattedSaldoDebit . '</td>';
    $html .= '    <td class="text-end">' . $formattedSaldoKredit . '</td>';
    $html .= '  </tr>';
}
$html .= '  </tbody>';

// Saldo akhir
if($running_balance >= 0){
    $final_saldo_debit  = $running_balance;
    $final_saldo_kredit = 0;
} else {
    $final_saldo_debit  = 0;
    $final_saldo_kredit = abs($running_balance);
}
$formattedFinalDebit = ($final_saldo_debit > 0)
    ? '<span class="fw-bold" style="color:green;">'.formatRupiah($final_saldo_debit).'</span>'
    : '-';
$formattedFinalKredit = ($final_saldo_kredit > 0)
    ? '<span class="fw-bold" style="color:red;">'.formatRupiah($final_saldo_kredit).'</span>'
    : '-';

$html .= '  <tfoot>';
$html .= '    <tr>';
$html .= '      <td colspan="5" class="text-end fw-bold" style="font-weight:bold;">Saldo Akhir</td>';
$html .= '      <td class="text-end" style="font-weight:bold;">'.$formattedFinalDebit.'</td>';
$html .= '      <td class="text-end" style="font-weight:bold;">'.$formattedFinalKredit.'</td>';
$html .= '    </tr>';
$html .= '  </tfoot>';

$html .= '</table>';

$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "data" => $html]);
