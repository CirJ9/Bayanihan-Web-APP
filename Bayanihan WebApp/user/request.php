<?php
require_once "../config/db_conn.php";
require_once "../config/session.php";
requireLogin(); 

// Initialize variables for the form
$user_id = $_SESSION['user_id'];
$full_name = $_SESSION['full_name'];

// Fetch user details for auto-filling
$user_query = mysqli_query($conn, "SELECT email, phone, community_id FROM users WHERE user_id='$user_id'");
$user_data = mysqli_fetch_assoc($user_query);
$email = $user_data['email'];
$contact = $user_data['phone'];
$default_community = $user_data['community_id'];

// HANDLE FORM SUBMISSION
if (isset($_POST["submit"])) {
   $poster_id = $_SESSION['user_id']; 
   $community_id = $_POST['location']; 
   $task_type = $_POST['task']; 
   
   $date = $_POST['date'];
   $time = $_POST['time'];
   $desc_input = $_POST['other'];
   
   // NEW: Get Max Volunteers
   $volunteers_needed = $_POST['volunteers_needed'];

   $full_description = "$desc_input (Scheduled: $date at $time)";

   // Insert Query
   $sql = "INSERT INTO volunteer_requests 
           (poster_id, title, description, reward_points, volunteers_needed, status, community_id) 
           VALUES (?, ?, ?, 50, ?, 'open', ?)";

   $stmt = mysqli_prepare($conn, $sql);
   // 'issii' -> integer, string, string, integer, integer
   mysqli_stmt_bind_param($stmt, "issii", $poster_id, $task_type, $full_description, $volunteers_needed, $community_id);

   if (mysqli_stmt_execute($stmt)) {
      // Success: Redirect to history
      header("Location: history.php?msg=Request posted successfully");
      exit();
   } else {
      echo "Failed: " . mysqli_error($conn);
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Post Request</title>
   
   <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
   
   <link rel="stylesheet" href="../assets/css/main.css"> 
   <link rel="stylesheet" href="../assets/css/task.css"> 
</head>

<body>
   <?php include "../includes/sidebar.php"; ?>

   <div class="container">
      <?php include "../includes/header.php"; ?>

      <div class="content">
         <div class="form-container">
            <h3 class="text-center">Post a Request</h3>
            <p class="text-muted text-center">Ask for help from your community.</p>

            <form action="" method="post">
                <div class="row mb-3">
                   <div class="col-md-6">
                      <label class="form-label">Requester</label>
                      <input type="text" class="form-control" value="<?php echo htmlspecialchars($full_name); ?>" disabled>
                   </div>
                   <div class="col-md-6">
                      <label class="form-label">Contact</label>
                      <input type="text" class="form-control" value="<?php echo htmlspecialchars($contact); ?>" disabled>
                   </div>
                </div>

                <div class="mb-3">
                   <label class="form-label">Location</label>
                   <select class="form-control" name="location" required>
                      <option value="">-- Select Location --</option>
                      <?php 
                        $coms = mysqli_query($conn, "SELECT * FROM communities");
                        while($row = mysqli_fetch_assoc($coms)) {
                            $selected = ($row['community_id'] == $default_community) ? 'selected' : '';
                            echo "<option value='".$row['community_id']."' $selected>".$row['municipality_name']."</option>";
                        }
                      ?>
                   </select>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                       <label class="form-label">Date Needed</label>
                       <input type="date" name="date" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                       <label class="form-label">Time Needed</label>
                       <input type="time" name="time" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Volunteers Needed (Max)</label>
                    <input type="number" name="volunteers_needed" class="form-control" min="1" max="50" value="1" required>
                    <small class="text-muted">The request will stop appearing in the feed once this many people are accepted.</small>
                </div>

                <div class="mb-3">
                   <label class="form-label">Task Category</label>
                      <select class="form-control" name="task" required>
                         <option value="Cleaning">Cleaning</option>
                         <option value="Delivery">Delivery</option>
                         <option value="Donation">Donation</option>
                         <option value="Rescue">Rescue</option>
                         <option value="Education">Education</option>
                         <option value="Other">Other</option>
                      </select>
                </div>

                <div class="mb-3">
                   <label class="form-label">Description / Instructions</label>
                   <input type="text" class="form-control" name="other" placeholder="Specific details..." required>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                   <div>
                       <a href="dashboard.php" class="btn btn-outline-danger me-2">Cancel</a>
                       <a href="history.php" class="btn btn-secondary">View History</a>
                   </div>
                   <button type="submit" class="btn btn-success px-5" name="submit">Post Now</button>
                </div>
            </form>
         </div>
      </div>
   </div>
</body>
</html>