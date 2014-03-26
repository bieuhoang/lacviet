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

class quantri_F {
	function quantri_f_activation() {
		quantri_F :: quantri_f_maybe_install_tables();
	}
	function quantri_f_maybe_install_tables() {
		global $wpdb;
		$created = date('Y-m-d H:i:s');
		// Array to store SQL queries
		$sql = array ();

		if (!$wpdb->dich_vu) {
			$wpdb->dich_vu = $wpdb->base_prefix . 'dich_vu';
		}
		if (!$wpdb->status) {
			$wpdb->status = $wpdb->base_prefix . 'status';
		}
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

		$sql = "CREATE TABLE {$wpdb->dich_vu} (
		      id mediumint(9) NOT NULL AUTO_INCREMENT,
			  name varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			  noidung text COLLATE utf8_unicode_ci,										  
		      createdBy mediumint(9) NOT NULL,
			  status int(11),					   
			  created datetime,
			  updated datetime,			 					
		      other VARCHAR(255),
		      UNIQUE KEY id (id)
		    );";
		dbDelta($sql);
		$sql = "CREATE TABLE {$wpdb->status} (
		      id mediumint(9) NOT NULL AUTO_INCREMENT,
			  type varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			  num varchar(100) COLLATE utf8_unicode_ci NOT NULL,												 						  
			  name varchar(100) COLLATE utf8_unicode_ci NOT NULL,
			  noidung text COLLATE utf8_unicode_ci,										  
		      createdBy mediumint(9) NOT NULL,
			  status int(11),					   
			  created datetime,
			  updated datetime,			 					
		      other VARCHAR(255),
		      UNIQUE KEY id (id)
		    );";
		dbDelta($sql);
		$sqlInsert = "INSERT INTO `{$wpdb->base_prefix}status`(`type`, `num`, `name`, `created`, `updated`) VALUES ('stDichvu', '1', 'Đang khởi tạo','$created','$created')";
		dbDelta($sqlInsert);
		$sqlInsert = "INSERT INTO `{$wpdb->base_prefix}status`(`type`, `num`, `name`, `created`, `updated`) VALUES ('stDichvu', '2', 'Đang sử dụng','$created','$created')";
		dbDelta($sqlInsert);
		$sqlInsert = "INSERT INTO `{$wpdb->base_prefix}status`(`type`, `num`, `name`, `created`, `updated`) VALUES ('stDichvu', '3', 'Sắp hết hạn','$created','$created')";
		dbDelta($sqlInsert);
		$sqlInsert = "INSERT INTO `{$wpdb->base_prefix}status`(`type`, `num`, `name`, `created`, `updated`) VALUES ('stDichvu', '4', 'Đã hết hạn','$created','$created')";
		dbDelta($sqlInsert);
	}
	function qt_themDichVu($data) {
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;
		$sql = array ();
		$user_id = get_current_user_id();
		$created = date('Y-m-d H:i:s');
		$sql = "INSERT INTO `{$wpdb->base_prefix}dich_vu` (`name`, `createdBy`, `status`, `created`, `updated`) VALUES ('$data[name]', $user_id,'$data[status]','$created','$created')";
		dbDelta($sql);
	}
	function getListStatusDichVu(){
		global $wpdb;
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = array ();
		$sql = "SELECT * FROM `{$wpdb->base_prefix}status` WHERE `type` = 'stDichvu';";
		$list = $wpdb->get_results($sql);
		return $list;
	}
	function getListDichVu(){
		global $wpdb;
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = array ();
		$sql = "SELECT * FROM `{$wpdb->base_prefix}dich_vu`;";
		$list = $wpdb->get_results($sql);
		return $list;
	}
}