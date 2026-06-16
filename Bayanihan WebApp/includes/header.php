<div class="header">
    <div class="nav">
        Welcome, <?php echo isset($_SESSION['full_name']) ? htmlspecialchars($_SESSION['full_name']) : 'Guest'; ?>
    </div>
    <div class="user">
        <a href="../auth/logout.php" style="background:#e74c3c; color:white; padding:5px 15px; border-radius:5px; font-size:14px;">Logout</a>
        
        <?php 
            // Fallback image if none exists
            $img = !empty($_SESSION['profile_img']) ? $_SESSION['profile_img'] : 'user.png'; 
        ?>
        <a href="profile.php">
            <img src="../assets/img/<?php echo $img; ?>" alt="Profile">
        </a>
    </div>
</div>