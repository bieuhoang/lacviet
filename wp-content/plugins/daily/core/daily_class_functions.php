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
		if (!$wpdb->dl_dv) {
			$wpdb->dl_dv = $wpdb->base_prefix . 'dl_dv';
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
		
		$sql = "CREATE TABLE {$wpdb->dl_dv} (
		      id mediumint(9) NOT NULL AUTO_INCREMENT,
		      daiLy mediumint(9) NOT NULL,
			  dichVu mediumint(9) NOT NULL,							  
			  start datetime,						  
			  end datetime,
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
	function add_themKhachHang($data) {
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;
		$sql = array ();
		$user_id = get_current_user_id();
		$created = date('Y-m-d H:i:s');
		$sql = "INSERT INTO `{$wpdb->base_prefix}khach_hang`(`name`, `dichVu`, `daiLy`, `status`, `created`, `updated`) VALUES ('$data[name]', '$data[dichvu]', '$user_id', 1,'$created', '$created')";
		dbDelta($sql);
	}
	function getDichVuDaiLy(){
		global $wpdb;
		$user_id = get_current_user_id();
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = array ();
		$sql = "SELECT * FROM `{$wpdb->base_prefix}dich_vu`";
		$list = $wpdb->get_results($sql);
		return $list;
	}
	function add_dangKyDichVu($data){
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;
		$sql = array ();
		$user_id = get_current_user_id();
		$created = date('Y-m-d H:i:s');
		$sql = "SELECT * FROM `{$wpdb->base_prefix}dl_dv` WHERE `daiLy` = '$user_id' AND `dichVu` = '$data[dichvu]'";
		error_log("-------------------SQL1:".$sql);
		$list = $wpdb->get_results($sql);
		if($list != null){
			return $list;
		}else{
			$sql = "INSERT INTO `{$wpdb->base_prefix}dl_dv`(`daiLy`, `dichVu`, `start`, `end`, `status`, `created`, `updated`) VALUES ('$user_id', '$data[dichvu]','$data[start]','$data[end]', 1,'$created', '$created')";
			error_log("-------------------SQL2:".$sql);
			dbDelta($sql);
		}
	}
	
	function dl_getListDichVu($op){
		global $wpdb;
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = array ();
		$user_id = get_current_user_id();
		$dk = "where `status` > 0 AND `dichvu` > 0 and `daiLy` = $user_id";
		if($op!= null && $op[stt] != null && $op[stt] != ""){
			$dk = $dk."AND `status` = '$op[stt]'";
		}
		else{
		}
		
		if($op != null && $op[order] != null && $op[order] != ""){
			$dk = $dk."ORDER BY $op[order]";
		}
		$sql = "SELECT * FROM `{$wpdb->base_prefix}dl_dv` $dk;";
		$list = $wpdb->get_results($sql);
		return $list;
	}
	
	function dl_userNameById($id){
		global $wpdb;
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = "SELECT * FROM `{$wpdb->base_prefix}users` where ID = $id;";
		$list = $wpdb->get_results($sql);
		return $list;
	}
	
	function dl_updateStatusDichVu($id, $type){
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;
		$sql = array ();
		$user_id = get_current_user_id();
		$created = date('Y-m-d H:i:s');
		$sql = "Update `{$wpdb->base_prefix}dl_dv` SET `status` = $type WHERE `id` = $id";		
		$wpdb->get_results($sql);
	}
	
	function dl_listAllDichVu(){
		global $wpdb;
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = array ();
		$sql = "SELECT * FROM `{$wpdb->base_prefix}dich_vu`;";
		$list = $wpdb->get_results($sql);
		return $list;
	}
	function dl_themDichVu($data) {
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		global $wpdb;
		$sql = array ();
		$user_id = get_current_user_id();
		$created = date('Y-m-d H:i:s');
		$sql = "INSERT INTO `{$wpdb->base_prefix}dich_vu` (`name`, `createdBy`, `status`, `created`, `updated`) VALUES ('$data[name]', $user_id,'$data[status]','$created','$created')";
		dbDelta($sql);
	}
	function dl_getListStatusDichVu(){
		global $wpdb;
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = array ();
		$sql = "SELECT * FROM `{$wpdb->base_prefix}status` WHERE `type` = 'stDichvu';";
		$list = $wpdb->get_results($sql);
		return $list;
	}
	function getDichvuById($id){
		global $wpdb;
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		$sql = array ();
		$sql = "SELECT * FROM `{$wpdb->base_prefix}dich_vu` WHERE `id` = $id;";
		$list = $wpdb->get_results($sql);
		return $list;
	}
	function dl_giahan_DichVu($option){
		
	}
}