<?php
/**
 * BlueSolarPanel
 * Copyright (c) 2026 Björn Eriksson
 * Licensed under the MIT License
 */

function generateDeviceProfile($macAddress, $scannedName = '') {
    // Standardize all folder paths using absolute paths (__DIR__)
    $baseDir    = dirname(__DIR__) . '/'; 
    $dataDir    = $baseDir . 'devicedata/';
    $profileDir = $baseDir . 'deviceprofiles/';
    $driverDir  = $baseDir . 'drivers/';

    // Clean MAC address for filename consistency
    $cleanMac = str_replace([':', '-'], '', $macAddress);
    $rawFilePath = $dataDir . $cleanMac . ".txt";

    // Wait for the file to be created and written by the executable
    $startTime = microtime(true);
    while ((!file_exists($rawFilePath) || filesize($rawFilePath) === 0) && (microtime(true) - $startTime) < 3) {
        usleep(250000); 
    }

    if (!file_exists($rawFilePath) || filesize($rawFilePath) === 0) {
        return false; 
    }

    $rawData = file_get_contents($rawFilePath);

    // 1. Retrieve and clean the advertised device name from the initial BLE scan
    $scannedNameClean = trim($scannedName) ?: 'Unknown Device';

    // 2. Attempt to read the official GAP device name from the raw GATT data (UUID 00002a00)
    $internalNameClean = 'Unknown';
    if (preg_match('/00002a00-[^\r\n]+-\|\|-\s*([0-9A-Fa-f\s]+)\s*$/m', $rawData, $matches)) {
        $hexName = preg_replace('/\s+/', '', $matches);
        if (!empty($hexName) && ctype_xdigit($hexName) && (strlen($hexName) % 2 === 0)) {
            $decodedName = @hex2bin($hexName);
            if ($decodedName !== false) {
                $internalNameClean = trim($decodedName, "\0 \t\n\r\x0B");
            }
        }
    }


    // Default values if no hardware driver matches the device fingerprint
    $matchedDriver = 'standard_ble';
    $category      = 'unknown';
    $vendorName    = 'Unknown Brand'; 
    $commands      = [];

    // Scan all driver files in data/drivers/
    $driverFiles = glob($driverDir . "*.php");
    if (!empty($driverFiles)) {
        foreach ($driverFiles as $file) {
            $currentDriver = include($file); 
            
            if (is_array($currentDriver)) {
                $matchFound = false;

                // Test A: Match on unique UUID/fingerprint in the raw GATT data
                if (!empty($currentDriver['fingerprint']) && str_contains($rawData, $currentDriver['fingerprint'])) {
                    $matchFound = true;
                }
                
                // Test B: Fallback - Match on partial device name if the primary UUID is missing
                if (!$matchFound && !empty($currentDriver['name_contains'])) {
                    if (str_contains($scannedNameClean, $currentDriver['name_contains']) || 
                        str_contains($internalNameClean, $currentDriver['name_contains'])) {
                        $matchFound = true;
                    }
                }

                // Apply driver metadata if a match is found
                if ($matchFound) {
                    $matchedDriver = $currentDriver['driver_id'];
                    $category      = $currentDriver['category'];
                    $vendorName    = $currentDriver['device_name']; 
                    $commands      = $currentDriver['commands'] ?? [];
                    break;
                }
            }
        }
    }

    // Structure profile data for JSON storage
    $profileData = [
        'mac_address'   => $macAddress,
        'category'      => $category,
        'driver'        => $matchedDriver,
        'vendor_name'   => $vendorName,
        'scanned_name'  => $scannedNameClean,   // Advertised name from initial BLE scan
        'internal_name' => $internalNameClean,  // Internal GATT name (0x2A00)
        'custom_name'   => '',                  // Left blank for user-defined alias
        'notes'         => '',
        'identified_at' => date('Y-m-d H:i:s'),
        'status'        => 'active',
        'commands'      => $commands
    ];

    if (!is_dir($profileDir)) {
        mkdir($profileDir, 0777, true);
    }

    // Write the JSON profile to the deviceprofiles folder
    $profileFilePath = $profileDir . $cleanMac . ".json";
    file_put_contents($profileFilePath, json_encode($profileData, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

    return true;
}
