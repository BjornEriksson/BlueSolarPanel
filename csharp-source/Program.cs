// BlueSolarPanel - BLE Device Scanner
// Copyright (c) 2026 Björn Eriksson
// Licensed under the MIT License

using System;
using System.Linq;
using System.IO;
using System.Collections.Generic;
using System.Threading.Tasks;
using Windows.Devices.Bluetooth.Advertisement;

class Program
{
    class BleDevice
    {
        public string Mac { get; set; }
        public string Name { get; set; }
        public short Rssi { get; set; }
    }

    private static Dictionary<string, BleDevice> foundDevices = new Dictionary<string, BleDevice>();

    static async Task Main(string[] args)
    {
        // 1. Set default scan time to 5 seconds
        int scanTimeSeconds = 5;

        // 2. Parse argument -sek= from args
        foreach (string arg in args)
        {
            if (arg.StartsWith("-sek="))
            {
                string valueStr = arg.Replace("-sek=", "").Trim();
                if (int.TryParse(valueStr, out int parsedTime))
                {
                    scanTimeSeconds = parsedTime;
                    Console.WriteLine($"[Argument] Scan time set to {scanTimeSeconds} seconds from argument.");
                }
            }
        }

        // 3. Limit maximum scan time to 15 seconds
        if (scanTimeSeconds > 15)
        {
            Console.WriteLine($"[Limit] Requested time ({scanTimeSeconds}s) exceeds maximum. Adjusting to 15 seconds.");
            scanTimeSeconds = Math.Min(scanTimeSeconds, 15);
        }

        // Get the current directory where the .exe is running
        string currentDir = AppDomain.CurrentDomain.BaseDirectory;
        string finalFilePath = Path.Combine(currentDir, "last_scan.txt");
        string tempFilePath = Path.Combine(currentDir, "last_scan.tmp");

        Console.WriteLine("BlueSolarPanel BLE-scan starting...");
        Console.WriteLine($"Scanning for unique MAC addresses for {scanTimeSeconds} seconds...\n");

        // 4. Delete old file so any observer knows a new scan has started
        if (File.Exists(finalFilePath))
        {
            try { File.Delete(finalFilePath); } catch { }
        }

        // Start Bluetooth LE Scanner
        var watcher = new BluetoothLEAdvertisementWatcher();
        watcher.ScanningMode = BluetoothLEScanningMode.Active;
        watcher.Received += Watcher_Received;
        watcher.Start();

        // Wait for the specified scan time
        await Task.Delay(scanTimeSeconds * 1000);

        // Stop Bluetooth LE Scanner
        watcher.Stop();
        watcher.Received -= Watcher_Received;

        Console.WriteLine("--- SCAN FINISHED. SAVING TO FILE... ---");
        
        var fileLines = new List<string>();
        lock (foundDevices)
        {
            foreach (var device in foundDevices.Values)
            {
                // Format: MAC-||-Name-||-RSSI
                string line = $"{device.Mac}-||-{device.Name}-||-{device.Rssi}";
                fileLines.Add(line);
                Console.WriteLine($"Found: {line}");
            }
        }

        try
        {
            // 5. Write to temporary file first
            File.WriteAllLines(tempFilePath, fileLines);
            
            // 6. Rename atomically to final filename in the same folder
            File.Move(tempFilePath, finalFilePath, overwrite: true);
            
            Console.WriteLine($"\nData successfully saved to: {finalFilePath}");
        }
        catch (Exception ex)
        {
            Console.WriteLine($"Could not save file. Error: {ex.Message}");
        }

        Console.WriteLine("\nScan process finished.");
    }

    private static void Watcher_Received(BluetoothLEAdvertisementWatcher sender,
                                         BluetoothLEAdvertisementReceivedEventArgs args)
    {
        string mac = string.Join(":", Enumerable.Range(0, 6)
            .Select(i => ((args.BluetoothAddress >> (40 - i * 8)) & 0xFF).ToString("X2")));

        string name = string.IsNullOrEmpty(args.Advertisement.LocalName) 
            ? "Unknown Name" 
            : args.Advertisement.LocalName;

        short rssi = args.RawSignalStrengthInDBm;

        lock (foundDevices)
        {
            if (foundDevices.ContainsKey(mac))
            {
                foundDevices[mac].Rssi = rssi;
                if (name != "Unknown Name" && foundDevices[mac].Name == "Unknown Name") 
                {
                    foundDevices[mac].Name = name;
                }
            }
            else
            {
                var newDevice = new BleDevice { Mac = mac, Name = name, Rssi = rssi };
                foundDevices.Add(mac, newDevice);
            }
        }
    }
}
