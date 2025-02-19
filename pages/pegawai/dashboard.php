<?php
// session_start();
include_once __DIR__ . '/../../config/database.php';

// Pastikan branch_id sudah tersedia di session
if (!isset($_SESSION['branch_id'])) {
    // Redirect atau tampilkan pesan error jika user belum login
    die("Unauthorized access.");
}

$branch_id = $_SESSION['branch_id'];

/**
 * Fungsi untuk mendapatkan nama cabang berdasarkan branch_id
 */
function getBranchName($conn, $branch_id) {
    $query = "SELECT branch_name FROM branches WHERE id = $branch_id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
       $row = mysqli_fetch_assoc($result);
       return $row['branch_name'];
    }
    return "N/A";
}

/**
 * Fungsi untuk menghitung total user pada cabang
 */
function getTotalUser($conn, $branch_id) {
    $query  = "SELECT COUNT(*) AS total FROM users WHERE branch_id = $branch_id";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

/**
 * Fungsi untuk menghitung total kategori biaya pada cabang
 */
function getTotalKategoriBiaya($conn, $branch_id) {
    $query  = "SELECT COUNT(*) AS total FROM cost_categories WHERE branch_id = $branch_id";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

/**
 * Fungsi untuk menghitung total transaksi pada cabang
 */
function getTotalTransaksi($conn, $branch_id) {
    $query  = "SELECT COUNT(*) AS total FROM transactions WHERE branch_id = $branch_id";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

/**
 * Fungsi untuk menghitung total pengeluaran pada cabang (dari finance dengan tipe 'expense')
 */
function getTotalPengeluaran($conn, $branch_id) {
    $query  = "SELECT SUM(amount) AS total FROM finance WHERE branch_id = $branch_id AND type = 'expense'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

/**
 * Fungsi untuk menghitung total pemasukan pada cabang (dari transactions)
 */
function getTotalPemasukan($conn, $branch_id) {
    $query  = "SELECT SUM(total_amount) AS total FROM transactions WHERE branch_id = $branch_id";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Ambil data untuk dashboard sesuai cabang user
$branchName        = getBranchName($conn, $branch_id);
$totalUser         = getTotalUser($conn, $branch_id);
$totalKategoriBiaya= getTotalKategoriBiaya($conn, $branch_id);
$totalTransaksi    = getTotalTransaksi($conn, $branch_id);
$totalPengeluaran  = getTotalPengeluaran($conn, $branch_id);
$totalPemasukan    = getTotalPemasukan($conn, $branch_id);
?>

<div class="container">
  <!-- Baris Pertama: Cabang, Total User, Kategori Biaya -->
  <div class="row">
    <!-- Card Cabang -->
    <div class="col-md-4 grid-margin stretch-card">
      <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <i class="fas fa-building fa-2x text-primary"></i>
          <div class="ms-3">
            <h6 class="mb-0">Cabang</h6>
            <h3 class="mb-0"><?php echo $branchName; ?></h3>
          </div>
        </div>
      </div>
    </div>
    <!-- Card Total User -->
    <div class="col-md-4 grid-margin stretch-card">
      <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <i class="fas fa-users fa-2x text-info"></i>
          <div class="ms-3">
            <h6 class="mb-0">Total User</h6>
            <h3 class="mb-0"><?php echo $totalUser; ?></h3>
          </div>
        </div>
      </div>
    </div>
    <!-- Card Kategori Biaya -->
    <div class="col-md-4 grid-margin stretch-card">
      <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <i class="fas fa-tags fa-2x text-success"></i>
          <div class="ms-3">
            <h6 class="mb-0">Kategori Biaya</h6>
            <h3 class="mb-0"><?php echo $totalKategoriBiaya; ?></h3>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Baris Pertama -->

  <br>

  <!-- Baris Kedua: Transaksi, Pengeluaran, Pemasukan -->
  <div class="row">
    <!-- Card Transaksi -->
    <div class="col-md-4 grid-margin stretch-card">
      <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <i class="fas fa-file-invoice-dollar fa-2x text-warning"></i>
          <div class="ms-3">
            <h6 class="mb-0">Transaksi</h6>
            <h3 class="mb-0"><?php echo $totalTransaksi; ?></h3>
          </div>
        </div>
      </div>
    </div>
    <!-- Card Pengeluaran -->
    <div class="col-md-4 grid-margin stretch-card">
      <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <i class="fas fa-arrow-down fa-2x text-danger"></i>
          <div class="text-end">
            <h6 class="mb-0">Pengeluaran</h6>
            <h3 class="mb-0"><?php echo 'Rp. ' . number_format($totalPengeluaran, 0, ',', '.'); ?></h3>
          </div>
        </div>
      </div>
    </div>
    <!-- Card Pemasukan -->
    <div class="col-md-4 grid-margin stretch-card">
      <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <i class="fas fa-arrow-up fa-2x text-secondary"></i>
          <div class="text-end">
            <h6 class="mb-0">Pemasukan</h6>
            <h3 class="mb-0"><?php echo 'Rp. ' . number_format($totalPemasukan, 0, ',', '.'); ?></h3>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Baris Kedua -->
</div>
