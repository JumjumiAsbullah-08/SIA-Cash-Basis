<?php
// session_start();
include_once __DIR__ . '/../../config/database.php';

$user_branch_id = $_SESSION['branch_id'] ?? 0;

// --- Data Pemasukan (Income) ---
// Ambil data transaksi untuk cabang user
$queryIncome = "SELECT * FROM transactions WHERE branch_id = '$user_branch_id' ORDER BY transaction_date DESC";
$resultIncome = $conn->query($queryIncome);

// Hitung total pemasukan
$totalIncomeQuery = "SELECT SUM(total_amount) AS total_income FROM transactions WHERE branch_id = '$user_branch_id'";
$totalIncomeResult = $conn->query($totalIncomeQuery);
$totalIncome = 0;
if ($totalIncomeResult && $row = $totalIncomeResult->fetch_assoc()) {
    $totalIncome = $row['total_income'];
}

// --- Data Pengeluaran (Expense) ---
// Ambil data pengeluaran dari tabel finance (tipe expense)
$queryExpense = "SELECT * FROM finance WHERE branch_id = '$user_branch_id' AND type = 'expense' ORDER BY date DESC";
$resultExpense = $conn->query($queryExpense);

// Hitung total pengeluaran
$totalExpenseQuery = "SELECT SUM(amount) AS total_expense FROM finance WHERE branch_id = '$user_branch_id' AND type='expense'";
$totalExpenseResult = $conn->query($totalExpenseQuery);
$totalExpense = 0;
if ($totalExpenseResult && $row = $totalExpenseResult->fetch_assoc()) {
    $totalExpense = $row['total_expense'];
}

// Hitung saldo: total pemasukan - total pengeluaran
$saldo = $totalIncome - $totalExpense;
?>

<div class="container mt-4">
  <h2>Menu Keuangan</h2>
  <!-- Tab Navigation -->
  <ul class="nav nav-tabs" id="financeTab" role="tablist">
    <li class="nav-item" role="presentation">
      <button class="nav-link active" id="income-tab" data-bs-toggle="tab" data-bs-target="#income" type="button" role="tab" aria-controls="income" aria-selected="true">
        <i class="fas fa-arrow-circle-up"></i> Pemasukan
      </button>
    </li>
    <li class="nav-item" role="presentation">
      <button class="nav-link" id="expense-tab" data-bs-toggle="tab" data-bs-target="#expense" type="button" role="tab" aria-controls="expense" aria-selected="false">
        <i class="fas fa-arrow-circle-down"></i> Pengeluaran
      </button>
    </li>
  </ul>
  <div class="tab-content" id="financeTabContent">
    <!-- Tab Pemasukan -->
    <div class="tab-pane fade show active" id="income" role="tabpanel" aria-labelledby="income-tab">
      <div class="mt-3 d-flex justify-content-between align-items-center">
        <h5>Total Pemasukan: Rp <?php echo number_format($totalIncome, 2); ?></h5>
        <a href="process_kasir/download_income.php" class="btn btn-success">
          <i class="fas fa-download"></i> Download PDF
        </a>
      </div>
      <div class="table-responsive mt-3">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>Invoice</th>
              <th>Nama Pembeli</th>
              <th>Total Amount</th>
              <th>Tanggal Transaksi</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($resultIncome && $resultIncome->num_rows > 0) {
              $i = 1;
              while ($row = $resultIncome->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $i++ . "</td>";
                echo "<td>" . htmlspecialchars($row['invoice_number']) . "</td>";
                echo "<td>" . htmlspecialchars($row['buyer_name']) . "</td>";
                echo "<td>" . number_format($row['total_amount'], 2) . "</td>";
                echo "<td>" . htmlspecialchars($row['transaction_date']) . "</td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='5' class='text-center'>Tidak ada data pemasukan.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Tab Pengeluaran -->
    <div class="tab-pane fade" id="expense" role="tabpanel" aria-labelledby="expense-tab">
      <div class="d-flex justify-content-between align-items-center mt-3">
        <div>
          <h5>Data Pengeluaran</h5>
          <p>Saldo saat ini: Rp <?php echo number_format($saldo, 2); ?></p>
        </div>
        <div>
          <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="fas fa-plus"></i> Tambah Pengeluaran
          </button>
          <a href="process_kasir/download_expense.php" class="btn btn-success">
            <i class="fas fa-download"></i> Download PDF
          </a>
        </div>
      </div>
      <div class="table-responsive mt-3">
        <table class="table table-bordered">
          <thead>
            <tr>
              <th>No</th>
              <th>Deskripsi</th>
              <th>Jumlah</th>
              <th>Tanggal</th>
            </tr>
          </thead>
          <tbody>
            <?php
            if ($resultExpense && $resultExpense->num_rows > 0) {
              $i = 1;
              while ($row = $resultExpense->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $i++ . "</td>";
                echo "<td>" . htmlspecialchars($row['description']) . "</td>";
                echo "<td>" . number_format($row['amount'], 2) . "</td>";
                echo "<td>" . htmlspecialchars($row['date']) . "</td>";
                echo "</tr>";
              }
            } else {
              echo "<tr><td colspan='4' class='text-center'>Tidak ada data pengeluaran.</td></tr>";
            }
            ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal Tambah Pengeluaran -->
<div class="modal fade" id="addExpenseModal" tabindex="-1" aria-labelledby="addExpenseModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="addExpenseForm">
        <div class="modal-header">
          <h5 class="modal-title" id="addExpenseModalLabel"><i class="fas fa-arrow-circle-down"></i> Tambah Pengeluaran</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="expenseDescription" class="form-label">Deskripsi</label>
            <input type="text" class="form-control" id="expenseDescription" name="description" required>
          </div>
          <div class="mb-3">
            <label for="expenseAmount" class="form-label">Jumlah</label>
            <input type="number" step="0.01" class="form-control" id="expenseAmount" name="amount" required>
          </div>
          <div class="mb-3">
            <label for="expenseDate" class="form-label">Tanggal</label>
            <input type="date" class="form-control" id="expenseDate" name="date" required>
          </div>
          <div class="alert alert-info">
            Saldo saat ini: Rp <?php echo number_format($saldo, 2); ?>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- jQuery, Bootstrap JS, dan SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
  // Proses submit form tambah pengeluaran dengan validasi saldo melalui AJAX
  $('#addExpenseForm').on('submit', function(e){
    e.preventDefault();
    $.ajax({
      url: 'process_kasir/add_expense.php',
      method: 'POST',
      data: $(this).serialize(),
      success: function(response){
        Swal.fire({
          icon: 'success',
          title: 'Berhasil',
          text: response,
        }).then(() => {
          location.reload();
        });
      },
      error: function(xhr){
        Swal.fire({
          icon: 'error',
          title: 'Gagal',
          text: xhr.responseText,
        });
      }
    });
  });
});
</script>