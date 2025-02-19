<?php
// pages/kasir/transaksi.php
// session_start();
include_once __DIR__ . '/../../config/database.php';

// Ambil data user dari session
$role = $_SESSION['role'] ?? '';
$user_branch_id = $_SESSION['branch_id'] ?? 0;

// Query untuk mengambil daftar barang sesuai cabang (untuk Kasir)
if($role === 'Kasir'){
    $itemQuery = "SELECT * FROM items WHERE branch_id = '$user_branch_id' ORDER BY item_name";
} else {
    $itemQuery = "SELECT * FROM items ORDER BY item_name";
}
$itemResult = $conn->query($itemQuery);

// Generate nomor invoice secara otomatis, misal: INV-YYMMDD-RAND4
$invoice_number = 'INV-' . date('ymd') . '-' . rand(1000, 9999);
?>

<div class="container mt-4">
  <h2>Transaksi Penjualan</h2>
  <form id="transactionForm">
    <div class="row mb-3">
      <div class="col-md-4">
        <label for="invoiceNumber" class="form-label">Invoice Number</label>
        <input type="text" id="invoiceNumber" name="invoice_number" class="form-control" readonly value="<?php echo $invoice_number; ?>">
      </div>
      <div class="col-md-4">
        <label for="buyerName" class="form-label">Nama Pembeli</label>
        <input type="text" id="buyerName" name="buyer_name" class="form-control" required>
      </div>
      <div class="col-md-4">
        <!-- Untuk Kasir, branch_id diambil dari session -->
        <input type="hidden" name="branch_id" value="<?php echo $user_branch_id; ?>">
      </div>
    </div>
    <div class="mb-3">
      <label for="transactionDate" class="form-label">Tanggal Transaksi</label>
      <input type="text" id="transactionDate" name="transaction_date" class="form-control" readonly value="<?php echo date('Y-m-d H:i:s'); ?>">
    </div>
    
    <table class="table table-bordered" id="transactionItemsTable">
      <thead>
        <tr>
          <th>Barang</th>
          <th>Harga</th>
          <th>Quantity</th>
          <th>Total</th>
          <th>Aksi</th>
        </tr>
      </thead>
      <tbody>
        <tr class="item-row">
          <td>
            <select name="item_id[]" class="form-control item-select" required>
              <option value="">Pilih Barang</option>
              <?php
              if($itemResult && $itemResult->num_rows > 0){
                  while($item = $itemResult->fetch_assoc()){
                      // Sertakan harga dan available stock di attribute data-
                      echo '<option value="'.$item['id'].'" data-price="'.$item['price'].'" data-stock="'.$item['stock'].'">'.$item['item_name'].' ('.$item['item_code'].')</option>';
                  }
              }
              ?>
            </select>
          </td>
          <td>
            <input type="number" name="price[]" class="form-control price" readonly>
          </td>
          <td>
            <input type="number" name="quantity[]" class="form-control quantity" min="1" value="1" required>
          </td>
          <td>
            <input type="number" name="line_total[]" class="form-control line-total" readonly>
          </td>
          <td>
            <button type="button" class="btn btn-sm btn-danger btn-remove-row">Hapus</button>
          </td>
        </tr>
      </tbody>
    </table>
    <button type="button" class="btn btn-secondary" id="addRow">Tambah Baris</button>
    <div class="mt-3">
      <label for="grandTotal" class="form-label">Grand Total</label>
      <input type="number" id="grandTotal" name="grand_total" class="form-control" readonly>
    </div>
    <div class="mt-3">
      <button type="submit" class="btn btn-primary">Proses Transaksi</button>
    </div>
  </form>
</div>

<!-- CDN: jQuery, Bootstrap Bundle, SweetAlert2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function(){
  // Fungsi hitung total per baris dan grand total
  function updateRowTotal(row) {
    var price = parseFloat(row.find('.price').val()) || 0;
    var qty = parseInt(row.find('.quantity').val()) || 0;
    var total = price * qty;
    row.find('.line-total').val(total.toFixed(2));
    updateGrandTotal();
  }
  
  function updateGrandTotal() {
    var grandTotal = 0;
    $('#transactionItemsTable tbody .line-total').each(function(){
      grandTotal += parseFloat($(this).val()) || 0;
    });
    $('#grandTotal').val(grandTotal.toFixed(2));
  }
  
  // Saat item dipilih, set harga dan available stock
  $(document).on('change', '.item-select', function(){
    var price = $(this).find('option:selected').data('price');
    var row = $(this).closest('tr');
    row.find('.price').val(price);
    updateRowTotal(row);
  });
  
  // Saat quantity berubah, pastikan tidak melebihi available stock
  $(document).on('input', '.quantity', function(){
    var row = $(this).closest('tr');
    var available = row.find('.item-select option:selected').data('stock') || 0;
    var qty = parseInt($(this).val());
    if(qty > available){
       Swal.fire({
         icon: 'warning',
         title: 'Quantity Melebihi Stock',
         text: 'Quantity tidak boleh lebih dari stock yang tersedia ('+available+').'
       });
       $(this).val(available);
       qty = available;
    }
    updateRowTotal(row);
  });
  
  // Tambah baris baru
  $('#addRow').click(function(){
    var newRow = `<tr class="item-row">
          <td>
            <select name="item_id[]" class="form-control item-select" required>
              <option value="">Pilih Barang</option>
              <?php
              // Query ulang untuk list barang
              $itemResult = $conn->query($itemQuery);
              if($itemResult && $itemResult->num_rows > 0){
                  while($item = $itemResult->fetch_assoc()){
                      echo '<option value="'.$item['id'].'" data-price="'.$item['price'].'" data-stock="'.$item['stock'].'">'.$item['item_name'].' ('.$item['item_code'].')</option>';
                  }
              }
              ?>
            </select>
          </td>
          <td>
            <input type="number" name="price[]" class="form-control price" readonly>
          </td>
          <td>
            <input type="number" name="quantity[]" class="form-control quantity" min="1" value="1" required>
          </td>
          <td>
            <input type="number" name="line_total[]" class="form-control line-total" readonly>
          </td>
          <td>
            <button type="button" class="btn btn-sm btn-danger btn-remove-row">Hapus</button>
          </td>
        </tr>`;
    $('#transactionItemsTable tbody').append(newRow);
  });
  
  // Hapus baris
  $(document).on('click', '.btn-remove-row', function(){
    $(this).closest('tr').remove();
    updateGrandTotal();
  });
  
  // Proses transaksi via AJAX
  $("#transactionForm").on("submit", function(e){
    e.preventDefault();
    var formData = $(this).serialize();
    $.ajax({
       url: 'process_kasir/process_add_transaction.php',
       method: 'POST',
       data: formData,
       dataType: 'json',
       success: function(response){
         if(response.status === 'success'){
           Swal.fire({
             title: 'Transaksi berhasil!',
             html: 'Download:<br>' +
                   '<button id="btnDownloadInvoice" class="swal2-confirm swal2-styled" style="margin:5px;">Download Faktur PDF</button> ',
             showConfirmButton: false,
             allowOutsideClick: false
           });
           // Event untuk download Faktur PDF
           $(document).on('click', '#btnDownloadInvoice', function(){
            window.open('process_kasir/download_invoice.php?transaction_id=' + response.transaction_id, '_blank');
            location.reload();
           });
         } else {
           Swal.fire({
             icon: 'error',
             title: 'Gagal',
             text: response.debug
           });
         }
       },
       error: function(xhr, status, error){
         Swal.fire({
           icon: 'error',
           title: 'Gagal',
           text: 'Terjadi kesalahan saat memproses transaksi.'
         });
       }
    });
  });
});
</script>
