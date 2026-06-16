<?php
require_once "../config/session.php";
requireLogin();
$page = 'about';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>About Us</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/about.css">
</head>
<body>

    <?php include "../includes/sidebar.php"; ?>

    <div class="container">
        <?php include "../includes/header.php"; ?>

        <div class="content">
            <section class="section reveal">
                <div class="container-fluid" style="max-width: 1000px; margin: 0 auto;">
                    <header class="section-header">
                        <h1 style="color:#1e3a8a;">About Us</h1>
                        <p class="subtitle">Helping people is a powerful way to create positive change.</p>
                    </header>

                    <div class="about-grid">
                        <article class="about-text">
                            <h2 style="color:#1e3a8a;">Who We Are</h2>
                            <p>We are a passionate team of developers committed to building secure, modern, and user-friendly systems.</p>
                        </article>
                        <div class="about-image">
                            <img src="../assets/img/about1.jpg" alt="Team" style="width:100%; border-radius:12px;">
                        </div>
                    </div>
                </div>
            </section>

            <section class="section reveal">
                <div class="container-fluid" style="max-width: 1000px; margin: 0 auto;">
                    <header class="section-header center">
                        <h2 style="color:#1e3a8a;">Meet Our Team</h2>
                    </header>
                    <div class="team-grid">
                        <div class="team-card">
                            <img src="../assets/img/carlo.jpg" alt="Carlo">
                            <h3>Carlo Garcia</h3><p class="role">Lead Developer</p>
                        </div>
                        <div class="team-card">
                            <img src="../assets/img/loyd.jpg" alt="Loyd">
                            <h3>Johnloyd Allaraiz</h3><p class="role">UI Designer</p>
                        </div>
                        <div class="team-card">
                            <img src="../assets/img/ric.jpg" alt="Ric">
                            <h3>Johnric Baguisi</h3><p class="role">System Analyst</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
    <script src="../assets/js/about.js"></script>
</body>
</html>