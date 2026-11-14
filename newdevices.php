<?php
/**
 * BlueSolarPanel
 * Copyright (c) 2026 Björn Eriksson
 * Licensed under the MIT License
 */
 
// Define file paths for saved and ignored devices data storage
$savedFile = 'data/devicelists/saved_devices.txt';
$ignoredFile = 'data/devicelists/ignored_devices.txt';
                
// Handle incoming GET actions (save or ignore)
if (isset($_GET['action'])) {
    $scantime = 0;
    if (!empty($_GET['action'])) {
        $action = $_GET['action'];
        $mac = "";
        $data = "";
        
        // Extract parameters and ensure the storage directory exists
        if ((!empty($_GET['mac'])) && (!empty($_GET['data']))) {
            $mac = $_GET['mac'];
            $data = hex2bin($_GET['data']);
            $deviceString = $mac . "-||-" . $data;

            if (!is_dir('data/devicelists')) { 
                mkdir('data/devicelists', 0777, true);
            }

            // Action: Save device to the trusted list
            if ($action == 'save') {
                $oldSaved = "";
                
                if (file_exists($savedFile)) {
                    $oldSaved = trim(file_get_contents($savedFile));
                }

                if (!str_contains($oldSaved, $mac)) {
                    $newSaveFile = $oldSaved . PHP_EOL . $deviceString;
                    file_put_contents($savedFile, trim($newSaveFile));
                }
            }

            // Action: Move device to the ignored list
            if ($action == 'ignore') {
                $oldIgnored = "";
                if (file_exists($ignoredFile)) {
                    $oldIgnored = trim(file_get_contents($ignoredFile));
                }

                if (!str_contains($oldIgnored, $mac)) {
                    $newIgnoreFile = $oldIgnored . PHP_EOL . $deviceString;
                    file_put_contents($ignoredFile, trim($newIgnoreFile));
                }
            }

            if (($action == 'save') || ($action == 'ignore')) {
                // Prepare paths for the data fetching executable
                $relativeExePath = __DIR__ . '/data/win64-getdata/win64-getdata.exe';
                $exePath = realpath($relativeExePath);
                $relativePath = "../devicedata/"; 

                if (!$exePath) {
                    die("Error: Can't find .exe-file.");
                }

                // Run executable synchronously and wait for it to complete
                $safeMac = escapeshellarg($mac);
                $safePath = escapeshellarg($relativePath);
                $command = '"' . $exePath . '" ' . $safeMac . ' ' . $safePath . ' 2>&1';
                exec($command);

                // Trigger the automated analyzer immediately after the .exe file finishes
                if ($action == 'save') {
                    require_once 'data/include/analyzer.php';
                    generateDeviceProfile($mac, $data);
                }
            }
        }
    }
} else {
    // Default scan duration if no action is requested
    $scantime = 5;
    $configFile = 'data/config/config.txt';
    
    // Read custom scan time from the configuration file
    if (file_exists($configFile)) {
        $fileContent = trim(file_get_contents($configFile));

        if (preg_match('/scantime\s*=\s*(\d+)/i', $fileContent, $matches)) {
            $scantime = (int)$matches[1];
            $scantime = min($scantime, 15); // Cap maximum scan duration at 15 seconds
        }
    }

    // Set the relative path to the folder and the executable
    $exeDir = __DIR__ . '/data/win64-blescan/';
    $exeName = 'win64-blescan.exe';

    // Define the scan time in seconds
    //$safeSeconds = escapeshellarg($scantime);
	$safeSeconds = (int)$scantime;

    // Build the command with the argument and redirect errors (2>&1)
    $command = '"' . $exeDir . $exeName . '" -sek=' . $safeSeconds . ' 2>&1';
    $output = [];
    $resultCode = 0;

    // Switch to the program's folder during execution to locate all .NET dependencies
    $oldDir = getcwd();
    chdir($exeDir);
    exec($command, $output, $resultCode);

    // Instantly revert back to PHP's original working directory
    chdir($oldDir);

    // Save an execution log in the same folder for debugging
    $outputString = print_r($output, true);
    $logMessage = "Result code: " . $resultCode . PHP_EOL . PHP_EOL . "Console output:" . PHP_EOL . $outputString;
    file_put_contents($exeDir . 'php_execution_log.txt', trim($logMessage));

    // Display an error message if the C# executable fails
    if ($resultCode != 0) {
        echo "Something went wrong. Please check the log file at: " . htmlspecialchars($exeDir) . "php_execution_log.txt";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="data/css/style.css">
    <title>BlueSolarPanel - New Bluetooth devices</title>
</head>
<body>

    <!-- Header Section containing the main branding link -->
    <header class="top-section">
        <h1><a href="index.php" class="logo-link">BlueSolarPanel</a></h1>
    </header>

    <!-- Main Content Area -->
    <main class="middle-section">
        <h2>New Bluetooth devices</h2>
        <p><a href="ms-settings:bluetooth">Make sure Bluetooth is turned on</a>
        and a good scan time is set in <a href="config.php">configuration</a>.</p>

        <?php
            $lastScanFile = 'data/win64-blescan/last_scan.txt';
            $deviceCount = 0;
            $startTime = microtime(true);

            // Retry loop: Wait up to 2 seconds for the scan results file to appear (polls every 1/4 second)
            while (!file_exists($lastScanFile) && (microtime(true) - $startTime) < 2) {
                usleep(250000); 
            }
            
            // Process scan results and filter out already saved or ignored devices
            if (file_exists($lastScanFile)) {
                $lastScanContent = trim(file_get_contents($lastScanFile));
                $deviceArray = preg_split('/\r\n|\r|\n/', $lastScanContent);
                $deviceCount = count($deviceArray);
                $newDeviceCount = 0;

                if ($deviceCount != 0) {
                    $newDevices = [];
                    $oldDevices = "";
                    $oldDevices .= @file_get_contents($savedFile);
                    $oldDevices .= PHP_EOL;
                    $oldDevices .= @file_get_contents($ignoredFile);
                    $oldDevices = trim($oldDevices);
                    
                    if (!is_dir('data/tmpdata')) { 
                        mkdir('data/tmpdata', 0777, true); 
                    }
                    file_put_contents('data/tmpdata/allstoreddevices.txt', $oldDevices);

                    // Add to the view list only if the device's MAC is not already stored
                    for ($i = 0; $i < $deviceCount; $i++) {
                        $devicePart = explode("-||-", $deviceArray[$i]);

                        if (!str_contains($oldDevices, $devicePart[0])) {
                            $newDevices[] = $deviceArray[$i];
                        }
                    }
                }

                $newDeviceCount = count($newDevices);
            }
        ?>

        <h3>🔍 New devices</h3>
        <ul class="device-list">
            <?php
                // Loop through and display undiscovered devices
                if ((isset($newDeviceCount)) && ($newDeviceCount != 0)) {
                    for ($i = 0; $i < $newDeviceCount; $i++): 
                        $devicePart = explode("-||-", $newDevices[$i]);
            ?>
            <li class="device-item">
                <div class="device-info">
                    <span class="device-name"><?php echo htmlspecialchars($devicePart[1]); ?></span>
                    <span class="device-mac"><?php echo htmlspecialchars($devicePart[0]) . " (" . htmlspecialchars($devicePart[2]) . " dBm)"; ?></span>
                </div>
                <div class="device-actions">
                    <a href="?action=save&mac=<?php echo urlencode($devicePart[0]); ?>&data=<?php echo bin2hex($devicePart[1]); ?>" class="action-btn scan-trigger" title="Save device">💾</a>
                    <a href="?action=ignore&mac=<?php echo urlencode($devicePart[0]); ?>&data=<?php echo bin2hex($devicePart[1]); ?>" class="action-btn" title="Disable/Ignore">🚫</a>
                </div>
            </li>
            <?php     
                    endfor; 
                } else {
                    // Fallback message when no new hardware is detected
                    echo "<p>No new Bluetooth devices were found.</p>\n";
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

    <!-- Hidden loading screen that triggers on scan click -->
    <div id="wait-screen" class="loading-overlay" style="display: none;">
        <div class="loading-text">Wait...</div>
    </div>

    <script>
        // Trigger the pulsating overlay animation when the user clicks the scan button
        document.querySelectorAll('.scan-trigger').forEach(function(button) {
            button.addEventListener('click', function() {
                document.getElementById('wait-screen').style.display = 'flex';
            });
        });
    </script>
</body>
</html>
