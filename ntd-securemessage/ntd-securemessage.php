<?php
/**
 * @package ntd-securemessage
 */
/*
Plugin Name: Secure Message
Description: Generate use-once read only message
Version: 1.0
Author: NTD3004
License: GPLv2 or later
*/
session_start();

if ( ! defined( 'NTD_SECUREMESSAGE_BASE_FILE' ) )
    define( 'NTD_SECUREMESSAGE_BASE_FILE', __FILE__ );
if ( ! defined( 'NTD_SECUREMESSAGE_BASE_DIR' ) )
    define( 'NTD_SECUREMESSAGE_BASE_DIR', dirname( NTD_SECUREMESSAGE_BASE_FILE ) );
if ( ! defined( 'NTD_SECUREMESSAGE_PLUGIN_URL' ) )
    define( 'NTD_SECUREMESSAGE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); //for check plugin status

register_activation_hook( __FILE__, 'plugin_activation' );
register_deactivation_hook( __FILE__, 'plugin_deactivation' );

include('lib/ssms.php'); //include library
include('includes/settings.php');
include('includes/shortcodes.php');

//==============================//
//===========FUNCTIONS==========//

function plugin_activation() 
{
	global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS `".$wpdb->prefix . 'securemessage'."` (
`id` INT(255) NOT NULL AUTO_INCREMENT,
`message` mediumtext NOT NULL,
`viewed` tinyint(1) NOT NULL,
`timestamp` text,
`ipaddress` text,
PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
}

function plugin_deactivation()
{
	global $wpdb;

	$sql = "DROP TABLE IF EXISTS `" .$wpdb->prefix . 'securemessage'. "`";
	$wpdb->query($sql);
}

add_action('wp_enqueue_scripts','ub_ssms_assets');
function ub_ssms_assets() {
	if( !wp_script_is('jquery', 'enqueued') ) {
		wp_enqueue_script('jquery');
	}
}

add_action('init','ssms_init_actions',11);
function ssms_init_actions()
{
	$ssms = new SSMS();

	// Save the message that was posted from the form
	if (isset($_POST['ssmsmessage'])) {
		if(!isset($_SESSION['message_id'])) {
			if (base64_encode(trim($_POST['ssmsmessage']))) {
				$ssms->saveMessage(base64_encode(trim($_POST['ssmsmessage'])));
				$_SESSION['message_id'] = $ssms->message_id;
			}
		} else {
			global $wp;
			$messageid = $_SESSION['message_id'];
			$result = $ssms->getMessageById($messageid);
			if(!$result) {
				unset($_SESSION['message_id']);
			}

			parse_str($_SERVER['QUERY_STRING'], $vars);
			$queryString = http_build_query($vars);
			wp_redirect(admin_url('/admin.php?'.$queryString, 'http'), 301);
		}
	}

	if(isset($_GET['ssmsaction']) and $_GET['ssmsaction'] == 'refreshssms' ) {
		unset($_SESSION['message_id']);
		parse_str($_SERVER['QUERY_STRING'], $vars);
		if(isset($vars['ssmsaction'])) {
			unset($vars['ssmsaction']);
			$queryString = http_build_query($vars);
			wp_redirect(admin_url('/admin.php?'.$queryString, 'http'), 301);
		}
	}
}

