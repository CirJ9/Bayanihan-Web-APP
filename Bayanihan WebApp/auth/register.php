<?php
session_start();
require_once "../config/db_conn.php"; 

$error = "";

if (isset($_POST["submit"])) {
    $fullname = $_POST["fullname"];
    $email = $_POST["email"];
    $password = $_POST["password"];
    $repeat_password = $_POST["repeat_password"];
    $community_id = $_POST["community_id"];

    if ($password !== $repeat_password) {
        $error = "Passwords do not match!";
    } else {
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        
        $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
        if (mysqli_num_rows($check) > 0) {
            $error = "Email already exists!";
        } else {
            // Insert user
            $sql = "INSERT INTO users (full_name, email, password_hash, community_id, role, is_verified) VALUES (?, ?, ?, ?, 'user', 0)";
            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", $fullname, $email, $passwordHash, $community_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['temp_email'] = $email; 
                header("Location: otp.php");
                exit();
            } else {
                $error = "Database error: " . mysqli_error($conn);
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Bayanihan</title>
    <link rel="stylesheet" href="../assets/css/auth.css"> 
</head>
<body>
    <div class="container">
        <h3 style="text-align:center; color:#4e73df;">Create Account</h3>
        <?php if($error) echo "<div class='alert' style='color:red; text-align:center;'>$error</div>"; ?>
        
        <form method="post">
            <div class="form-group">
                <input type="text" class="form-control" name="fullname" placeholder="Full Name" required>
            </div>
            <div class="form-group">
                <input type="email" class="form-control" name="email" placeholder="Email" required>
            </div>
            
            <div class="form-group">
                <select name="community_id" class="form-control" required>
                    <option value="">Select Municipality</option>
                    <?php 
                    $coms = mysqli_query($conn, "SELECT * FROM communities");
                    while($row = mysqli_fetch_assoc($coms)) {
                        echo "<option value='".$row['community_id']."'>".$row['municipality_name']."</option>";
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <input type="password" class="form-control" name="password" placeholder="Password (min 8 chars)" minlength="8" required>
            </div>
            <div class="form-group">
                <input type="password" class="form-control" name="repeat_password" placeholder="Repeat Password" required>
            </div>
            
            <div class="form-btn">
                <input type="submit" value="Register" name="submit" class="btn btn-primary">
            </div>
        </form>
        <p>Already have an account? <a href="login.php">Login</a></p>
    </div>
</body>
</html>