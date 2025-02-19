<?php
// download_invoice_sj.php

// Sertakan autoload Composer (pastikan path-nya sesuai)
require_once __DIR__ . './../vendor/autoload.php';
include_once __DIR__ . './../config/database.php';

// Pastikan parameter transaction_id ada
if (!isset($_GET['transaction_id'])) {
    die("Transaction ID tidak ditemukan.");
}

$transaction_id = $_GET['transaction_id'];

// Query untuk mengambil data transaksi
$sql = "SELECT * FROM transactions WHERE id = '$transaction_id'";
$result = $conn->query($sql);
if ($result->num_rows <= 0) {
    die("Transaksi tidak ditemukan.");
}
$transaction = $result->fetch_assoc();

// Query untuk mengambil item transaksi
$sqlItems = "SELECT ti.*, i.item_name 
             FROM transaction_items ti 
             LEFT JOIN items i ON ti.item_id = i.id 
             WHERE ti.transaction_id = '$transaction_id'";
$resultItems = $conn->query($sqlItems);

// Buat konten HTML untuk Invoice
$htmlInvoice = '<h1 style="text-align:center;">Invoice</h1>';
$htmlInvoice .= '<p><strong>Nomor Invoice:</strong> ' . $transaction['invoice_number'] . '</p>';
$htmlInvoice .= '<p><strong>Tanggal Transaksi:</strong> ' . $transaction['transaction_date'] . '</p>';
$htmlInvoice .= '<p><strong>Nama Pembeli:</strong> ' . $transaction['buyer_name'] . '</p>';
$htmlInvoice .= '<table border="1" cellspacing="3" cellpadding="4">';
$htmlInvoice .= '<tr style="background-color:#f2f2f2;">
                   <th>No</th>
                   <th>Barang</th>
                   <th>Harga</th>
                   <th>Quantity</th>
                   <th>Total</th>
                 </tr>';

$i = 1;
while ($item = $resultItems->fetch_assoc()) {
    $htmlInvoice .= '<tr>';
    $htmlInvoice .= '<td>' . $i++ . '</td>';
    $htmlInvoice .= '<td>' . $item['item_name'] . '</td>';
    $htmlInvoice .= '<td>' . number_format($item['price'], 2) . '</td>';
    $htmlInvoice .= '<td>' . $item['quantity'] . '</td>';
    $htmlInvoice .= '<td>' . number_format($item['line_total'], 2) . '</td>';
    $htmlInvoice .= '</tr>';
}
$htmlInvoice .= '</table>';
$htmlInvoice .= '<p><strong>Grand Total:</strong> ' . number_format($transaction['grand_total'], 2) . '</p>';

// Buat konten HTML untuk Surat Jalan
$htmlSuratJalan = '<h1 style="text-align:center;">Surat Jalan</h1>';
$htmlSuratJalan .= '<p><strong>Nomor Surat Jalan:</strong> SJ-' . $transaction['invoice_number'] . '</p>';
$htmlSuratJalan .= '<p><strong>Tanggal Transaksi:</strong> ' . $transaction['transaction_date'] . '</p>';
$htmlSuratJalan .= '<p><strong>Nama Pembeli:</strong> ' . $transaction['buyer_name'] . '</p>';
$htmlSuratJalan .= '<table border="1" cellspacing="3" cellpadding="4">';
$htmlSuratJalan .= '<tr style="background-color:#f2f2f2;">
                      <th>No</th>
                      <th>Barang</th>
                      <th>Quantity</th>
                    </tr>';

// Untuk Surat Jalan, biasanya hanya diperlukan nama barang dan quantity
$i = 1;
// Karena resultItems sudah digunakan di invoice, kita jalankan query ulang
$resultItems = $conn->query($sqlItems);
while ($item = $resultItems->fetch_assoc()) {
    $htmlSuratJalan .= '<tr>';
    $htmlSuratJalan .= '<td>' . $i++ . '</td>';
    $htmlSuratJalan .= '<td>' . $item['item_name'] . '</td>';
    $htmlSuratJalan .= '<td>' . $item['quantity'] . '</td>';
    $htmlSuratJalan .= '</tr>';
}
$htmlSuratJalan .= '</table>';

// Buat instance TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Atur informasi dokumen
$pdf->SetCreator('NamaAplikasiAnda');
$pdf->SetAuthor('NamaAplikasiAnda');
$pdf->SetTitle('Invoice & Surat Jalan ' . $transaction['invoice_number']);

// Atur margin dan header jika diperlukan (opsional)
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Halaman pertama untuk Invoice
$pdf->AddPage();
$pdf->writeHTML($htmlInvoice, true, false, true, false, '');

// Halaman kedua untuk Surat Jalan
$pdf->AddPage();
$pdf->writeHTML($htmlSuratJalan, true, false, true, false, '');

// Output PDF dengan mode download
$pdf->Output('Invoice_SuratJalan_' . $transaction['invoice_number'] . '.pdf', 'D');
