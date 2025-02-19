<?php
include_once __DIR__ . '/../../config/database.php';

/**
 * Fungsi untuk menghitung total cabang dari tabel branches
 */
function getTotalCabang($conn) {
    $query  = "SELECT COUNT(*) AS total FROM branches";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

/**
 * Fungsi untuk menghitung total user dari tabel users
 */
function getTotalUser($conn) {
    $query  = "SELECT COUNT(*) AS total FROM users";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

/**
 * Fungsi untuk menghitung total kategori biaya dari tabel cost_categories
 */
function getTotalKategoriBiaya($conn) {
    $query  = "SELECT COUNT(*) AS total FROM cost_categories";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

/**
 * Fungsi untuk menghitung total transaksi dari tabel transactions
 */
function getTotalTransaksi($conn) {
    $query  = "SELECT COUNT(*) AS total FROM transactions";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'];
    }
    return 0;
}

/**
 * Fungsi untuk menghitung total pengeluaran dari tabel finance dengan type = 'expense'
 */
function getTotalPengeluaran($conn) {
    $query  = "SELECT SUM(amount) AS total FROM finance WHERE type = 'expense'";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

/**
 * Fungsi untuk menghitung total pemasukan dari tabel transactions dengan menjumlahkan total_amount
 */
function getTotalPemasukan($conn) {
    $query  = "SELECT SUM(total_amount) AS total FROM transactions";
    $result = mysqli_query($conn, $query);
    if ($result) {
        $row = mysqli_fetch_assoc($result);
        return $row['total'] ? $row['total'] : 0;
    }
    return 0;
}

// Mengambil nilai untuk ditampilkan di card
$totalCabang        = getTotalCabang($conn);
$totalUser          = getTotalUser($conn);
$totalKategoriBiaya = getTotalKategoriBiaya($conn);
$totalTransaksi     = getTotalTransaksi($conn);
$totalPengeluaran   = getTotalPengeluaran($conn);
$totalPemasukan     = getTotalPemasukan($conn);
?>

<div class="container">
  <!-- Baris Pertama: Total Cabang, Total User, Kategori Biaya -->
  <div class="row">
    <!-- Card Total Cabang -->
    <div class="col-md-4 grid-margin stretch-card">
      <div class="card">
        <div class="card-body d-flex justify-content-between align-items-center">
          <i class="fas fa-building fa-2x text-primary"></i>
          <div class="ms-3">
            <h6 class="mb-0">Total Cabang</h6>
            <h3 class="mb-0"><?php echo $totalCabang; ?></h3>
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

  <!-- Baris Ketiga: Data Grafik & User Status -->
  <div class="row">
    <!-- Kolom Kiri: Grafik Audience Metrics -->
    <div class="col-12 col-xl-6 grid-margin stretch-card">
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
    <!-- Kolom Kanan: User Status per Cabang -->
    <div class="col-12 col-xl-6 grid-margin stretch-card">
      <div class="card">
        <div class="card-body pb-0">
          <h4 class="card-title">User Status per Cabang</h4>
          <p class="text-muted">Last update: <span id="last-update"><?php echo date('H:i:s'); ?></span></p>
          <!-- Container yang akan di-update via AJAX -->
          <div id="user-status-container">
            <?php include 'process/user_status.php'; ?>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- End Baris Ketiga -->

  <br>

  <!-- Baris Keempat: Financial Management Review -->
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
              // Asumsi: terdapat tabel items dengan kolom id, item_name, dan stock
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
      labels: ["Total Cabang", "Total User", "Kategori Biaya", "Transaksi", "Pengeluaran", "Pemasukan"],
      datasets: [{
        label: 'Metrics',
        data: [0, 0, 0, 0, 0, 0],
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
      url: 'process/monthly_data.php',
      type: 'GET',
      data: { year: year, month: month },
      dataType: 'json',
      success: function(response) {
        var newData = [
          response.totalCabang,
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

<script>
  // Fungsi update status pengguna via AJAX tiap 5 detik
  function updateUserStatus() {
    $.ajax({
      url: 'process/user_status.php',
      type: 'GET',
      dataType: 'html',
      success: function(data) {
        $('#user-status-container').html(data);
        // Update waktu terakhir
        $('#last-update').text(new Date().toLocaleTimeString());
      },
      error: function() {
        console.error('Gagal mengambil data status pengguna.');
      }
    });
  }
  // Update data setiap 5000 ms (5 detik)
  setInterval(updateUserStatus, 5000);
</script>
