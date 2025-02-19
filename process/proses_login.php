<?php
session_start();
require_once '../config/database.php'; // Pastikan path sesuai dengan struktur folder

// Ambil data dari form login
$username = $_POST['username'] ?? '';
$password = $_POST['pass'] ?? '';

// Enkripsi password (hanya demo, gunakan password_hash() & password_verify() untuk produksi)
$password_hashed = md5($password);

// Siapkan query untuk memeriksa user
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
$stmt->bind_param("ss", $username, $password_hashed);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    // Jika user ditemukan
    $user = $result->fetch_assoc();
    
    // Update status user menjadi online
    $updateQuery = "UPDATE users SET is_online = 1, updated_at = NOW() WHERE id = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $user['id']);
    $updateStmt->execute();

    // Simpan data user ke session, serta simpan branch_id dan role secara terpisah
    $_SESSION['user']      = $user;
    $_SESSION['branch_id'] = $user['branch_id'];
    $_SESSION['role']      = $user['role'];

    // Tentukan redirect berdasarkan role user
    if ($user['role'] == 'Owner') {
        $redirect = "../index.php?page=owner/dashboard";
    } elseif ($user['role'] == 'Kasir') {
        $redirect = "../index.php?page=kasir/dashboard";
    } elseif ($user['role'] == 'Pegawai') {
        $redirect = "../index.php?page=pegawai/dashboard";
    } else {
        $redirect = "../index.php?page=home";
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login Successful</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Login Successful',
            text: 'Welcome <?= addslashes($user['name'] ?? $user['username']) ?>!',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = "<?= $redirect ?>";
        });
    </script>
    </body>
    </html>
    <?php
} else {
    // Jika login gagal
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Login Failed</title>
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>
    <body>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Login Failed',
            text: 'Username or password is incorrect!',
            timer: 2000,
            showConfirmButton: false
        }).then(() => {
            window.location.href = "../index.php?page=auth/login";
        });
    </script>
    </body>
    </html>
    <?php
}
?>
