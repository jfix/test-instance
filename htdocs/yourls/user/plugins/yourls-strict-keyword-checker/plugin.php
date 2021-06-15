<?php
/*
Plugin Name: Strict Keyword Checker
Plugin URI: https://github.com/jfix/yourls-strict-keyword-checker
Description: Make sure a custom keyword doesn't contain a reserved word
Version: 1.0
Author: Jakob Fix
Author URI: https://github.com/jfix
*/

// No direct call
if( !defined( 'YOURLS_ABSPATH' ) ) die();

yourls_add_filter( 'shunt_add_new_link', 'jfix_strict_check_keyword' );

function jfix_strict_check_keyword( $result, $url, $keyword, $title ) {
  global $yourls_reserved_URL;

  // if the keyword is empty, continue the flow of execution
  if ( empty($keyword) )  {
    return false;
  }

  // otherwise go through all reserved keywords
  foreach ($yourls_reserved_URL as $a_keyword) {
    // does the submitted keyword contain a reserved word?
    if (preg_match("/$a_keyword/i", $keyword)) {
      // return an array that will trigger a message and stop there
      return array(
        'status'    => 'fail',
        'code'      => 'error:keyword',
        'message'   => yourls__( 'Please do not use this type of language' ),
        'errorCode' => '400'
      );
    }
  }
  // if no reserved keyword contained in submitted keyword
  // continue flow of execution
  return false;
}
