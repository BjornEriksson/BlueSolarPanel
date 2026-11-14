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
    <title>BlueSolarPanel - To Do List</title>
</head>
<body>

    <!-- Header Section containing the main branding link -->
    <header class="top-section">
        <h1><a href="index.php" class="logo-link">BlueSolarPanel</a></h1>
    </header>

    <!-- Main Content Area -->
    <main class="middle-section">
        <h2>My to do list</h2>
        <p style="color: #666; margin-bottom: 30px;">Planned features, improvements, and roadmap for future development.</p>

        <!-- Section 1: Planned Features -->
        <div class="test-box" style="margin-bottom: 25px; border-color: #ddd; background-color: #fafafa;">
            <h3 style="margin-top: 0;">📝 Upcoming Features & Ideas</h3>
            <ul style="line-height: 1.8; color: #333; padding-left: 20px;">
                <li>Convert raw hex values in device details into readable metrics.</li>
                <li>Present data in a user-friendly dashboard, keeping raw data conversion files separate from clean user-facing views.</li>
                <li>Add saved BLE devices to specific solar grid systems.</li>
                <li>Implement a warning/alert system for each solar grid as well as individual devices.</li>
                <li>Consider merging the C# utilities into a single <code>.exe</code> file for all background functions.</li>
            </ul>
        </div>

        <!-- Navigation back to the main dashboard -->
        <p style="margin-top: 30px;"><a href="index.php" class="btn-match">Back to home</a></p>
    </main>

    <!-- Footer Section with dynamic PHP include -->
    <footer class="bottom-section">
        <?php include "data/include/footer.php"; ?>
    </footer>
</body>
</html>
