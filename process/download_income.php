<?php
session_start();
include_once __DIR__ . './../config/database.php';
require_once __DIR__ . './../vendor/tecnickcom/tcpdf/tcpdf.php';

// Ambil data pemasukan (transaksi) dari semua cabang dengan JOIN ke tabel branches
$sql = "SELECT t.*, b.branch_name 
        FROM transactions t 
        LEFT JOIN branches b ON t.branch_id = b.id 
        ORDER BY t.transaction_date DESC";
$result = $conn->query($sql);

// Buat instance TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set informasi dokumen
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nama Anda');
$pdf->SetTitle('Laporan Pemasukan');
$pdf->SetSubject('Laporan Pemasukan');
$pdf->SetKeywords('TCPDF, PDF, Laporan, Pemasukan');

// Nonaktifkan header dan footer bawaan
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Tambahkan halaman baru
$pdf->AddPage();

// Judul Laporan
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Laporan Pemasukan', 0, 1, 'C');

// Header Tabel
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(10, 10, 'No', 1, 0, 'C');
$pdf->Cell(30, 10, 'Cabang', 1, 0, 'C');
$pdf->Cell(40, 10, 'Invoice', 1, 0, 'C');
$pdf->Cell(50, 10, 'Nama Pembeli', 1, 0, 'C');
$pdf->Cell(30, 10, 'Total', 1, 0, 'C');
$pdf->Cell(30, 10, 'Tanggal', 1, 1, 'C');

// Data Tabel
$pdf->SetFont('helvetica', '', 12);
$i = 1;
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(10, 10, $i++, 1, 0, 'C');
    $pdf->Cell(30, 10, $row['branch_name'] ?? '-', 1, 0, 'C');
    $pdf->Cell(40, 10, $row['invoice_number'], 1, 0, 'C');
    $pdf->Cell(50, 10, $row['buyer_name'], 1, 0, 'C');
    $pdf->Cell(30, 10, number_format($row['total_amount'], 2), 1, 0, 'R');
    
    // Format tanggal: dd/mm/yyyy (tanpa jam)
    $formattedDate = date('d/m/Y', strtotime($row['transaction_date']));
    $pdf->Cell(30, 10, $formattedDate, 1, 1, 'C');
}

// Output PDF untuk didownload
$pdf->Output('Laporan_Pemasukan.pdf', 'D');
?>
