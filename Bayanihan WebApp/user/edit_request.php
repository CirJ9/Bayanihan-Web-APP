<?php
require_once "../config/db_conn.php";
require_once "../config/session.php";
requireLogin(); 

$user_id = $_SESSION['user_id'];
$request_id = $_GET['id'] ?? null;
$msg = "";

// 1. Fetch Existing Data
if ($request_id) {
    // Secure check: Ensure the task belongs to the logged-in user
    $sql = "SELECT * FROM volunteer_requests WHERE request_id = ? AND poster_id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ii", $request_id, $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $task = mysqli_fetch_assoc($result);

    if (!$task) {
        echo "<script>alert('Request not found or permission denied.'); window.location.href='history.php';</script>";
        exit();
    }
    
    // Parse description to remove the old "Scheduled: ..." part so user can edit cleanly
    $raw_desc = explode(" (Scheduled:", $task['description'])[0];
    
} else {
    header("Location: history.php");
    exit();
}

// 2. Handle Update
if (isset($_POST["update"])) {
   $community_id = $_POST['location']; 
   $task_type = $_POST['task']; 
   $date = $_POST['date'];
   $time = $_POST['time'];
   $desc_input = $_POST['other'];
   $volunteers_needed = $_POST['volunteers_needed'];

   // --- VALIDATION: CHECK DATE & TIME ---
   $submitted_timestamp = strtotime("$date $time");
   $current_timestamp = time();

   if ($submitted_timestamp <= $current_timestamp) {
       // Stop the update if date is in the past
       echo "<script>alert('Error: You cannot reschedule a task to the past. Please check the Date and Time.');</script>";
   } else {
       // Proceed if valid
       $full_description = "$desc_input (Scheduled: $date at $time)";

       $update_sql = "UPDATE volunteer_requests 
                      SET title=?, description=?, volunteers_needed=?, community_id=? 
                      WHERE request_id=? AND poster_id=?";

       $stmt = mysqli_prepare($conn, $update_sql);
       mysqli_stmt_bind_param($stmt, "ssiiii", $task_type, $full_description, $volunteers_needed, $community_id, $request_id, $user_id);

       if (mysqli_stmt_execute($stmt)) {
          echo "<script>alert('Request updated successfully!'); window.location.href='history.php';</script>";
          exit();
       } else {
          $msg = "Failed: " . mysqli_error($conn);
       }
   }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="UTF-8">
   <title>Edit Request</title>
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   
   <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
   
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
   
   <link rel="stylesheet" href="../assets/css/main.css"> 
   
   <link rel="stylesheet" href="../assets/css/task.css"> 
   
   <style>
       .content { 
           display: flex; 
           justify-content: center; 
           padding-top: 40px; 
       }
   </style>
</head>

<body>
   <?php include "../includes/sidebar.php"; ?>

   <div class="container">
      <?php include "../includes/header.php"; ?>

      <div class="content">
         <div class="form-card">
            <h3 class="text-center">Edit Request #<?php echo $request_id; ?></h3>
            
            <?php if($msg) echo "<div class='alert alert-danger'>$msg</div>"; ?>

            <form action="" method="post">
                
                <div class="mb-3">
                   <label class="form-label">Location</label>
                   <select class="form-control" name="location" required>
                      <?php 
                        $coms = mysqli_query($conn, "SELECT * FROM communities");
                        while($row = mysqli_fetch_assoc($coms)) {
                            $selected = ($row['community_id'] == $task['community_id']) ? 'selected' : '';
                            echo "<option value='".$row['community_id']."' $selected>".$row['municipality_name']."</option>";
                        }
                      ?>
                   </select>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                       <label class="form-label">Date Needed</label>
                       <input type="date" name="date" id="dateInput" class="form-control" required>
                    </div>
                    <div class="col-md-6">
                       <label class="form-label">Time Needed</label>
                       <input type="time" name="time" id="timeInput" class="form-control" required>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Volunteers Needed (Max)</label>
                    <input type="number" name="volunteers_needed" class="form-control" min="1" max="50" value="<?php echo $task['volunteers_needed']; ?>" required>
                </div>

                <div class="mb-3">
                   <label class="form-label">Task Category</label>
                      <select class="form-control" name="task" required>
                         <?php
                            $options = ["Cleaning", "Delivery", "Donation", "Rescue", "Education", "Other"];
                            foreach($options as $opt) {
                                $sel = ($task['title'] == $opt) ? 'selected' : '';
                                echo "<option value='$opt' $sel>$opt</option>";
                            }
                         ?>
                      </select>
                </div>

                <div class="mb-3">
                   <label class="form-label">Description / Instructions</label>
                   <input type="text" class="form-control" name="other" value="<?php echo htmlspecialchars($raw_desc); ?>" required>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4">
                   <a href="history.php" class="btn btn-secondary">Cancel</a>
                   <button type="submit" class="btn btn-primary px-5" name="update">Update Request</button>
                </div>
            </form>
         </div>
      </div>
   </div>

   <script>
       document.addEventListener("DOMContentLoaded", () => {
           const dateInput = document.getElementById('dateInput');
           const timeInput = document.getElementById('timeInput');

           // 1. Set MIN DATE to Today
           const now = new Date();
           const todayStr = now.toISOString().split('T')[0];
           dateInput.setAttribute('min', todayStr);

           // 2. Validate Time on Change
           function validateTime() {
               if(dateInput.value === todayStr) {
                   const currentTime = new Date();
                   const currentHours = currentTime.getHours();
                   const currentMinutes = currentTime.getMinutes();
                   
                   const selectedTime = timeInput.value; 
                   if(selectedTime) {
                       const [selHours, selMinutes] = selectedTime.split(':').map(Number);
                       
                       // Check if selected time is in the past
                       if (selHours < currentHours || (selHours === currentHours && selMinutes <= currentMinutes)) {
                           alert("You cannot select a time in the past for today.");
                           timeInput.value = ""; // Reset time
                       }
                   }
               }
           }

           // Listeners
           dateInput.addEventListener('change', validateTime);
           timeInput.addEventListener('change', validateTime);
       });
   </script>
</body>
</html>