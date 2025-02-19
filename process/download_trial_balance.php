<?php
// process/download_trial_balance.php
session_start();
include_once __DIR__ . './../config/database.php';

// Autoload Composer
require_once __DIR__ . './../vendor/autoload.php';

// Pindahkan deklarasi use di bagian atas file
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$branch_id = $_GET['branch_id'] ?? '';
$period    = $_GET['period'] ?? '';
$format    = $_GET['format'] ?? '';

if(empty($branch_id) || empty($period) || empty($format)){
    die("Parameter tidak lengkap.");
}

// Ambil data laporan neraca saldo (detail jurnal)
$query = "SELECT je.ref_number, cc.category_name, je.debit, je.credit
          FROM journal_entries je
          LEFT JOIN cost_categories cc ON je.cost_category_id = cc.id
          WHERE je.branch_id = ? AND DATE_FORMAT(je.entry_date, '%Y-%m') = ?
          ORDER BY je.entry_date ASC, je.ref_number ASC";
$stmt = $conn->prepare($query);
if(!$stmt) {
    die("Error: " . $conn->error);
}
$stmt->bind_param("is", $branch_id, $period);
$stmt->execute();
$result = $stmt->get_result();

$rows = [];
$totalDebit = 0;
$totalCredit = 0;
$totalNet = 0;
while($row = $result->fetch_assoc()){
    $debit  = floatval($row['debit']);
    $credit = floatval($row['credit']);
    $net = $debit - $credit;
    $totalDebit += $debit;
    $totalCredit += $credit;
    $totalNet += $net;
    $rows[] = $row;
}
$stmt->close();
$conn->close();

// Fungsi format Rupiah
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}

if($format == "pdf") {
    // --- Menggunakan TCPDF ---
    $pdf = new TCPDF();
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Your Company');
    $pdf->SetTitle('Laporan');
    $pdf->SetSubject('Laporan');
    $pdf->SetHeaderData('', 0, 'Laporan', "Branch ID: $branch_id, Periode: $period");
    $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
    $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
    $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
    $pdf->AddPage();

    $html = '<h2>Laporan</h2>';
    $html .= '<table border="1" cellspacing="3" cellpadding="4">';
    $html .= '<thead>';
    $html .= '<tr>';
    $html .= '<th width="5%">No</th>';
    $html .= '<th width="20%">Nomor Ref</th>';
    $html .= '<th width="30%">Nama Kategori</th>';
    $html .= '<th width="15%" align="right">Debit</th>';
    $html .= '<th width="15%" align="right">Kredit</th>';
    $html .= '<th width="15%" align="right">Total</th>';
    $html .= '</tr>';
    $html .= '</thead>';
    $html .= '<tbody>';
    $counter = 1;
    foreach($rows as $row) {
        $debit  = floatval($row['debit']);
        $credit = floatval($row['credit']);
        $net = $debit - $credit;
        if($net > 0) {
            $formattedNet = '<span style="color:green; font-weight:bold;">' . formatRupiah($net) . '</span>';
        } elseif($net < 0) {
            $formattedNet = '<span style="color:red; font-weight:bold;">' . formatRupiah(abs($net)) . '</span>';
        } else {
            $formattedNet = '-';
        }
        $html .= '<tr>';
        $html .= '<td>' . $counter++ . '</td>';
        $html .= '<td>' . htmlspecialchars($row['ref_number']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['category_name']) . '</td>';
        $html .= '<td align="right">' . ($debit > 0 ? formatRupiah($debit) : '-') . '</td>';
        $html .= '<td align="right">' . ($credit > 0 ? formatRupiah($credit) : '-') . '</td>';
        $html .= '<td align="right">' . $formattedNet . '</td>';
        $html .= '</tr>';
    }
    if($totalNet > 0) {
        $formattedTotalNet = '<span style="color:green; font-weight:bold;">' . formatRupiah($totalNet) . '</span>';
    } elseif($totalNet < 0) {
        $formattedTotalNet = '<span style="color:red; font-weight:bold;">' . formatRupiah(abs($totalNet)) . '</span>';
    } else {
        $formattedTotalNet = '-';
    }
    $html .= '<tr>';
    $html .= '<td colspan="3" align="right"><strong>Total</strong></td>';
    $html .= '<td align="right"><strong>' . ($totalDebit > 0 ? formatRupiah($totalDebit) : '-') . '</strong></td>';
    $html .= '<td align="right"><strong>' . ($totalCredit > 0 ? formatRupiah($totalCredit) : '-') . '</strong></td>';
    $html .= '<td align="right"><strong>' . $formattedTotalNet . '</strong></td>';
    $html .= '</tr>';
    if($totalNet == 0) {
        $status = "SEIMBANG";
        $bgColor = "#28a745";
    } else {
        $status = "TIDAK SEIMBANG";
        $bgColor = "#dc3545";
    }
    $html .= '<tr bgcolor="' . $bgColor . '">';
    $html .= '<td colspan="6" align="center" style="color:#fff; font-weight:bold;">' . $status . '</td>';
    $html .= '</tr>';
    $html .= '</tbody></table>';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output("laporan_{$branch_id}_{$period}.pdf", 'D');
    exit;
} elseif($format == "excel") {
    // --- Menggunakan PhpSpreadsheet ---
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Header kolom
    $sheet->setCellValue('A1', 'No');
    $sheet->setCellValue('B1', 'Nomor Ref');
    $sheet->setCellValue('C1', 'Nama Kategori');
    $sheet->setCellValue('D1', 'Debit');
    $sheet->setCellValue('E1', 'Kredit');
    $sheet->setCellValue('F1', 'Total');
    
    $rowNumber = 2;
    $counter = 1;
    foreach($rows as $row) {
        $debit = floatval($row['debit']);
        $credit = floatval($row['credit']);
        $net = $debit - $credit;
        $sheet->setCellValue('A' . $rowNumber, $counter);
        $sheet->setCellValue('B' . $rowNumber, $row['ref_number']);
        $sheet->setCellValue('C' . $rowNumber, $row['category_name']);
        $sheet->setCellValue('D' . $rowNumber, ($debit > 0 ? $debit : ""));
        $sheet->setCellValue('E' . $rowNumber, ($credit > 0 ? $credit : ""));
        if($net > 0){
            $sheet->setCellValue('F' . $rowNumber, $net);
        } elseif($net < 0){
            $sheet->setCellValue('F' . $rowNumber, abs($net));
        } else {
            $sheet->setCellValue('F' . $rowNumber, "-");
        }
        $rowNumber++;
        $counter++;
    }
    // Footer: Total
    $sheet->setCellValue('A' . $rowNumber, 'Total');
    $sheet->setCellValue('D' . $rowNumber, $totalDebit);
    $sheet->setCellValue('E' . $rowNumber, $totalCredit);
    if($totalNet > 0){
        $sheet->setCellValue('F' . $rowNumber, $totalNet);
    } elseif($totalNet < 0){
        $sheet->setCellValue('F' . $rowNumber, abs($totalNet));
    } else {
        $sheet->setCellValue('F' . $rowNumber, "-");
    }
    
    // Output file Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=laporan{$branch_id}_{$period}.xlsx");
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
} else {
    die("Format tidak valid.");
}
