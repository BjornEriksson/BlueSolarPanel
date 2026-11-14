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
    <title>BlueSolarPanel - Configuration</title>
</head>
<body>

    <!-- Header Section containing the main branding link -->
    <header class="top-section">
        <h1><a href="index.php" class="logo-link">BlueSolarPanel</a></h1>
    </header>

    <!-- Main Content Area -->
    <main class="middle-section">
        <h2>Configuration</h2>
        <p style="color: #666; margin-bottom: 30px;">Adjust system parameters and optimize your hardware scanning process.</p>
        
        <?php
            $configFile = 'data/config/config.txt';
            $scantime = '';
            $message = '';
            $error_message = '';

            // Ensure required data storage directories exist
            if (!is_dir('data')) { mkdir('data', 0777, true); }
            if (!is_dir('data/config')) { mkdir('data/config', 0777, true); }
            if (!is_dir('data/tmpdata')) { mkdir('data/tmpdata', 0777, true); }
            if (!is_dir('data/deviceprofiles')) { mkdir('data/deviceprofiles', 0777, true); }

            // Read the current scan time from the configuration file if it exists
            if (file_exists($configFile)) {
                $fileContent = trim(file_get_contents($configFile));

                if (preg_match('/scantime\s*=\s*(\d+)/i', $fileContent, $matches)) {
                    $scantime = (int)$matches[1];
                }
            } else {
                $scantime = "5"; // Default fallback scan duration
            }

            // Process form submission to update configuration settings
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $inputScantime = trim($_POST['scantime'] ?? '');

                if (ctype_digit($inputScantime) && (int)$inputScantime >= 1 && (int)$inputScantime <= 15) {
                    $scantime = (int)$inputScantime;
                    file_put_contents($configFile, "scantime = " . $scantime);
                    $message = "Configuration saved!";
                } else {
                    $error_message = "Please enter a valid number between 1 and 15 seconds.";
                }
            }
        ?>

        <!-- Success feedback alert -->
        <?php if (!empty($message)): ?>
            <div class="alert-success">✅ <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Error feedback alert -->
        <?php if (!empty($error_message)): ?>
            <div class="alert-success" style="background-color: #f8d7da; color: #842029;">⚠️ <?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Configuration input form wrapped inside a stylized interface box -->
        <div class="test-box" style="border-color: #ddd; background-color: #fafafa; padding: 25px;">
            <h3 style="margin-top: 0; margin-bottom: 15px;">⚙️ Scanner Settings</h3>
            
            <p style="font-size: 0.95rem; color: #555; margin-bottom: 20px;">
                If nearby devices are missing or their names are listed as unknown, increasing the scan time by a few seconds usually helps.
            </p>

            <form action="config.php" method="POST" autocomplete="off">
                <div class="form-group">
                    <label for="scantime" style="margin-bottom: 8px;">Seconds to scan for new Bluetooth devices (1–15)</label>
                    <input type="text" id="scantime" name="scantime" class="form-control" value="<?php echo htmlspecialchars($scantime); ?>" placeholder="5 seconds is recommended" required>
                </div>

                <button type="submit" class="btn-match" style="border: none; width: 100%; cursor: pointer; margin-top: 10px;">
                    Save Configuration
                </button>
            </form>
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
