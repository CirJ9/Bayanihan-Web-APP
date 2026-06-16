<?php
session_start();
require_once "../config/db_conn.php";

// 1. SECURITY: Ensure User is in Flow
if (!isset($_SESSION['temp_email'])) {
    header("Location: login.php");
    exit();
}

$msg = "";
$msg_type = ""; // 'success' or 'error'

// Clean the email to ensure database matching works
$email = strtolower(trim($_SESSION['temp_email'])); 

// 2. RESEND LOGIC: If user clicked "Resend", clear old code
if (isset($_GET['resend']) && $_GET['resend'] == '1') {
    unset($_SESSION['otp_code']);
    unset($_SESSION['otp_expiry']);
    // Redirect back to clean URL so refresh doesn't resend again
    header("Location: otp.php"); 
    exit();
}

// 3. GENERATE OTP (Fix: Check if expiry is missing too)
if (!isset($_SESSION['otp_code']) || !isset($_SESSION['otp_expiry'])) {
    $_SESSION['otp_code'] = rand(1000, 9999);
    $_SESSION['otp_expiry'] = time() + (10 * 60); // Expires in 10 minutes
}

$server_otp = $_SESSION['otp_code'];
// Safe calculation now that we ensured otp_expiry is set
$timeLeft = $_SESSION['otp_expiry'] - time(); 

// 4. VERIFY LOGIC
if (isset($_POST['verify'])) {
    $user_otp = implode("", $_POST['otp']); 

    // CHECK 1: Expiry
    if (time() > $_SESSION['otp_expiry']) {
        $msg = "Code Expired. Please click Resend.";
        $msg_type = "error";
    } 
    // CHECK 2: Code Match
    else if ($user_otp == $_SESSION['otp_code']) {
        
        // CHECK 3: Update Database
        $update_sql = "UPDATE users SET is_verified = 1 WHERE email = '$email'";
        
        if (mysqli_query($conn, $update_sql)) {
            // Success! Clean up session
            unset($_SESSION['otp_code']);
            unset($_SESSION['otp_expiry']);
            unset($_SESSION['temp_email']);
            
            echo "<script>
                    alert('✅ Account Verified! You can now login.'); 
                    window.location.href='login.php';
                  </script>";
            exit();
        } else {
            $msg = "Database Error: " . mysqli_error($conn);
            $msg_type = "error";
        }

    } else {
        $msg = "❌ Incorrect Code. Please try again.";
        $msg_type = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <title>OTP Verification</title>
  <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/@emailjs/browser@4/dist/email.min.js"></script>
  <link rel="stylesheet" href="../assets/css/otp.css" />
  <style>
      .msg-box { padding: 10px; border-radius: 5px; margin-bottom: 10px; font-size: 14px; }
      .error { background: #ffe6e6; color: red; border: 1px solid red; }
      .success { background: #e6fffa; color: green; border: 1px solid green; }
      .resend-link { display: block; margin-top: 15px; color: #007bff; text-decoration: underline; cursor: pointer; font-size: 14px; }
      .resend-link:hover { color: #0056b3; }
  </style>
</head>

<body>

  <div class="container step1" id="step1">
    <h2>Email Verification</h2>
    <p>Verify your account: <strong><?php echo htmlspecialchars($email); ?></strong></p>
    
    <button class="nextButton" onclick="sendOTP()">Send Verification Code</button>
  </div>

  <div class="container step2" id="step2" style="display:none;">
    <h2>Enter Code</h2>
    <p>We sent a 4-digit code to your email.</p>
    
    <?php if($msg) echo "<div class='msg-box $msg_type'>$msg</div>"; ?>
    
    <form method="POST">
        <div class="otp-group">
            <input type="text" name="otp[]" maxlength="1" class="otp-input" required autofocus>
            <input type="text" name="otp[]" maxlength="1" class="otp-input" required>
            <input type="text" name="otp[]" maxlength="1" class="otp-input" required>
            <input type="text" name="otp[]" maxlength="1" class="otp-input" required>
        </div>
        <button type="submit" name="verify" class="verifyButton">Verify Code</button>
    </form>

    <a href="otp.php?resend=1" class="resend-link">Code didn't arrive? Resend New Code</a>
  </div>

  <script>
    emailjs.init("vkQ9ajA3Nof2t1Kuq"); // YOUR PUBLIC KEY

    <?php if(isset($_POST['verify']) || $msg != ""): ?>
        document.getElementById("step1").style.display = "none";
        document.getElementById("step2").style.display = "block";
    <?php endif; ?>

    function sendOTP() {
        const email = "<?php echo $email; ?>";
        const otpCode = "<?php echo $server_otp; ?>"; 
        
        console.log("Sending Email to:", email);
        console.log("Code:", otpCode);

        var params = {
            to_email: email,
            message: "Your Verification Code is: " + otpCode, 
            otp: otpCode,     
            code: otpCode,
            OTP: otpCode
        };

        const serviceID = "service_wkbsw38"; // YOUR SERVICE ID
        const templateID = "template_oy6b70g"; // YOUR TEMPLATE ID

        const btn = document.querySelector(".nextButton");
        btn.innerHTML = "Sending...";
        btn.disabled = true;

        emailjs.send(serviceID, templateID, params)
        .then(() => {
            alert("✅ Code sent to " + email);
            document.getElementById("step1").style.display = "none";
            document.getElementById("step2").style.display = "block";
        })
        .catch((err) => {
            alert("FAILED to send email: " + JSON.stringify(err));
            btn.innerHTML = "Try Again";
            btn.disabled = false;
        });
    }

    const inputs = document.querySelectorAll(".otp-input");
    inputs.forEach((input, index) => {
        input.addEventListener("keyup", (e) => {
            if (input.value.length === 1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
            if (e.key === "Backspace" && index > 0) {
                 inputs[index - 1].focus();
            }
        });
    });
  </script>
</body>
</html>