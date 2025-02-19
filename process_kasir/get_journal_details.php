<?php
// process/get_journal_details.php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

$branch_id = $_POST['branch_id'] ?? '';
$period = $_POST['period'] ?? ''; // format "YYYY-MM"

if(empty($branch_id) || empty($period)){
    echo json_encode(["status" => "error", "debug" => "Branch dan periode harus diisi."]);
    exit;
}

// Query untuk mengambil detail jurnal pada branch dan periode tertentu
$query = "SELECT je.*, 
                 b.branch_name, 
                 cc.category_name 
          FROM journal_entries je
          LEFT JOIN branches b ON je.branch_id = b.id
          LEFT JOIN cost_categories cc ON je.cost_category_id = cc.id
          WHERE je.branch_id = ? 
            AND DATE_FORMAT(je.entry_date, '%Y-%m') = ?
          ORDER BY je.entry_date, je.id";
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
$html .= '<tr>';
$html .= '<th>No</th>';
$html .= '<th>Tanggal</th>';
$html .= '<th>Ref. Number</th>';
$html .= '<th>Nama Kategori</th>';
$html .= '<th>Deskripsi</th>';
$html .= '<th>Debit</th>';
$html .= '<th>Kredit</th>';
$html .= '<th>Aksi</th>';
$html .= '</tr>';
$html .= '</thead>';
$html .= '<tbody>';

$totalDebit = 0;
$totalCredit = 0;
$counter = 1;
while($row = $result->fetch_assoc()){
    $html .= '<tr>';
    $html .= '<td>' . $counter++ . '</td>';
    $html .= '<td>' . htmlspecialchars($row['entry_date']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['ref_number']) . '</td>';
    
    // Tampilkan nama kategori dengan alignment berbeda:
    // Jika debit > 0, align left; jika kredit > 0, align right.
    if($row['debit'] > 0){
        $html .= '<td class="text-start">' . htmlspecialchars($row['category_name']) . '</td>';
    } else {
        $html .= '<td class="text-end" style="text-align:right;">' . htmlspecialchars($row['category_name']) . '</td>';
    }
    
    $html .= '<td>' . htmlspecialchars($row['description']) . '</td>';
    
    // Format nilai debit dan kredit dengan awalan "Rp "
    $formattedDebit = "Rp " . number_format($row['debit'], 2, ',', '.');
    $formattedCredit = "Rp " . number_format($row['credit'], 2, ',', '.');
    $html .= '<td class="text-end">' . $formattedDebit . '</td>';
    $html .= '<td class="text-end">' . $formattedCredit . '</td>';
    
    // Kolom Aksi: tombol Edit dan Hapus
    $html .= '<td>';
    $html .= '<button class="btn btn-warning btn-sm btn-edit-journal" 
                  data-id="' . $row['id'] . '"
                  data-entry_date="' . htmlspecialchars($row['entry_date']) . '"
                  data-branch_id="' . $row['branch_id'] . '"
                  data-cost_category_id="' . $row['cost_category_id'] . '"
                  data-ref_number="' . htmlspecialchars($row['ref_number']) . '"
                  data-description="' . htmlspecialchars($row['description']) . '"
                  data-debit="' . $row['debit'] . '"
                  data-credit="' . $row['credit'] . '"
                  data-category_name="' . htmlspecialchars($row['category_name']) . '"
                  title="Edit"><i class="fas fa-edit"></i></button> ';
    $html .= '<button class="btn btn-danger btn-sm btn-delete-journal" 
                  data-id="' . $row['id'] . '"
                  title="Hapus"><i class="fas fa-trash"></i></button>';
    $html .= '</td>';
    $html .= '</td>';
    
    $html .= '</tr>';
    
    $totalDebit += $row['debit'];
    $totalCredit += $row['credit'];
}
$html .= '</tbody>';

// Baris total dengan colspan
$formattedTotalDebit = "Rp " . number_format($totalDebit, 2, ',', '.');
$formattedTotalCredit = "Rp " . number_format($totalCredit, 2, ',', '.');

$html .= '<tfoot>';
$html .= '<tr>';
$html .= '<td colspan="5" class="text-end fw-bold" style="text-align:center;">Jumlah Total</td>';
$html .= '<td class="text-end fw-bold">' . $formattedTotalDebit . '</td>';
$html .= '<td class="text-end fw-bold">' . $formattedTotalCredit . '</td>';
$html .= '<td></td>';
$html .= '</tr>';

// Baris keseimbangan: cek apakah seimbang
if($totalDebit == $totalCredit){
    $balanceText = "SEIMBANG";
    $balanceClass = "bg-success text-white";
} else {
    $balanceText = "TIDAK SEIMBANG";
    $balanceClass = "bg-danger text-white";
}
$html .= '<tr class="'.$balanceClass.'">';
$html .= '<td colspan="8" class="text-center fw-bold">' . $balanceText . '</td>';
$html .= '</tr>';
$html .= '</tfoot>';

$html .= '</table>';

$stmt->close();
$conn->close();

echo json_encode(["status" => "success", "data" => $html]);
?>
