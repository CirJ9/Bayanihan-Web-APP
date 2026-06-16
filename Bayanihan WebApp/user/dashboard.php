<?php
require_once "../config/session.php";
require_once "../config/db_conn.php";
requireLogin();

$page = 'home';
$user_id = $_SESSION['user_id'];
$msg = isset($_GET['msg']) ? $_GET['msg'] : "";

// HANDLE JOIN TASK
if (isset($_POST['join_task'])) {
    $req_id = $_POST['request_id'];
    
    // Check duplication
    $check_sql = "SELECT * FROM task_volunteers WHERE request_id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $check_sql);
    mysqli_stmt_bind_param($stmt, "ii", $req_id, $user_id);
    mysqli_stmt_execute($stmt);
    if (mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0) {
        $msg = "You have already applied.";
    } else {
        // Status defaults to 'pending'
        $join_sql = "INSERT INTO task_volunteers (request_id, user_id, status) VALUES (?, ?, 'pending')";
        $join_stmt = mysqli_prepare($conn, $join_sql);
        mysqli_stmt_bind_param($join_stmt, "ii", $req_id, $user_id);
        if (mysqli_stmt_execute($join_stmt)) {
            $msg = "Application sent! Waiting for approval.";
        }
    }
}

// FETCH STATS
$helpers = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM users WHERE role='user'"))['c'];
$tasks = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM volunteer_requests"))['c'];
$completed = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM volunteer_requests WHERE status='completed'"))['c'];
$communities = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as c FROM communities"))['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/user_dashboard.css">
    <style>
        .task-feed { max-height: 600px; overflow-y: auto; display: flex; flex-direction: column; gap: 15px; padding-right:10px; }
        .feed-card { background: white; padding: 20px; border-radius: 12px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); border-left: 5px solid #1e3a8a; }
        .feed-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
        .btn-join { background: #1e3a8a; color: white; border: none; padding: 8px 18px; border-radius: 6px; cursor: pointer; }
        .btn-join:hover { background: #152c6f; }
        .capacity-badge { font-size:12px; background:#e0f2fe; color:#0369a1; padding:3px 8px; border-radius:10px; font-weight:bold; }
    </style>
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>
    <div class="container">
        <?php include "../includes/header.php"; ?>
        <div class="content">
            <?php if($msg): ?>
                <div class="alert" style="padding:15px; margin-bottom:20px; border-radius:8px; background:#d1fae5; color:#065f46;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <div class="cards">
                <div class="card"><h3>Helpers</h3><h2><?php echo $helpers; ?></h2></div>
                <div class="card"><h3>Requests</h3><h2><?php echo $tasks; ?></h2></div>
                <div class="card"><h3>Completed</h3><h2><?php echo $completed; ?></h2></div>
                <div class="card"><h3>Communities</h3><h2><?php echo $communities; ?></h2></div>
            </div>

            <h2 style="color:#1e3a8a; margin-top:20px;">Volunteer Opportunities</h2>
            
            <div class="feed-section" style="display:grid; grid-template-columns: 2fr 1fr; gap:25px; margin-top:20px;">
                <div class="task-feed">
                    <?php
                    // SQL: Fetch Open Tasks + Count Accepted Volunteers
                    $feed_sql = "SELECT r.*, c.municipality_name,
                                 (SELECT COUNT(*) FROM task_volunteers tv WHERE tv.request_id = r.request_id AND tv.status = 'accepted') as accepted_count
                                 FROM volunteer_requests r
                                 LEFT JOIN communities c ON r.community_id = c.community_id
                                 WHERE r.status = 'open' 
                                 AND r.poster_id != ? 
                                 AND r.request_id NOT IN (SELECT request_id FROM task_volunteers WHERE user_id = ?)
                                 ORDER BY r.created_at DESC";
                    
                    $stmt = mysqli_prepare($conn, $feed_sql);
                    mysqli_stmt_bind_param($stmt, "ii", $user_id, $user_id);
                    mysqli_stmt_execute($stmt);
                    $feed_res = mysqli_stmt_get_result($stmt);

                    $has_tasks = false;
                    while ($row = mysqli_fetch_assoc($feed_res)) {
                        // LOGIC: Check if task is FULL
                        // If accepted count is equal to or greater than needed, we skip showing it
                        if ($row['accepted_count'] >= $row['volunteers_needed']) {
                            continue; 
                        }
                        $has_tasks = true;
                    ?>
                        <div class="feed-card">
                            <div class="feed-header">
                                <h4 style="color:#1e3a8a; margin:0;"><?php echo htmlspecialchars($row['title']); ?></h4>
                                <span class="capacity-badge">
                                    <?php echo $row['accepted_count']; ?> / <?php echo $row['volunteers_needed']; ?> Filled
                                </span>
                            </div>
                            <p style="font-size:14px; color:#555; margin-bottom:15px;">
                                <?php echo htmlspecialchars(substr($row['description'], 0, 100)); ?>...
                            </p>
                            <div style="display:flex; justify-content:space-between; align-items:center;">
                                <span style="font-size:13px; color:#777;">
                                    <span class="material-symbols-outlined" style="font-size:16px; vertical-align:middle;">location_on</span>
                                    <?php echo htmlspecialchars($row['municipality_name']); ?>
                                </span>
                                <form method="POST">
                                    <input type="hidden" name="request_id" value="<?php echo $row['request_id']; ?>">
                                    <button type="submit" name="join_task" class="btn-join">Apply</button>
                                </form>
                            </div>
                        </div>
                    <?php 
                    } 
                    if (!$has_tasks) echo "<p style='color:#777; text-align:center;'>No available missions.</p>";
                    ?>
                </div>
                
                <div class="ranking" style="background:white; padding:20px; border-radius:12px; height:fit-content;">
                    <h3 style="color:#1e3a8a; margin-bottom:15px; border-bottom:1px solid #eee; padding-bottom:10px;">Top Volunteers</h3>
                    <?php
                    $rank_res = mysqli_query($conn, "SELECT u.full_name, p.points_total FROM points p JOIN users u ON p.user_id = u.user_id ORDER BY p.points_total DESC LIMIT 5");
                    while($row = mysqli_fetch_assoc($rank_res)){
                        echo "<div style='display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid #f9f9f9;'>";
                        echo "<span>".htmlspecialchars($row['full_name'])."</span>";
                        echo "<span style='font-weight:bold; color:#1e3a8a;'>".$row['points_total']."</span>";
                        echo "</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>