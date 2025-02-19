<?php
session_start();
include_once __DIR__ . './../config/database.php';
require_once __DIR__ . './../vendor/tecnickcom/tcpdf/tcpdf.php';

// Dapatkan ID Surat Jalan dari parameter
$sj_id = $_GET['sj_id'] ?? 0;
if (!$sj_id) {
    exit("ID surat jalan tidak valid.");
}

// Ambil data surat jalan + data transaksi + data cabang
$sqlSJ = "SELECT sj.*,
                 t.invoice_number, t.buyer_name, t.branch_id AS t_branch_id, t.transaction_date,
                 b.branch_name, b.address
          FROM surat_jalan sj
          JOIN transactions t ON sj.transaction_id = t.id
          JOIN branches b ON sj.branch_id = b.id
          WHERE sj.id = '$sj_id'";
$resultSJ = $conn->query($sqlSJ);
$suratJalan = $resultSJ->fetch_assoc();
if (!$suratJalan) {
    exit("Data surat jalan tidak ditemukan.");
}

// Ambil detail barang dari transaction_items dan items
$transactionId = $suratJalan['transaction_id'];
$sqlItems = "
    SELECT ti.*, i.item_name
    FROM transaction_items ti
    JOIN items i ON ti.item_id = i.id
    WHERE ti.transaction_id = '$transactionId'
";
$resultItems = $conn->query($sqlItems);

// Siapkan data toko/cabang
$branchName       = $suratJalan['branch_name'] ?? '';
$branchAddress    = $suratJalan['address'] ?? '';
$branchSuratJalan = $suratJalan['surat_jalan_number'] ?? '';

// Buat instance TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// Set info dokumen
$pdf->SetCreator('Sistem');
$pdf->SetAuthor('CV. Tamora Electric');
$pdf->SetTitle('Surat Jalan');
$pdf->SetSubject('Surat Jalan PDF');

// Nonaktifkan header/footer default
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Tambah halaman
$pdf->AddPage();

// ================== HEADER TOKO & JUDUL ==================
$pdf->SetFont('helvetica','B',12);
$pdf->Cell(0,5,"CV. Tamora Electric | ".$branchName,0,1,'L');

$pdf->SetFont('helvetica','',10);
$pdf->Cell(0,5,$branchAddress,0,1,'L');
// Tampilkan nomor surat jalan
$pdf->Cell(0,5,"No. ".$branchSuratJalan,0,1,'L');

$pdf->Ln(5);

$pdf->SetFont('helvetica','B',14);
$pdf->Cell(0,10,"SURAT JALAN",0,1,'C');

// ================== INFO SURAT JALAN & PENERIMA ==================
$pdf->SetFont('helvetica','',10);
$pdf->Cell(30,5,"Kepada Yth.",0,0,'L');
$pdf->Cell(70,5,": ".$suratJalan['buyer_name'],0,1,'L');

$pdf->Cell(30,5,"Pengirim",0,0,'L');
$pdf->Cell(70,5,": ".$suratJalan['sender_name'],0,1,'L');

$pdf->Cell(30,5,"No. Invoice",0,0,'L');
$pdf->Cell(70,5,": ".$suratJalan['invoice_number'],0,1,'L');

$pdf->Cell(30,5,"Tanggal",0,0,'L');
$pdf->Cell(70,5,": ".$suratJalan['transaction_date'],0,1,'L');

$pdf->Ln(5);

// ================== TABEL BARANG ==================
$pdf->SetFont('helvetica','B',10);
$pdf->Cell(70,8,"Nama Barang",1,0,'C');
$pdf->Cell(20,8,"Qty",1,0,'C');
$pdf->Cell(30,8,"Harga",1,0,'C');
$pdf->Cell(30,8,"Total",1,1,'C');

$pdf->SetFont('helvetica','',10);
$grandTotal = 0; // Variabel penampung total keseluruhan

if ($resultItems && $resultItems->num_rows > 0) {
    while ($item = $resultItems->fetch_assoc()) {
        $pdf->Cell(70,8,$item['item_name'],1,0,'L');
        $pdf->Cell(20,8,$item['quantity'],1,0,'C');
        $pdf->Cell(30,8,number_format($item['price'],2),1,0,'R');
        $pdf->Cell(30,8,number_format($item['total'],2),1,1,'R');
        $grandTotal += $item['total'];
    }
    // Baris Total Amount (colspan kolom pertama: 70+20+30 = 120)
    $pdf->SetFont('helvetica','B',10);
    $pdf->Cell(120,8,"Total Amount",1,0,'R');
    $pdf->Cell(30,8,number_format($grandTotal,2),1,1,'R');
} else {
    $pdf->Cell(70,8,"-",1,0,'C');
    $pdf->Cell(20,8,"-",1,0,'C');
    $pdf->Cell(30,8,"-",1,0,'C');
    $pdf->Cell(30,8,"-",1,1,'C');
}

$pdf->Ln(5);

// ================== FOOTER & CATATAN ==================
$pdf->SetFont('helvetica','',10);
$pdf->MultiCell(0,5,
    "BARANG SUDAH DITERIMA DALAM KEADAAN BAIK DAN CUKUP.\n" .
    "Surat Jalan ini merupakan bukti penyerahan barang.\n" .
    "Surat Jalan ini akan dilampirkan invoice sebagai bukti penjualan.",
    0,'L'
);

$pdf->Ln(10);

// ================== QUERY QR CODE SIGNATURE ==================
// Untuk Owner: ambil user dengan role Owner dan branch_id NULL
$sqlOwner = "SELECT u.name, s.qr_code_image 
             FROM users u 
             LEFT JOIN signatures s ON u.id = s.user_id 
             WHERE u.role = 'Owner' AND u.branch_id IS NULL 
             LIMIT 1";
$resultOwner = $conn->query($sqlOwner);
$owner = $resultOwner ? $resultOwner->fetch_assoc() : null;

// Untuk Pegawai: ambil user dengan role Pegawai sesuai cabang surat jalan
$sqlPegawai = "SELECT u.name, s.qr_code_image 
               FROM users u 
               LEFT JOIN signatures s ON u.id = s.user_id 
               WHERE u.role = 'Pegawai' AND u.branch_id = '".$suratJalan['branch_id']."' 
               LIMIT 1";
$resultPegawai = $conn->query($sqlPegawai);
$pegawai = $resultPegawai ? $resultPegawai->fetch_assoc() : null;

// ================== TANDA TANGAN & QR CODE ==================
$pdf->SetFont('helvetica','',10);
// Cetak label tanda tangan
$pdf->Cell(63,8,"Penerima / Pembeli",0,0,'C');
$pdf->Cell(63,8,"Bagian Pengiriman",0,0,'C');
$pdf->Cell(63,8,"Owner / Pemilik",0,1,'C');

$pdf->Ln(2);

// Dapatkan posisi dan margin untuk penempatan QR Code
$margins = $pdf->getMargins();
$startX = $margins['left'];
$cellWidth = 63;
$yPosition = $pdf->GetY();
$imageWidth = 30;
$imageHeight = 20;

// Penerima / Pembeli: tidak ada QR Code, jadi lewati

// Bagian Pengiriman (Pegawai)
$pegawaiCellX = $startX + $cellWidth; // cell kedua
$pegawaiImageX = $pegawaiCellX + ($cellWidth - $imageWidth) / 2;
if ($pegawai && !empty($pegawai['qr_code_image'])) {
    $filePegawai = __DIR__ . "/../uploads/signatures/" . $pegawai['qr_code_image'];
    if (file_exists($filePegawai)) {
        $pdf->Image($filePegawai, $pegawaiImageX, $yPosition, $imageWidth, $imageHeight, '', '', '', false, 300, '', false, false, 0);
    }
}

// Owner / Pemilik
$ownerCellX = $startX + 2 * $cellWidth; // cell ketiga
$ownerImageX = $ownerCellX + ($cellWidth - $imageWidth) / 2;
if ($owner && !empty($owner['qr_code_image'])) {
    $fileOwner = __DIR__ . "/../uploads/signatures/" . $owner['qr_code_image'];
    if (file_exists($fileOwner)) {
        $pdf->Image($fileOwner, $ownerImageX, $yPosition, $imageWidth, $imageHeight, '', '', '', false, 300, '', false, false, 0);
    }
}

$pdf->Ln($imageHeight + 2);

// Cetak nama di bawah QR Code (atau placeholder jika tidak ada)
$pdf->Cell(63,8, $suratJalan['buyer_name'], 0,0, 'C');
$pdf->Cell(63,8, $pegawai['name'] ?? "( ........................ )", 0,0, 'C');
$pdf->Cell(63,8, $owner['name'] ?? "( ........................ )", 0,1, 'C');

// Output PDF (ditampilkan di browser)
$pdf->Output("Surat_Jalan_{$suratJalan['surat_jalan_number']}.pdf",'I');
?>
