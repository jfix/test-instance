<?php

require_once __DIR__.'/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__.'/../../../');
$dotenv->load();
$dotenv->required(['DB_USER', 'DB_PASS', 'DB_NAME', 'DB_HOST', 'DB_PREFIX', 
'SITE', 'COOKIEKEY', 'FLOOD_IP_WHITELIST', 
'ADMIN_USER', 'ADMIN_PASS', 'ADMIN_IPS']);

/*
 * $Id$
 *
 *
 * simply delete everything but "/user", and upload the new version.
 */


/*
 ** MySQL settings - You can get this info from your web host
 */

/** MySQL database username */
define( 'YOURLS_DB_USER', $_ENV['DB_USER'] );

/** MySQL database password */
define( 'YOURLS_DB_PASS', $_ENV['DB_PASS'] );

/** The name of the database for YOURLS */
define( 'YOURLS_DB_NAME', $_ENV['DB_NAME'] );

/** MySQL hostname */
define( 'YOURLS_DB_HOST', $_ENV['DB_HOST'] );

/** MySQL tables prefix */
define( 'YOURLS_DB_PREFIX', $_ENV['DB_PREFIX'] );

/*
 ** Site options
 */

/** YOURLS installation URL, no trailing slash */
define( 'YOURLS_SITE', $_ENV['SITE'] );

/** Timezone GMT offset */
define( 'YOURLS_HOURS_OFFSET', 1 );

/** Allow multiple short URLs for a same long URL
 ** Set to true to have only one pair of shortURL/longURL (default YOURLS behavior)
 ** Set to false to allow multiple short URLs pointing to the same long URL (bit.ly behavior) */
define( 'YOURLS_UNIQUE_URLS', false );

/** Private means protected with login/pass as defined below. Set to false for public usage. */
define( 'YOURLS_PRIVATE', true );

define('YOURLS_PRIVATE_INFOS', true);

define('YOURLS_PRIVATE_API', false);

/** A random secret hash used to encrypt cookies. You don't have to remember it, make it long and complicated. Hint: copy from http://yourls.org/cookie **/
define('YOURLS_COOKIEKEY', $_ENV['COOKIEKEY']);

/** should be more in real life, default: 15 */
define('YOURLS_FLOOD_DELAY_SECONDS', 15 );

/** comma-separated list of IP addresses that are not affected by the throttling mechanism */
define('YOURLS_FLOOD_IP_WHITELIST', $_ENV['FLOOD_IP_WHITELIST']);

/**  Username(s) and password(s) allowed to access the site */
$yourls_user_passwords = array(
	$_ENV['ADMIN_USER'] => $_ENV['ADMIN_PASS'] 
);
define( 'YOURLS_NO_HASH_PASSWORD', true );

/** Debug mode to output some internal information
 ** Default is false for live site. Enable when coding or before submitting a new issue */
define( 'YOURLS_DEBUG', false );

/*
 ** URL Shortening settings
 */

/** URL shortening method: 36 or 62 */
define( 'YOURLS_URL_CONVERT', 62 );
/*
 * 36: generates case insentitive lowercase keywords (ie: 13jkm)
 * 62: generate case sensitive keywords (ie: 13jKm or 13JKm)
 * Stick to one setting, don't change after you've created links as it will change all your short URLs!
 * Base 36 should be picked. Use 62 only if you understand what it implies.
 */

/**
* Reserved keywords (so that generated URLs won't match them)
* Define here negative, unwanted or potentially misleading keywords.
*/
$yourls_reserved_URL = array(
	'porn', 'faggot', 'sex', 'nigger', 'fuck', 'cunt', 'dick', 'gay', 'suck', 'SUCK', 'FUCK', 'DICK'
);

/*
 ** Personal settings would go after here.
 */

/**
 *
 * Only accept long urls for these domains
 * TODO: any subdomain of these domains should also be accepted.
 *
 */
$whitelisted_domains = array(
    'oecd.org',
    'oecdcode.org',
    'oecd-ilibrary.org',
    'oecdobserver.org',
    'observateurocde.org',
    'oecdinsights.org',
    'oecdfactblog.org',
    'africaneconomicoutlook.org',
    'internationaltransportforum.org',
    'oecdbookshop.org',
    'oecdwash.org',
    'oecdtokyo.org',
    'eoi-tax.org',
    'oecdbetterlifeindex.org',
    'oecd-nea.org',
    'activecharts.org',
    'oecdcharts.org',
    'keepeek.com',
    'gitvfd.github.io',
    'fatf-gafi.org',
    'oecd360.org',
    'oecd.taleo.net',
    'itf-oecd.org',
    'innovationpolicyplatform.org',
    'oecd-development-matters.org',
    'oecdecoscope.wordpress.com',
    'aopkb.org',
    'oecd-opsi.org',
    'compareyourcountry.org',
    'oecdskillsandwork.wordpress.com',
    'oecdeducationtoday.blogspot.fr',
    'oecd-inclusive.com',
    'dearchiver.herokuapp.com',
    'dearchiver.azurewebsites.net',
    'oecd-forum.org',
    'oecd-forum.org',
    'stat.link',
	'betterentrepreneurship.eu',
	'oecd.github.io',
    'oecdtv.webtv-solution.com',
	'cities-innovation-oecd.com',
    'oecd.dam-broadcast.com'
);

/**
 * Only Those IP addresses are allowed to access the API and Batch pages
 * Otherwhise the users will be redirected to the home page
 * Array of IP strings with or without netmasks (IP/CIDR format)
 */
$whitelisted_ips = explode($_ENV['AUTH_IPS'], ",");

/**
 * Namespaces
 * The namespace chosen for a long URL will be the most precise for an entered URL (case of overlapping subdomains)
 * If a namespace links to more than one domain, enter them as an array of domains
 * Please enter all domains in lowercase and decode entities ("http%3a%2f%2f" in "http://")
 * You can enter a REGEX wildcard ".*" for any number of characters if needed (all other REGEX patterns will be escaped)
 * Array of namespaces array( $namespace => array( 'display_name', 'domain' )
 */
$namespaces = array (
    'sl'  => array (
        'display_name' => 'StatLinks',
        'domain' => array( 'statlinks.oecdcode.org', 'stat.link' ) ),
    'il'  => array (
        'display_name' => 'iLibrary',
        'domain' => 'oecd-ilibrary.org' ),
    'fp'  => array (
        'display_name' => 'FreePreview',
        'domain' => array( 'keepeek.com', 'oecd-ilibrary.org/deliver/fulltext.*redirecturl=http://www.keepeek.com' ) ),
    'ds'  => array (
        'display_name' => 'DotStat',
        'domain' => 'stats.oecd.org' ),
    'dp'  => array (
        'display_name' => 'Data Portal',
        'domain' => 'data.oecd.org' ),
    'obs' => array (
        'display_name' => 'Observer',
        'domain' => array( 'oecdobserver.org', 'observateurocde.org' ) ),
    'pub' => array (
        'display_name' => 'PubTemplate',
        'domain' => 'oecd.org/publications' )
);
