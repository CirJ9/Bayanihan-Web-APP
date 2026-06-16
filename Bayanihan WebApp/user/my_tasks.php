<?php
require_once "../config/session.php";
require_once "../config/db_conn.php";
requireLogin();

$page = 'my_tasks'; // Sidebar highlight
$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// --- HANDLE CANCELLATION LOGIC ---
if (isset($_POST['cancel_task'])) {
    $vol_id = $_POST['vol_id'];
    $current_status = $_POST['current_status'];
    
    // CASE 1: If Pending -> Just Cancel (No Penalty)
    if ($current_status == 'pending') {
        $sql = "UPDATE task_volunteers SET status='cancelled' WHERE id=?";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "i", $vol_id);
        mysqli_stmt_execute($stmt);
        $msg = "Application cancelled. No points deducted.";
        $msg_type = "info";
    } 
    // CASE 2: If Accepted -> Cancel AND Penalize
    elseif ($current_status == 'accepted') {
        // Start Transaction to ensure both happen or neither
        mysqli_begin_transaction($conn);
        try {
            // A. Update Status to Cancelled
            $sql1 = "UPDATE task_volunteers SET status='cancelled' WHERE id=?";
            $stmt1 = mysqli_prepare($conn, $sql1);
            mysqli_stmt_bind_param($stmt1, "i", $vol_id);
            mysqli_stmt_execute($stmt1);

            // B. Deduct Points (Penalty: 20 Points)
            $penalty = 20;
            // Prevent negative points (Optional: Remove GREATEST if you allow debt)
            $sql2 = "UPDATE points SET points_total = GREATEST(0, points_total - ?) WHERE user_id=?";
            $stmt2 = mysqli_prepare($conn, $sql2);
            mysqli_stmt_bind_param($stmt2, "ii", $penalty, $user_id);
            mysqli_stmt_execute($stmt2);

            mysqli_commit($conn);
            $msg = "Task cancelled. You have been penalized $penalty points for cancelling an accepted task.";
            $msg_type = "danger";
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $msg = "Error cancelling task.";
            $msg_type = "danger";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Volunteer Tasks</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/main.css">
    
    <style>
        .task-list {
            display: grid;
            gap: 20px;
        }
        .task-item {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-left: 5px solid #ccc;
        }
        /* Color Coded Borders */
        .task-item.accepted { border-left-color: #16a34a; } /* Green */
        .task-item.pending { border-left-color: #eab308; } /* Yellow */
        .task-item.cancelled { border-left-color: #dc2626; opacity: 0.7; } /* Red */
        .task-item.completed { border-left-color: #2563eb; } /* Blue */

        .info h3 { color: #1e3a8a; font-weight: 600; }
        
        .status-badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-accepted { background: #dcfce7; color: #166534; }
        .status-pending { background: #fef9c3; color: #854d0e; }
        .status-cancelled { background: #fee2e2; color: #991b1b; }
        .status-completed { background: #dbeafe; color: #1e40af; }

        /* CANCEL BUTTON STYLE */
        .btn-cancel {
            background: #fff;
            border: 1px solid #dc2626;
            color: #dc2626;
            padding: 8px 16px;
            border-radius: 6px;
            cursor: pointer;
            transition: 0.3s;
            font-weight: 500;
        }
        .btn-cancel:hover { background: #dc2626; color: white; }
        
        /* Disable button if cancelled */
        .task-item.cancelled .btn-cancel,
        .task-item.completed .btn-cancel {
            display: none;
        }
    </style>
</head>
<body>

    <?php include "../includes/sidebar.php"; ?>

    <div class="container">
        <?php include "../includes/header.php"; ?>

        <div class="content">
            <h2 style="color:#1e3a8a; margin-bottom:20px;">My Volunteer Missions</h2>

            <?php if($msg): ?>
                <div class="alert" style="padding:15px; margin-bottom:20px; border-radius:8px; 
                    background: <?php echo ($msg_type=='danger')?'#fee2e2':'#d1fae5'; ?>;
                    color: <?php echo ($msg_type=='danger')?'#991b1b':'#065f46'; ?>;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <div class="task-list">
                <?php
                // Fetch tasks I volunteered for
                $sql = "SELECT tv.id as vol_id, tv.status as my_status, tv.joined_at, 
                               r.title, r.description, r.reward_points, c.municipality_name 
                        FROM task_volunteers tv
                        JOIN volunteer_requests r ON tv.request_id = r.request_id
                        LEFT JOIN communities c ON r.community_id = c.community_id
                        WHERE tv.user_id = ? 
                        ORDER BY tv.joined_at DESC";
                
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $user_id);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        $status = $row['my_status']; // pending, accepted, cancelled, completed
                ?>
                    <div class="task-item <?php echo $status; ?>">
                        <div class="info">
                            <h3 style="margin-bottom:5px;"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <p style="color:#666; font-size:14px; margin-bottom:8px;">
                                <?php echo htmlspecialchars(substr($row['description'], 0, 80)); ?>...
                            </p>
                            <div style="font-size:13px; color:#888; display:flex; gap:15px;">
                                <span><span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle;">location_on</span> <?php echo $row['municipality_name']; ?></span>
                                <span><span class="material-symbols-outlined" style="font-size:14px; vertical-align:middle;">calendar_month</span> <?php echo date("M d, Y", strtotime($row['joined_at'])); ?></span>
                            </div>
                        </div>

                        <div class="actions" style="text-align:right;">
                            <span class="status-badge status-<?php echo $status; ?>">
                                <?php echo ucfirst($status); ?>
                            </span>
                            <br><br>
                            
                            <?php if ($status == 'pending' || $status == 'accepted'): ?>
                                <form method="POST" onsubmit="return confirmCancel('<?php echo $status; ?>');">
                                    <input type="hidden" name="vol_id" value="<?php echo $row['vol_id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $status; ?>">
                                    
                                    <button type="submit" name="cancel_task" class="btn-cancel">
                                        <?php echo ($status == 'accepted') ? 'Cancel (Penalty)' : 'Cancel Application'; ?>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php 
                    }
                } else {
                    echo "<p style='color:#777; text-align:center; padding:20px;'>You haven't volunteered for any tasks yet. Check the Dashboard!</p>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        function confirmCancel(status) {
            if (status === 'accepted') {
                return confirm("WARNING: You have been accepted for this task.\n\nCancelling now will deduct 20 POINTS from your account.\n\nAre you sure you want to proceed?");
            } else {
                return confirm("Are you sure you want to cancel your application?");
            }
        }
    </script>
</body>
</html>