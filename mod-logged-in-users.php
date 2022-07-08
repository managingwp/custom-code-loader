<?php
/**
 * User Sessions Widget
 */
ccl_create_option('ccl_user_sessions', 'off', 'WordPress User Sessions Widget');
if ( get_site_option('ccl_user_sessions', 'off') == "on" ) {   
    new Logged_In_Users_Widget();
    $user_sessions = new Logged_In_Users_Widget();
//    $ccl_data_area .= "<div class=\"logged-in-users\">".$user_sessions->widget_content()."</div>";
}

class Logged_In_Users_Widget {

    public function __construct() {
        if (is_multisite()) {
            add_action('wp_network_dashboard_setup', array($this, 'add_network_dashboard_widget'));
        } else {
            add_action('wp_dashboard_setup', array($this, 'add_dashboard_widget'));
        }
    }

    public function add_network_dashboard_widget() {
        wp_add_dashboard_widget('logged_in_users_widget', 'Logged-In Users', array($this, 'widget_content'), null, 'top');
    }

    public function add_dashboard_widget() {
        wp_add_dashboard_widget('logged_in_users_widget', 'Logged-In Users', array($this, 'widget_content'));
    }

    public function widget_content() {
        global $wpdb;
    
        // Retrieve all users with session_tokens
        $user_ids_with_session_tokens = $wpdb->get_col(
            "SELECT DISTINCT user_id FROM {$wpdb->usermeta} WHERE meta_key = 'session_tokens'"
        );

        // Prepare the header for the table
        echo '<table>';
        echo '<tr><th>User ID</th><th>Username</th><th>Email</th><th>Role/Capability</th><th>Session Expiration</th></tr>';

        // Categorize users by role and capability
        $logged_in_users_by_role = array();
        $users_with_capabilities = array();

        foreach ($user_ids_with_session_tokens as $user_id) {
            $user = get_userdata($user_id);

            if ($user) {                                
                // Get session expiration time
                $session_tokens = get_user_meta($user_id, 'session_tokens', true);
                $session_expiration = '';
                if ($session_tokens) {
                    $latest_session = end($session_tokens);
                    $session_expiration_timestamp = $latest_session['expiration'];
                    $session_expiration = date('Y-m-d H:i:s', $session_expiration_timestamp);
                }

                if (!empty($user->roles)) {
                    foreach ($user->roles as $role) {
                        if (!isset($logged_in_users_by_role[$role])) {
                            $logged_in_users_by_role[$role] = array();
                        }
                        $logged_in_users_by_role[$role][] = array(
                            'user_id' => $user->ID,
                            'username' => $user->user_login,
                            'email' => $user->user_email,
                            'role' => $user->roles,
                            'session_expiration' => $session_expiration,
                        );
                    }
                } else {
                    // If user has no roles, store their capabilities
                    $capabilities = $user->allcaps;
                    foreach ($capabilities as $capability => $value) {
                        if (!isset($users_with_capabilities[$capability])) {
                            $users_with_capabilities[$capability] = array();
                        }
                        $users_with_capabilities[$capability][] = array(
                            'user_id' => $user->ID,
                            'username' => $user->user_login,
                            'email' => $user->user_email,
                            'capability' => $capability,
                            'session_expiration' => $session_expiration,
                        );
                    }
                }
            }
        }

        // Combine and sort all user data
        $all_users_data = array();

        foreach ($logged_in_users_by_role as $users) {
            $all_users_data = array_merge($all_users_data, $users);
        }

        foreach ($users_with_capabilities as $users) {
            $all_users_data = array_merge($all_users_data, $users);
        }

        // Sort the users by session expiration time
        usort($all_users_data, function ($a, $b) {
            return strtotime($a['session_expiration']) - strtotime($b['session_expiration']);
        });

        // Print the sorted table
        foreach ($all_users_data as $user) {

            echo '<tr>';
            echo '<td>' . $user['user_id'] . '</td>';
            echo '<td>' . $user['username'] . '</td>';
            echo '<td>' . $user['email'] . '</td>';
            echo '<td>' . (isset($user['role']) ? implode(', ', $user['role']) : $user['capability']) . '</td>';
            echo '<td>' . $user['session_expiration'] . '</td>';
            echo '<td>1</td>';
            echo '</tr>';
        }

        echo '</table>';


        // Collapsible area for user data
        echo '<br><button class="collapsible">Show User Data</button>';
        echo '<div class="collapsible-content" style="display: none;">';
        echo '<pre>';
        foreach ($user_ids_with_session_tokens as $user_id) {
            $user = get_userdata($user_id);
            if ($user) {
                print_r($user);
                echo '<br>';
            }
        }
        echo '</pre>';
        echo '</div>';

        // JavaScript for collapsible functionality
        echo '<script>';
        echo 'var coll = document.getElementsByClassName("collapsible");';
        echo 'for (var i = 0; i < coll.length; i++) {';
        echo '    coll[i].addEventListener("click", function() {';
        echo '        this.classList.toggle("active");';
        echo '        var content = this.nextElementSibling;';
        echo '        if (content.style.display === "block") {';
        echo '            content.style.display = "none";';
        echo '        } else {';
        echo '            content.style.display = "block";';
        echo '        }';
        echo '    });';
        echo '}';
        echo '</script>';
    }  
}
