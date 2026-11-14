<?php
/**
 * BlueSolarPanel
 * Copyright (c) 2026 Björn Eriksson
 * Licensed under the MIT License
 */
 
// Define file paths for saved and ignored devices data storage
$savedFile = 'data/devicelists/saved_devices.txt';
$ignoredFile = 'data/devicelists/ignored_devices.txt';

// Handle incoming GET actions (restore or delete)
if (isset($_GET['action'])) {
    if (!empty($_GET['action'])) {
        $action = $_GET['action'];
        $mac = ""; 
        $data = "";
        
        // Extract and decode MAC address and device data parameters
        if ((!empty($_GET['mac'])) && (!empty($_GET['data']))) {
            $mac = $_GET['mac'];
            $data = hex2bin($_GET['data']);
            $deviceString = $mac . "-||-" . $data;

            // Action: Restore device back to the saved list
            if ($action == 'restore') {
                $oldIgnoredFile = file_exists($ignoredFile) ? trim(file_get_contents($ignoredFile)) : "";
                $oldSavedFile = file_exists($savedFile) ? trim(file_get_contents($savedFile)) : "";

                // Move device if it doesn't already exist in the saved file
                if (!str_contains($oldSavedFile, $mac)) {
                    $newSaveFile = $oldSavedFile . PHP_EOL . trim($deviceString);
                    file_put_contents($savedFile, trim($newSaveFile));
                    
                    // Safe deletion: Trim both the file and the line, and clean up duplicate newlines
                    $newIgnoreFile = str_replace(trim($deviceString), '', $oldIgnoredFile);            
                    $newIgnoreFile = str_replace(PHP_EOL . PHP_EOL, PHP_EOL, $newIgnoreFile);
                    file_put_contents($ignoredFile, trim($newIgnoreFile));
                }
            }

            // Action: Permanently delete device from the ignored list and remove associated data
            if ($action == 'delete') {
                $oldIgnoredFile = file_exists($ignoredFile) ? trim(file_get_contents($ignoredFile)) : "";

                if (str_contains($oldIgnoredFile, $mac)) {
                    // 1. Remove from the ignored devices text list
                    $newIgnoreFile = str_replace(trim($deviceString), '', $oldIgnoredFile);            
                    $newIgnoreFile = str_replace(PHP_EOL . PHP_EOL, PHP_EOL, $newIgnoreFile);
                    file_put_contents($ignoredFile, trim($newIgnoreFile));
                    
                    // 2. Delete the raw data text file
                    $oldDataFile = trim("data/devicedata/" . str_replace(':', '', $mac) . ".txt");
                    if (file_exists($oldDataFile)) {
                        unlink($oldDataFile);
                    }

                    // 3. Delete the detailed JSON profile file if it exists
                    $cleanMac = str_replace([':', '-'], '', $mac);
                    $profileFile = "data/deviceprofiles/" . $cleanMac . ".json";
                    if (file_exists($profileFile)) {
                        unlink($profileFile);
                    }
                }
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
    <link rel="stylesheet" href="data/css/style.css">
    <title>BlueSolarPanel - Ignored Bluetooth devices</title>
</head>
<body>

    <!-- Header Section containing the main branding link -->
    <header class="top-section">
        <h1><a href="index.php" class="logo-link">BlueSolarPanel</a></h1>
    </header>

    <!-- Main Content Area -->
    <main class="middle-section">
        <h2>Ignored Bluetooth devices</h2>
        <p>Devices here will not be listed when searching for new devices.</p>

        <?php
            $deviceCount = 0;

            // Check if the ignored devices file exists and read its content
            if (file_exists($ignoredFile)) {
                $ignoredDevices = trim(file_get_contents($ignoredFile));
                
                // If the file is completely empty (or contains only spaces), set count directly to 0
                if ($ignoredDevices === "") {
                    $deviceCount = 0;
                } else {
                    // Split the file string into an array by newline characters
                    $deviceArray = preg_split('/\r\n|\r|\n/', $ignoredDevices);
                    $deviceCount = count($deviceArray);
                }
            }
        ?>

        <h3>📁 Ignored devices</h3>
        <ul class="device-list">
            <?php
                // Loop through and display each ignored device if any are found
                if ($deviceCount != 0) {
                    for ($i = 0; $i < $deviceCount; $i++): 
                        $devicePart = explode("-||-", $deviceArray[$i]);
            ?>
            <li class="device-item">
                <div class="device-info" style="opacity: 0.5;">
                    <span class="device-name"><?php echo htmlspecialchars($devicePart[1]); ?></span>
                    <span class="device-mac"><?php echo htmlspecialchars($devicePart[0]); ?></span>
                </div>
                <div class="device-actions">
                    <a href="readdevice.php?mac=<?php echo urlencode($devicePart[0]); ?>&data=<?php echo bin2hex($devicePart[1]); ?>" class="action-btn" title="Inspect/Read device">🔍</a>
                    <a href="?action=restore&mac=<?php echo urlencode($devicePart[0]); ?>&data=<?php echo bin2hex($devicePart[1]); ?>" class="action-btn" title="Restore device">🔄</a>
                    <a href="?action=delete&mac=<?php echo urlencode($devicePart[0]); ?>&data=<?php echo bin2hex($devicePart[1]); ?>" class="action-btn" title="Delete">🗑️</a>
                </div>
            </li>
            <?php     
                    endfor; 
                } else {
                    // Fallback message when the list is empty
                    echo "<p>There are no ignored Bluetooth devices yet.</p>\n";
                }
            ?>
        </ul>

        <!-- Navigation back to the main dashboard -->
        <p style="margin-top: 30px;"><a href="index.php" class="btn-match">Back to home</a></p>
    </main>

    <!-- Footer Section with dynamic PHP include -->
    <footer class="bottom-section">
        <?php include "data/include/footer.php"; ?>
    </footer>
</body>
</html>
