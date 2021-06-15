<?php
/*
Plugin Name: OECD Namespaces
Plugin URI: http://no.url.yet
Description: Allow the use of namespaces in order to better differentiate short URLs
Version: 1.0
Author: OECD
Author URI: http://oe.cd
*/

/**
 * Parse an URL and get the namespace associated
 * @param string $url long URL to parse
 * @return string namespace for this URL
 */
function oecd_get_namespace_for_url ( $url ) {
    global $namespaces;
    $protocol = yourls_get_protocol( $url );
    
    // Browse each namespace and extract all those who fit the URL
    $found_namespaces = array();
    foreach ( $namespaces as $label => $data ) {
        $domains = $data['domain'];
        // Transform all domains in array
        if ( !is_array( $domains ) ) {
            $domains = array( $domains );
        }
        
        // Check all domains (and subdomains) for a namespace by regex (only at the beginning of the URL)
        foreach ( $domains as $domain ) {
            $regex = '/^'.preg_quote( $protocol, '/' ).'(?:[^@\/\n]*)'.preg_quote( $domain, '/' ).'/i';
            // Specific replacement to enable REGEX wildcards ".*" in the domain pattern
            $regex = str_replace('\.\*', '.*', $regex);
            
            if ( preg_match( $regex, $url ) ) {
                // Order by position then length to get the most precise match if more than one are found
                $found_namespaces[ strpos($url, $domain) ][ strlen($domain) ] = $label;
            }
        }
    }
    
    // If nothing match, return an empty string
    if ( empty( $found_namespaces ) ) {
        return '';
    }
    
    // If more than one match is returned, get the first match in the URL then the most precise one
    // Using array_pop instrad of array_shift to prevent array reordering (faster)
    krsort( $found_namespaces, SORT_NUMERIC ); // Descending to get the first match in the last position
    $selected_domains = array_pop( $found_namespaces );
    ksort( $selected_domains, SORT_NUMERIC ); // Ascending to get the longest pattern in the last position
    return array_pop( $selected_domains );
}

/**
 * Get all namespaces for string replacement
 * @return array namespaces
 */
function oecd_get_all_namespaces() {
    global $namespaces;
    $return = array_keys( $namespaces );
    
    // Adding slashes to prevent deletion of patterns without slashes
    foreach ( $return as &$string ) {
        $string = $string.'/';
    }
    unset ( $string );
    
    return $return;
}

yourls_add_filter( 'get_shorturl_charset', 'oecd_add_slashes_in_charset' );
/**
 * Allow shashes to be included in short URLs
 * @param string $charset charset used in short url
 * @return string modified charset
 */
function oecd_add_slashes_in_charset( $charset ) {
    return $charset.'/';
}

yourls_add_filter( 'random_keyword', 'oecd_add_namespace' );
yourls_add_filter( 'custom_keyword', 'oecd_add_namespace' );
/**
 * Add namespace to keyword for the short URL for random or custom URLs
 * @param string $keyword short URL keyword (to be used)
 * @param string $url long URL (to be used)
 * @param string $title title of the long URL page
 * @return string keyword with namespace
 */
function oecd_add_namespace ( $keyword, $url, $title ) {
    // Get associated namespace
    $namespace = oecd_get_namespace_for_url( $url );
    $namespaces_to_clean = oecd_get_all_namespaces();
    
    // Clean keyword to get rid of residual namespaces and manually entered slashes 
    $keyword = str_ireplace( $namespaces_to_clean, '', $keyword );
    $keyword = str_replace( '/', '', $keyword );
    
    // If no keyword is found, nothing is added
    if (strlen( $namespace ) == 0) {
        return $keyword;
    }
    
    return $namespace.'/'.$keyword;
}

?>