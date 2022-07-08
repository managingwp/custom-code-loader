<?php
/**
 * WordPress Tweaks
 */

/**
 * WordPress Extend Session
 */
ccl_create_option('ccl_enable_extend_login_session', 'off', 'WordPress Extend Session Time');
if ( get_site_option('ccl_enable_extend_login_session', 'off') == "on" ) {   
    add_filter('auth_cookie_expiration', function( $expirein ) {
        return get_site_option('ccl_enable_extend_login_session_value', 'WEEK_IN_SECONDS'); // 1 week in seconds
    });
}

/**
 * Set Autosave Interval
 */
ccl_create_option('ccl_enable_autosave_interval', 'off', 'Set Autosave Interval');
ccl_create_option_value('ccl_enable_autosave_interval', '300', 'Autosave Interval (seconds)');
if ( get_site_option('ccl_enable_autosave_interval', 'off') == "on" ) {    
    define( 'AUTOSAVE_INTERVAL', get_site_option('ccl_enable_autosave_interval_value', '300'));
}

/**
 * Set WP Post Revision Limit
 */
ccl_create_option('ccl_enable_post_revisions', 'off', 'Set WP Post Revision Limit');
ccl_create_option_value('ccl_enable_post_revisions', '3','Post Revision Count');

if ( get_site_option('ccl_enable_post_revisions', 'off') == "on" ) {
    define( 'WP_POST_REVISIONS', get_site_option('ccl_enable_post_revisions_value', '3'));
}

/**
 * Set Cookie Domain
 */
ccl_create_option('ccl_enable_cookie_domain', 'off', 'Enable Cookie Domain $_SERVER[\'HTTP_HOST\']');
if ( get_site_option('ccl_enable_cookie_domain', 'off') == "on" ) {
    if ( isset( $_SERVER['HTTP_HOST'] ) ) {
        define( 'COOKIE_DOMAIN', $_SERVER['HTTP_HOST'] );    
    }    
}