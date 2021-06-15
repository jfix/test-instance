<?php
/*
Plugin Name: Check for sites
Plugin URI: http://oe.cd/whitelist
Description: Allows you to restrict the sites for which you accept short urls. (initial list of domains is in the config.php file
Version: 1.0
Author: Jakob
Author URI: http://jfix.com/
*/

// $Id$

// find list of whitelisted domains in the config.php file

/**
 * see config.php where the global array of whitelisted domains is defined.
 */

yourls_add_action('pre_add_new_link', 'check_for_whitelisted_sites');
function check_for_whitelisted_sites( $args ) {
    global $whitelisted_domains;
    $url = $args[0];

    // full host name, could be statlinks.oecdcode.org
    $host = parse_url($url, PHP_URL_HOST ); /* returns a string */

    // let's get just the fqdn w/o subdomain
    $domain = implode(".", array_slice( explode(".", $host), -2, 2) );
    
    if ( !in_array( $host, $whitelisted_domains ) && !in_array( $domain, $whitelisted_domains ) ) {
        $string_of_whitelisted_domains = implode(', ', $whitelisted_domains);
        $site = YOURLS_SITE;
        
        header("HTTP/1.0 406 Not acceptable");
        echo "This URL doesn't seem to belong to an OECD-related website. <a href=''>Get in touch</a> if this is wrong.";       
        
        die();
    }
}
?>
