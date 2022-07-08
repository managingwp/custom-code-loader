<?php

/**
  *  Ultimo Check Bypass
  */

// Add option to $ccl_options
ccl_create_option('ccl_enable_ultimo_check_bypass', 'off', 'Ultimo Bypass Check (Fix Slow Pages');

if ( get_site_option('ccl_enable_ultimo_check_bypass', 'off') == "on" ) {
    /* wu_golden_ticket */
    function wu_gold_ticket_return_true($value, $transient) {
        if ($transient === 'wu_golden_ticket') {
            return true;
        }
        return $value; // Return original value for other transients
    }
    add_filter('pre_site_transient_wu_golden_ticket', 'wu_gold_ticket_return_true', 10, 2);

        /* wu_saved_ff_count */
    function wu_saved_ff_count_return_true($value, $transient) {
        if ($transient === 'wu_saved_ff_count') {
            return "10";
        }
        return $value; // Return original value for other transients
    }
    add_filter('pre_site_transient_wu_saved_ff_count', 'wu_saved_ff_count_return_true', 10, 2);
}