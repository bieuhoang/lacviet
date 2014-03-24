<?php
if (!function_exists(AWM_get_wp_header_path)) {
	function AWM_get_wp_header_path()
	{
		$base = dirname(__FILE__);
		$path = false;

		if (@file_exists(dirname(dirname($base))."/wp-blog-header.php")) {
			$path = dirname(dirname($base))."/wp-blog-header.php";
		} elseif (@file_exists(dirname(dirname(dirname($base)))."/wp-blog-header.php")) {
			$path = dirname(dirname(dirname($base)))."/wp-blog-header.php";
		} else $path = false;

		if ($path != false) {
			$path = str_replace("\\", "/", $path);
		}
		return $path;
	}
}

/** Loads the WordPress Environment and Template */
require_once(AWM_get_wp_header_path());

$nonce=$_REQUEST['_wpnonce'];
if (! wp_verify_nonce($nonce, 'my-nonce') ) die('Security check'); 


ob_start();
define('WP_USE_THEMES', false);

if (!session_id()) session_start();

require_once (ABSPATH . 'wp-admin/includes/file.php');
global $wpdb;
$awm_table_name = $wpdb->prefix . "awm";

include_once WP_PLUGIN_DIR.'/allwebmenus-wordpress-menu-plugin/include.php';
include_once WP_PLUGIN_DIR.'/allwebmenus-wordpress-menu-plugin/widgetClass.php';


$awm_total_tabs = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $awm_table_name",null));

/*
 * Do the Form Error-Checking
 */

for ($awm_t=0; $awm_t<$awm_total_tabs; $awm_t++) {
	// AWM_categories_subitems_no
	if (isset($_POST['AWM_categories_subitems_no_'.$awm_t])) {
		if ($_POST['AWM_categories_subitems_no_'.$awm_t]<1) $_POST['AWM_categories_subitems_no_'.$awm_t]=1;
		if ($_POST['AWM_categories_subitems_no_'.$awm_t]>50) $_POST['AWM_categories_subitems_no_'.$awm_t]=50;
	}
	// AWM_menu_name
	if (isset($_POST["AWM_menu_name_".$awm_t])) $_POST["AWM_menu_name_".$awm_t] = awm_fix_menu_name($_POST["AWM_menu_name_".$awm_t]);
}
// AWM_menu_path
if (isset($_POST["AWM_menu_path"])) {
	$awm_path = (string) $_POST["AWM_menu_path"];
	if ((strpos($awm_path, "/") != 0) || (strpos($awm_path, "/") === FALSE)) $awm_path = "/" . $awm_path;
	if (substr($awm_path, strlen($awm_path)-1,1) != "/") $awm_path = $awm_path . "/";
	$_POST["AWM_menu_path"] = $awm_path;
}

if (!isset($_POST['theaction'])){
    ob_end_clean();
    wp_redirect($_POST['ref'] );
    exit;

}
if ($_POST['theaction'] == "createnew") {
	$message = awm_create_new_menu();
	$_SESSION['message'] = $message;
	ob_end_clean();
	wp_redirect($_POST['ref'] );
	exit;
} else if ($_POST['theaction']=="delete") {
	// update all values
	$message = awm_delete_menu();
	$_SESSION['message'] =  '<div class="updated fade"><p><strong>'.$message.'</strong></p></div>';
	ob_end_clean();
	wp_redirect($_POST['ref'] );
	exit;
} elseif ($_POST['theaction']=="generate_structure") {
	// first update all values, then generate the current tab's structure
	global $awm_total_tabs;
	if ($awm_total_tabs) {
		awm_update_option_values();
		ob_end_clean();
		wp_redirect($_POST['ref']."&generated=true" );
	} else{
		$_SESSION['message'] =  '<div class="updated fade"><p><strong>There are no menus. You can create one using the appropriate button.</strong></p></div>';
		ob_end_clean();
		wp_redirect($_POST['ref'] );
	}
	exit;
} else if ($_POST['theaction']=="set_defaults") {
	// first update all values, then reset this tab to defaults
	awm_update_option_values();
	awm_set_default_option_values(get_option('AWM_selected_tab'));
	$_SESSION['message'] =  '<div class="updated fade"><p><strong>Default Settings Loaded!</strong></p></div>';
	ob_end_clean();
	wp_redirect($_POST['ref'] );
	exit;
} else if ($_POST['theaction']=="info_update") {
	// update all values
	$message = awm_update_option_values();
	$_SESSION['message'] =  '<div class="updated fade"><p><strong>'.$message.'</strong></p></div>';
	ob_end_clean();
	wp_redirect($_POST['ref'] );
	exit;
} else if ($_POST['theaction']=='hide_msg') {
	update_option('AWM_Check_show', 0);
	ob_end_clean();
	wp_redirect($_POST['ref'] );
	exit;
} else if ($_POST['theaction']=='hide_addcode') {
	update_option("AWM_code_check", 0);
	ob_end_clean();
	wp_redirect($_POST['ref'] );
	exit;
} else if ($_POST['theaction']=="zip_update") {
	if (strpos($_SERVER['HTTP_REFERER'],"wp-admin/options-general.php?page=allwebmenus-wordpress-menu-plugin/allwebmenus-wordpress-menu.php")<1) { echo "Access error! Please contact: support@likno.com";exit;}
	$message = awm_update_zip();
	$_SESSION['message'] =  '<div class="updated fade"><p><strong>'.$message.'</strong></p></div>';
	ob_end_clean();
	wp_redirect($_POST['ref'] );
	exit;
} else {
	ob_end_clean();
	wp_redirect($_POST['ref'] );
	exit;
}
?>
