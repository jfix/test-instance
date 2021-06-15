<?php
    /* $Id$ */
define('YOURLS_API', true);
require_once( '../yourls/includes/load-yourls.php' );
yourls_maybe_require_auth();

$action = ( isset( $_REQUEST['action'] ) ? $_REQUEST['action'] : null );

function yourls_api_shorturl_batch($format, $urls)
{
    $long_urls = preg_split("/\s+/", trim($urls), -1, PREG_SPLIT_NO_EMPTY);
    $ret = array();
    $return = "";

    $header = "Content-type: ";
    switch ( $format ) {
        case "json":
            $header .= "application/json";
            foreach ($long_urls as $longurl) {
                $longurl = trim($longurl);
                if (parse_url($longurl)) {
                    $link = yourls_add_new_link( $longurl );
                    $ret[] = array("shorturl" => $link["shorturl"], "longurl" => $longurl, "title" => $link["url"]["title"]);
                }
            }
            $return = json_encode($ret);
            break;
        case "xml":
            $header .= "text/xml";
            $xml = simplexml_load_string("<?xml version='1.0' encoding='utf-8'?><shortlinks/>");
            foreach ($long_urls as $longurl) {
                $longurl = trim($longurl);
                if (parse_url($longurl)) {
                    $link = yourls_add_new_link( $longurl );
                    $xmllink = $xml->addChild("shortlink");
                    $xmllink->addChild('short', htmlentities($link["shorturl"]));
                    $xmllink->addChild('long', htmlentities($longurl));
                    $xmllink->addChild('title', htmlentities($link["url"]["title"]));
                }
            }
            $return = $xml->asXML();
            break;
        case "csv":
        default:
            $header .= "text/csv";
            $return .= "shorturl\tlongurl\ttitle\n";
            foreach ($long_urls as $longurl) {
                $longurl = trim($longurl);
                if (parse_url($longurl)) {
                    $link = yourls_add_new_link( $longurl );
                    $return .= sprintf("'%s'\t'%s'\t'%s'\n", 
                            addslashes($link["shorturl"]),
                            addslashes($longurl),
                            addslashes($link["url"]["title"]));
                }
            }
            break;
    }
    header($header);
    header("Content-Disposition: attachment; filename=\"shortlinks.$format\"");
    echo $return;
    die();
}


$format = ( isset( $_REQUEST['format'] ) ? $_REQUEST['format'] : 'csv' );
$urls = ( isset( $_REQUEST['urls'] ) ? $_REQUEST['urls'] : '' );
yourls_api_shorturl_batch($format, $urls);
die();

