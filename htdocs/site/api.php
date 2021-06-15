<?php
  /* $Id$ */
  require_once( './functions.php' );
  redirect_to_home_if_not_in_whitelist( $whitelisted_ips, 'API' );
  require_once( '../yourls/yourls-api.php' );
  yourls_maybe_require_auth();
?>
