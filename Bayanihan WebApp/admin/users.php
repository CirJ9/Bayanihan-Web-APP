<?php
require_once "../config/db_conn.php";
require_once "../config/session.php";
requireRole('admin');

$page = 'users';

// HANDLE DELETE
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM users WHERE user_id='$id'");
    header("Location: users.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .btn-edit { background: #eab308; color: white; padding: 6px 12px; border-radius: 4px; text-decoration:none; font-size:14px; margin-right:5px; }
        .btn-edit:hover { background: #ca8a04; }
    </style>
</head>
<body>
    <?php include "../includes/admin_sidebar.php"; ?>

    <div class="main">
        <div class="header">
            <h1>User Management</h1>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Email</th>
                        <th>Points</th> <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Join with points to show current balance
                    $sql = "SELECT u.*, p.points_total 
                            FROM users u 
                            LEFT JOIN points p ON u.user_id = p.user_id 
                            WHERE u.role='user' 
                            ORDER BY u.created_at DESC";
                    $result = mysqli_query($conn, $sql);

                    while($row = mysqli_fetch_assoc($result)) {
                        $pts = $row['points_total'] ? $row['points_total'] : 0;
                        echo "<tr>";
                        echo "<td>#".$row['user_id']."</td>";
                        echo "<td>".htmlspecialchars($row['full_name'])."</td>";
                        echo "<td>".htmlspecialchars($row['email'])."</td>";
                        echo "<td><b style='color:#1e3a8a;'>".$pts."</b></td>";
                        echo "<td>
                                <a href='edit_user.php?id=".$row['user_id']."' class='btn-edit'>Edit</a>
                                <a href='users.php?delete=".$row['user_id']."' 
                                   onclick='return confirm(\"Are you sure you want to delete this user?\")'>
                                   <button class='action-btn delete'>Delete</button>
                                </a>
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