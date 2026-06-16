<?php
require_once "../config/session.php";
require_once "../config/db_conn.php";
requireLogin();

$page = 'request';
$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// --- HANDLE CANCEL REQUEST ---
if (isset($_POST['cancel_id'])) {
    $cancel_id = $_POST['cancel_id'];
    
    // Check if task is eligible (Only 'open' tasks can be cancelled to avoid disrupting active volunteers)
    $check_sql = "SELECT status FROM volunteer_requests WHERE request_id = ? AND poster_id = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "ii", $cancel_id, $user_id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $task = mysqli_fetch_assoc($res);

    if ($task && $task['status'] == 'open') {
        $update_sql = "UPDATE volunteer_requests SET status = 'cancelled' WHERE request_id = ?";
        $up_stmt = mysqli_prepare($conn, $update_sql);
        mysqli_stmt_bind_param($up_stmt, "i", $cancel_id);
        
        if (mysqli_stmt_execute($up_stmt)) {
            $msg = "Request cancelled successfully.";
            $msg_type = "success";
        } else {
            $msg = "Error cancelling request.";
            $msg_type = "danger";
        }
    } else {
        $msg = "Cannot cancel: Task is already ongoing, completed, or cancelled.";
        $msg_type = "warning";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Request History</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/main.css"> 
    <style>
        .content { display: flex; flex-direction: column; align-items: center; }
        .table-card { background: white; padding: 30px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); width: 95%; max-width: 1100px; }
        .action-btn { padding: 5px 10px; font-size: 12px; border-radius: 5px; text-decoration: none; color: white; display: inline-block; margin-right: 5px; border:none; cursor: pointer; }
        .btn-edit { background: #3b82f6; } .btn-edit:hover { background: #2563eb; color:white; }
        .btn-cancel { background: #ef4444; } .btn-cancel:hover { background: #dc2626; color:white; }
        .disabled { opacity: 0.5; pointer-events: none; }
    </style>
</head>

<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="container">
        <?php include "../includes/header.php"; ?>

        <div class="content">
            <div style="width:95%; max-width:1100px; display:flex; justify-content:space-between; margin-bottom:20px;">
                <h2 style="color:#1e3a8a; margin:0; font-weight:700;">My Requests</h2>
                
                <div style="display:flex; gap:10px;">
                    <a href="manage_requests.php" class="btn btn-warning d-flex align-items-center gap-2" style="color:white; font-weight:bold;">
                        <span class="material-symbols-outlined">group</span> Manage Volunteers
                    </a>
                    <a href="request.php" class="btn btn-primary d-flex align-items-center gap-2" style="background:#1e3a8a; border:none;">
                        <span class="material-symbols-outlined">add_circle</span> Post New
                    </a>
                </div>
            </div>

            <?php if($msg): ?>
                <div class="alert alert-<?php echo $msg_type; ?>" style="width:95%; max-width:1100px;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <div class="table-card">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Task Title</th>
                                <th>Status</th>
                                <th>Needed</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT * FROM volunteer_requests WHERE poster_id = '$user_id' ORDER BY created_at DESC";
                            $result = mysqli_query($conn, $sql);
                            
                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    $status = $row['status'];
                                    $badge = ($status == 'completed') ? 'success' : (($status == 'cancelled') ? 'danger' : 'warning');
                                    
                                    // Disable buttons if not Open
                                    $isDisabled = ($status != 'open') ? 'disabled' : '';
                            ?>
                                <tr>
                                    <td>#<?php echo $row["request_id"]; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row["title"]); ?></strong><br>
                                        <small class="text-muted"><?php echo date("M d, Y", strtotime($row['created_at'])); ?></small>
                                    </td>
                                    <td><span class="badge bg-<?php echo $badge; ?>"><?php echo ucfirst($status); ?></span></td>
                                    <td><?php echo $row["volunteers_needed"]; ?></td>
                                    <td>
                                        <a href="edit_request.php?id=<?php echo $row['request_id']; ?>" class="action-btn btn-edit <?php echo $isDisabled; ?>">
                                            Edit
                                        </a>
                                        
                                        <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to cancel this request?');">
                                            <input type="hidden" name="cancel_id" value="<?php echo $row['request_id']; ?>">
                                            <button type="submit" class="action-btn btn-cancel <?php echo $isDisabled; ?>">
                                                Cancel
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-3'>No requests found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>