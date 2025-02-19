<?php
session_start();
include_once 'config/config.php';
include_once 'config/database.php';

// Jika belum login, tampilkan halaman login (tanpa tampilan sidebar & header)
if (!isset($_SESSION['user'])) {
    include 'pages/auth/login.php';
    exit;
}

// Ambil data user dan parameter page
$user = $_SESSION['user'];
$page = isset($_GET['page']) ? trim($_GET['page']) : '';

// Set default page jika kosong
if ($page == '') {
    if ($user['role'] == 'Owner') {
        $page = 'owner/dashboard';
    } elseif ($user['role'] == 'Kasir') {
        $page = 'kasir/dashboard';
    } elseif ($user['role'] == 'Pegawai') {
        $page = 'pegawai/dashboard';
    } else {
        $page = 'home';
    }
}

// Sinkronkan nilai default dengan $_GET agar menu.php mendapat nilai yang tepat
$_GET['page'] = $page;

if ($page == 'logout') {
    require_once 'process/process_logout.php';
    exit;
}
?>

<!-- Container utama -->
<div class="container-scroller d-flex">
    <!-- Sidebar (menu) -->
    <?php include_once 'includes/menu.php'; ?>
    
    <!-- Kontainer untuk header & konten -->
    <div class="container-fluid page-body-wrapper">
        <!-- Header (navbar) -->
        <?php include_once 'includes/header.php'; ?>
        
        <!-- Konten halaman -->
        <div class="main-panel">
            <div class="content-wrapper">
                <?php
                // Pastikan file yang diminta ada di folder pages
                $file = 'pages/' . $page . '.php';
                if (file_exists($file)) {
                    include $file;
                } else {
                    // Tentukan link dashboard berdasarkan role user
                    $dashboardLink = "index.php?page=home";
                    if (isset($_SESSION['user'])) {
                        $role = $_SESSION['user']['role'];
                        if ($role === 'Owner') {
                            $dashboardLink = "index.php?page=owner/dashboard";
                        } elseif ($role === 'Kasir') {
                            $dashboardLink = "index.php?page=kasir/dashboard";
                        } elseif ($role === 'Pegawai') {
                            $dashboardLink = "index.php?page=pegawai/dashboard";
                        }
                    }
                    echo '
                    <div class="container-fluid">
                        <!-- 404 Error Text -->
                        <div class="text-center">
                            <div class="error mx-auto" data-text="404">404</div>
                            <p class="lead text-gray-800 mb-5">Page Not Found</p>
                            <a href="' . $dashboardLink . '">&larr; Back to Dashboard</a>
                        </div>
                    </div>
                    ';
                }
                ?>
            </div>
        </div>
        
        <!-- Footer -->
        <?php include_once 'includes/footer.php'; ?>
    </div>
</div>
