<?php
// process/search_profit_loss.php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}

$period = $_POST['period'] ?? '';

if(empty($period)){
    echo json_encode(["status" => "error", "debug" => "Periode harus diisi."]);
    exit;
}

/*
  Kita gunakan CASE untuk menentukan account_type berdasarkan category_name.
  Asumsi: 
    - Jika category_name mengandung 'Pendapatan' → 'Pendapatan'
    - Jika category_name mengandung 'Beban' → 'Beban'
    - Lainnya akan dikategorikan sebagai 'Lainnya' (tidak dihitung dalam laporan laba rugi)
*/
$query = "SELECT 
             CASE
               WHEN cc.category_name LIKE '%Pendapatan%' THEN 'Pendapatan'
               WHEN cc.category_name LIKE '%Beban%' THEN 'Beban'
               ELSE 'Lainnya'
             END AS account_type,
             cc.category_name,
             SUM(je.debit) AS total_debit, 
             SUM(je.credit) AS total_credit
          FROM journal_entries je
          LEFT JOIN cost_categories cc ON je.cost_category_id = cc.id
          WHERE DATE_FORMAT(je.entry_date, '%Y-%m') <= ?
          GROUP BY account_type, cc.category_name
          ORDER BY account_type, cc.category_name";

$stmt = $conn->prepare($query);
if(!$stmt){
    echo json_encode(["status" => "error", "debug" => $conn->error]);
    exit;
}
$stmt->bind_param("s", $period);
$stmt->execute();
$result = $stmt->get_result();

$revenueHTML = "";
$expenseHTML = "";
$totalRevenue = 0;
$totalExpense = 0;

while($row = $result->fetch_assoc()){
    // Hanya proses akun Pendapatan dan Beban (abaikan 'Lainnya')
    if($row['account_type'] == 'Pendapatan'){
        // Untuk pendapatan, nilai bersih = total_credit - total_debit
        $net = floatval($row['total_credit']) - floatval($row['total_debit']);
        $totalRevenue += $net;
        $revenueHTML .= "<tr>
                           <td>" . htmlspecialchars($row['category_name']) . "</td>
                           <td class='text-end'>" . formatRupiah($net) . "</td>
                         </tr>";
    } elseif($row['account_type'] == 'Beban'){
        // Untuk beban, nilai bersih = total_debit - total_credit
        $net = floatval($row['total_debit']) - floatval($row['total_credit']);
        $totalExpense += $net;
        $expenseHTML .= "<tr>
                           <td>" . htmlspecialchars($row['category_name']) . "</td>
                           <td class='text-end'>" . formatRupiah($net) . "</td>
                         </tr>";
    }
}
$stmt->close();
$conn->close();

$netProfit = $totalRevenue - $totalExpense;

if($netProfit > 0){
    $profitStatus = '<span class="fw-bold" style="color:green;">Laba Bersih</span>';
} elseif($netProfit < 0){
    $profitStatus = '<span class="fw-bold" style="color:red;">Rugi Bersih</span>';
} else {
    $profitStatus = '<span class="fw-bold">Nol</span>';
}

$html = '<h4>Laporan Laba Rugi s/d Periode: ' . htmlspecialchars($period) . '</h4>';
$html .= '<div class="row">';
$html .= '  <div class="col-md-6">';
$html .= '    <h5>Pendapatan</h5>';
$html .= '    <table class="table table-bordered table-sm">';
$html .= '      <thead><tr><th>Nama Akun</th><th class="text-end">Nilai</th></tr></thead>';
$html .= '      <tbody>' . ($revenueHTML ? $revenueHTML : "<tr><td colspan='2' class='text-center'>Tidak ada data</td></tr>") . '</tbody>';
$html .= '      <tfoot><tr><td class="fw-bold">Total Pendapatan</td><td class="text-end fw-bold">' . formatRupiah($totalRevenue) . '</td></tr></tfoot>';
$html .= '    </table>';
$html .= '  </div>';
$html .= '  <div class="col-md-6">';
$html .= '    <h5>Beban</h5>';
$html .= '    <table class="table table-bordered table-sm">';
$html .= '      <thead><tr><th>Nama Akun</th><th class="text-end">Nilai</th></tr></thead>';
$html .= '      <tbody>' . ($expenseHTML ? $expenseHTML : "<tr><td colspan='2' class='text-center'>Tidak ada data</td></tr>") . '</tbody>';
$html .= '      <tfoot><tr><td class="fw-bold">Total Beban</td><td class="text-end fw-bold">' . formatRupiah($totalExpense) . '</td></tr></tfoot>';
$html .= '    </table>';
$html .= '  </div>';
$html .= '</div>';

$html .= '<h5 class="text-center">' . $profitStatus . ' : ' . formatRupiah(abs($netProfit)) . '</h5>';

echo json_encode(["status" => "success", "data" => $html]);
