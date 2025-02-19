<?php
session_start();
include_once __DIR__ . './../config/database.php';
require_once __DIR__ . './../vendor/tecnickcom/tcpdf/tcpdf.php';

$user_branch_id = $_SESSION['branch_id'] ?? 0;

// Ambil data pengeluaran
$sql = "SELECT * FROM finance WHERE branch_id = '$user_branch_id' AND type = 'expense' ORDER BY date DESC";
$result = $conn->query($sql);

// Buat instance TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set informasi dokumen
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('Nama Anda');
$pdf->SetTitle('Laporan Pengeluaran');
$pdf->SetSubject('Laporan Pengeluaran');
$pdf->SetKeywords('TCPDF, PDF, Laporan, Pengeluaran');

// Nonaktifkan header dan footer bawaan
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Tambahkan halaman baru
$pdf->AddPage();

// Judul Laporan
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Laporan Pengeluaran', 0, 1, 'C');

// Header Tabel
$pdf->SetFont('helvetica', 'B', 12);
$pdf->Cell(10, 10, 'No', 1, 0, 'C');
$pdf->Cell(70, 10, 'Deskripsi', 1, 0, 'C');
$pdf->Cell(40, 10, 'Jumlah', 1, 0, 'C');
$pdf->Cell(40, 10, 'Tanggal', 1, 1, 'C');

// Data Tabel
$pdf->SetFont('helvetica', '', 12);
$i = 1;
while ($row = $result->fetch_assoc()) {
    $pdf->Cell(10, 10, $i++, 1, 0, 'C');
    $pdf->Cell(70, 10, $row['description'], 1, 0, 'C');
    $pdf->Cell(40, 10, number_format($row['amount'], 2), 1, 0, 'R');
    $pdf->Cell(40, 10, $row['date'], 1, 1, 'C');
}

// Output PDF untuk didownload
$pdf->Output('Laporan_Pengeluaran.pdf', 'D');
?>
