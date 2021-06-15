<?php
/*
Plugin Name: Redirect to my own stats page for a given shortlink
Plugin URI: http://no.url.yet
Description: redirect a stat request to my own stats page rather than the default one
Version: 1.0
Author: Jakob
Author URI: http://jfix.com/
*/

// $Id$

define('YOURLS_INFOS', true);
include_once( YOURLS_INC . '/load-yourls.php' );
require_once( YOURLS_INC . '/functions.php' );
require_once( YOURLS_INC . '/functions-infos.php' );

yourls_add_action('load_template_infos', 'my_own_stats');

// Return the number of clicks for a given longurl
function yourls_get_longurl_stats( $longurl ) {
    global $ydb;
    $table_url = YOURLS_DB_TABLE_URL;
    $query = "SELECT SUM(clicks) as clicks FROM `$table_url` WHERE url = '".$ydb->escape($longurl)."';";
    $res = $ydb->get_results($query);
    $return = -1;

    if ($res) {
        $return = $res[0]->clicks;
    }
    return $return;
}



// Generate an HTML table that's being used several times
function plugin_stats_table( $id ) {
    $label;
    switch ($id) {
        case "referrers": $label = "Referrers"; break;
        case "countries": $label = "Countries"; break;
        case "browsers": $label = "Browsers"; break;
        case "platforms": $label = "Platforms"; break;
    }
    echo <<<E
  <div class="stats">
        <table id="$id">
            <tbody>
                <tr class="first">
                    <th style="width:auto">$label</th>
                    <th style="width:4.2em"></th>
                </tr>
                <tr>
                    <!-- TODO: add real stats here, based on $id -->
                    <td class="first" colspan="2">No data available.</td>
                </tr>
            </tbody>
        </table>
    </div>
E;
}

function my_own_stats( $args ) {
    $site = YOURLS_SITE;
    $table = YOURLS_DB_TABLE_LOG;
    
    $short = yourls_sanitize_string(  $args[0] );
    $longurl = yourls_get_keyword_longurl( $short );
    $longurl_noproto = rtrim(preg_replace("/^https?:\/\//", '', $longurl), '/');
    $longurl_wbr = preg_replace("/\//", "<wbr>/", $longurl_noproto);
    $clicks = yourls_get_keyword_clicks( $short );
    $longurl_clicks = yourls_get_longurl_stats($longurl);
    $click_word = ($clicks == 1 ? "click" : "clicks");
    $timestamp = yourls_get_keyword_timestamp( $short );
    $timestamp_display = date("j M Y", strtotime($timestamp));

    $shorturl = $site . "/" . $short;
    $shorturl_noproto = preg_replace("/^https?:\/\//", '', $shorturl);
  
    echo<<<E1

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="description" content="OECD URL Shortener at oe.cd is used to create short URLs of OECD websites that can be easily shared, tweeted, or emailed to colleagues and friends."/>
        <title>$shorturl_noproto Details - OECD URL Shortener</title>
        <link rel="canonical" href="/"/>
        <link rel="icon" type="image/x-icon" href="/site/css/favicon.ico"/>
        <link rel="stylesheet" href="/site/css/oecd.css" type="text/css"/>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.min.js"><!-- // --></script>
        <script type="text/javascript" src="https://www.google.com/jsapi"></script>
        <script type="text/javascript">
            google.load("visualization", "1", {packages:["corechart"]});
        </script>
        <script type="text/javascript" src="/site/js/detail_page.js"><!-- // --></script>
        <script type="text/javascript" src="/site/js/date.js"><!-- // --></script>
        <script type="text/javascript" src="/site/js/message.js"><!-- // --></script>
    </head>

    <!-- all the layout and styling is shamefully copied from goo.gl -->

<body class="product" id="body">

    <div id="page">

        <a href="/" class="title">
            <img id="logo" src="/site/img/logo.gif" alt="OECD URL Shortener">
        </a>

        <div id="message_container" style="visibility: hidden; ">
            <span id="message_bar">
                <span id="message"></span>
                <a id="message_dismiss" href="#" style="display: inline; ">x</a>
            </span>
        </div>

        <!-- the QR code -->
        <a title="click for bigger version" class="qr" href="$shorturl.qr"><img id="qr" src="http://chart.apis.google.com/chart?cht=qr&chs=100x100&choe=UTF-8&chld=H|0&chl=$shorturl" alt="QR code">
        <p id="qr-source">$shorturl.qr</p></a>

        <!-- attributes section -->

        <div id="attributes">
            <p><span class="label">Long URL:</span>
            <a id="long_url" class="info" href="$longurl" title="$longurl_noproto">$longurl_wbr</a>
            <p><span class="label">Short URL:</span>
            <a class="info" href="$shorturl" title="$shorturl_noproto">$shorturl_noproto</a></p>
            <p><span class="label">Created:</span>
            <span id="createDate">$timestamp_display</span> 
        </div>


        <!-- more details -->

        <div id="details_body" style="display: inline;">
            <div id="resolutions" class="lazy heading" style="visibility: visible; display: block; ">
                <p>Click statistics:</p>
                <input type="hidden" name="current_resolution" id="current_resolution" value="4">
                <ul>
                    <li class="first">
                        <a href="#" id="two_hours" onclick="this.blur(); urls.details_body.load(0, '$short', this); return false;">Two hours</a></li>
                    <li><a href="#" id="day" onclick="this.blur(); urls.details_body.load(1, '$short', this); return false;">Day</a></li>
                    <li><a href="#" id="week" onclick="this.blur(); urls.details_body.load(2, '$short', this); return false;">Week</a></li>
                    <li><a href="#" id="month" onclick="this.blur(); urls.details_body.load(3, '$short', this); return false;">Month</a></li>
                    <li><a href="#" id="all_time" onclick="this.blur(); urls.details_body.load(4, '$short', this); return false;" class="selected">All&nbsp;time</a></li>
                </ul>
            </div>

            <h2>Clicks</h2>
            <div id="clicks">
                <p class="yours">
                    <span id="short_clicks">$clicks</span> $click_word on this short URL</p>
                <p class="even">
                    <span id="long_clicks">$longurl_clicks</span> total clicks on all oe.cd short URLs pointing to this long URL</p>
            <div class="stats" style="float:right;margin-top:-50px;">    
                <table id="shorturls">
                <tbody>
                    <tr class="first">
                        <th style="width:auto">Short URLs</th>
                        <th style="width:4.2em"></th>
                    </tr>
                    <tr>
                        <!-- TODO: add real stats here, based on $id -->
                        <td class="first" colspan="2">No data available.</td>
                    </tr>
                </tbody>
                </table>
            </div>
            </div>

            <h2>Traffic history</h2>

            <div id="chart">
            <div id="clicks_chart">
            <table class="annotatedtimelinetable" border="0" cellpadding="0" cellspacing="0">
            <tbody>
            <tr valign="top"><td>
            <div id="chartDiv1" style="width: 420px; ">
            </div>
            </td>
            <td>
            <div id="annotationsDiv1" class="annotationsdiv" style="height: 217px; width: 0px; "></div>
            </td>
            </tr>
            </tbody>
            </table>
            </div>
            </div>
E1;
    
    plugin_stats_table("referrers");
    echo '<h2>Visitor profile</h2><div id="profile">';
    plugin_stats_table("countries");
    plugin_stats_table("browsers");
    plugin_stats_table("platforms");
    echo '</div>';

    echo <<<THE_END
        </div>
        <p id="footer"><span>&copy; OECD</span>
            <a href="/site/help.html">Help</a>
            <a href="/site/api.html">API</a>
			<!--	<a href="/site/terms.html">Terms of Service</a>	-->
            <a href="http://yourls.org">Powered by Yourls</a>
            <a href="http://www.oecd.org/">OECD home</a>
        </p>
    </div>
</body>
</html>
THE_END;
    exit;
}


?>