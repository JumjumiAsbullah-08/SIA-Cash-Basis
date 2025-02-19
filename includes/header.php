<!DOCTYPE html>
<html lang="en">

<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <title><?php echo $_SESSION['user']['role'] ?? 'User'; ?> | CV. Tamora Electric</title>
  <link href="./themes/css/sb-admin-2.css" rel="stylesheet">
  <!-- base:css -->
  <link rel="icon" type="image/png" href="./assets/images/bg_1.jpg"/>
  <link href="./themes/css/sb-admin-2.min.css" rel="stylesheet">
  <link href="./themes/vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
</head>

<body>
<!-- Topbar -->
<nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

    <!-- Sidebar Toggle (Topbar) -->
    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
        <i class="fa fa-bars"></i>
    </button>
    
    <!-- Welcome Message & Branch Name / User Name -->
    <li class="nav-item d-none d-md-flex align-items-center mr-3">
        <?php
            // Tentukan role user
            $role = $_SESSION['user']['role'] ?? '';
            
            // Jika role Owner, tampilkan nama user, selain itu tampilkan nama cabang
            if($role === 'Owner'){
                $welcome_text = htmlspecialchars($_SESSION['user']['name'] ?? 'User');
            } else {
                // Ambil nama cabang berdasarkan branch_id user
                $branch_id = $_SESSION['user']['branch_id'] ?? 0;
                $branch_name = "Unknown Branch";
                if($branch_id > 0){
                    $query = "SELECT branch_name FROM branches WHERE id = ?";
                    if($stmt = $conn->prepare($query)){
                        $stmt->bind_param("i", $branch_id);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        if($result->num_rows > 0){
                            $row = $result->fetch_assoc();
                            $branch_name = $row['branch_name'];
                        }
                        $stmt->close();
                    }
                }
                $welcome_text = htmlspecialchars($branch_name);
            }
        ?>
        <span class="h5 mb-0 font-weight-bold">Welcome, <?php echo $welcome_text; ?></span>
    </li>

    <!-- Topbar Navbar -->
    <ul class="navbar-nav ml-auto">
        <!-- Date & Time Display (dinamis) -->
        <li class="nav-item d-none d-xl-flex align-items-center mr-3">
            <span id="timeDisplay" class="h5 mb-0 font-weight-bold"></span>
        </li>

        <!-- Nav Item - User Information -->
        <li class="nav-item dropdown no-arrow">
            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button"
                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small">
                    <?php echo htmlspecialchars($_SESSION['user']['name'] ?? 'User'); ?>
                </span>
                <img class="img-profile rounded-circle" src="<?php echo isset($_SESSION['user']['photo']) && !empty($_SESSION['user']['photo']) ? 'uploads/users/' . htmlspecialchars($_SESSION['user']['photo']) : 'img/default-profile.png'; ?>">
            </a>
            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="userDropdown">
                <a class="dropdown-item" href="index.php?page=profile">
                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                    Profile
                </a>
            </div>
        </li>
    </ul>
</nav>
<!-- End of Topbar -->

<!-- JavaScript untuk update waktu setiap detik -->
<script>
function updateTime() {
    var now = new Date();
    var options = {
        weekday: 'short', year: 'numeric', month: 'short',
        day: 'numeric', hour: '2-digit', minute: '2-digit', second: '2-digit'
    };
    document.getElementById('timeDisplay').textContent = now.toLocaleString('en-US', options);
}
setInterval(updateTime, 1000);
updateTime();
</script>
