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
    <title>BlueSolarPanel - Information</title>
</head>
<body>

    <!-- Header Section containing the main branding link -->
    <header class="top-section">
        <h1><a href="index.php" class="logo-link">BlueSolarPanel</a></h1>
    </header>

    <!-- Main Content Area -->
    <main class="middle-section">
        <h2>Information</h2>
        <p style="color: #666; margin-bottom: 30px;">Learn more about how the system handles Bluetooth scanning and device storage.</p>

        <!-- Section 1: Scan Duration Settings -->
        <div class="test-box" style="margin-bottom: 25px; border-color: #ddd; background-color: #fafafa;">
            <h3 style="margin-top: 0;">⏱️ Scan time in settings</h3>
            <p>This is the amount of seconds used to search for Bluetooth devices. Most of the time, 5 seconds is a good value to also retrieve device names.</p>
            <p>You want to keep this value quite low to ensure fast page loads and avoid sending too many requests to nearby devices. If a device is not found within 10 seconds, something else is probably wrong.</p>
            <p>There is also a built-in safety limit: if you enter a higher value, the system will automatically cap it at a maximum of 15 seconds. If the setting is completely missing, it will default to 5 seconds.</p>
        </div>

        <!-- Section 2: Filtering Search Results -->
        <div class="test-box" style="margin-bottom: 25px; border-color: #ddd; background-color: #fafafa;">
            <h3 style="margin-top: 0;">🔍 Results from search</h3>
            <p>To keep your workspace clean and relevant, devices that are already <strong>saved</strong> or <strong>ignored</strong> will not be listed when searching for new devices.</p>
        </div>

        <!-- Section 3: Device Deletion Logic -->
        <div class="test-box" style="margin-bottom: 25px; border-color: #ddd; background-color: #fafafa;">
            <h3 style="margin-top: 0;">🗑️ Delete for real</h3>
            <p>When you remove a saved device, it will first be moved to your <em>Ignored devices</em> list so it stays hidden from new scans.</p>
            <p>If you want to delete it permanently, go to the ignored devices page and use the delete button. After a permanent deletion, the device can be discovered in searches again.</p>
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
