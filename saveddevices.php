<?php
/**
 * BlueSolarPanel
 * Copyright (c) 2026 Björn Eriksson
 * Licensed under the MIT License
 */
 
// Define file paths for saved and ignored devices data storage
$savedFile = 'data/devicelists/saved_devices.txt';
$ignoredFile = 'data/devicelists/ignored_devices.txt';

// Handle incoming GET actions (ignore or edit)
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

            // Action: Move device from the saved list to the ignored list
            if ($action == 'ignore') {
                $oldIgnoredFile = file_exists($ignoredFile) ? trim(file_get_contents($ignoredFile)) : "";
                $oldSavedFile = file_exists($savedFile) ? trim(file_get_contents($savedFile)) : "";

                // Move device if it doesn't already exist in the ignored file
                if (!str_contains($oldIgnoredFile, $mac)) {
                    $newIgnoreFile = $oldIgnoredFile . PHP_EOL . $deviceString;
                    file_put_contents($ignoredFile, trim($newIgnoreFile));
                    
                    // Safe deletion from saved file: Remove the line and clean up duplicate newlines
                    $newSaveFile = str_replace($deviceString, '', $oldSavedFile);            
                    $newSaveFile = str_replace(PHP_EOL . PHP_EOL, PHP_EOL, $newSaveFile);
                    file_put_contents($savedFile, trim($newSaveFile));
                }
            }

            // Action: Edit device information
            if ($action == 'edit') {
                // Future implementation placeholder
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
    <title>BlueSolarPanel - Saved Bluetooth devices</title>
</head>
<body>

    <!-- Header Section containing the main branding link -->
    <header class="top-section">
        <h1><a href="index.php" class="logo-link">BlueSolarPanel</a></h1>
    </header>

    <!-- Main Content Area -->
    <main class="middle-section">
        <h2>Saved Bluetooth devices</h2>
        <p>Devices here can be added to your solar grid systems.</p>
        
        <?php
            $deviceCount = 0;

            // Check if the saved devices file exists and read its content
            if (file_exists($savedFile)) {
                $savedDevices = trim(file_get_contents($savedFile));
                
                // If the file is completely empty (or contains only spaces), set count directly to 0
                if ($savedDevices === "") {
                    $deviceCount = 0;
                } else {
                    // Split the file string into an array by newline characters
                    $deviceArray = preg_split('/\r\n|\r|\n/', $savedDevices);
                    $deviceCount = count($deviceArray);
                }
            }
        ?>

        <h3>💾 Saved devices</h3>
        <ul class="device-list">
            <?php
                // Loop through and display each saved device if any are found
                if ($deviceCount != 0) {
                    for ($i = 0; $i < $deviceCount; $i++): 
                        $devicePart = explode("-||-", $deviceArray[$i]);
            ?>
            <li class="device-item">
                <div class="device-info">
                    <span class="device-name"><?php echo htmlspecialchars($devicePart[1]); ?></span>
                    <span class="device-mac"><?php echo htmlspecialchars($devicePart[0]); ?></span>
                </div>
                <div class="device-actions">
                    <a href="readdevice.php?mac=<?php echo urlencode($devicePart[0]); ?>&data=<?php echo bin2hex($devicePart[1]); ?>" class="action-btn" title="Inspect/Read device">🔍</a>
                    <a href="editdevice.php?mac=<?php echo urlencode($devicePart[0]); ?>&data=<?php echo bin2hex($devicePart[1]); ?>" class="action-btn" title="Edit info">✏️</a>
                    <a href="?action=ignore&mac=<?php echo urlencode($devicePart[0]); ?>&data=<?php echo bin2hex($devicePart[1]); ?>" class="action-btn" title="Disable/Ignore">🚫</a>
                </div>
            </li>
            <?php     
                    endfor; 
                } else {
                    // Fallback message when the list is empty
                    echo "<p>There are no saved Bluetooth devices yet.</p>\n";
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
