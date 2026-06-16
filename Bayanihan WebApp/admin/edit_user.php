<?php
require_once "../config/db_conn.php";
require_once "../config/session.php";
requireRole('admin');

$id = $_GET['id'] ?? null;
if (!$id) { header("Location: users.php"); exit(); }

$msg = "";

// FETCH USER DATA
$sql = "SELECT u.*, p.points_total FROM users u LEFT JOIN points p ON u.user_id = p.user_id WHERE u.user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$user = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// HANDLE UPDATE
if (isset($_POST['update_user'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $points = $_POST['points'];

    mysqli_begin_transaction($conn);
    try {
        // 1. Update Info
        $u_sql = "UPDATE users SET full_name=?, email=? WHERE user_id=?";
        $u_stmt = mysqli_prepare($conn, $u_sql);
        mysqli_stmt_bind_param($u_stmt, "ssi", $name, $email, $id);
        mysqli_stmt_execute($u_stmt);

        // 2. Update Points (Insert if not exists)
        $p_sql = "INSERT INTO points (user_id, points_total) VALUES (?, ?) ON DUPLICATE KEY UPDATE points_total=?";
        $p_stmt = mysqli_prepare($conn, $p_sql);
        mysqli_stmt_bind_param($p_stmt, "iii", $id, $points, $points);
        mysqli_stmt_execute($p_stmt);

        mysqli_commit($conn);
        $msg = "User updated successfully!";
        // Refresh data
        $user['full_name'] = $name;
        $user['email'] = $email;
        $user['points_total'] = $points;
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $msg = "Error updating user.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit User</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <style>
        .form-container { background:white; padding:30px; border-radius:10px; max-width:600px; margin:0 auto; }
        .form-group { margin-bottom:15px; }
        .form-group label { display:block; margin-bottom:5px; font-weight:bold; }
        .form-group input { width:100%; padding:10px; border:1px solid #ddd; border-radius:5px; }
        .btn-save { background:#1e3a8a; color:white; padding:10px 20px; border:none; border-radius:5px; cursor:pointer; }
    </style>
</head>
<body>
    <?php include "../includes/admin_sidebar.php"; ?>

    <div class="main">
        <div class="header">
            <h1>Edit User #<?php echo $id; ?></h1>
            <a href="users.php" style="color:#555;">&larr; Back</a>
        </div>

        <?php if($msg) echo "<div style='background:#dcfce7; color:#166534; padding:10px; margin-bottom:20px; border-radius:5px;'>$msg</div>"; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Wallet Points (Modify)</label>
                    <input type="number" name="points" value="<?php echo $user['points_total'] ?? 0; ?>" required>
                </div>
                <button type="submit" name="update_user" class="btn-save">Save Changes</button>
            </form>
        </div>
    </div>
</body>
</html>