<?php
// session_start();
include_once __DIR__ . '/../config/database.php'; // Perbaiki include path

// Cek apakah user sudah login dan ada data user di session
if (isset($_SESSION['user']) && isset($_SESSION['user']['id'])) {
    $user_id = $_SESSION['user']['id'];
    
    // Update status user menjadi offline (is_online = 0)
    $updateQuery = "UPDATE users SET is_online = 0, updated_at = NOW() WHERE id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
}

// Hancurkan session
session_destroy();
?>
<!DOCTYPE html>
<html>
<head>
  <title>Logout</title>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Logout Successful',
      text: 'You have been logged out!',
      timer: 2000,
      showConfirmButton: false
    }).then(() => {
      window.location.href = "index.php?page=auth/login";
    });
  </script>
</body>
</html>
