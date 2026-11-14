<?php
/**
 * BlueSolarPanel - Device Driver: Victron Energy SmartSolar
 * Copyright (c) 2026 Björn Eriksson
 * Licensed under the MIT License
 */

return [
    'driver_id'     => 'victron_smartsolar',
    'category'      => 'solar_charger',
    'device_name'   => 'Victron Energy',
    
    // Primary identification via unique GATT service UUID
    'fingerprint'   => '97580001-ddf1-48be-b73e-182664615d8e',
    
    // Fallback match if a device dump does not expose the primary UUID
    'name_contains' => 'SmartSolar',
    
    'commands'      => [
        'Live Data Channel' => '97580002-ddf1-48be-b73e-182664615d8e',
        'Write Control'     => '97580004-ddf1-48be-b73e-182664615d8e',
        'Device Settings'   => '97580006-ddf1-48be-b73e-182664615d8e'
    ]
];
