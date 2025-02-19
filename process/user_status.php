<?php
// user_status.php
include_once __DIR__ . './../config/database.php';
header('Content-Type: text/html; charset=utf-8');

// Query untuk mengambil jumlah online/offline dan daftar user online (beserta role)
$query = "
  SELECT 
    b.id,
    b.branch_name,
    SUM(CASE WHEN u.is_online = 1 THEN 1 ELSE 0 END) AS onlineCount,
    SUM(CASE WHEN u.is_online = 0 THEN 1 ELSE 0 END) AS offlineCount,
    GROUP_CONCAT(
      CASE WHEN u.is_online = 1 
           THEN CONCAT(u.name, ' (', u.role, ')') 
           ELSE NULL 
      END SEPARATOR ', '
    ) AS onlineUsers
  FROM branches b
  JOIN users u ON u.branch_id = b.id
  WHERE u.role IN ('Kasir', 'Pegawai')
  GROUP BY b.id
";
$result = mysqli_query($conn, $query);
?>

<div class="server-status-legend">
  <?php
    if ($result && mysqli_num_rows($result) > 0) {
      while ($row = mysqli_fetch_assoc($result)) {
        ?>
        <div class="card border mb-3">
          <div class="card-body">
            <h5 class="card-title"><?php echo htmlspecialchars($row['branch_name']); ?></h5>
            <p>
              <span class="text-success">Online: <?php echo $row['onlineCount']; ?></span>
              &nbsp;/&nbsp;
              <span class="text-danger">Offline: <?php echo $row['offlineCount']; ?></span>
            </p>
            <?php if ($row['onlineCount'] > 0) { ?>
              <p class="card-text">
                <small class="text-muted">User online: <?php echo $row['onlineUsers']; ?></small>
              </p>
            <?php } else { ?>
              <p class="card-text">
                <small class="text-muted">Tidak ada user yang online.</small>
              </p>
            <?php } ?>
          </div>
        </div>
        <?php
      }
    } else {
      echo '<p>Tidak ada data pengguna.</p>';
    }
  ?>
</div>
