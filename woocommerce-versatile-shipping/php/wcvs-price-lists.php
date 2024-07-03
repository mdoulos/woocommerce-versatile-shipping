<?php
// ------------------------------
// Price Lists
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function wcvs_get_ups_price_list() {
    return array(
        1 => array( // UPS Ground Zone 2
            1 => 10.70,
            50 => 26.17,
            100 => 66.16,
            150 => 107.57
        ),
        2 => array( // UPS Ground Zone 5
            1 => 12.70,
            50 => 47.31,
            100 => 78.81,
            150 => 121.77
        ),
        3 => array( // UPS Ground Zone 8
            1 => 13.38,
            50 => 79.65,
            100 => 112.92,
            150 => 162.63
        ),
        4 => array( // UPS Ground Zone 44
            1 => 38.56,
            50 => 215.10,
            100 => 388.28,
            150 => 565.38
        ),
        5 => array( // UPS Ground Zone 45
            1 => 39.02,
            50 => 263.43,
            100 => 505.38,
            150 => 758.07
        ),
        6 => array( // UPS Ground Zone 46
            1 => 50.79,
            50 => 227.29,
            100 => 398.43,
            150 => 595.19
        ),
    );
}

function wcvs_get_canpar_price_list() {
    return array(
        4 => array( // Canpar Ground Zone 4
            1 => 7.87,
            50 => 15.77,
            100 => 27.40,
            150 => 39.04
        ),
        8 => array( // Canpar Ground Zone 8
            1 => 10.66,
            50 => 21.05,
            100 => 34.71,
            150 => 48.38
        ),
        12 => array( // Canpar Ground Zone 12
            1 => 12.61,
            50 => 25.02,
            100 => 42.23,
            150 => 59.45
        ),
        16 => array( // Canpar Ground Zone 16
            1 => 26.77,
            50 => 53.53,
            100 => 91.89,
            150 => 130.25
        ),
    );
}

function wcvs_get_oversized_price_list_usa() {
    // Returns an array of prices for oversized items according to UPS zones.
    return array(
        1 => array( // UPS Ground Zone 2
            'OS097' => 132.05,
            'OS108' => 254.15,
            'OW150' => 254.15,
        ),
        2 => array( // UPS Ground Zone 5
            'OS097' => 141.55,
            'OS108' => 254.15,
            'OW150' => 254.15,
        ),
        3 => array( // UPS Ground Zone 8
            'OS097' => 152.05,
            'OS108' => 254.15,
            'OW150' => 254.15,
        ),
        4 => array( // UPS Ground Zone 44
            'OS097' => 150.55,
            'OS108' => 254.15,
            'OW150' => 254.15,
        ),
        5 => array( // UPS Ground Zone 45
            'OS097' => 150.55,
            'OS108' => 254.15,
            'OW150' => 254.15,
        ),
        6 => array( // UPS Ground Zone 46
            'OS097' => 150.55,
            'OS108' => 254.15,
            'OW150' => 254.15,
        ),
    );
}

function wcvs_get_oversized_price_list_can() {
    // Returns an array of prices for oversized items according to Canpar zones.
    return array(
        1 => array( // Canpar Ground Zone 4
            'OS097' => 132.05,
            'OS108' => 254.15,
            'OW150' => 254.15,
        ),
        2 => array( // Canpar Ground Zone 8
            'OS097' => 141.55,
            'OS108' => 254.15,
            'OW150' => 254.15,
        ),
        3 => array( // Canpar Ground Zone 12
            'OS097' => 150.55,
            'OS108' => 254.15,
            'OW150' => 254.15,
        ),
        4 => array( // Canpar Ground Zone 16
            'OS097' => 150.55,
            'OS108' => 254.15,
            'OW150' => 254.15,
        ),
    );
}

function wcvs_get_oversized_repeating_price_list_can() {
    // Returns an array of prices for secondary instances of oversized items according to Canpar zones.
    return array(
        1 => array( // Canpar Ground Zone 4
            'OS097' => 6.95,
            'OS108' => 44.85,
            'OW150' => 44.85,
        ),
        2 => array( // Canpar Ground Zone 8
            'OS097' => 7.45,
            'OS108' => 44.85,
            'OW150' => 44.85,
        ),
        3 => array( // Canpar Ground Zone 12
            'OS097' => 8.45,
            'OS108' => 44.85,
            'OW150' => 44.85,
        ),
        4 => array( // Canpar Ground Zone 16
            'OS097' => 8.45,
            'OS108' => 44.85,
            'OW150' => 44.85,
        ),
    );
}

function wcvs_get_oversized_repeating_price_list_usa() {
    // Returns an array of prices for secondary instances of oversized items according to UPS zones.
    return array(
        1 => array( // UPS Ground Zone 2
            'OS097' => 6.95,
            'OS108' => 44.85,
            'OW150' => 44.85,
        ),
        2 => array( // UPS Ground Zone 5
            'OS097' => 7.45,
            'OS108' => 44.85,
            'OW150' => 44.85,
        ),
        3 => array( // UPS Ground Zone 8
            'OS097' => 8.45,
            'OS108' => 44.85,
            'OW150' => 44.85,
        ),
        4 => array( // UPS Ground Zone 44
            'OS097' => 8.45,
            'OS108' => 44.85,
            'OW150' => 44.85,
        ),
        5 => array( // UPS Ground Zone 45
            'OS097' => 8.45,
            'OS108' => 44.85,
            'OW150' => 44.85,
        ),
        6 => array( // UPS Ground Zone 46
            'OS097' => 8.45,
            'OS108' => 44.85,
            'OW150' => 44.85,
        ),
    );
}