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
//session_start();

if ( ! defined( 'NTD_SECUREMESSAGE_BASE_FILE' ) )
    define( 'NTD_SECUREMESSAGE_BASE_FILE', __FILE__ );
if ( ! defined( 'NTD_SECUREMESSAGE_BASE_DIR' ) )
    define( 'NTD_SECUREMESSAGE_BASE_DIR', dirname( NTD_SECUREMESSAGE_BASE_FILE ) );
if ( ! defined( 'NTD_SECUREMESSAGE_PLUGIN_URL' ) )
    define( 'NTD_SECUREMESSAGE_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

//include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); //for check plugin status

register_activation_hook( NTD_SECUREMESSAGE_BASE_FILE, 'ntd_securemessage_plugin_activation' );
register_deactivation_hook( NTD_SECUREMESSAGE_BASE_FILE, 'ntd_securemessage_plugin_deactivation' );

include('lib/ssms.php'); //include library
include('lib/custom_admin_notices.php'); //include library
include('includes/settings.php');
include('includes/shortcodes.php');

//==============================//
//===========FUNCTIONS==========//

function ntd_securemessage_plugin_activation() 
{
	global $wpdb;

    $sql = "CREATE TABLE IF NOT EXISTS `ntd_securemessage` (
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

function ntd_securemessage_plugin_deactivation()
{
	global $wpdb;

	$sql = "DROP TABLE IF EXISTS `ntd_securemessage`";
	$wpdb->query($sql);
}

add_action('wp_enqueue_scripts','ntd_securemessage_assets');
function ntd_securemessage_assets() {
	if( !wp_script_is('jquery', 'enqueued') ) {
		wp_enqueue_script('jquery');
	}
}

add_action('init','ntd_securemessage_init_actions',11);
function ntd_securemessage_init_actions()
{
	if(session_id() == '') {
		session_start();
	}

	$ssms = new NTD_SecureMessage();

	// Save the message that was posted from the form
	if (isset($_POST['ntd_secure_message'])) {
		if(strlen(trim($_POST['ntd_secure_message'])) == 0) { //validate the message
			new NTD_CustomAdminNotices( 'The message field is required.', false );
		} else {
			$message = sanitize_text_field(trim(esc_html($_POST['ntd_secure_message'])));
			if(!isset($_SESSION['ntd_securemessage_id'])) { //insert new message 
				if (base64_encode($message)) {
					$ssms->saveMessage(base64_encode($message));
					$_SESSION['ntd_securemessage_id'] = $ssms->message_id;
					new NTD_CustomAdminNotices( 'Inserted successfully!' );
				}
			} else {
				global $wp;
				$messageid = $_SESSION['ntd_securemessage_id'];
				$result = $ssms->getMessageById($messageid);
				if(!$result) { //the message has been read
					unset($_SESSION['ntd_securemessage_id']);

					parse_str($_SERVER['QUERY_STRING'], $vars);
					$queryString = http_build_query($vars);

					wp_redirect(admin_url('/admin.php?'.$queryString, 'http'), 301);
				} else { //otherwise update the message
					$ssms->updateMessageById($messageid, base64_encode($message));
					new NTD_CustomAdminNotices( 'Updated successfully!' );
				}
			}
		}
	}

	if(isset($_GET['ntd_securemessage_action']) and $_GET['ntd_securemessage_action'] == 'refreshssms' ) { //refresh to create new message
		unset($_SESSION['ntd_securemessage_id']);
		parse_str($_SERVER['QUERY_STRING'], $vars);
		if(isset($vars['ntd_securemessage_action'])) { //make sure we will pass all necessary url query parameters of previous page
			unset($vars['ntd_securemessage_action']);
			$queryString = http_build_query($vars);
			wp_redirect(admin_url('/admin.php?'.$queryString, 'http'), 301);
		}
	}
}

