<?php
require_once "../config/session.php";
requireLogin();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../assets/css/about.css">
</head>
<body>

<div class="side-menu">
    <div class="brand-name"><h1>BAYANIHAN <span>APP</span></h1></div>
    <ul>
        <li><a href="dashboard.php">Home</a></li>
        <li><a href="request.php">Request</a></li>
        <li><a href="rewards.php">Rewards</a></li>
        <li><a href="statistics.php">Statistics</a></li>
        <li><a href="announcement.php">Announcement</a></li>
        <li><a href="about.php">About Us</a></li>
    </ul>
</div>

<main style="margin-left: 20vw; padding: 20px;"> <section class="section reveal">
        <div class="container">
            <header class="section-header">
                <h1 id="about-title">About Us</h1>
                <p class="subtitle">
                    Helping people is a powerful way to create positive change in the world.
                </p>
            </header>

            <div class="about-grid">
                <article class="about-text">
                    <h2>Who We Are</h2>
                    <p>
                        We are a passionate team of developers and designers committed to building a world where everyone feels valued.
                    </p>
                </article>
                <div class="about-image">
                   <img src="../assets/img/user.png" alt="Team" style="width:100%; border-radius:10px;">
                </div>
            </div>
        </div>
    </section>

</main>

<script src="../assets/js/about.js"></script>

</body>
</html>