<?php
/**
 * MGI Viveris : file containing some functions used in the frontend only (not part of YOURLS)
 * This file uses the configuration of YOURLS in "/yourls/user/config.php" to have all the settings in one place
 */

// Inclusion of the config file, if it fails, dies
require_once( __DIR__.'/../yourls/user/config.php');

/**
 * Checks if the user current IPv4 address is in the whitelisted IPs ($whitelisted_ips)
 * @Param array $ip_array array of whitlisted IPs in IP/CIDR format
 * @Return boolean
 */
function is_ip_in_whitelist( $ip_array ) {
    // If input is not an array, return false
    if ( !is_array( $ip_array) ) {
        return false;
    }
    
    // Get user IP and convert it to long integer for comparison
    $user_ip_decimal = ip2long($_SERVER['REMOTE_ADDR']);
    
    // Checks user IP against whitelist
    foreach ( $ip_array as $ip ) {
        // If no netmask is detected, /32 is assumed
        if ( !strpos($ip, '/', 1) ) {
            $ip .= '/32';
        }
        
        // Separate range and netmask
        list( $range, $netmask ) = explode('/', $ip);
        
        // Convert IPs to long integers
        $range_decimal = ip2long($range);
        
        // Generate a netmask of authorized changing bits using the NOT (~) bitwise operator
        $netmask_decimal = ~ ( 2 ** ( 32 - $netmask ) - 1 );
        
        // If at least one IP is in range, it's OK, else loop
        // Test using the AND (&) bitwise operator to check if the bits in the netmask are unchanged
        if ( ( $user_ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) ) {
            return true;
        }
    }
    
    // If the IP is not in the whitelist, returns false
    return false;
}

/**
 * Redirect to the home page if the user current IP is not in the whitelist. Triggers a custom error message.
 * @Param array $ip_array array of whitlisted IPs in IP/CIDR format
 * @Param string $page_from page name to be displayed in the error message
 */
function redirect_to_home_if_not_in_whitelist( $ip_array, $page_from ) {
    // currently disabled
    return;

    if ( !is_ip_in_whitelist( $ip_array ) ) {
        session_start();
        $_SESSION['USER_NOT_ALLOWED'] = true;
        $_SESSION['PAGE_FROM'] = $page_from;
        header( 'Location: /' );
        die;
    }
}

/**
 * Build the namespace part of the search section
 * @param array $namespaces namespace array
 * @param int $max_before_dropdown number max of namespaces before switching from a single line to a dropdown list
 * @return string HTML code to echo directly
 */
function build_filter_list ( $namespaces, $max_before_dropdown = 10 ) {
    // Case more than X elements (including "All" and "Others") : drop-down
    if (count($namespaces) > $max_before_dropdown - 2) {
        // Menu button
        $html = '<button type="button" class="menu-button" onclick="">';
        $html .= 'All&nbsp;&#9660;'; // Dropdown triangle
        $html .= '</button>';
        
        $html .= '<div id="namespace_dropdown">';
        $html .= '<div id="namespace_dropdown_flex">';
        
        // Add "All" by default at the beginning
        $html .= '<button type="button" class="button button-selected" id="namespace_get_all">';
        $html .= 'All';
        $html .= '</button>';
        
        // Extract useful information in the namespace array
        foreach ( $namespaces as $short_label => $namespace_data ) {
            $html .= '<button type="button" class="button" id="namespace_'.$short_label.'">';
            $html .= str_replace( ' ', '&nbsp;', $namespace_data['display_name'] ); // Prevent line-breaks
            $html .= '</button>';
        }
        
        // Add "Others" at the end
        $html .= '<button type="button" class="button" id="namespace_get_other">';
        $html .= 'Others';
        $html .= '</button>';
        
        $html .= '</div>';
        $html .= '</div>';
    }
    // Case less or equal than X elements (including "All" and "Others") : linear buttons
    else {
        $html = '<div id="namespace_buttons">';
        
        // Add "All" by default at the beginning
        $html .= '<button type="button" class="button button-selected" id="namespace_get_all">';
        $html .= 'All';
        $html .= '</button>';
        
        // Extract useful information in the namespace array
        foreach ( $namespaces as $short_label => $namespace_data ) {
            $html .= '<button type="button" class="button" id="namespace_'.$short_label.'">';
            $html .= str_replace( ' ', '&nbsp;', $namespace_data['display_name'] ); // Prevent line-breaks
            $html .= '</button>';
        }
        
        // Add "Others" at the end
        $html .= '<button type="button" class="button" id="namespace_get_other">';
        $html .= 'Others';
        $html .= '</button>';
        
        $html .= '</div>';
    }
    
    return $html;
}
?>
