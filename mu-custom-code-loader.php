<?php
/* Plugin Name: Custom Code Loader
Description: A plugin to enable and disable customizations for the BLU site.
*/

/**
 * Initialize the plugin
 */
function init_ccl() {
    global $ccl_documentation,$ccl_data_area;
    // Variables
    if ( ! defined( 'CCL_MENU_TITLE') ) { define( 'CCL_MENU_TITLE', 'CCL' ); }
    if ( ! defined( 'CCL_MENU_LABEL') ) { define( 'CCL_MENU_LABEL', 'CCL' ); }
    if ( ! defined( 'CCL_MENU_SLUG') ) { define( 'CCL_MENU_SLUG', 'ccl' ); }
    if ( ! defined( 'CCL_MENU_CALLBACK') ) { define( 'CCL_MENU_CALLBACK', 'ccl_callback' ); }
    if ( ! defined( 'CCL_MENU_ICON') ) { define( 'CCL_MENU_ICON', 'dashicons-hammer' ); }

    // Some information
    $ccl_documentation = 
    '<h2>Documentation</h2>
    <div class="ccl-define-documentation">    
    You can add the following items to your wp-config.php to customize CCL:
        <pre>
        define( \'CCL_MENU_TITLE\', \'CCL\' );
        define( \'CCL_MENU_LABEL\', \'CCL\' );
        define( \'CCL_MENU_SLUG\', \'ccl\' );
        define( \'CCL_MENU_CALLBACK\', \'ccl_callback\' );
        define( \'CCL_MENU_ICON\', \'dashicons-hammer\' );        
        </pre>
    </div>
    ';

    // CCL Data Area
    global $ccl_options, $ccl_options_default, $ccl_options_desc, $ccl_options_value_default, $ccl_options_value_desc;
    $ccl_options_area = '';
    $ccl_data_area = '';
    $ccl_options = []; // All options loaded by this plugin
    $ccl_options_default = []; // All default values in an array
    $ccl_options_desc = []; // All mods loaded by this plugin
    $ccl_options_value_default = []; // All default values in an array
    $ccl_options_value_desc = []; // All mods loaded by this plugin

    // Load all files in the /mu-plugins/ directory
    foreach ( glob( __DIR__ . '/mod-*.php' ) as $file ) {
        // Add each file to array
        $mod_files[] = $file;
        // Include each file
        require_once $file;
    }

    /**
     * Add a menu item to the network admin menu
     * @return void
     */    
    function ccl_page() {
        // Set the menu capability
        if (is_multisite()) {
            $menu_capability = 'manage_network';
        } else {
            $menu_capability = 'manage_options';
        }

        // Add the menu item
        add_menu_page(
        CCL_MENU_TITLE,      // Title for the menu page
        CCL_MENU_LABEL,         // Menu label
        $menu_capability,             // Capability (for super admins)
        CCL_MENU_SLUG,      // Menu slug
        CCL_MENU_CALLBACK, // Callback
        CCL_MENU_ICON, // Icon
        '2' // Order
        );
    }
    
    // Add the menu item
    if (is_multisite()) { 
        add_action('network_admin_menu', 'ccl_page'); 
    } else { 
        add_action('admin_menu', 'ccl_page'); 
    }
}
init_ccl();

/**
 * Callback for the menu page
 */
function ccl_callback() {
    global $ccl_options, $ccl_options_default, $ccl_options_desc, $ccl_options_value_default,$ccl_options_value_desc;
    global $ccl_documentation,$ccl_data_area;

    // Set Debug when adding ?debug to URL
    if ( isset( $_GET['debug'] ) ) {
        $debug = true;
    } else {
        $debug = false;
    }    

    // Check if any options are in $POST using a loop
    if (isset($_POST['ccl_submit'])) {
        foreach ($ccl_options as $option) {
            if (isset($_POST[$option])) {
                update_site_option($option, $_POST[$option]);
            } else {
                update_site_option($option, $ccl_options_default[$option]);
            }
        }
    }    

    // Get all options and set them to variables via loop
    foreach ($ccl_options as $option) {
        $$option = get_site_option($option, $ccl_options_default[$option]);
        if ( isset( $ccl_options_value_default[$option]) ) {            
            ${option.'_value'} = get_site_option($option . '_value', $ccl_options_value_default[$option]);            
        }
    }
    
    // Print Debug
    if ( $debug ) {
        echo "<div>";
        echo "Options<pre>".print_r($ccl_options,true)."</pre>";
        echo "Options Default<pre>".print_r($ccl_options_default,true)."</pre>";
        echo "Options Value Default<pre>".print_r($ccl_options_value_default,true)."</pre>";
        echo "</div>";
    }
    
    ?>
    <div class="wrap">
        <h1><?php echo CCL_MENU_TITLE;?></h1>
        <h2>Enable and Disable Customizations</h2>
        <form method="post" action="">
            <?php wp_nonce_field('ccl_nonce'); ?>
            <h3>Customizations</h3>
            <ul>
                <?php
                // Generate the options
                foreach ($ccl_options as $option) {
                    if ( $debug == 1 ) { echo $option.'/'.$$option .'/'.$ccl_options_default[$option]; }
                    echo '<li><label><input type="checkbox" name="' . $option . '" ' . checked($$option, 'on', false) . ' />' . $ccl_options_desc[$option] . '</label></li>';                    
                    // Print Value if $ccl_<option_name>_value is set                                        
                    if ( isset( $ccl_options_value_default[$option]) ) {                        
                        if ( $debug == 1 ) { echo $option .'/'. ${option.'_value'}.'/'.$ccl_options_value_default[$option]; }
                        
                        echo '<li><label for="' . $option . '_value">' . $ccl_options_value_desc[$option] . ' Value:</label><input type="number" id="' . $option . '_value" name="' . $option . '_value" value="' . ${option . '_value'} . '" min="0" step="1"' . disabled($$option,'off') . '>';
                        echo 'Default Value: '.$ccl_options_value_default[$option];
                        echo '</li>';
                    }
                }
                ?>
            </ul>
            <?php submit_button('Save Changes', 'primary', 'ccl_submit'); ?>
        </form>
        <div class="ccl-documentation">
        <?php echo $ccl_documentation;?>    
        </div>
        <div class="data-area">
        <?php $ccl_data_area; ?>
        <div>
    </div>

    <?php
}

// ****************************************************************************************************
function ccl_create_option ($option_name, $option_default, $option_desc ) {
    global $ccl_options, $ccl_options_default, $ccl_options_desc;
    $ccl_options[] = $option_name;    
    $ccl_options_default[$option_name] = $option_default;
    $ccl_options_desc[$option_name] = $option_desc;    
}

function ccl_create_option_value ($option_name, $option_value_default, $option_value_desc ) {
    global $ccl_options_value_default,$ccl_options_value_desc;    
    $ccl_options_value_default[$option_name] = $option_value_default;
    $ccl_options_value_desc[$option_name] = $option_value_desc;
}

