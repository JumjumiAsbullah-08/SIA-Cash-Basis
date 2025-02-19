<?php
// process_kasir/download_invoice.php

// Mulai output buffering untuk mencegah error "headers already sent"
ob_start();

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
if (!$result || $result->num_rows <= 0) {
    die("Transaksi tidak ditemukan.");
}
$transaction = $result->fetch_assoc();

// Query untuk mengambil item transaksi
$sqlItems = "SELECT ti.*, i.item_name 
             FROM transaction_items ti 
             LEFT JOIN items i ON ti.item_id = i.id 
             WHERE ti.transaction_id = '$transaction_id'";
$resultItems = $conn->query($sqlItems);

// Buat konten HTML untuk invoice
$html = '<h1 style="text-align:center;">Invoice</h1>';
$html .= '<p><strong>Nomor Invoice:</strong> ' . $transaction['invoice_number'] . '</p>';
$html .= '<p><strong>Tanggal Transaksi:</strong> ' . $transaction['transaction_date'] . '</p>';
$html .= '<p><strong>Nama Pembeli:</strong> ' . $transaction['buyer_name'] . '</p>';

$html .= '<table border="1" cellspacing="3" cellpadding="4" width="100%">';
$html .= '<tr style="background-color:#f2f2f2;">
            <th>No</th>
            <th>Barang</th>
            <th>Harga</th>
            <th>Quantity</th>
            <th>Total</th>
          </tr>';

$i = 1;
while ($item = $resultItems->fetch_assoc()) {
    $html .= '<tr>';
    $html .= '<td>' . $i++ . '</td>';
    $html .= '<td>' . $item['item_name'] . '</td>';
    $html .= '<td>' . number_format($item['price'], 2) . '</td>';
    $html .= '<td>' . $item['quantity'] . '</td>';
    $html .= '<td>' . number_format($item['total'], 2) . '</td>';
    $html .= '</tr>';
}
$html .= '</table>';
$html .= '<p><strong>Total Amount:</strong> ' . number_format($transaction['total_amount'], 2) . '</p>';

// Ambil data Owner dan Kasir
// Untuk Owner, karena branch_id diset null, jangan gunakan kondisi branch_id di sini.
$sqlOwner = "SELECT u.name, s.qr_code_image 
             FROM users u 
             LEFT JOIN signatures s ON u.id = s.user_id 
             WHERE u.role = 'Owner' AND u.branch_id IS NULL LIMIT 1";
$resultOwner = $conn->query($sqlOwner);
$owner = $resultOwner ? $resultOwner->fetch_assoc() : null;

// Untuk Kasir, tetap gunakan branch_id dari transaksi
$branch_id = $transaction['branch_id'];
$sqlKasir = "SELECT u.name, s.qr_code_image 
             FROM users u 
             LEFT JOIN signatures s ON u.id = s.user_id 
             WHERE u.role = 'Kasir' AND u.branch_id = '$branch_id' LIMIT 1";
$resultKasir = $conn->query($sqlKasir);
$kasir = $resultKasir ? $resultKasir->fetch_assoc() : null;

$html .= '<br/><br/>';
$html .= '<table border="0" cellspacing="3" cellpadding="4" width="100%">';
$html .= '<tr>';

// Bagian Owner
$html .= '<td width="50%" align="center">';
$html .= '<strong>Owner</strong><br/>';
if ($owner && !empty($owner['qr_code_image'])) {
    // Path absolut ke file QR Code di server
    $filePath = __DIR__ . "/../uploads/signatures/" . $owner['qr_code_image'];
    if (file_exists($filePath)) {
        // Baca file dan encode ke base64
        $imageData = base64_encode(file_get_contents($filePath));
        // Dapatkan MIME type file (misal: image/png atau image/jpeg)
        $mimeType = mime_content_type($filePath);
        $src = 'data:' . $mimeType . ';base64,' . $imageData;
        $html .= '<img src="' . $src . '" width="100" /><br/>';
    } else {
        $html .= 'File QR Code tidak ditemukan<br/>';
    }
} else {
    $html .= 'QR Code tidak tersedia<br/>';
}
$html .= htmlspecialchars($owner['name'] ?? 'Owner');
$html .= '</td>';

// Bagian Kasir
$html .= '<td width="50%" align="center">';
$html .= '<strong>Kasir</strong><br/>';
if ($kasir && !empty($kasir['qr_code_image'])) {
    $filePath = __DIR__ . "/../uploads/signatures/" . $kasir['qr_code_image'];
    if (file_exists($filePath)) {
        $imageData = base64_encode(file_get_contents($filePath));
        $mimeType = mime_content_type($filePath);
        $src = 'data:' . $mimeType . ';base64,' . $imageData;
        $html .= '<img src="' . $src . '" width="100" /><br/>';
    } else {
        $html .= 'File QR Code tidak ditemukan<br/>';
    }
} else {
    $html .= 'QR Code tidak tersedia<br/>';
}
$html .= htmlspecialchars($kasir['name'] ?? 'Kasir');
$html .= '</td>';

$html .= '</tr>';
$html .= '</table>';

// Buat instance TCPDF
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Atur informasi dokumen
$pdf->SetCreator('NamaAplikasiAnda');
$pdf->SetAuthor('NamaAplikasiAnda');
$pdf->SetTitle('Invoice ' . $transaction['invoice_number']);
$pdf->SetSubject('Invoice PDF');

// Atur margin dan header (opsional)
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

// Tambahkan halaman baru
$pdf->AddPage();

// Tulis konten HTML ke PDF
$pdf->writeHTML($html, true, false, true, false, '');

// Bersihkan output buffer agar tidak ada output sebelumnya
ob_end_clean();

// Output PDF dengan mode download
$pdf->Output('Invoice_' . $transaction['invoice_number'] . '.pdf', 'D');
?>
