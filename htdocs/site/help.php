<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<!-- $Id: batch.php 73 2011-02-14 01:05:36Z Fix_J $ -->
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="description" content="OECD URL Shortener at oe.cd is used to create short URLs of OECD websites that can be easily shared, tweeted, or emailed to colleagues and friends."/>
        <title>OECD URL Shortener - get help</title>
        <link rel="canonical" href="/"/>
        <link rel="icon" type="image/x-icon" href="/site/css/favicon.ico"/>
        <link rel="stylesheet" href="/site/css/oecd.css" type="text/css"/>
    </head>

    <!-- all the layout and styling is shamefully copied from goo.gl -->

    <body class="product" id="body">

        <div id="page">

            <a href="/" class="title"><img id="logo" src="/site/img/logo.gif" alt="OECD URL Shortener"></a>
		<div id="contents">
			<h3>Why a branded URL shortener?</h3>
			<p>The OECD's URL shortener at oe.cd can be used to create short URLs of OECD websites that can be <span class="post-it">easily shared, tweeted, or emailed to colleagues and friends</span>.</p>
			<p>Obviously, there is a <span class="post-it">branding</span> angle to it, but at the same time we can guarantee that our short URLs will only ever go to OECD-related websites. Which should be reassuring in today's security-crazy world.</p>

			<h3>Features</h3>

			<ul>
				<li>Shorten a long URL, either to a very short sequential identifier, or to a <span class="post-it">keyword you have chosen</span></li>
				<li>Link to <span class="post-it">OECD-related websites only, guaranteed</span> (yes, this is a feature, not a bug)</li>
				<li>Peruse statistics page with <span class="post-it">basic analytics information</span> (number of clicks over time, origin, browsers, platforms)</li>
				<li><span class="post-it">Search for existing long or short URLs</span> (although we allow duplicates and show them in the stats)</li>
				<li>View <span class="post-it">different time frames</span> (2 hours, daily, weekly, monthly, all time)</li>
				<li>Batch shorten many long links (available internally only)</li>
				<li>An API to remote-control it (via Tweetdeck, for example)</li>
			</ul>

			<h3>Stuff to come</h3>

			<p>There are many other things that one could do with this tool. Do you have an idea? Let us know. Here are couple of our ideas:</p>
			<ul>
				<li>Provide user-specific accounts</li>
				<li>Introduce a concept of private and public short URLs (closely linked to the user accounts feature)</li>
				<li>Integrate with Google Analytics, to make the short URL click histories available in the Google Analytics dashboard.</li>
			</ul>

			<h3>The design looks familiar ... hmmm</h3>
			<p>Admittedly, the design has been very strongly inspired by a previous version of <a href="http://goo.gl/">Google's URL shortener</a>.</p>
		</div>
            <p id="footer">
                <span>&copy; OECD</span>
                <a href="/site/help">About</a>
                <?php
                // Only the IP addresses in YOURLS configuration can access the API and Batch pages
                require_once( './functions.php' );
                if ( is_ip_in_whitelist( $whitelisted_ips ) ) {
                ?>
                    <a href="/site/api">API</a>
                    <a href="/site/batch">Batch</a>
                <?php
                }
                ?>
                <!--    <a href="/site/terms.html">Terms of Service</a> -->
                <a href="http://yourls.org">Powered by Yourls</a>
                <a href="http://www.oecd.org/">OECD home</a>
            </p>
        </div>
    </body>
</html>
