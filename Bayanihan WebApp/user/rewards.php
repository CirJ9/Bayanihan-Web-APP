<?php
require_once "../config/session.php";
require_once "../config/db_conn.php";
requireLogin();

$page = 'rewards';
$user_id = $_SESSION['user_id'];
$msg = "";
$msg_type = "";

// --- 1. AUTO-INITIALIZE WALLET ---
mysqli_query($conn, "INSERT IGNORE INTO points (user_id, points_total) VALUES ('$user_id', 0)");

// --- 2. HANDLE REDEMPTION ---
if (isset($_POST['redeem'])) {
    $reward_id = $_POST['reward_id'];

    // Fetch Item Details (Points & Stock) to prevent tampering
    $item_query = mysqli_query($conn, "SELECT * FROM rewards WHERE reward_id='$reward_id'");
    $item = mysqli_fetch_assoc($item_query);

    // Fetch User Balance
    $balance_query = mysqli_query($conn, "SELECT points_total FROM points WHERE user_id='$user_id'");
    $current_points = mysqli_fetch_assoc($balance_query)['points_total'];

    if ($item) {
        if ($item['stock'] <= 0) {
            $msg = "Sorry, this item is out of stock!";
            $msg_type = "danger";
        } elseif ($current_points < $item['points_required']) {
            $msg = "Insufficient points. You need " . ($item['points_required'] - $current_points) . " more.";
            $msg_type = "danger";
        } else {
            // PROCEED WITH TRANSACTION
            mysqli_begin_transaction($conn);
            try {
                // A. Deduct Points
                $cost = $item['points_required'];
                mysqli_query($conn, "UPDATE points SET points_total = points_total - $cost WHERE user_id='$user_id'");

                // B. Deduct Stock
                mysqli_query($conn, "UPDATE rewards SET stock = stock - 1 WHERE reward_id='$reward_id'");

                // C. Record Log (Using existing 'reward_redemption' table)
                // If you created 'redemptions' earlier, rename this query or the table. 
                // Using 'reward_redemption' based on your SQL dump structure.
                $log_sql = "INSERT INTO reward_redemption (user_id, reward_id, points_used) VALUES (?, ?, ?)";
                $stmt = mysqli_prepare($conn, $log_sql);
                mysqli_stmt_bind_param($stmt, "iii", $user_id, $reward_id, $cost);
                mysqli_stmt_execute($stmt);

                mysqli_commit($conn);
                $msg = "Successfully claimed " . htmlspecialchars($item['reward_name']) . "!";
                $msg_type = "success";
            } catch (Exception $e) {
                mysqli_rollback($conn);
                $msg = "Transaction failed: " . $e->getMessage();
                $msg_type = "danger";
            }
        }
    } else {
        $msg = "Item not found.";
        $msg_type = "danger";
    }
}

// --- 3. FETCH DISPLAY DATA ---
$p_query = mysqli_query($conn, "SELECT points_total FROM points WHERE user_id='$user_id'");
$my_points = mysqli_fetch_assoc($p_query)['points_total'];

// Progress Calculation
$goal = 500;
$percentage = ($my_points / $goal) * 100;
if($percentage > 100) $percentage = 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Rewards Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/rewards.css">
</head>
<body>

    <?php include "../includes/sidebar.php"; ?>

    <div class="container">
        <?php include "../includes/header.php"; ?>

        <div class="content">
            
            <?php if($msg): ?>
                <div class="alert" style="padding:15px; margin-bottom:20px; border-radius:8px; 
                    background: <?php echo ($msg_type=='success') ? '#d1fae5' : '#fee2e2'; ?>; 
                    color: <?php echo ($msg_type=='success') ? '#065f46' : '#991b1b'; ?>;">
                    <?php echo $msg; ?>
                </div>
            <?php endif; ?>

            <div class="rewards">
                <h2>Rewards Shop</h2>
                
                <div class="progress-container">
                    <h1 style="font-size: 48px; color: #1e3a8a; font-weight: 800; margin: 0;">
                        <?php echo number_format($my_points); ?>
                    </h1>
                    <p style="color: #666; margin-bottom: 15px;">Your Points</p>
                    
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $percentage; ?>%;"></div>
                    </div>
                    <div style="display:flex; justify-content:space-between; font-size:12px; margin-top:5px; color:#888;">
                        <span>0</span>
                        <span>Next Tier: <?php echo $goal; ?></span>
                    </div>
                </div>

                <h3 style="text-align:left; color:#1e3a8a; margin-bottom:20px; border-bottom:1px solid #ddd; padding-bottom:10px;">
                    Available Items
                </h3>

                <div class="reward-list">
                    <?php
                    // Fetch items from DB
                    $items_sql = "SELECT * FROM rewards ORDER BY points_required ASC";
                    $items_res = mysqli_query($conn, $items_sql);

                    if (mysqli_num_rows($items_res) > 0) {
                        while ($row = mysqli_fetch_assoc($items_res)) {
                            $disabled = ($my_points < $row['points_required'] || $row['stock'] <= 0) ? 'disabled' : '';
                            $btn_style = ($disabled) ? 'background:#ccc; cursor:not-allowed;' : '';
                            $icon = $row['reward_image'] ? $row['reward_image'] : 'redeem'; // Default icon
                            
                            // Colors based on cost for visual flair
                            $bg_color = '#e0f2fe'; // Blueish
                            if ($row['points_required'] >= 500) $bg_color = '#dcfce7'; // Greenish
                            if ($row['points_required'] >= 1000) $bg_color = '#fef9c3'; // Yellowish
                    ?>
                        <div class="reward-card">
                            <div style="width:100px; height:100px; background:<?php echo $bg_color; ?>; border-radius:50%; display:flex; align-items:center; justify-content:center; margin-bottom:15px;">
                                <span class="material-symbols-outlined" style="font-size:40px; color:#1e3a8a;">
                                    <?php echo htmlspecialchars($icon); ?>
                                </span>
                            </div>
                            
                            <h3><?php echo htmlspecialchars($row['reward_name']); ?></h3>
                            <p><?php echo $row['points_required']; ?> Points</p>
                            
                            <div style="font-size:13px; color:#666; margin-bottom:15px; min-height:40px;">
                                <?php echo htmlspecialchars($row['description']); ?>
                                <br>
                                <small style="color:<?php echo ($row['stock'] < 5) ? '#dc2626' : '#16a34a'; ?>">
                                    (<?php echo $row['stock']; ?> left in stock)
                                </small>
                            </div>

                            <form method="POST" onsubmit="return confirm('Spend <?php echo $row['points_required']; ?> points for <?php echo $row['reward_name']; ?>?');">
                                <input type="hidden" name="reward_id" value="<?php echo $row['reward_id']; ?>">
                                <button type="submit" name="redeem" <?php echo $disabled; ?> style="<?php echo $btn_style; ?>">
                                    <?php echo ($row['stock'] <= 0) ? 'Out of Stock' : 'Redeem'; ?>
                                </button>
                            </form>
                        </div>
                    <?php 
                        }
                    } else {
                        echo "<p style='width:100%; text-align:center; color:#777;'>No rewards available at the moment.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>