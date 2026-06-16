<?php
require_once "../config/db_conn.php";
require_once "../config/session.php";
requireRole('admin'); // Protects this page

$page = 'home';

// 1. FETCH REAL STATS
// Count Total Users
$user_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role='user'");
$total_users = mysqli_fetch_assoc($user_res)['count'];

// Count Total Requests
$req_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteer_requests");
$total_requests = mysqli_fetch_assoc($req_res)['count'];

// Count "Revenue" (Total Points Distributed)
$points_res = mysqli_query($conn, "SELECT SUM(points_total) as count FROM points");
$total_points = mysqli_fetch_assoc($points_res)['count'] ?? 0;

// Count Pending Requests
$pending_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM volunteer_requests WHERE status='open'");
$pending_tasks = mysqli_fetch_assoc($pending_res)['count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>

    <?php include "../includes/admin_sidebar.php"; ?>

    <div class="main">
        <div class="header">
            <h1>Dashboard Overview</h1>
            <span style="font-weight:bold; color:#555;"><?php echo date("F j, Y"); ?></span>
        </div>

        <div class="cards">
            <div class="card">
                <h3>Total Users</h3>
                <p><?php echo $total_users; ?></p>
            </div>
            <div class="card">
                <h3>Total Requests</h3>
                <p><?php echo $total_requests; ?></p>
            </div>
            <div class="card">
                <h3>Total Points (System)</h3>
                <p><?php echo number_format($total_points); ?></p>
            </div>
            <div class="card">
                <h3>Open Tasks</h3>
                <p><?php echo $pending_tasks; ?></p>
            </div>
        </div>

        <div class="table-container">
            <div class="table-header">
                <h2>Recent Joiners</h2>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Joined</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM users WHERE role='user' ORDER BY created_at DESC LIMIT 5";
                    $result = mysqli_query($conn, $sql);
                    if (mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<tr>";
                            echo "<td>#".$row['user_id']."</td>";
                            echo "<td>".htmlspecialchars($row['full_name'])."</td>";
                            echo "<td>".htmlspecialchars($row['email'])."</td>";
                            echo "<td>".date("M d, Y", strtotime($row['created_at']))."</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='4'>No users found</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>

</body>
</html>