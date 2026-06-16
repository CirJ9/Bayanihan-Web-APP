<?php
require_once "../config/session.php";
require_once "../config/db_conn.php";
requireLogin();

$user_id = $_SESSION['user_id'];
$page = 'request'; 
$msg = "";
$msg_type = "";

// --- 1. HANDLE VOLUNTEER ACTIONS (Accept/Decline) ---
if (isset($_POST['action'])) {
    $vol_entry_id = $_POST['vol_entry_id'];
    $request_id = $_POST['request_id'];
    $action = $_POST['action']; 

    if ($action == 'accept') {
        // Check Capacity
        $count_sql = "SELECT COUNT(*) as c FROM task_volunteers WHERE request_id=? AND status='accepted'";
        $stmt = mysqli_prepare($conn, $count_sql);
        mysqli_stmt_bind_param($stmt, "i", $request_id);
        mysqli_stmt_execute($stmt);
        $current_accepted = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt))['c'];

        // Get Max Needed
        $req_sql = "SELECT volunteers_needed FROM volunteer_requests WHERE request_id=?";
        $stmt2 = mysqli_prepare($conn, $req_sql);
        mysqli_stmt_bind_param($stmt2, "i", $request_id);
        mysqli_stmt_execute($stmt2);
        $max_needed = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt2))['volunteers_needed'];

        if ($current_accepted >= $max_needed) {
            $msg = "Cannot accept: Task is full!";
            $msg_type = "danger";
        } else {
            mysqli_query($conn, "UPDATE task_volunteers SET status='accepted' WHERE id='$vol_entry_id'");
            $msg = "Volunteer accepted!";
            $msg_type = "success";
        }
    } elseif ($action == 'decline') {
        mysqli_query($conn, "UPDATE task_volunteers SET status='declined' WHERE id='$vol_entry_id'");
        $msg = "Volunteer declined.";
        $msg_type = "warning";
    }
}

// --- 2. HANDLE TASK LIFECYCLE (Start / End) ---
if (isset($_POST['task_action'])) {
    $req_id = $_POST['req_id'];
    $task_act = $_POST['task_action']; 

    // Verify ownership
    $check_owner = mysqli_query($conn, "SELECT * FROM volunteer_requests WHERE request_id='$req_id' AND poster_id='$user_id'");
    
    if (mysqli_num_rows($check_owner) > 0) {
        $task_data = mysqli_fetch_assoc($check_owner);
        
        // A. START TASK
        if ($task_act == 'start') {
            if ($task_data['status'] == 'open') {
                mysqli_query($conn, "UPDATE volunteer_requests SET status='in_progress' WHERE request_id='$req_id'");
                $msg = "Task started! Volunteers notified.";
                $msg_type = "info";
            }
        }
        // B. END TASK & DISTRIBUTE POINTS
        elseif ($task_act == 'end') {
            if ($task_data['status'] == 'in_progress') {
                mysqli_begin_transaction($conn);
                try {
                    // 1. Mark Request Completed
                    mysqli_query($conn, "UPDATE volunteer_requests SET status='completed' WHERE request_id='$req_id'");

                    // 2. Mark Volunteers Completed
                    mysqli_query($conn, "UPDATE task_volunteers SET status='completed' WHERE request_id='$req_id' AND status='accepted'");

                    // 3. DISTRIBUTE POINTS
                    $points = $task_data['reward_points'];
                    
                    // Get all valid volunteers
                    $v_sql = "SELECT user_id FROM task_volunteers WHERE request_id='$req_id' AND status='completed'";
                    $v_res = mysqli_query($conn, $v_sql);

                    while ($vol = mysqli_fetch_assoc($v_res)) {
                        $vid = $vol['user_id'];
                        
                        // ROBUST POINT ADDITION:
                        // If user has wallet, update it. If not, create it with points.
                        // (Requires 'user_id' to be UNIQUE in 'points' table, which your SQL dump confirms)
                        $pt_sql = "INSERT INTO points (user_id, points_total) VALUES ('$vid', '$points') 
                                   ON DUPLICATE KEY UPDATE points_total = points_total + VALUES(points_total)";
                        mysqli_query($conn, $pt_sql);
                    }

                    mysqli_commit($conn);
                    $msg = "Task ended! $points points sent to all volunteers.";
                    $msg_type = "success";

                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $msg = "Error ending task.";
                    $msg_type = "danger";
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Volunteers</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/main.css">
    <style>
        .request-group { background: white; border-radius: 12px; padding: 25px; margin-bottom: 30px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 5px solid #ccc; }
        
        .status-open { border-left-color: #16a34a; }        
        .status-in_progress { border-left-color: #eab308; } 
        .status-completed { border-left-color: #2563eb; }   
        
        .request-header { border-bottom: 2px solid #f0f0f0; padding-bottom: 15px; margin-bottom: 15px; display:flex; justify-content:space-between; align-items:center; }
        
        .status-badge { padding: 5px 12px; border-radius: 20px; font-size: 12px; font-weight: bold; text-transform: uppercase; display:inline-block; margin-top:5px; }
        .bg-open { background: #dcfce7; color: #166534; }
        .bg-in_progress { background: #fef9c3; color: #854d0e; }
        .bg-completed { background: #dbeafe; color: #1e40af; }

        .vol-list { display: grid; gap: 10px; }
        .vol-item { background: #f9fafb; padding: 15px; border-radius: 8px; display: flex; justify-content: space-between; align-items: center; }
        
        .btn-act { padding: 6px 14px; border-radius: 6px; cursor: pointer; border: none; font-size: 13px; font-weight: 500; transition:0.2s; }
        .btn-accept { background: #dcfce7; color: #166534; } .btn-accept:hover { background: #16a34a; color: white; }
        .btn-decline { background: #fee2e2; color: #991b1b; } .btn-decline:hover { background: #dc2626; color: white; }
        
        .task-controls { margin-top: 15px; padding-top: 15px; border-top: 1px dashed #ddd; text-align: right; }
        .btn-start { background: #eab308; color: white; } .btn-start:hover { background: #ca8a04; }
        .btn-end { background: #2563eb; color: white; } .btn-end:hover { background: #1d4ed8; }
        .btn-disabled { background: #ccc; cursor: not-allowed; color: #666; }
    </style>
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>
    <div class="container">
        <?php include "../includes/header.php"; ?>
        <div class="content">
            <h2 style="color:#1e3a8a; margin-bottom:20px;">Manage My Requests</h2>
            
            <?php if($msg): ?>
                <div class="alert" style="padding:15px; margin-bottom:20px; border-radius:8px; 
                    background: <?php echo ($msg_type=='success' || $msg_type=='info') ? '#d1fae5' : '#fee2e2'; ?>; 
                    color: #333;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <?php
            $sql = "SELECT * FROM volunteer_requests WHERE poster_id = '$user_id' ORDER BY created_at DESC";
            $res = mysqli_query($conn, $sql);

            if(mysqli_num_rows($res) > 0) {
                while($req = mysqli_fetch_assoc($res)) {
                    $req_id = $req['request_id'];
                    $status = $req['status'];
                    
                    $c_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM task_volunteers WHERE request_id='$req_id' AND status IN ('accepted', 'completed')");
                    $accepted_count = mysqli_fetch_assoc($c_res)['c'];
            ?>
                <div class="request-group status-<?php echo $status; ?>">
                    <div class="request-header">
                        <div>
                            <h3 style="color:#1e3a8a; margin:0;"><?php echo htmlspecialchars($req['title']); ?></h3>
                            <span class="status-badge bg-<?php echo $status; ?>"><?php echo ucfirst(str_replace('_', ' ', $status)); ?></span>
                            <div style="font-size:13px; color:#777; margin-top:5px;">
                                <?php echo date("M d, Y", strtotime($req['created_at'])); ?> • Reward: <?php echo $req['reward_points']; ?> pts
                            </div>
                        </div>
                        <div style="text-align:right;">
                            <span style="font-weight:bold; font-size:18px; color: #1e3a8a;"><?php echo $accepted_count; ?> / <?php echo $req['volunteers_needed']; ?></span>
                            <br><small>Volunteers</small>
                        </div>
                    </div>

                    <?php if($status != 'completed'): ?>
                        <h4 style="font-size:14px; margin-bottom:10px; color:#555;">Pending Applicants:</h4>
                        <div class="vol-list">
                            <?php
                            $v_sql = "SELECT tv.id as entry_id, u.full_name, u.phone 
                                      FROM task_volunteers tv 
                                      JOIN users u ON tv.user_id = u.user_id 
                                      WHERE tv.request_id = '$req_id' AND tv.status = 'pending'";
                            $v_res = mysqli_query($conn, $v_sql);

                            if(mysqli_num_rows($v_res) > 0) {
                                while($vol = mysqli_fetch_assoc($v_res)) {
                            ?>
                                <div class="vol-item">
                                    <div>
                                        <strong><?php echo htmlspecialchars($vol['full_name']); ?></strong><br>
                                        <small><?php echo htmlspecialchars($vol['phone']); ?></small>
                                    </div>
                                    <form method="POST" style="display:flex; gap:10px;">
                                        <input type="hidden" name="vol_entry_id" value="<?php echo $vol['entry_id']; ?>">
                                        <input type="hidden" name="request_id" value="<?php echo $req_id; ?>">
                                        <button type="submit" name="action" value="accept" class="btn-act btn-accept">Accept</button>
                                        <button type="submit" name="action" value="decline" class="btn-act btn-decline">Decline</button>
                                    </form>
                                </div>
                            <?php 
                                }
                            } else {
                                echo "<p style='color:#999; font-style:italic; font-size:14px;'>No new applicants.</p>";
                            }
                            ?>
                        </div>
                    <?php endif; ?>

                    <div class="task-controls">
                        <form method="POST">
                            <input type="hidden" name="req_id" value="<?php echo $req_id; ?>">
                            
                            <?php if ($status == 'open'): ?>
                                <?php if ($accepted_count > 0): ?>
                                    <button type="submit" name="task_action" value="start" class="btn-act btn-start">Start Task Now</button>
                                <?php else: ?>
                                    <span style="color:#888; font-size:13px; margin-right:10px;">Accept volunteers to start</span>
                                    <button type="button" class="btn-act btn-disabled">Start Task</button>
                                <?php endif; ?>
                                
                            <?php elseif ($status == 'in_progress'): ?>
                                <span style="color:#eab308; font-weight:bold; margin-right:10px;">Mission in Progress...</span>
                                <button type="submit" name="task_action" value="end" class="btn-act btn-end" onclick="return confirm('Are you sure the task is finished? This will send points to all volunteers.');">End Task & Distribute Points</button>
                            
                            <?php elseif ($status == 'completed'): ?>
                                <span style="color:#16a34a; font-weight:bold;">Mission Accomplished & Points Distributed</span>
                            <?php endif; ?>
                        </form>
                    </div>

                </div>
            <?php 
                }
            } else {
                echo "<p>You haven't posted any requests.</p>";
            }
            ?>
        </div>
    </div>
</body>
</html>