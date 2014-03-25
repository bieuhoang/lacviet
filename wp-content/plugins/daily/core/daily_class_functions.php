<?php

/**
 * WP-CRM General Functions
 *
 * Contains all the general functions used by the plugin.
 *
 * @version 0.01
 * @author Andy Potanin <andy.potnain@twincitiestech.com>
 * @package WP-CRM
 * @subpackage Functions
 */

class DaiLy_F {
	function daily_f_activation() {
		DaiLy_F :: daily_f_maybe_install_tables();
	}

	/**
	 * Install DB tables.
	 *
	 * @since 0.01
	 * @uses $wpdb
	 *
	 */
	function daily_f_maybe_install_tables() {
		global $wpdb;

		// Array to store SQL queries
		$sql = array ();

		if (!$wpdb->nap_tien) {
			$wpdb->nap_tien = $wpdb->base_prefix . 'nap_tien';
		}

		if (!$wpdb->nap_tien_log) {
			$wpdb->nap_tien_log = $wpdb->nap_tien . '_log';
		}

		if (!$wpdb->khach_hang) {
			$wpdb->khach_hang = $wpdb->base_prefix . 'khach_hang';
		}

		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

		$sql = "CREATE TABLE {$wpdb->nap_tien} (
		      id mediumint(9) NOT NULL AUTO_INCREMENT,
		      user_id mediumint(9) NOT NULL,
			  tong_tien int(11),
			  cap int(11),
			  status int(11),					   
			  created datetime,
			  updated datetime,			 					
		      other VARCHAR(255),
		      UNIQUE KEY id (id)
		    );";
		dbDelta($sql);

		$sql = "CREATE TABLE {$wpdb->nap_tien_log} (
		      id mediumint(9) NOT NULL AUTO_INCREMENT,
		      user_id mediumint(9) NOT NULL,
		      tien int(11),
			  noidung text COLLATE utf8_unicode_ci,			   
			  status int(11),
			  created datetime,
			  updated datetime,	
		      archived DATETIME,
		      UNIQUE KEY id (id)
		    );";
		dbDelta($sql);

		$sql = "CREATE TABLE {$wpdb->khach_hang} (
		      id mediumint(9) NOT NULL AUTO_INCREMENT,
		      name varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			  dichVu varchar(100) COLLATE utf8_unicode_ci NOT NULL,												 										  
		      daiLy mediumint(9) NOT NULL,
			  noidung text COLLATE utf8_unicode_ci,			   
			  status int(11),
			  created datetime,
			  updated datetime,	
		      UNIQUE KEY id (id)
		    );";
		dbDelta($sql);
	}

	function add_naptienLog($data) {
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;
		$sql = array ();
		$user_id = get_current_user_id();
		$created = date('Y-m-d H:i:s');
		$sql = "INSERT INTO `{$wpdb->base_prefix}nap_tien_log` (`user_id`, `tien`,`noidung`,`created`, `updated`) VALUES ($user_id,'$data[tien]','$data[noidung]','$created','$created')";
		dbDelta($sql);
	}
	function add_add_themKhachHang($data) {
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;
		$sql = array ();
		$user_id = get_current_user_id();
		$created = date('Y-m-d H:i:s');
		$sql = "INSERT INTO `{$wpdb->base_prefix}_khach_hang`(`name`, `dichVu`, `daiLy`, `status`, `created`, `updated`) VALUES ('$data[name]', '$data[dichvu]', '$user_id', 1,'$created', '$created')";
		dbDelta($sql);
	}
}