<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "bayanihanapp";

// Enable error reporting for debugging
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    $conn->set_charset("utf8mb4"); // Ensures symbols/emojis work
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>