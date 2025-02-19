<?php 
// process/search_trial_balance_summary.php
session_start();
include_once __DIR__ . './../config/database.php';

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}

header('Content-Type: application/json');

$period = $_POST['period'] ?? '';

$query = "SELECT je.branch_id, b.branch_name,
                 DATE_FORMAT(je.entry_date, '%Y-%m') AS period,
                 DATE_FORMAT(je.entry_date, '%M %Y') AS month_year,
                 SUM(je.debit) AS total_debit,
                 SUM(je.credit) AS total_credit
          FROM journal_entries je
          LEFT JOIN branches b ON je.branch_id = b.id ";
if(!empty($period)){
    $query .= "WHERE DATE_FORMAT(je.entry_date, '%Y-%m') = ? ";
}
$query .= "GROUP BY je.branch_id, DATE_FORMAT(je.entry_date, '%Y-%m')
           ORDER BY je.entry_date DESC";

if(!empty($period)){
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $period);
} else {
    $stmt = $conn->prepare($query);
}

if(!$stmt){
    echo json_encode(["status" => "error", "debug" => $conn->error]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$html = '';
$counter = 1;
if($result->num_rows > 0){
    while($row = $result->fetch_assoc()){
        $net = $row['total_debit'] - $row['total_credit'];
        $color = ($net > 0) ? 'green' : (($net < 0) ? 'red' : 'black');
        $html .= "<tr>";
        $html .= "<td>{$counter}</td>";
        $html .= "<td>" . htmlspecialchars($row['branch_name']) . "</td>";
        $html .= "<td>" . htmlspecialchars($row['month_year']) . "<br><small style='color:{$color};'>Net: " . formatRupiah($net) . "</small></td>";
        $html .= "<td><button class='btn btn-info btn-sm btn-download-report'
                         data-branch_id='{$row['branch_id']}'
                         data-period='{$row['period']}'
                         data-branch_name='" . htmlspecialchars($row['branch_name']) . "'
                         data-month_year='" . htmlspecialchars($row['month_year']) . "'
                         title='Download Laporan'><i class='fas fa-download'></i> Download Laporan</button></td>";
        $html .= "</tr>";
        $counter++;
    }
} else {
    $html .= "<tr><td colspan='4' class='text-center'>Tidak ada data untuk periode tersebut.</td></tr>";
}
$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "data" => $html]);
