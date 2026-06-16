<?php
require_once "../config/db_conn.php";
require_once "../config/session.php";
requireRole('admin');

$page = 'requests';

if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM volunteer_requests WHERE request_id='$id'");
    header("Location: requests.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Moderate Tasks</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .status-open { background:#dcfce7; color:#166534; padding:2px 8px; border-radius:10px; font-size:12px; }
        .status-cancelled { background:#fee2e2; color:#991b1b; padding:2px 8px; border-radius:10px; font-size:12px; }
        .status-completed { background:#dbeafe; color:#1e40af; padding:2px 8px; border-radius:10px; font-size:12px; }
    </style>
</head>
<body>
    <?php include "../includes/admin_sidebar.php"; ?>

    <div class="main">
        <div class="header">
            <h1>Task Moderation</h1>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Title</th>
                        <th>Posted By</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT r.*, u.full_name 
                            FROM volunteer_requests r 
                            JOIN users u ON r.poster_id = u.user_id 
                            ORDER BY r.created_at DESC";
                    $res = mysqli_query($conn, $sql);

                    while($row = mysqli_fetch_assoc($res)) {
                        $cls = 'status-'.$row['status'];
                        echo "<tr>";
                        echo "<td>".htmlspecialchars($row['title'])."</td>";
                        echo "<td>".htmlspecialchars($row['full_name'])."</td>";
                        echo "<td>".date("M d, Y", strtotime($row['created_at']))."</td>";
                        echo "<td><span class='$cls'>".ucfirst($row['status'])."</span></td>";
                        echo "<td>
                                <a href='requests.php?delete=".$row['request_id']."' 
                                   onclick='return confirm(\"Delete this task?\")' 
                                   style='color:red;'>Delete</a>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>