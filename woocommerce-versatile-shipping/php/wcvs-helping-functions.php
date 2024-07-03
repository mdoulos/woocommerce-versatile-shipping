<?php
// ------------------------------
// Basic functions that other functions use.
// ------------------------------

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function within_bounds($lower_bound, $number, $upper_bound) {
    return $number >= $lower_bound && $number <= $upper_bound; // Returns true if the number is within the bounds.
}