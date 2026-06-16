<?php
require_once "../config/db_conn.php";
require_once "../config/session.php";
requireRole('admin');

$page = 'stats';

// DATA 1: Users per Community
$com_sql = "SELECT c.municipality_name, COUNT(u.user_id) as count 
            FROM communities c 
            LEFT JOIN users u ON c.community_id = u.community_id 
            GROUP BY c.community_id";
$com_res = mysqli_query($conn, $com_sql);
$com_labels = [];
$com_data = [];
while($row = mysqli_fetch_assoc($com_res)){
    $com_labels[] = $row['municipality_name'];
    $com_data[] = $row['count'];
}

// DATA 2: Task Status Distribution
$task_sql = "SELECT status, COUNT(*) as count FROM volunteer_requests GROUP BY status";
$task_res = mysqli_query($conn, $task_sql);
$task_labels = [];
$task_data = [];
while($row = mysqli_fetch_assoc($task_res)){
    $task_labels[] = ucfirst($row['status']);
    $task_data[] = $row['count'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Site Statistics</title>
    <link rel="stylesheet" href="../assets/css/admin.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .charts-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .chart-box { background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
    </style>
</head>
<body>
    <?php include "../includes/admin_sidebar.php"; ?>

    <div class="main">
        <div class="header">
            <h1>Site Analytics</h1>
        </div>

        <div class="charts-grid">
            <div class="chart-box">
                <h3>Users per Community</h3>
                <canvas id="comChart"></canvas>
            </div>
            <div class="chart-box">
                <h3>Task Status Breakdown</h3>
                <canvas id="taskChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Chart 1: Users per Community
        new Chart(document.getElementById('comChart'), {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($com_labels); ?>,
                datasets: [{
                    label: 'Users',
                    data: <?php echo json_encode($com_data); ?>,
                    backgroundColor: '#3b82f6'
                }]
            }
        });

        // Chart 2: Tasks
        new Chart(document.getElementById('taskChart'), {
            type: 'doughnut',
            data: {
                labels: <?php echo json_encode($task_labels); ?>,
                datasets: [{
                    data: <?php echo json_encode($task_data); ?>,
                    backgroundColor: ['#4ade80', '#facc15', '#f87171', '#60a5fa']
                }]
            }
        });
    </script>
</body>
</html>