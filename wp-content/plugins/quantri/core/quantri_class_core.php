<?php
class Quantri_Core {
	function Quantri_Core() {
		add_action('init', array ($this,'init'));
	}
	function init() {
		add_action("admin_menu", array ('quantri_Core',	"quantri_core_quantri_menu"	), 150);
	}
	function quantri_core_quantri_menu() {
		global $quanly_quantri, $menu, $submenu, $current_user;
		/** Setup main overview page */
		add_menu_page('Quản Trị', 'Quản Trị', '', 'menu_quantri', array (
			'Quantri_Core',
			'quantri_core_page_loader'
		), '', null);
		add_submenu_page('menu_quantri', __('All People', 'menu_quantri'), __('Thêm mới dịch vụ', 'quanly_quantri'), 'WP-CRM: View Overview', 'themdv', array (
			'Quantri_Core',
			'quantri_core_page_loader'
		));
		add_submenu_page('menu_quantri', __('All People', 'menu_quantri'), __('Danh sách dịch vụ', 'quanly_quantri'), 'WP-CRM: View Overview', 'dsdv', array (
			'Quantri_Core',
			'quantri_core_page_loader'
		));
		
		add_submenu_page('menu_quantri', __('All People', 'menu_quantri'), __('Quản lý dịch vụ Khách hàng', 'quanly_quantri'), 'WP-CRM: View Overview', 'qldvKh', array (
			'Quantri_Core',
			'quantri_core_page_loader'
		));
		
		add_submenu_page('menu_quantri', __('All People', 'menu_quantri'), __('Quản lý nạp tiền', 'quanly_quantri'), 'WP-CRM: View Overview', 'qlnt', array (
			'Quantri_Core',
			'quantri_core_page_loader'
		));
		add_submenu_page('menu_quantri', __('All People', 'menu_quantri'), __('Quản lý Đại lý', 'quanly_quantri'), 'WP-CRM: View Overview', 'qldl', array (
			'Quantri_Core',
			'quantri_core_page_loader'
		));
		
		if ($quanly_quantri['configuration']['track_detailed_user_activity'] == 'true') {
			$quanly_quantri['system']['pages']['user_logs'] = add_submenu_page('quanly_quantri', __('Activity Logs', 'quanly_quantri'), __('Activity Logs', 'quanly_quantri'), 'WP-CRM: View Detailed Logs', 'quanly_quantri_detailed_logs', array (
				'Quantri_Core',
				'quantri_core_page_loader'
			));
		}
	}

	/**
	 * Used for loading back-end UI
	 *
	 * All back-end pages call this function, which then determines that UI to load below the headers.
	 *
	 * @since 0.01
	 */
	function quantri_core_page_loader() {
		global $quanly_quantri, $screen_layout_columns, $current_screen, $wpdb, $crm_messages, $user_ID, $quanly_quantri_user;

		$file_path = QuanTri_Path . "/core/ui/{$current_screen->base}.php";

		if (file_exists($file_path)) {
			include $file_path;
		} else {
			echo "<div class='wrap'><h2>Error</h2><p>Template not found:" . $file_path . "</p></div>";
		}

	}
}