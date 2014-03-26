<?php
class DaiLy_Core {
	function DaiLy_Core() {
		add_action('init', array (
			$this,
			'init'
		));
	}
	function init() {
		global $wpdb, $quanly_daily, $wp_roles;
		add_action("admin_menu", array (
			'DaiLy_Core',
			"daily_core_daily_menu"
		), 100);
	}

	/**
	  * Runs pre-header functions on admin-side only - ran on ALL admin pages
	  *
	  * Checks if plugin has been updated.
	  *
	  * @since 0.1
	  *
	  */
	function daily_core_admin_init() {
		global $wp_rewrite, $wp_roles, $quanly_daily, $wpdb, $current_user;
		//** Check if current page is profile page, and load global variable */
		quanly_daily_F :: maybe_load_profile();

		do_action('quanly_daily_metaboxes');

		//** Add overview table rows. Static because admin_menu is not loaded on ajax calls. */

		add_action('admin_print_scripts-' . $quanly_daily['system']['pages']['settings'], create_function('', "wp_enqueue_script('jquery-ui-tabs');wp_enqueue_script('jquery-cookie');"));


		// Add metaboxes
		if (is_array($quanly_daily['system']['pages'])) {

			$sidebar_boxes = array (
				'special_actions'
			);

			foreach ($quanly_daily['system']['pages'] as $screen) {

				if (!class_exists($screen)) {
					continue;
				}

				$location_prefixes = array (
					'side_',
					'normal_',
					'advanced_'
				);

				foreach (get_class_methods($screen) as $box) {

					// Set context and priority if specified for box

					$context = 'normal';

					if (strpos($box, "side_") === 0 || in_array($box, $sidebar_boxes)) {
						$context = 'side';
					}

					if (strpos($box, "advanced_") === 0) {
						$context = 'advanced';
					}

					// Get name from slug
					$label = CRM_UD_F :: slug_to_label(str_replace($location_prefixes, '', $box));

					add_meta_box($box, $label, array (
						$screen,
						$box
					), $screen, $context, 'default');
				}
			}
		}

		//** Handle actions */
		if (isset ($_REQUEST['quanly_daily_action'])) {

			$_wpnonce = $_REQUEST['_wpnonce'];

			switch ($_REQUEST['quanly_daily_action']) {

				case 'delete_user' :
					$user_id = $_REQUEST['user_id'];

					if (wp_verify_nonce($_wpnonce, 'wp-crm-delete-user-' . $user_id)) {
						//** Get IDs of users posts */
						$post_ids = $wpdb->get_col($wpdb->prepare("SELECT ID FROM $wpdb->posts WHERE post_author = %d", $user_id));

						//** Delete user and reassign all their posts to the current user */
						if (wp_delete_user($user_id, $current_user->data->ID)) {

							//** Trash all posts */
							if (is_array($post_ids)) {
								foreach ($post_ids as $trash_post) {
									wp_trash_post($trash_post);
								}
							}

							wp_redirect(admin_url('admin.php?page=quanly_daily&message=user_deleted'));
						}
					}

					break;

			}

		}

		add_filter('admin_title', array (
			'quanly_daily_F',
			'admin_title'
		));

		quanly_daily_F :: manual_activation();
	}

	/**
	 * Sets up plugin pages and loads their scripts
	 *
	 * @since 0.01
	 * @todo Make position incriment by one to not override anything
	 *
	 */
	function daily_core_daily_menu() {
		global $quanly_daily, $menu, $submenu, $current_user;

		do_action('naptien_admin_menu');
		//** Replace default user management screen if set */
		$position = (($quanly_daily['configuration']['replace_default_user_page'] == 'true' && current_user_can('manage_options')) ? '70' : '33');

		/** Setup main overview page */
		$quanly_daily['system']['pages']['core'] = add_menu_page('Đại lý', 'ĐẠI LÝ', '', 'quanly_daily', array (
			'DaiLy_Core',
			'daily_core_page_loader'
		), '', $position);
		//* Setup child pages (first one is used to be loaded in place of 'CRM' */
		$quanly_daily['system']['pages']['naptien'] = add_submenu_page('quanly_daily', __('All People', 'quanly_daily'), __('Nạp thêm tiền', 'quanly_daily'), 'WP-CRM: View Overview', 'napthemtien', array (
			'DaiLy_Core',
			'daily_core_page_loader'
		));
		//$quanly_daily[ 'system' ][ 'pages' ][ 'naptien' ] = add_submenu_page( 'quanly_daily', __( 'All People', 'quanly_daily' ), __( 'Danh sách đã nạp', 'quanly_daily' ), 'WP-CRM: View Overview', 'dsnap', array( 'DaiLy_Core', 'page_loader' ) );
		$quanly_daily['system']['pages']['khachhang'] = add_submenu_page('quanly_daily', __('All People', 'quanly_daily'), __('Thêm mới khách hàng', 'quanly_daily'), 'WP-CRM: View Overview', 'themmoikhachhang', array (
			'DaiLy_Core',
			'daily_core_page_loader'
		));
		$quanly_daily['system']['pages']['khachhang'] = add_submenu_page('quanly_daily', __('All People', 'quanly_daily'), __('Đăng ký dịch vụ', 'quanly_daily'), 'WP-CRM: View Overview', 'dkdv', array (
			'DaiLy_Core',
			'daily_core_page_loader'
		));
		$quanly_daily['system']['pages']['khachhang'] = add_submenu_page('quanly_daily', __('All People', 'quanly_daily'), __('Các Dịch vụ đã Đăng ký', 'quanly_daily'), 'WP-CRM: View Overview', 'dsdvdk', array (
			'DaiLy_Core',
			'daily_core_page_loader'
		));

		if ($quanly_daily['configuration']['track_detailed_user_activity'] == 'true') {
			$quanly_daily['system']['pages']['user_logs'] = add_submenu_page('quanly_daily', __('Activity Logs', 'quanly_daily'), __('Activity Logs', 'quanly_daily'), 'WP-CRM: View Detailed Logs', 'quanly_daily_detailed_logs', array (
				'DaiLy_Core',
				'daily_core_page_loader'
			));
		}

		//** Migrate any pages that are under default user page */
		if ($quanly_daily['configuration']['replace_default_user_page'] == 'true') {

			$quanly_daily_excluded_sub_pages = apply_filters('quanly_daily_excluded_sub_pages', array (
				5,
				10,
				15
			));
			if (is_array($submenu['users.php'])) {

				foreach ($submenu['users.php'] as $sub_key => $sub_pages_data) {

					if (in_array($sub_key, $quanly_daily_excluded_sub_pages)) {
						continue;
					}
				}
			}

		}
	}

	/**
	 * Used for loading back-end UI
	 *
	 * All back-end pages call this function, which then determines that UI to load below the headers.
	 *
	 * @since 0.01
	 */
	function daily_core_page_loader() {
		global $quanly_daily, $screen_layout_columns, $current_screen, $wpdb, $crm_messages, $user_ID, $quanly_daily_user;

		$file_path = Daily_Path . "/core/ui/{$current_screen->base}.php";

		if (file_exists($file_path)) {
			include $file_path;
		} else {
			echo "<div class='wrap'><h2>Error</h2><p>Template not found:" . $file_path . "</p></div>";
		}

	}
}