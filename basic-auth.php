<?php
/**
 * Plugin Name: JSON Basic Authentication Fork BY Rewrite
 * Description: Basic Authentication handler for the JSON API, used for development and debugging purposes
 * Author: Rewrite / Based on the work of WordPress API Team
 * Author URI: https://github.com/WP-API
 * Version: 0.1.1
 * Plugin URI: https://github.com/WP-API/Basic-Auth
 */


// Set to TRUE for logging to file fro debug pourpose
// file is created in plugin folder: logs.txt
define("RW_JSON_LOG_ENABLED", true);

function json_basic_auth_handler( $user ) {
	global $wp_json_basic_auth_error;
	$wp_json_basic_auth_error = null;

	// Don't authenticate twice
	if ( ! empty( $user ) ) {
		return $user;
	}

	// Check that we're trying to authenticate with api
	if( isset($_SERVER['PHP_AUTH_USER']) || isset($_REQUEST["Authorization"]) ) {
        rw_json_basic_auth_log('Login attempt IS REST-API', true);
		// error_log('Login attempt IS REST-API'.PHP_EOL, 3, $logFile);
		rw_json_basic_auth_log('Get user/pw from PHP_AUTH_USER/PHP_AUTH_PW: ['.$_SERVER["PHP_AUTH_USER"].']['.$_SERVER["PHP_AUTH_PW"].']');
		$username = $_SERVER['PHP_AUTH_USER'];
		$password = $_SERVER['PHP_AUTH_PW'];
		
		// if no user/pw, extract from param request
		if(!$username || !$password) {
			rw_json_basic_auth_log('PHP_AUTH_USER/PHP_AUTH_PW: empty!');
		 	// GET PARAM FROM REQUEST
	 		rw_json_basic_auth_log('Get user/pw from REQUEST[Authorization]: ['.$_REQUEST["Authorization"].']');
			// extract  user / password
			list($username, $password) = explode(':' , base64_decode(substr($_REQUEST['Authorization'], 6)));
			rw_json_basic_auth_log('Extracted from REQUEST[Authorization]: "'.$username.'", "'.$password.'"');	
		}		
	}
	else{
		rw_json_basic_auth_log('Login attempt IS NOT REST-API: [SERVER]['.$_SERVER["PHP_AUTH_USER"].'], [Request]['.$_REQUEST["Authorization"].']', true);
		return $user; 
	}

	/**
	 * In multi-site, wp_authenticate_spam_check filter is run on authentication. This filter calls
	 * get_currentuserinfo which in turn calls the determine_current_user filter. This leads to infinite
	 * recursion and a stack overflow unless the current function is removed from the determine_current_user
	 * filter during authentication.
	 */
	remove_filter( 'determine_current_user', 'json_basic_auth_handler', 20 );
	
	rw_json_basic_auth_log('Authenticating with ['.$username.']['.$password.'] ...');
	$user = wp_authenticate( $username, $password );

	add_filter( 'determine_current_user', 'json_basic_auth_handler', 20 );

	if ( is_wp_error( $user ) ) {
		$wp_json_basic_auth_error = $user;
		return null;
	}

	$wp_json_basic_auth_error = true;

	return $user->ID;
}
add_filter( 'determine_current_user', 'json_basic_auth_handler', 20 );

function json_basic_auth_error( $error ) {
	// Passthrough other errors
	if ( ! empty( $error ) ) {
		return $error;
	}

	global $wp_json_basic_auth_error;

	return $wp_json_basic_auth_error;
}
add_filter( 'rest_authentication_errors', 'json_basic_auth_error' );

function rw_json_basic_auth_log($text, $add_new_line_first = false) {
	// file target
    $logFile = plugin_dir_path( __DIR__ )."Basic-Auth-master/logs.txt";    
    // log to file if enabled
    if(RW_JSON_LOG_ENABLED) {
        $string = $add_new_line_first==true ? PHP_EOL : '';
        $string.= date('Y-m-d h:i').' '.$text.PHP_EOL;
        error_log($string, 3, $logFile);
    }
}