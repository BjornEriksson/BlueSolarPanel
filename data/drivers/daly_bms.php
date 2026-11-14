<?php
/**
 * BlueSolarPanel - Device Driver: Daly / JBD Smart BMS
 * Copyright (c) 2026 Björn Eriksson
 * Licensed under the MIT License
 */

return [
    'driver_id'     => 'daly_bms',
    'category'      => 'battery',
    'device_name'   => 'Daly Smart BMS',
    'fingerprint'   => '0000ff02-0000-1000-8000-00805f9b34fb',
    
    // Fallback match string for increased reliability in the analyzer loop
    'name_contains' => 'Daly', 
    
    'commands'      => [
        'Main Status'   => 'DDA50300FFFD77',
        'Cell Voltages' => 'DDA50400FFFC77',
        'Hardware Info' => 'DDA50500FFFB77'
    ]
];
