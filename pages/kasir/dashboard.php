<?php
// session_start();
include_once __DIR__ . '/../../config/database.php';

// Ambil branch_id dari session (pastikan sudah di-set saat login)
$branch_id = $_SESSION['branch_id'];

// Fungsi untuk mendapatkan nama cabang (berdasarkan branch_id)
function getBranchName($conn, $branch_id) {
    $query = "SELECT branch_name FROM branches WHERE id = $branch_id";
    $result = mysqli_query($conn, $query);
    if ($result && mysqli_num_rows($result) > 0) {
       $row = mysqli_fetch_assoc($result);
       return $row['branch_name'];
    }
    return "Unknown";
}

// Fungsi untuk menghitung total user pada cabang
function getTotalUser($conn, $branch_id) {
    $query  = "SELECT COUNT(*) AS total FROM users WHERE branch_id = $branch_id";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

// Fungsi untuk menghitung total kategori biaya pada cabang
function getTotalKategoriBiaya($conn, $branch_id) {
    $query  = "SELECT COUNT(*) AS total FROM cost_categories WHERE branch_id = $branch_id";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

// Fungsi untuk menghitung total transaksi pada cabang
function getTotalTransaksi($conn, $branch_id) {
    $query  = "SELECT COUNT(*) AS total FROM transactions WHERE branch_id = $branch_id";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

// Fungsi untuk menghitung total pengeluaran (finance dengan type 'expense') pada cabang
function getTotalPengeluaran($conn, $branch_id) {
    $query  = "SELECT SUM(amount) AS total FROM finance WHERE type = 'expense' AND branch_id = $branch_id";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Fungsi untuk menghitung total pemasukan (dari transaksi) pada cabang
function getTotalPemasukan($conn, $branch_id) {
    $query  = "SELECT SUM(total_amount) AS total FROM transactions WHERE branch_id = $branch_id";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Ambil nilai dashboard berdasarkan cabang user
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

  <br>

  <!-- Baris Ketiga: Data Grafik (berdasarkan cabang pengguna) -->
  <div class="row">
    <div class="col-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <p class="text-muted">Pilih Tahun dan Bulan untuk melihat data grafik</p>
          <div class="row mb-3">
            <!-- Dropdown Pilih Tahun -->
            <div class="col-md-3">
              <select id="year-select" class="form-control">
                <?php 
                  $currentYear = date('Y');
                  for ($y = 2020; $y <= $currentYear + 1; $y++) {
                    echo "<option value=\"$y\"" . ($y == $currentYear ? " selected" : "") . ">$y</option>";
                  }
                ?>
              </select>
            </div>
            <!-- Dropdown Pilih Bulan -->
            <div class="col-md-3">
              <select id="month-select" class="form-control">
                <?php 
                  $months = [
                    1 => "Januari", 2 => "Februari", 3 => "Maret", 4 => "April", 
                    5 => "Mei", 6 => "Juni", 7 => "Juli", 8 => "Agustus", 
                    9 => "September", 10 => "Oktober", 11 => "November", 12 => "Desember"
                  ];
                  $currentMonth = date('n');
                  foreach ($months as $num => $name) {
                    echo "<option value=\"$num\"" . ($num == $currentMonth ? " selected" : "") . ">$name</option>";
                  }
                ?>
              </select>
            </div>
            <!-- Tombol Tampilkan -->
            <div class="col-md-3">
              <button class="btn btn-primary" onclick="fetchMonthlyData()">Tampilkan</button>
            </div>
          </div>
          <!-- Canvas untuk Chart -->
          <canvas id="audience-chart"></canvas>
        </div>
      </div>
    </div>
  </div>
  <!-- End Baris Ketiga -->

  <br>

  <!-- Baris Keempat: Financial Management Review (berdasarkan cabang pengguna) -->
  <div class="row">
    <div class="col-lg-12 grid-margin stretch-card">
      <div class="card">
        <div class="card-body">
          <h4 class="card-title">Financial Management Review</h4>
          <div class="table-responsive">
            <table class="table table-striped">
              <thead>
                <tr>
                  <th>Cabang</th>
                  <th>Nama Barang</th>
                  <th>Progress</th>
                  <th>Amount</th>
                  <th>Tanggal</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Fungsi untuk menghasilkan warna acak
                function random_color() {
                    return sprintf('#%06X', mt_rand(0, 0xFFFFFF));
                }

                // Query untuk mendapatkan data penjualan per barang per cabang
                // Filter berdasarkan branch_id milik user (kasir)
                $queryFM = "
                  SELECT 
                    b.branch_name,
                    i.item_name,
                    SUM(ti.quantity) AS sold_quantity,
                    i.stock AS remaining_stock,
                    ROUND((SUM(ti.quantity) / (SUM(ti.quantity) + i.stock)) * 100, 0) AS progress_percentage,
                    DATE_FORMAT(MAX(t.transaction_date), '%d/%m/%Y') AS transaction_date,
                    SUM(ti.total) AS total_amount
                  FROM transactions t
                  JOIN transaction_items ti ON t.id = ti.transaction_id
                  JOIN branches b ON t.branch_id = b.id
                  JOIN items i ON ti.item_id = i.id
                  WHERE t.branch_id = $branch_id
                  GROUP BY b.id, i.id
                  ORDER BY b.branch_name, i.item_name
                ";
                $resultFM = mysqli_query($conn, $queryFM);
                if ($resultFM && mysqli_num_rows($resultFM) > 0) {
                  while ($row = mysqli_fetch_assoc($resultFM)) {
                    // Dapatkan warna acak untuk progress bar
                    $color = random_color();
                    ?>
                    <tr>
                      <td><?php echo htmlspecialchars($row['branch_name']); ?></td>
                      <td><?php echo htmlspecialchars($row['item_name']); ?></td>
                      <td>
                        <div class="progress" style="height: 20px;">
                          <div class="progress-bar" role="progressbar" style="width: <?php echo $row['progress_percentage']; ?>%; background-color: <?php echo $color; ?>;" aria-valuenow="<?php echo $row['progress_percentage']; ?>" aria-valuemin="0" aria-valuemax="100">
                            <?php echo $row['progress_percentage']; ?>%
                          </div>
                        </div>
                        <small class="text-muted">Terjual: <?php echo $row['sold_quantity']; ?>, Sisa: <?php echo $row['remaining_stock']; ?></small>
                      </td>
                      <td><?php echo 'Rp. ' . number_format($row['total_amount'], 0, ',', '.'); ?></td>
                      <td><?php echo $row['transaction_date']; ?></td>
                    </tr>
                    <?php
                  }
                } else {
                  ?>
                  <tr>
                    <td colspan="5">Tidak ada data transaksi.</td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Baris Keempat -->
</div>
<!-- End Container -->

<!-- Sertakan Chart.js dan jQuery -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
  // Inisialisasi chart dengan data awal kosong
  var ctx = document.getElementById('audience-chart').getContext('2d');
  var monthlyChart = new Chart(ctx, {
    type: 'line',
    data: {
      // Label disesuaikan; data grafik nantinya menampilkan statistik dashboard berdasarkan cabang
      labels: ["Total User", "Kategori Biaya", "Transaksi", "Pengeluaran", "Pemasukan"],
      datasets: [{
        label: 'Metrics',
        data: [0, 0, 0, 0, 0],
        fill: false,
        borderColor: 'rgba(75, 192, 192, 1)',
        tension: 0.4,
        pointBackgroundColor: 'rgba(75, 192, 192, 1)',
        pointRadius: 5
      }]
    },
    options: {
      responsive: true,
      animation: { duration: 1500 },
      scales: {
        y: { beginAtZero: true }
      },
      plugins: {
        legend: { display: false }
      }
    }
  });

  // Fungsi untuk mengambil data grafik berdasarkan pilihan tahun dan bulan via AJAX
  function fetchMonthlyData() {
    var year  = $('#year-select').val();
    var month = $('#month-select').val();
    
    $.ajax({
      url: 'process_kasir/monthly_data.php',
      type: 'GET',
      data: { year: year, month: month },
      dataType: 'json',
      success: function(response) {
        var newData = [
          response.totalUser,
          response.totalKategoriBiaya,
          response.totalTransaksi,
          response.totalPengeluaran,
          response.totalPemasukan
        ];
        monthlyChart.data.datasets[0].data = newData;
        monthlyChart.update();
      },
      error: function(xhr, status, error) {
        console.error("Terjadi kesalahan: " + error);
        alert('Gagal mengambil data. Silakan coba lagi.');
      }
    });
  }

  // Ambil data default pada saat halaman pertama kali dimuat
  $(document).ready(function(){
    fetchMonthlyData();
  });
</script>
