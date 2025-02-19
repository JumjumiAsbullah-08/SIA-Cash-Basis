<?php
// search_transaction.php

session_start();
include_once __DIR__ . './../config/database.php';

// Dapatkan informasi user dari session
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;

// Ambil input pencarian
$search = trim($_GET['search'] ?? '');

// Buat query filter: jika bukan Owner, hanya tampilkan transaksi dari cabang user
if ($role !== 'Owner') {
    $queryFilter = "WHERE t.branch_id = ?";
} else {
    $queryFilter = "WHERE 1"; // Owner melihat semua data
}

// Jika ada input pencarian, tambahkan filter untuk nomor invoice atau nama pembeli
if (!empty($search)) {
    $queryFilter .= " AND (t.invoice_number LIKE ? OR t.buyer_name LIKE ?)";
}

// Query untuk mengambil transaksi dengan nama cabang dan status surat jalan (jika ada)
$sql = "SELECT t.*, 
               b.branch_name, 
               sj.status AS surat_status 
        FROM transactions t
        LEFT JOIN branches b ON t.branch_id = b.id
        LEFT JOIN surat_jalan sj ON t.id = sj.transaction_id
        $queryFilter
        ORDER BY t.transaction_date DESC";

$stmt = $conn->prepare($sql);

// Bind parameter sesuai dengan kondisi
if ($role !== 'Owner' && !empty($search)) {
    $searchWildcard = "%$search%";
    $stmt->bind_param("iss", $user_branch_id, $searchWildcard, $searchWildcard);
} elseif ($role !== 'Owner') {
    $stmt->bind_param("i", $user_branch_id);
} elseif (!empty($search)) {
    $searchWildcard = "%$search%";
    $stmt->bind_param("ss", $searchWildcard, $searchWildcard);
}

$stmt->execute();
$result = $stmt->get_result();

// Menampilkan hasil pencarian
if ($result && $result->num_rows > 0) {
    $i = 1;
    while ($transaction = $result->fetch_assoc()) {
        // Tentukan badge status surat jalan
        if (empty($transaction['surat_status'])) {
            $statusBadge = '<span class="badge bg-secondary" style="color:#fff !important;">Belum Dibuat</span>';
        } else {
            switch($transaction['surat_status']){
                case 'pending':
                    $statusBadge = '<span class="badge bg-warning">Pending</span>';
                    break;
                case 'sent':
                    $statusBadge = '<span class="badge bg-success" style="color:#fff !important;">Berhasil</span>';
                    break;
                case 'delivered':
                    $statusBadge = '<span class="badge bg-info">Delivered</span>';
                    break;
                case 'canceled':
                    $statusBadge = '<span class="badge bg-danger">Dibatalkan</span>';
                    break;
                default:
                    $statusBadge = '<span class="badge bg-secondary">'.htmlspecialchars($transaction['surat_status']).'</span>';
                    break;
            }
        }

        echo '<tr>';
        echo '<td>' . $i++ . '</td>';
        echo '<td>' . htmlspecialchars($transaction['branch_name'] ?? 'Tidak Diketahui') . '</td>';
        echo '<td>' . htmlspecialchars($transaction['invoice_number']) . '</td>';
        echo '<td>' . htmlspecialchars($transaction['buyer_name']) . '</td>';
        echo '<td>' . number_format($transaction['total_amount'], 2) . '</td>';
        echo '<td>' . date('d/m/Y', strtotime($transaction['transaction_date'])) . '</td>';
        echo '<td>' . $statusBadge . '</td>';
        echo '<td>
                <button class="btn btn-info btn-sm btn-detail" data-id="' . $transaction['id'] . '">
                  <i class="fas fa-eye"></i> Detail
                </button>
              </td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="8" class="text-center">Tidak ada data transaksi.</td></tr>';
}
?>
