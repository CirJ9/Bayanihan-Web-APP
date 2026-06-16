<?php
require_once "../config/db_conn.php";
require_once "../config/session.php";
requireLogin();

$user_id = $_SESSION['user_id'];
$message = "";

// 1. HANDLE FORM SUBMISSION 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $full_name = $_POST['full_name'];
    $role_title = $_POST['role_title']; // e.g. "Web Developer"
    $bio = $_POST['bio'];
    $phone = $_POST['phone'];
    
    $sql = "UPDATE users SET full_name=?, role_title=?, bio=?, phone=? WHERE user_id=?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $full_name, $role_title, $bio, $phone, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Profile updated successfully!";
        $_SESSION['full_name'] = $full_name; // Update session immediately
    } else {
        $message = "Error updating profile.";
    }
}

// 2. FETCH USER DATA
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

// Default values if empty
$role_title = $user['role_title'] ?? 'Community Volunteer';
$bio = $user['bio'] ?? 'I love helping my community.';
$phone = $user['phone'] ?? 'Not set';
$joined = date("F Y", strtotime($user['created_at']));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>User Profile</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <style>
        /* Simple alert style */
        .alert { padding: 10px; background: #d4edda; color: #155724; border-radius: 5px; margin-bottom: 15px; text-align: center; }
        .side-menu-link { text-decoration: none; color: #555; display: block; margin-top: 20px; text-align: center; }
    </style>
</head>
<body>

<main class="profile-wrapper">
    
    <a href="dashboard.php" class="side-menu-link">← Back to Dashboard</a>

    <header class="profile-header">
        <img src="../assets/img/user.png" alt="Profile Picture" class="profile-pic" id="profilePic">
        
        <?php if($message) echo "<div class='alert'>$message</div>"; ?>

        <form method="POST" action="">
            <h1 class="profile-name view"><?php echo htmlspecialchars($user['full_name']); ?></h1>
            <p class="profile-role view"><?php echo htmlspecialchars($role_title); ?></p>

            <input class="edit-field name-input" type="text" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" style="display:none;">
            <input class="edit-field role-input" type="text" name="role_title" value="<?php echo htmlspecialchars($role_title); ?>" placeholder="Your Role (e.g. Volunteer)" style="display:none;">

            <button type="button" id="editBtn" class="edit-btn">Edit Profile</button>
            <button type="submit" id="saveBtn" class="edit-btn" style="display:none; background:#28a745;">Save Changes</button>
    </header>

    <nav class="profile-tabs">
        <button type="button" class="tab active" data-tab="about">About</button>
        <button type="button" class="tab" data-tab="details">Details</button>
    </nav>

    <section class="profile-content">

        <div id="about" class="tab-content active">
            <h2>About Me</h2>
            <p class="about-text view"><?php echo htmlspecialchars($bio); ?></p>
            <textarea name="bio" class="edit-field about-input" style="display:none; width:100%; height:100px; padding:10px;"><?php echo htmlspecialchars($bio); ?></textarea>
        </div>

        <div id="details" class="tab-content">
            <h2>Contact & Info</h2>
            <ul class="details-list">
                <li>
                    <span>Email</span>
                    <strong class="view"><?php echo htmlspecialchars($user['email']); ?></strong>
                    </li>
                <li>
                    <span>Phone</span>
                    <strong class="view phone-text"><?php echo htmlspecialchars($phone); ?></strong>
                    <input class="edit-field phone-input" type="text" name="phone" value="<?php echo htmlspecialchars($phone); ?>" style="display:none;">
                </li>
                <li>
                    <span>Joined</span>
                    <strong class="view"><?php echo $joined; ?></strong>
                </li>
            </ul>
        </div>
        </form> </section>
</main>

<script>
    // Simple JS to toggle Edit/View mode
    const editBtn = document.getElementById('editBtn');
    const saveBtn = document.getElementById('saveBtn');
    const viewElements = document.querySelectorAll('.view');
    const editElements = document.querySelectorAll('.edit-field');

    editBtn.addEventListener('click', () => {
        // Hide View, Show Inputs
        viewElements.forEach(el => el.style.display = 'none');
        editElements.forEach(el => el.style.display = 'block');
        editBtn.style.display = 'none';
        saveBtn.style.display = 'inline-block';
    });
    
    // Tabs Logic
    const tabs = document.querySelectorAll('.tab');
    const contents = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            tab.classList.add('active');
            document.getElementById(tab.dataset.tab).classList.add('active');
        });
    });
</script>

</body>
</html>