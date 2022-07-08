<?php
/**
 * User Sessions Widget
 */
ccl_create_option('ccl_check_gp_wp_config_root', 'off', 'Check if wp-config.php is in the WordPress root (BETA)');
if ( get_site_option('ccl_check_gp_wp_config_root', 'off') == "on" ) {   
    Do things.
}