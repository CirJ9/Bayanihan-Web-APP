<?php
require_once "../config/db_conn.php";
require_once "../config/session.php";
requireLogin();

$page = 'announce'; // Sidebar Highlight
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Announcements</title>
    
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/announcement.css"> 
</head>

<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="container">
        <?php include "../includes/header.php"; ?>

        <div class="content">
            <div class="announcement-container" style="padding: 20px;">
                <h2 style="color:#1e3a8a; margin-bottom:20px; border-bottom:2px solid #ddd; padding-bottom:10px;">Latest Updates</h2>
                
                <div class="announcement-list">
                    <?php
                    $sql = "SELECT * FROM announcements ORDER BY created_at DESC";
                    $result = mysqli_query($conn, $sql);
                    
                    if ($result && mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                    ?>
                        <div class="announcement-card" style="background:white; padding:20px; margin-bottom:15px; border-left:5px solid #1e3a8a; border-radius:5px; box-shadow:0 2px 5px rgba(0,0,0,0.1);">
                            <h3 style="margin:0; color:#333;"><?php echo htmlspecialchars($row['title']); ?></h3>
                            <small style="color:#888; display:block; margin:5px 0 10px;">
                                <?php echo date("M d, Y h:i A", strtotime($row['created_at'])); ?>
                            </small>
                            <p style="line-height:1.6; color:#555;"><?php echo nl2br(htmlspecialchars($row['message'])); ?></p>
                        </div>
                    <?php
                        }
                    } else {
                        echo "<div class='alert' style='background:white; padding:20px; border-radius:5px;'>No announcements posted yet.</div>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>