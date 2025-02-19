<?php
// includes/menu.php
// Pastikan session sudah dimulai di index.php
$currentPage = isset($_GET['page']) ? trim($_GET['page']) : '';
// Pastikan role user tersimpan di $_SESSION['user']['role']
$role = isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : 'Owner';

// Fungsi helper untuk menetapkan kelas "active"
function setActive($pageName) {
    global $currentPage;
    return ($currentPage === trim($pageName)) ? 'active' : '';
}
?>

<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="index.php?page=<?php echo strtolower($role); ?>/dashboard">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-lightbulb"></i>
        </div>
        <div class="sidebar-brand-text mx-3">CV. Tamora Electrik</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <?php if($role == 'Owner'): ?>
        <!-- Owner Menu Items -->
        <li class="nav-item <?php echo setActive('owner/dashboard'); ?>">
            <a class="nav-link" href="index.php?page=owner/dashboard">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">Master Data</div>

        <li class="nav-item <?php echo setActive('owner/branches'); ?>">
            <a class="nav-link" href="index.php?page=owner/branches">
                <i class="fas fa-fw fa-building"></i>
                <span>Data Cabang</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('owner/users'); ?>">
            <a class="nav-link" href="index.php?page=owner/users">
                <i class="fas fa-fw fa-users"></i>
                <span>Data User</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('owner/cost_categories'); ?>">
            <a class="nav-link" href="index.php?page=owner/cost_categories">
                <i class="fas fa-fw fa-dollar-sign"></i>
                <span>Kategori Biaya</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('owner/journal_entries'); ?>">
            <a class="nav-link" href="index.php?page=owner/journal_entries">
                <i class="fas fa-fw fa-folder-open"></i>
                <span>Journal Umum</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('owner/general_ledger'); ?>">
            <a class="nav-link" href="index.php?page=owner/general_ledger">
                <i class="fas fa-fw fa-book"></i>
                <span>Buku Besar</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('owner/neraca_saldo'); ?>">
            <a class="nav-link" href="index.php?page=owner/neraca_saldo">
                <i class="fas fa-fw fa-balance-scale"></i>
                <span>Neraca Saldo</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('owner/laporan'); ?>">
            <a class="nav-link" href="index.php?page=owner/laporan">
                <i class="fas fa-fw fa-file"></i>
                <span>Laporan</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">Transaksi</div>

        <li class="nav-item <?php echo setActive('owner/barang'); ?>">
            <a class="nav-link" href="index.php?page=owner/barang">
                <i class="fas fa-fw fa-cubes"></i>
                <span>Barang</span>
            </a>
        </li>

        <!-- <li class="nav-item <?php echo setActive('owner/transaksi'); ?>">
            <a class="nav-link" href="index.php?page=owner/transaksi">
                <i class="fas fa-fw fa-exchange-alt"></i>
                <span>Transaksi</span>
            </a>
        </li> -->

        <li class="nav-item <?php echo setActive('owner/keuangan'); ?>">
            <a class="nav-link" href="index.php?page=owner/keuangan">
                <i class="fas fa-fw fa-money-bill-wave"></i>
                <span>Keuangan</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('owner/laporan_transaksi'); ?>">
            <a class="nav-link" href="index.php?page=owner/laporan_transaksi">
                <i class="fas fa-fw fa-chart-line"></i>
                <span>Laporan Transaksi</span>
            </a>
        </li>

    <?php elseif($role == 'Kasir'): ?>
        <!-- Kasir Menu Items -->
        <li class="nav-item <?php echo setActive('kasir/dashboard'); ?>">
            <a class="nav-link" href="index.php?page=kasir/dashboard">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">Master Data</div>

        <li class="nav-item <?php echo setActive('kasir/cost_categories'); ?>">
            <a class="nav-link" href="index.php?page=kasir/cost_categories">
                <i class="fas fa-fw fa-dollar-sign"></i>
                <span>Kategori Biaya</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('kasir/journal_entries'); ?>">
            <a class="nav-link" href="index.php?page=kasir/journal_entries">
                <i class="fas fa-fw fa-folder-open"></i>
                <span>Journal Umum</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('kasir/general_ledger'); ?>">
            <a class="nav-link" href="index.php?page=kasir/general_ledger">
                <i class="fas fa-fw fa-book"></i>
                <span>Buku Besar</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('kasir/neraca_saldo'); ?>">
            <a class="nav-link" href="index.php?page=kasir/neraca_saldo">
                <i class="fas fa-fw fa-balance-scale"></i>
                <span>Neraca Saldo</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('kasir/laporan'); ?>">
            <a class="nav-link" href="index.php?page=kasir/laporan">
                <i class="fas fa-fw fa-file"></i>
                <span>Laporan</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <div class="sidebar-heading">Transaksi</div>

        <li class="nav-item <?php echo setActive('kasir/barang'); ?>">
            <a class="nav-link" href="index.php?page=kasir/barang">
                <i class="fas fa-fw fa-cubes"></i>
                <span>Barang</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('kasir/transaksi'); ?>">
            <a class="nav-link" href="index.php?page=kasir/transaksi">
                <i class="fas fa-fw fa-exchange-alt"></i>
                <span>Transaksi</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('kasir/keuangan'); ?>">
            <a class="nav-link" href="index.php?page=kasir/keuangan">
                <i class="fas fa-fw fa-money-bill-wave"></i>
                <span>Keuangan</span>
            </a>
        </li>

        <li class="nav-item <?php echo setActive('kasir/laporan_transaksi'); ?>">
            <a class="nav-link" href="index.php?page=kasir/laporan_transaksi">
                <i class="fas fa-fw fa-chart-line"></i>
                <span>Laporan Transaksi</span>
            </a>
        </li>

    <?php elseif($role == 'Pegawai'): ?>
        <!-- Pegawai Menu Items -->
        <li class="nav-item <?php echo setActive('pegawai/dashboard'); ?>">
            <a class="nav-link" href="index.php?page=pegawai/dashboard">
                <i class="fas fa-fw fa-tachometer-alt"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <hr class="sidebar-divider">

        <li class="nav-item <?php echo setActive('pegawai/surat_jalan'); ?>">
            <a class="nav-link" href="index.php?page=pegawai/surat_jalan">
                <i class="fas fa-fw fa-truck"></i>
                <span>Surat Jalan</span>
            </a>
        </li>
    <?php endif; ?>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Nav Item - Logout -->
    <li class="nav-item">
        <a class="nav-link" id="logout-link" href="#">
            <i class="fas fa-fw fa-sign-out-alt"></i>
            <span>Logout</span>
        </a>
    </li>

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>
</ul>

<!-- Script Logout menggunakan SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.getElementById('logout-link').addEventListener('click', function(e) {
    e.preventDefault();
    Swal.fire({
        title: 'Apakah Anda Yakin?',
        text: "Anda benar-benar ingin logout?",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ya, Logout!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if(result.isConfirmed) {
            window.location.href = "index.php?page=logout";
        }
    });
});
</script>
