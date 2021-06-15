<?php
// Only the IP addresses in YOURLS configuration can access the API and Batch pages
require_once( './functions.php' );
redirect_to_home_if_not_in_whitelist( $whitelisted_ips, 'batch' );
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<!-- $Id$ -->
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="description" content="OECD URL Shortener at oe.cd is used to create short URLs of OECD websites that can be easily shared, tweeted, or emailed to colleagues and friends."/>
        <title>OECD URL Shortener - batch loader</title>
        <link rel="canonical" href="/"/>
        <link rel="icon" type="image/x-icon" href="/site/css/favicon.ico"/>
        <link rel="stylesheet" href="/site/css/oecd.css" type="text/css"/>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js"><!-- // --></script>
		<!--	<script type="text/javascript" src="http://ajax.microsoft.com/ajax/jquery.templates/beta1/jquery.tmpl.js"> </script>	-->
        <script type="text/javascript" src="/site/js/jquery.tmpl.js"><!-- // --></script>
		<!--	<script type="text/javascript" src="/site/jquery.tablesorter.js">//</script>	-->
        <script type="text/javascript" src="/site/js/batch.js"><!-- // --></script>
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
            <script type="text/javascript">document.getElementById("message_container").style.visibility = "hidden";</script>

            <form id="batch_form" action="batch_results.php" method="post">

                <div id="batch_container">
                        <div id="batch_options">
                            <label for="shorten">Choose your preferred format:</label>
                            <input type="radio" id="csv" value="csv" name="format" checked=""/><label for="csv">CSV</label>
                            <input type="radio" id="xml" value="xml" name="format"/><label for="xml">XML</label>
                            <input type="radio" id="json" value="json" name="format"/><label for="json">JSON</label>

                            <div id="batch_button_container">
                                <img id="shorten_pending" src="/site/img/spin_24_e5ecf9.gif" alt="">
                                <button id="batch_button" type="submit">
                                    <p>Shorten many</p>
                                </button>
                            </div>
                        </div>
                        <!--    <div id="shorten_line">
                            <input type="text" id="shorten" name="url" tabindex="1">
                            <img id="shorten_pending" src="/site/img/spin_24_e5ecf9.gif" alt="">
                        </div>  -->

                        <p class="info">Only OECD-related URLs can be shortened on this website. One long URL per line please.</p>
                </div>


                <div id="batcharea_container">

                    <textarea id="urls" name="urls" rows="15" cols="100" tabindex="1">
http://www.oecd.org/
http://www.oecd-ilibrary.org/
http://www.oecdcode.org/</textarea>

                </div>
            </form>

            <p id="footer">
                <span>&copy; OECD</span>
                <a href="/site/help">Help</a>
                <a href="/site/api">API</a>
                <a href="/site/batch">Batch</a>
                <!--    <a href="/site/terms.html">Terms of Service</a> -->
                <a href="http://yourls.org">Powered by Yourls</a>
                <a href="http://www.oecd.org/">OECD home</a>
            </p>
        </div>
    </body>
</html>
