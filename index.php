<?php
/**
 * BlueSolarPanel
 * Copyright (c) 2026 Björn Eriksson
 * Licensed under the MIT License
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="data/css/style.css">
    <title>BlueSolarPanel - Main menu</title>
</head>
<body>

    <!-- Header Section containing the main branding link -->
    <header class="top-section">
        <h1><a href="index.php" class="logo-link">BlueSolarPanel</a></h1>
    </header>

    <!-- Main Content Area -->
    <main class="middle-section">
        <h2>Main menu</h2>
        <p style="color: #666; margin-bottom: 30px;">Welcome to your solar and device management dashboard.</p>

        <!-- Section 1: Bluetooth Device Management -->
        <div class="test-box" style="margin-bottom: 25px; border-color: #ddd; background-color: #fcfcfc;">
            <h3 style="margin-top: 0;">🔵 Handle your bluetooth devices</h3>
            <p style="font-size: 0.95rem; color: #555; margin-bottom: 15px;">Manage saved and ignored devices or scan for new ones nearby.</p>
            <div style="display: flex; flex-wrap: wrap; gap: 15px; align-items: center;">
                <a href="saveddevices.php" class="btn-match btn-green">Saved devices</a>
                <a href="ignoreddevices.php" class="btn-match btn-green">Ignored devices</a>
                <a href="newdevices.php" class="btn-match" id="scan-trigger">Scan new devices</a>
            </div>
        </div>

        <!-- Section 2: Solar grids -->
        <div class="test-box" style="margin-bottom: 25px; border-color: #b6d4fe; background-color: #f0f4f8;">
            <h3 style="margin-top: 0;">☀️ Handle your solar grids</h3>
            <p style="font-size: 0.95rem; color: #555; margin-bottom: 15px;">Monitor performance and manage your connected solar grid systems.</p>
            <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                <a href="#" class="btn-match" style="margin: 0;">Solar grid 1</a>
                <!-- Solar orange button -->
                <a href="#" class="btn-match" style="margin: 0; background-color: #e67e22;"><strong>+</strong> Add</a>
            </div>
        </div>
        
        <!-- Section 3: Software Settings and Configuration -->
        <div class="test-box" style="margin-bottom: 25px; border-color: #ddd; background-color: #fafafa;">
            <h3 style="margin-top: 0;">⚙️ Handle this software</h3>
            <p style="font-size: 0.95rem; color: #555; margin-bottom: 15px;">Change scan times, check configuration, or read system information.</p>
            <div style="display: flex; flex-wrap: wrap; gap: 15px;">
                <a href="config.php" class="btn-match" style="background-color: #6c757d; margin: 0;">Configuration</a>
                <a href="information.php" class="btn-match" style="background-color: #6c757d; margin: 0;">Information</a>
                <a href="todo.php" class="btn-match" style="background-color: #6c757d; margin: 0;">My to do list</a>
            </div>
        </div>

    </main>

    <!-- Footer Section with dynamic PHP include -->
    <footer class="bottom-section">
        <?php include "data/include/footer.php"; ?>
    </footer>

    <!-- Hidden loading screen that triggers on scan click -->
    <div id="wait-screen" class="loading-overlay" style="display: none;">
        <div class="loading-text">Wait...</div>
    </div>

    <script>
        // Trigger the pulsating overlay animation when the user clicks the blue scan button
        document.getElementById('scan-trigger').addEventListener('click', function() {
            document.getElementById('wait-screen').style.display = 'flex';
        });
    </script>
</body>
</html>
