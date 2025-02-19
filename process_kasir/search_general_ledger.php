<?php
// process/search_general_ledger.php
session_start();
include_once __DIR__ . './../config/database.php';

function formatRupiah($angka) {
    return "Rp " . number_format($angka, 2, ',', '.');
}

header('Content-Type: application/json');

// Ambil data dari session untuk filter cabang
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;

$period = $_POST['period'] ?? '';

$query = "SELECT je.branch_id, b.branch_name,
                 DATE_FORMAT(je.entry_date, '%Y-%m') AS period,
                 DATE_FORMAT(je.entry_date, '%M %Y') AS month_year,
                 SUM(je.debit) AS total_debit,
                 SUM(je.credit) AS total_credit
          FROM journal_entries je
          LEFT JOIN branches b ON je.branch_id = b.id ";

// Jika ada filter periode, tambahkan kondisi
if (!empty($period)) {
    $query .= "WHERE DATE_FORMAT(je.entry_date, '%Y-%m') = ? ";
}

// Jika user adalah Kasir, tampilkan hanya data sesuai cabangnya
if ($role === 'Kasir') {
    if (!empty($period)) {
        $query .= "AND je.branch_id = ? ";
    } else {
        $query .= "WHERE je.branch_id = ? ";
    }
}

$query .= "GROUP BY je.branch_id, DATE_FORMAT(je.entry_date, '%Y-%m')
           ORDER BY je.entry_date DESC";

// Siapkan statement dan bind parameter sesuai kondisinya
if (!empty($period) && $role === 'Kasir') {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $period, $user_branch_id);
} elseif (!empty($period)) {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $period);
} elseif ($role === 'Kasir') {
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_branch_id);
} else {
    $stmt = $conn->prepare($query);
}

if (!$stmt) {
    echo json_encode(["status" => "error", "debug" => $conn->error]);
    exit;
}

$stmt->execute();
$result = $stmt->get_result();

$html = '';
$counter = 1;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $net_total = $row['total_debit'] - $row['total_credit'];
        $total_color = ($net_total > 0) ? "green" : (($net_total < 0) ? "red" : "black");
        $html .= '<tr>';
        $html .= '<td>' . $counter++ . '</td>';
        $html .= '<td>' . htmlspecialchars($row['branch_name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['month_year']) . '<br>
                  <small style="color:' . $total_color . ';">Total: ' . formatRupiah($net_total) . '</small></td>';
        $html .= '<td>
                    <button class="btn btn-info btn-sm btn-view-ledger" 
                        data-branch_id="' . $row['branch_id'] . '"
                        data-period="' . $row['period'] . '"
                        data-branch_name="' . htmlspecialchars($row['branch_name']) . '"
                        data-month_year="' . htmlspecialchars($row['month_year']) . '"
                        data-net_total="' . $net_total . '"
                        title="Lihat Buku Besar">
                        <i class="fas fa-eye"></i> Lihat Buku Besar
                    </button>
                  </td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="4" class="text-center">Tidak ada data posting untuk periode tersebut.</td></tr>';
}
$stmt->close();
$conn->close();
echo json_encode(["status" => "success", "data" => $html]);
?>
