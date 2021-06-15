<?php
// Only the IP addresses in YOURLS configuration can access the API and Batch pages
require_once( './site/functions.php' );
// If not in whitelisted IPs, session_start() at the beginning to prevent E_WARNINGs
// Used to display the rejection message below if needed
if ( !is_ip_in_whitelist( $whitelisted_ips ) ) {
    session_start();
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
        <meta name="description" content="OECD URL Shortener at oe.cd is used to create short URLs of OECD websites that can be easily shared, tweeted, or emailed to colleagues and friends."/>
        <title>OECD URL Shortener</title>
        <link rel="canonical" href="/"/>
        <link rel="icon" type="image/x-icon" href="/site/css/favicon.ico"/>
        <link rel="stylesheet" href="/site/css/oecd.css" type="text/css"/>
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.4/jquery.js"></script>
        <script type="text/javascript" src="/site/js/jquery.tmpl.js"><!-- // --></script>
        <script type="text/javascript" src="/site/js/oecd.js"><!-- // --></script>
        <script type="text/javascript" src="/site/js/message.js"><!-- // --></script>
        <script type="text/javascript" src="/site/js/date.js"><!-- // --></script>
        <script type="text/javascript" src="/site/js/jquery.sparkline.js"><!-- // --></script>
    </head>

    <!-- all the layout and styling is shamefully copied from goo.gl -->

    <body class="product" id="body">
        <div id="page">

            <a href="/" class="title"><img id="logo" src="/site/img/logo.gif" alt="OECD URL Shortener"></a>

            <div id="message_container" style="visibility: hidden;">
                <span id="message_bar">
                    <span id="message"></span>
                    <a id="message_dismiss" href="#" style="display: inline; ">x</a>
                </span>
            </div>
            <script type="text/javascript">document.getElementById("message_container").style.visibility = "hidden";</script>

            <div id="shorten_container">
                <form id="shorten_form" action="/" method="post">
                    <div>
                        <label for="shorten">Paste your long URL here:</label>
                    </div>
                    <div id="shorten_line">
                        <input type="text" id="shorten" name="url" tabindex="1">
                        <input type="text" id="result" value="https://oe.cd/" readonly="">
                        <input type="text" id="keyword" name="keyword" tabindex="2" placeholder="...">
                        <button tabindex="3" id="shorten_button" type="submit">
                            <p>Shorten</p>
                        </button>
                        <img id="shorten_pending" src="/site/img/spin_24_e5ecf9.gif" alt="">
                    </div>

                    <p class="info">Only OECD-related URLs can be shortened on this website.</p>
                </form>
            </div>

            <div id="resolutions" class="lazy heading" style="visibility: visible; display: block; ">
                <div id="search_container">
                    <form id="search_form" action="/" method="post">
                    Search query:<br />
                        <input type="text" id="search"/>
                        <button id="search_button" type="submit"><p>Search</p></button>
                    </form>
                </div>

                <input type="hidden" name="current_resolution" id="current_resolution" value="4">
                <div id="resolution_filter">
                    Click statistics:<br />
                    <div id="resolution_buttons">
                        <button type="button" onclick="urls.history.links=[];urls.history.load(0,4,this,$('#search').val()); return false;" class="button button-selected" id="all_time">All time</button>
                        <button type="button" onclick="urls.history.links=[];urls.history.load(0,3,this,$('#search').val()); return false;" class="button" id="month">Month</button>
                        <button type="button" onclick="urls.history.links=[];urls.history.load(0,2,this,$('#search').val()); return false;" class="button" id="week">Week</button>
                        <button type="button" onclick="urls.history.links=[];urls.history.load(0,1,this,$('#search').val()); return false;" class="button" id="day">Day</button>
                        <button type="button" onclick="urls.history.links=[];urls.history.load(0,0,this,$('#search').val()); return false;" class="button" id="two_hours">Two&nbsp;hours</button>
                    </div>
                </div>

                <input type="hidden" name="current_namespace" id="current_namespace" value="get_all">
                <div id="namespace_filter">
                Restrict search results to:<br />
                    <?php
                    // Display the generated namespaces filter
                    echo build_filter_list ( $namespaces );
                    ?>
                </div>
            </div>

            <input type="hidden" name="current_page" id="current_page" value="1">

            <div id="history_container" style="float:left;">
                <table id="history">
                    <thead>
                        <tr class="heading">
                            <th class="first" style="width:18.5em">Long URL</th>
                            <th style="width:9em">Short URL</th>
                            <th style="width:7.5em">Created</th>
                            <th class="clicks" style="width:4.2em">Clicks</th>
                            <th style="width:60px"></th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr id="history_pending">
                            <td colspan="6">
                                <img id="pagination_pending" src="/site/img/spin_16_e5ecf9.gif" alt=""> Loading history...
                            </td>
                        </tr>
                        <tr id="no_data">
                            <td colspan="6">
                                 No data available, please check your search parameters...
                            </td>
                        </tr>
                    </tbody>
                </table>

                <div id="pagination">
                    <span class="info">Page
                        <span id="current_page_displayed" class="page">1</span>  of
                        <span id="total_pages" class="page">1</span>

                        <a id="newer" href="#">‹ Newer</a>
                        <a id="older" href="#">Older ›</a>
                    </span>
                </div>
            </div>

            <p id="footer">
                <span>&copy; OECD</span>
                <a href="/site/help">About</a>       
	            <?php
	            if ( is_ip_in_whitelist( $whitelisted_ips ) ) {
	            ?>
                    <a href="/site/api">API</a>
                    <a href="/site/batch">Batch</a>
                <?php
	            }
                ?>
                <!--	<a href="/site/terms.html">Terms of Service</a>    -->
                <a href="http://yourls.org">Powered by Yourls</a>
                <a href="http://www.oecd.org/">OECD home</a>
            </p>

            <?php
            // Display message if redirect because of direct access to the API or batch while not allowed
            if ( !is_ip_in_whitelist( $whitelisted_ips ) && isset( $_SESSION['USER_NOT_ALLOWED'] ) && $_SESSION['USER_NOT_ALLOWED'] ) {
                ?>
                <script type="text/javascript">
                $(function () {
                    urls.message.show("You are not allowed to access the <?php echo $_SESSION['PAGE_FROM']; ?>. <a href=''>Get in touch</a> if this is wrong.", "error");
                    $("#message_container").delay(2000).fadeTo(3000, 0);
                });
                </script>
                <?php
                // Prevent multiple messages if refresh
                $_SESSION = array();
            }
            ?>

        </div>
    </body>
</html>
