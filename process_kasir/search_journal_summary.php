<?php
// process/search_journal_summary.php
session_start();
include_once __DIR__ . './../config/database.php';
header('Content-Type: application/json');

// Ambil data dari session untuk filter cabang
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;

$period = $_POST['period'] ?? '';

// Query dasar
$query = "SELECT je.branch_id, 
                 DATE_FORMAT(je.entry_date, '%Y-%m') AS period, 
                 DATE_FORMAT(je.entry_date, '%M %Y') AS bulan_tahun, 
                 b.branch_name 
          FROM journal_entries je 
          LEFT JOIN branches b ON je.branch_id = b.id ";

// Jika ada filter periode, tambahkan kondisi
if (!empty($period)) {
    $query .= "WHERE DATE_FORMAT(je.entry_date, '%Y-%m') = ? ";
}

// Jika user adalah Kasir, tampilkan hanya data sesuai cabangnya
if ($role === 'Kasir') {
    // Jika sudah ada kondisi (periode), tambahkan dengan AND, jika belum gunakan WHERE
    if (!empty($period)) {
        $query .= "AND je.branch_id = ? ";
    } else {
        $query .= "WHERE je.branch_id = ? ";
    }
}

$query .= "GROUP BY je.branch_id, DATE_FORMAT(je.entry_date, '%Y-%m')
           ORDER BY je.entry_date DESC";

// Siapkan statement sesuai kondisi parameter
if (!empty($period) && $role === 'Kasir') {
    // Dua parameter: period (string) dan branch_id (integer)
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $period, $user_branch_id);
} elseif (!empty($period)) {
    // Hanya parameter periode
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $period);
} elseif ($role === 'Kasir') {
    // Hanya parameter branch_id
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_branch_id);
} else {
    // Tanpa parameter (untuk user selain Kasir)
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
        $html .= '<tr>';
        $html .= '<td>' . $counter++ . '</td>';
        $html .= '<td>' . htmlspecialchars($row['branch_name']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['bulan_tahun']) . '</td>';
        $html .= '<td>';
        $html .= '<button class="btn btn-info btn-sm btn-view-journal-summary" 
                          data-branch_id="' . $row['branch_id'] . '"
                          data-period="' . $row['period'] . '"
                          title="Lihat Journal">
                    <i class="fas fa-eye"></i> Lihat Journal
                  </button>';
        $html .= '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="4" class="text-center">Tidak ada data jurnal untuk periode tersebut.</td></tr>';
}

$stmt->close();
$conn->close();
echo json_encode(["status" => "success", "data" => $html]);
?>
