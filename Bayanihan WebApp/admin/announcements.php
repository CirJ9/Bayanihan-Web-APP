<?php
require_once "../config/db_conn.php";
require_once "../config/session.php";
requireRole('admin');

$page = 'announce';

// HANDLE POSTING
if (isset($_POST['post_announcement'])) {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $admin_id = $_SESSION['user_id'];

    $stmt = mysqli_prepare($conn, "INSERT INTO announcements (title, message, admin_id) VALUES (?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssi", $title, $message, $admin_id);
    mysqli_stmt_execute($stmt);
}

// HANDLE DELETING
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    mysqli_query($conn, "DELETE FROM announcements WHERE id='$id'");
    header("Location: announcements.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Announcements</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        /* Simple form styling for Admin */
        .form-box { background:white; padding:20px; border-radius:10px; margin-bottom:20px; }
        .form-input { width:100%; padding:10px; margin-bottom:10px; border:1px solid #ddd; border-radius:5px; }
        .btn-post { background:#2563eb; color:white; padding:10px 20px; border:none; cursor:pointer; border-radius:5px; }
    </style>
</head>
<body>
    <?php include "../includes/admin_sidebar.php"; ?>

    <div class="main">
        <div class="header">
            <h1>Announcements</h1>
        </div>

        <div class="form-box">
            <h3>Post New Update</h3>
            <form method="POST">
                <input type="text" name="title" class="form-input" placeholder="Title (e.g. System Maintenance)" required>
                <textarea name="message" class="form-input" rows="4" placeholder="Message details..." required></textarea>
                <button type="submit" name="post_announcement" class="btn-post">Post Announcement</button>
            </form>
        </div>

        <div class="table-container">
            <h3>History</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Title</th>
                        <th>Message Preview</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT * FROM announcements ORDER BY created_at DESC";
                    $result = mysqli_query($conn, $sql);

                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>".date("M d, Y", strtotime($row['created_at']))."</td>";
                        echo "<td>".htmlspecialchars($row['title'])."</td>";
                        echo "<td>".substr(htmlspecialchars($row['message']), 0, 50)."...</td>";
                        echo "<td>
                                <a href='announcements.php?delete=".$row['id']."' style='color:red;'>Delete</a>
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