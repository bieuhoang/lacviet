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

	/**
	 * Install DB tables.
	 *
	 * @since 0.01
	 * @uses $wpdb
	 *
	 */
	function quantri_f_maybe_install_tables() {
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
	}
}