<?php
/*
Plugin Name: Quan tri (Admin)
Plugin URI: http://
Description: Plugin thuc hien chuc nang quan ly Dai ly.
Version: 1.0.0
Author: BieuHv
Author URI: http://
License: Lacviet pay by the hour
*/
/** Plugin Version */
define('QuanTri_Version', '1.0.0');

/** Path for Includes */
define( 'QuanTri_Path', untrailingslashit( plugin_dir_path( __FILE__ ) ) );

include_once QuanTri_Path . '/core/quantri_class_core.php';
/** Loads general functions used by WP-crm */
include_once QuanTri_Path . '/core/quantri_class_functions.php';

//* Register activation hook -> has to be in the main plugin file */
register_activation_hook( __FILE__, array( 'Quantri_F', 'quantri_f_activation' ) );

global $wp_version;
if(version_compare($wp_version, '3.8.0', '<')){
	exit("Plugin này chỉ hoạt động tốt ở phiên bản 3.8.0 trở lên.<br> Phiên bản hiện tại của ban: $wp_version .<br> Làm ơn cập nhật phiên bản mới hơn.");
}
add_action( 'plugins_loaded', create_function( '', 'new Quantri_Core;' ) );  
?>
