<?php
/**
 * BlueSolarPanel
 * Copyright (c) 2026 Björn Eriksson
 * Licensed under the MIT License
 */

$mac = "";
$data = "";
$deviceData = "";

// Extract and decode MAC address and device data parameters
if ((!empty($_GET['mac'])) && (!empty($_GET['data']))) {
    $mac = $_GET['mac'];
    $data = hex2bin($_GET['data']);
    $dataFileName = trim("data/devicedata/" . str_replace(':', '', $mac) . ".txt");
    
    if (file_exists($dataFileName)) {
        $deviceData = trim(file_get_contents($dataFileName));
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="data/css/style.css">
    <title>BlueSolarPanel - Device Information</title>
</head>
<body>

    <!-- Header Section containing the main branding link -->
    <header class="top-section">
        <h1><a href="index.php" class="logo-link">BlueSolarPanel</a></h1>
    </header>

    <!-- Main Content Area -->
    <main class="middle-section">
        <h2>Device information</h2>
        <p style="color: #666; margin-bottom: 30px;">Inspect raw GATT services and characteristic values read from the device.</p>

        <!-- Section: Device Raw Data Output -->
        <div class="test-box" style="margin-bottom: 25px; border-color: #ddd; background-color: #fafafa;">
            <h3 style="margin-top: 0;">🔍 Data from <?php echo htmlspecialchars($mac . " - " . $data); ?></h3>

            <?php
            if (!empty($deviceData)) {
                $dataRows = preg_split('/\r\n|\r|\n/', $deviceData);
                $rowCount = count($dataRows);

                if ($rowCount != 0) {
                    for ($i = 0; $i < $rowCount; $i++) {
                        if (!str_contains($dataRows[$i], "ERROR")) {
                            if (!str_contains($dataRows[$i], "SERVICE")) {
                                $rowPart = explode("-||-", $dataRows[$i]);
                                $hexOrNot = trim(str_replace(' ', '', $rowPart[2]));

                                if (($hexOrNot != 'NOTIFY_ONLY') && ($hexOrNot != 'WRITE_ONLY') && ($hexOrNot != 'READ_LOCKED')) {
                                    $bytes = array_values(unpack('C*', hex2bin($hexOrNot)));
                                    $hexArray = [];

                                    foreach ($bytes as $byte) {
                                        // Convert each byte to hex (e.g., 10 becomes "0a") and pad with a leading zero if needed
                                        $hexArray[] = "0x" . str_pad(dechex($byte), 2, "0", STR_PAD_LEFT);
                                    }

                                    // Join into a readable hex byte string
                                    $readableOutput = implode(' ', $hexArray);
                                    echo "<p>UUID: " . htmlspecialchars($rowPart[1]) . " ---> [" . htmlspecialchars($readableOutput) . "]</p>";
                                } else {
                                    echo "<p>UUID: " . htmlspecialchars($rowPart[1]) . " ---> " . htmlspecialchars($hexOrNot) . "</p>";
                                }
                            }
                        } else {
                            echo "<p>Could not read services. Status: Unreachable</p>";
                        }
                    }
                }
            } else {
                echo "<p>No raw data file found for this device.</p>";
            }
            ?>
            
            <hr>
            <p><code><small><?php echo nl2br(htmlspecialchars($deviceData)); ?></small></code></p>
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
