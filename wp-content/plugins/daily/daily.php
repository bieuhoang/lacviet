<?php
/*
Plugin Name: Dai ly
Plugin URI: http://
Description: Plugin thuc hien chuc nang quan ly thong tin nap tien cua dai ly va quan ly khach hang.
Version: 1.0.0
Author: BieuHv
Author URI: http://
License: Lacviet pay by the hour
*/
/** Plugin Version */
define('Nap_Tien_Version', '1.0.0');

/** Path for Includes */
define( 'Nap_Tien_Path', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

include_once Nap_Tien_Path . '/core/class_core.php';
/** Loads general functions used by WP-crm */
include_once Nap_Tien_Path . '/core/daily_class_functions.php';

error_log("********************".Nap_Tien_Path);

//* Register activation hook -> has to be in the main plugin file */
register_activation_hook( __FILE__, array( 'DaiLy_F', 'activation' ) );

global $wp_version;
if(version_compare($wp_version, '3.8.0', '<')){
	exit("Plugin này chỉ hoạt động tốt ở phiên bản 3.8.0 trở lên.<br> Phiên bản hiện tại của ban: $wp_version .<br> Làm ơn cập nhật phiên bản mới hơn.");
}
add_action( 'plugins_loaded', create_function( '', 'new DaiLy_Core;' ) );  
?>
