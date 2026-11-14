<?php
/**
 * BlueSolarPanel
 * Copyright (c) 2026 Björn Eriksson
 * Licensed under the MIT License
 */

$profileDir = 'data/deviceprofiles/';
$mac = $_GET['mac'] ?? '';

// Retrieve and decode the raw hardware name string passed from the link
$passedHardwareName = '';
if (!empty($_GET['data'])) {
    $passedHardwareName = trim(hex2bin($_GET['data']));
}

$message = '';

// Standardize MAC address to locate the matching JSON profile file
$cleanMac = str_replace([':', '-'], '', $mac);
$profilePath = $profileDir . $cleanMac . ".json";

// Redirect to saved devices if no MAC address is provided
if (empty($mac)) {
    header('Location: saveddevices.php');
    exit;
}

// Initialize or load profile data structure safely
$profile = [];
if (file_exists($profilePath)) {
    // Load the existing profile file
    $profile = json_decode(file_get_contents($profilePath), true);
} else {
    // Fallback: If no profile file exists yet, build one directly using the URL arguments
    $profile = [
        'mac_address'    => $mac,
        'category'       => 'unknown',
        'driver'         => 'standard_ble',
        'vendor_name'    => 'Unknown Brand',
        'hardware_model' => !empty($passedHardwareName) ? $passedHardwareName : 'Unknown Model',
        'user_name'      => '',          
        'notes'          => '',          
        'identified_at'  => date('Y-m-d H:i:s'),
        'status'         => 'active',
        'commands'       => []
    ];
}

// Process form submission to update user customizations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $profile['user_name'] = trim($_POST['user_name'] ?? '');
    $profile['notes']     = trim($_POST['notes'] ?? '');

    // Make sure the profile directory exists before saving
    if (!is_dir($profileDir)) {
        mkdir($profileDir, 0777, true);
    }

    // Save updated data back to the JSON file
    file_put_contents($profilePath, json_encode($profile, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    $message = "Device custom settings saved successfully!";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="data/css/style.css">
    <title>BlueSolarPanel - Edit Device</title>
</head>
<body>

    <!-- Header Section containing the main branding link -->
    <header class="top-section">
        <h1><a href="index.php" class="logo-link">BlueSolarPanel</a></h1>
    </header>

    <!-- Main Content Area -->
    <main class="middle-section">
        <h2>Edit Device Information</h2>
        <p style="color: #666; margin-bottom: 30px;">Customize display names and add specific operational notes for this hardware.</p>

        <!-- Success feedback alert -->
        <?php if (!empty($message)): ?>
            <div class="alert-success">✅ <?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <!-- Configuration input form wrapped inside a stylized interface box -->
        <div class="test-box" style="border-color: #ddd; background-color: #fafafa; padding: 25px;">
            <h3 style="margin-top: 0; margin-bottom: 15px;">⚙️ Device Customization</h3>
            
            <p style="font-size: 0.95rem; color: #555; margin-bottom: 20px;">
                <strong>MAC Address:</strong> <?php echo htmlspecialchars($profile['mac_address']); ?><br>
                <strong>Brand:</strong> <?php echo htmlspecialchars($profile['vendor_name'] ?? 'Unknown'); ?><br>
                <strong>Hardware Model:</strong> <?php echo htmlspecialchars($profile['hardware_model'] ?? 'Unknown'); ?>
            </p>

            <form action="editdevice.php?mac=<?php echo urlencode($mac); ?>&data=<?php echo urlencode($_GET['data'] ?? ''); ?>" method="POST" autocomplete="off">
                
                <!-- Field 1: Custom Alias Name -->
                <div class="form-group">
                    <label for="user_name">Custom Device Name (Alias)</label>
                    <input type="text" id="user_name" name="user_name" class="form-control" 
                           value="<?php echo htmlspecialchars($profile['user_name'] ?? ''); ?>" 
                           placeholder="e.g. Service Battery, Main Solar Controller...">
                </div>

                <!-- Field 2: Custom Text Notes -->
                <div class="form-group" style="margin-top: 15px;">
                    <label for="notes">Notes & Logs</label>
                    <textarea id="notes" name="notes" class="form-control" rows="5" style="resize: vertical; font-family: inherit;"
                              placeholder="Add battery configurations, installation dates, or wiring info here..."><?php echo htmlspecialchars($profile['notes'] ?? ''); ?></textarea>
                </div>

                <button type="submit" class="btn-match" style="border: none; width: 100%; cursor: pointer; margin-top: 20px;">
                    Save Device Profile
                </button>
            </form>
        </div>

        <!-- Navigation back to the saved devices view -->
        <p style="margin-top: 30px;"><a href="saveddevices.php" class="btn-match" style="background-color: #6c757d;">Back to saved devices</a></p>
    </main>

    <!-- Footer Section with dynamic PHP include -->
    <footer class="bottom-section">
        <?php include "data/include/footer.php"; ?>
    </footer>
</body>
</html>