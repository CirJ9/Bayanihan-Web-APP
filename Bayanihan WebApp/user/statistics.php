<?php
require_once "../config/session.php";
require_once "../config/db_conn.php";
requireLogin();

$page = 'stats';
$user_id = $_SESSION['user_id'];

// --- 1. FETCH KEY METRICS ---

// A. Total Points
$points_res = mysqli_query($conn, "SELECT points_total FROM points WHERE user_id='$user_id'");
$my_points = ($row = mysqli_fetch_assoc($points_res)) ? $row['points_total'] : 0;

// B. Tasks Completed
$comp_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM task_volunteers WHERE user_id='$user_id' AND status='completed'");
$completed_tasks = mysqli_fetch_assoc($comp_res)['c'];

// C. Global Rank (Logic: Count how many people have MORE points than me, then add 1)
$rank_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM points WHERE points_total > $my_points");
$my_rank = mysqli_fetch_assoc($rank_res)['c'] + 1;

// D. Success Rate (Completed / Total Joined)
$total_joined_res = mysqli_query($conn, "SELECT COUNT(*) as c FROM task_volunteers WHERE user_id='$user_id'");
$total_joined = mysqli_fetch_assoc($total_joined_res)['c'];
$success_rate = ($total_joined > 0) ? round(($completed_tasks / $total_joined) * 100) : 0;


// --- 2. FETCH DATA FOR CHARTS ---

// CHART 1 DATA: Task Status Breakdown
// Result format: ['pending'=>2, 'completed'=>5, 'cancelled'=>1]
$status_sql = "SELECT status, COUNT(*) as c FROM task_volunteers WHERE user_id='$user_id' GROUP BY status";
$status_res = mysqli_query($conn, $status_sql);
$status_data = [];
while($row = mysqli_fetch_assoc($status_res)) {
    $status_data[$row['status']] = $row['c'];
}
// Ensure defaults exist to prevent JS errors
$s_pending   = $status_data['pending']   ?? 0;
$s_accepted  = $status_data['accepted']  ?? 0;
$s_completed = $status_data['completed'] ?? 0;
$s_cancelled = $status_data['cancelled'] ?? 0;
// Group 'pending' and 'joined' if you use both
$s_pending += $status_data['joined'] ?? 0;


// CHART 2 DATA: Monthly Activity (Last 6 Months)
// We want to count how many tasks were joined in each month
$months = [];
$activity_counts = [];

for ($i = 5; $i >= 0; $i--) {
    $month_start = date("Y-m-01", strtotime("-$i months"));
    $month_end   = date("Y-m-t", strtotime("-$i months"));
    $month_label = date("M", strtotime("-$i months")); // Jan, Feb...
    
    $act_sql = "SELECT COUNT(*) as c FROM task_volunteers 
                WHERE user_id='$user_id' 
                AND joined_at BETWEEN '$month_start 00:00:00' AND '$month_end 23:59:59'";
    $act_res = mysqli_query($conn, $act_sql);
    $count = mysqli_fetch_assoc($act_res)['c'];
    
    $months[] = $month_label;
    $activity_counts[] = $count;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Statistics</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/statistics.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

    <?php include "../includes/sidebar.php"; ?>

    <div class="container">
        <?php include "../includes/header.php"; ?>

        <div class="content">
            <h2 style="color:#1e3a8a; margin-bottom:20px;">Performance Analytics</h2>

            <div class="cards">
                <div class="card">
                    <h3>Current Points</h3>
                    <h2 style="color:#1e3a8a;"><?php echo number_format($my_points); ?></h2>
                    <span class="material-symbols-outlined" style="color:#eab308; font-size:30px;">military_tech</span>
                </div>
                
                <div class="card">
                    <h3>Community Rank</h3>
                    <h2 style="color:#2563eb;">#<?php echo $my_rank; ?></h2>
                    <span class="material-symbols-outlined" style="color:#2563eb; font-size:30px;">leaderboard</span>
                </div>

                <div class="card">
                    <h3>Tasks Completed</h3>
                    <h2 style="color:#16a34a;"><?php echo $completed_tasks; ?></h2>
                    <span class="material-symbols-outlined" style="color:#16a34a; font-size:30px;">task_alt</span>
                </div>

                <div class="card">
                    <h3>Success Rate</h3>
                    <h2 style="color:#9333ea;"><?php echo $success_rate; ?>%</h2>
                    <span class="material-symbols-outlined" style="color:#9333ea; font-size:30px;">trending_up</span>
                </div>
            </div>

            <div class="statistics-section">
                <div class="charts">
                    
                    <div class="chart-card">
                        <h3 style="margin-bottom:15px;">Mission Status</h3>
                        <canvas id="statusChart"></canvas>
                    </div>

                    <div class="chart-card" style="width: 100%; max-width: 600px;">
                        <h3 style="margin-bottom:15px;">Activity (Last 6 Months)</h3>
                        <canvas id="activityChart"></canvas>
                    </div>
                    
                </div>
            </div>

        </div>
    </div>

    <script>
        // --- DATA FROM PHP ---
        const statusData = {
            pending: <?php echo $s_pending; ?>,
            accepted: <?php echo $s_accepted; ?>,
            completed: <?php echo $s_completed; ?>,
            cancelled: <?php echo $s_cancelled; ?>
        };

        const activityLabels = <?php echo json_encode($months); ?>;
        const activityData = <?php echo json_encode($activity_counts); ?>;


        // --- CHART 1: DOUGHNUT (Status) ---
        const ctx1 = document.getElementById('statusChart').getContext('2d');
        new Chart(ctx1, {
            type: 'doughnut',
            data: {
                labels: ['Pending', 'Accepted', 'Completed', 'Cancelled'],
                datasets: [{
                    data: [statusData.pending, statusData.accepted, statusData.completed, statusData.cancelled],
                    backgroundColor: [
                        '#fbbf24', // Yellow (Pending)
                        '#60a5fa', // Blue (Accepted)
                        '#4ade80', // Green (Completed)
                        '#f87171'  // Red (Cancelled)
                    ],
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'bottom' }
                }
            }
        });

        // --- CHART 2: BAR (Activity) ---
        const ctx2 = document.getElementById('activityChart').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: activityLabels,
                datasets: [{
                    label: 'Tasks Joined',
                    data: activityData,
                    backgroundColor: '#1e3a8a',
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    </script>
</body>
</html>