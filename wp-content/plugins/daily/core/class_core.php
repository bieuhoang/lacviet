<?php
class DaiLy_Core {
  function DaiLy_Core() {
    add_action( 'init', array( $this, 'init' ) );
  }
  function init() {
    global $wpdb, $quanly_daily, $wp_roles;

    if ( $quanly_daily[ 'configuration' ][ 'replace_default_user_page' ] == 'true' ) {
      $current_user = wp_get_current_user();
      if ( $quanly_daily[ 'configuration' ][ 'replace_default_user_page' ] == 'true' && basename( $_SERVER[ 'SCRIPT_NAME' ] ) == 'profile.php' && !empty( $current_user->ID ) && current_user_can( 'edit_users' ) ) {
        die( wp_redirect( "admin.php?page=quanly_daily_add_new&user_id={$current_user->ID}" ) );
      }
      add_filter( 'edit_profile_url', array( 'quanly_daily_F', 'edit_profile_url' ), 10, 3 );
    }
    // Setup pages and overview columns
    
    
    add_action( "admin_menu", array( 'DaiLy_Core', "daily_menu" ), 100 );


    //** Modify default WP password reset message */
    add_filter( "admin_body_class", create_function( '', "return DaiLy_Core::admin_body_class(); " ) );
  }

  function template_redirect() {
    global $post, $wp, $wp_query, $wp_styles;

    if ( !strpos( $post->post_content, "quanly_daily_form" ) ) {
      return;
    }

    //** Print front-end styles */
    add_action( "wp_print_styles", array( 'DaiLy_Core', "wp_print_styles" ) );

  }

  /**
   * Loads front-end styles
   *
   * Only ran when quanly_daily_form shortcode is present in content.
   *
   * @since 0.1
   *
   */
  function wp_print_styles() {
    global $post, $wp, $wp_query, $wp_styles;

    // Load theme-specific stylesheet if it exists
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'wp-crm-theme-specific' );
    wp_enqueue_style( 'wp-crm-default-styles' );
  }

  /**
   * Runs pre-header functions on admin-side only for the overview page
   *
   * @todo Enure displayed settings are being honored when saved.
   * @since 0.1
   *
   */
  function toplevel_page_quanly_daily() {
    add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
    add_screen_option( 'per_page', array( 'label' => __( 'Users', 'quanly_daily' ) ) );

    //** Top level page contextual help data */
    $contextual_help[ 'General Help' ][ ] = '<p>' . __( 'This page is used to filter and find various users. Visit the Settings page to select which attributes to show on the overview.', 'quanly_daily' ) . '</p>';
    $contextual_help[ 'General Help' ][ ] = '<h3>' . __( 'Exporting', 'quanly_daily' ) . '</h3>';
    $contextual_help[ 'General Help' ][ ] = '<p>' . __( 'Once you narrow down the user results to the ones you want to export, click "Show Actions" and then "Export to CSV" to generate a comma separated flle.', 'quanly_daily' ) . '</p>';
    $contextual_help[ 'General Help' ][ ] = '<p>' . __( 'The CSV export will only include the user data as defined in Data tab, on the Settings page.', 'quanly_daily' ) . '</p>';

    //** Hook this filter if you need to add something */
    $contextual_help = apply_filters( 'toplevel_page_quanly_daily_help', $contextual_help );

    do_action( 'quanly_daily_contextual_help', array( 'contextual_help' => $contextual_help ) );
  }

  /**
   * Runs pre-header functions for profile page
   *
   * @since 0.22
   *
   */
  function crm_page_traditional_user_page() {
    global $quanly_daily, $current_screen, $hook_suffix, $typenow, $taxnow;

    /* If avatar-delection redirection originated from CRM profile, we muts return there */
    if ( $_GET[ 'delete_avatar' ] == 'true' && strpos( $_SERVER[ 'HTTP_REFERER' ], 'admin.php?page=quanly_daily_add_new' ) ) {
      die( wp_redirect( "admin.php?page=quanly_daily_add_new&user_id={$_GET['user_id']}" ) );
    }

    if ( $quanly_daily[ 'configuration' ][ 'replace_default_user_page' ] != 'true' ) {
      return;
    }

    switch ( $current_screen->id ) {

      case 'user-edit':

        if ( isset( $_GET[ 'user_id' ] ) && is_numeric( $_GET[ 'user_id' ] ) ) {
          die( wp_redirect( "admin.php?page=quanly_daily_add_new&user_id={$_GET['user_id']}" ) );
        } else {
          die( wp_redirect( "admin.php?page=quanly_daily_add_new" ) );
        }

        break;

      case 'users':
        die( wp_redirect( "admin.php?page=quanly_daily" ) );
        break;

      case 'user':
        die( wp_redirect( "admin.php?page=quanly_daily_add_new" ) );
        break;

    }

  }

  /**
   * Runs pre-header functions for profile page
   *
   * @since 0.22
   *
   */
  function crm_page_quanly_daily_add_new() {
    global $quanly_daily;

    //Something wrong with this function. Looks like Metaboxed now had been made in another way. odokienko@UD
    //quanly_daily_F::crm_profile_page_metaboxes();

    //** If we are on 'crm_page_quanly_daily_add_new' screen - render metaboxes for groups */
    if ( $quanly_daily[ 'configuration' ][ 'allow_attributes_grouping' ] == 'true' ) {
      quanly_daily_F::grouped_metaboxes();
    }

    add_filter( "screen_settings", array( 'quanly_daily_F', 'crm_screen_options' ) );

    //** Screen Options */
    if ( function_exists( 'add_screen_option' ) ) {
      add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
      add_screen_option( 'per_page', array( 'label' => __( 'Notifications', 'quanly_daily' ), 'default' => 10 ) );
    }

    //** Help items */
    $contextual_help[ 'General Help' ][ ] = '<h3>' . __( 'User Editing', 'quanly_daily' ) . '</h3>';
    $contextual_help[ 'General Help' ][ ] = '<p>' . __( 'Please visit the WP-CRM Settings page to determine which fields to display on the editing page.', 'quanly_daily' ) . '</p>';

    $contextual_help[ 'General Help' ][ ] = '<h3>' . __( 'User Activity History', 'quanly_daily' ) . '</h3>';
    $contextual_help[ 'General Help' ][ ] = '<p>' . __( 'The activity history can be used to log notes regarding a user, and will display any incoming messages generated by the user when using a WP-CRM shortcode forms.', 'quanly_daily' ) . '</p>';

    //** Hook this filter if you need to add something */
    $contextual_help = apply_filters( 'crm_page_quanly_daily_add_new_help', $contextual_help );

    do_action( 'quanly_daily_contextual_help', array( 'contextual_help' => $contextual_help ) );

  }

  /**
   * Runs pre-header functions for settings page
   *
   *
   * @since 0.1
   *
   */
  function crm_page_quanly_daily_settings() {

    //** Download backup of configuration */
    if ( $_REQUEST[ 'quanly_daily_action' ] == 'download-quanly_daily-backup'
      && wp_verify_nonce( $_REQUEST[ '_wpnonce' ], 'download-quanly_daily-backup' )
    ) {
      global $quanly_daily;

      $sitename = sanitize_key( get_bloginfo( 'name' ) );
      $filename = $sitename . '-wp-crm.' . date( 'Y-m-d' ) . '.txt';

      header( "Cache-Control: public" );
      header( "Content-Description: File Transfer" );
      header( "Content-Disposition: attachment; filename=$filename" );
      header( "Content-Transfer-Encoding: binary" );
      header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ), true );

      echo json_encode( $quanly_daily );
      die();
    }

    //** Make sure tables are up to date */
    quanly_daily_F::maybe_install_tables();

    //** Help items for this page */
    $contextual_help[ 'General Help' ][ ] = '<h3>' . __( 'Roles - Hidden Attributes', 'quanly_daily' ) . '</h3>';
    $contextual_help[ 'General Help' ][ ] = '<p>' . __( 'If certain user attributes are not applicable to certain roles, such as "Client Type" to the "Administrator" role, you can elect to hide the unapplicable attributes on profile editing pages.', 'quanly_daily' ) . '</p>';
    $contextual_help[ 'General Help' ][ ] = '<h3>' . __( 'Predefined Values', 'quanly_daily' ) . '</h3>';
    $contextual_help[ 'General Help' ][ ] = '<p>' . __( 'If you want your attributes to have predefined values, such as in a dropdown, or a checkbox list, enter a comma separated list of values you want to use.  You can also get more advanced by using taxonomies - to load all values from a taxonomy, simply type line: <b>taxonomy:taxonomy_name</b>.', 'quanly_daily' ) . '</p>';
    $contextual_help[ 'Shortcode Forms' ][ ] = '<h3>' . __( 'Shortcode Forms', 'quanly_daily' ) . '</h3>';
    $contextual_help[ 'Shortcode Forms' ][ ] = '<p>' . __( 'Shortcode Forms, which can be used for contact forms, or profile editing, are setup here, and then inserted using a shortcode into a page, or a widget. The available shortcode form attributes are taken from the WP-CRM attributes, and when filled out by a user, are mapped over directly into their profile. User profiles are created based on the e-mail address, if one does not already exist, for keeping track of users. ', 'quanly_daily' ) . '</p>';

    $contextual_help[ 'Shortcode Forms' ][ ] = '<h3>' . __( 'Shortcode Forms attributes', 'quanly_daily' ) . '</h3>';

    $contextual_help[ 'Shortcode Forms' ][ ] = '<p> - ' . __( 'display_notes = [ true | <b>false</b> ] &mdash; If a note exists for an attribute, it will be shown on the right.', 'quanly_daily' ) . '</p>';
    $contextual_help[ 'Shortcode Forms' ][ ] = '<p> - ' . __( 'require_login_for_existing_users = [ <b>true</b> | false ]', 'quanly_daily' ) . '</p>';
    $contextual_help[ 'Shortcode Forms' ][ ] = '<p> - ' . __( 'use_current_user = [ <b>true</b> | false ]', 'quanly_daily' ) . '</p>';
    $contextual_help[ 'Shortcode Forms' ][ ] = '<p> - ' . __( 'success_message = "<i>custom text</i>"  &mdash; default value is "', 'quanly_daily' ) . __( 'Your message has been sent. Thank you.', 'quanly_daily' ) . '".</p>';
    $contextual_help[ 'Shortcode Forms' ][ ] = '<p> - ' . __( 'submit_text = "<i>custom text</i>"  &mdash; default value is "', 'quanly_daily' ) . __( 'Submit', 'quanly_daily' ) . '".</p>';
    $contextual_help[ 'Shortcode Forms' ][ ] = '<p> - ' . __( 'js_callback_function = "<i>custom_function_name</i>"  &mdash; default value is "', 'quanly_daily' ) . __( 'false', 'quanly_daily' ) . '".</p>';
    $contextual_help[ 'Shortcode Forms' ][ ] = '<p> - ' . __( 'js_validation_function = "<i>custom_function_name</i>"  &mdash; default value is "', 'quanly_daily' ) . __( 'false', 'quanly_daily' ) . '".</p>';

    $contextual_help[ 'Shortcode Forms' ][ ] = '<p>' . __( 'For example, <b>[quanly_daily_form form=example_from display_notes=true success_message="Your message was successfully sent!" submit_text="Send message!"]</b>', 'quanly_daily' ) . '</p>';

    $contextual_help[ 'Shortcode Forms' ][ ] = '<p>' . __( 'If a new user fills out a form, an account will be created for them based on the specified role.  ', 'quanly_daily' ) . '</p>';
    $contextual_help[ 'Shortcode Forms' ][ ] = '<p>' . __( '<b>Important</b>: user\'s email attribute should have slug \'user_email\'.', 'quanly_daily' ) . '</p>';

    $contextual_help[ 'Shortcodes' ][ ] = '<h3>' . __( 'Automation', 'quanly_daily' ) . '</h3>';
    $contextual_help[ 'Shortcodes' ][ ] = '<p>' . __( 'Use other attribute as components. Example: <b>[last_name], [rank]</b> will become <b>Smith, Sgt.</b>', 'quanly_daily' ) . '</p>';
    $contextual_help[ 'Shortcodes' ][ ] = '<h3>' . __( 'Notifications and Trigger Actions', 'quanly_daily' ) . '</h3>';
    $contextual_help[ 'Shortcodes' ][ ] = '<p>' . __( 'Notification messages can be fired off when certain events, such as contact form submission, are executed.  Multiple notification events can be attached to a single <b>trigger action</b>. Multiple tags, such as <b>[user_email]</b> and <b>[display_name]</b>, are available to be used as dynamically replaceable tags when setting up notifications.', 'quanly_daily' ) . '</p>';
    $contextual_help[ 'Shortcodes' ][ ] = '<p>' . __( 'Which tags are available depend on the trigger event, but in most cases all user data slugs can be used.  On a shortcode form message, <b>[message_content]</b>, <b>[profile_link]</b> and <b>[trigger_action]</b> variables are also available.', 'quanly_daily' ) . '</p>';

    //** Hook this filter if you need to add something */
    $contextual_help = apply_filters( 'crm_page_quanly_daily_settings_help', $contextual_help );

    do_action( 'quanly_daily_contextual_help', array( 'contextual_help' => $contextual_help ) );
  }

  /**
   * Runs pre-header functions on admin-side only - ran on ALL admin pages
   *
   * Checks if plugin has been updated.
   *
   * @since 0.1
   *
   */
  function admin_init() {
    global $wp_rewrite, $wp_roles, $quanly_daily, $wpdb, $current_user;
    //** Check if current page is profile page, and load global variable */
    quanly_daily_F::maybe_load_profile();

    do_action( 'quanly_daily_metaboxes' );

    //** Add overview table rows. Static because admin_menu is not loaded on ajax calls. */
    add_filter( "manage_toplevel_page_quanly_daily_columns", array( 'DaiLy_Core', "overview_columns" ) );

    add_action( 'admin_print_scripts-' . $quanly_daily[ 'system' ][ 'pages' ][ 'settings' ], create_function( '', "wp_enqueue_script('jquery-ui-tabs');wp_enqueue_script('jquery-cookie');" ) );

    add_action( 'load-crm_page_quanly_daily_add_new', array( 'DaiLy_Core', 'quanly_daily_save_user_data' ) );

    // Add metaboxes
    if ( is_array( $quanly_daily[ 'system' ][ 'pages' ] ) ) {

      $sidebar_boxes = array( 'special_actions' );

      foreach ( $quanly_daily[ 'system' ][ 'pages' ] as $screen ) {

        if ( !class_exists( $screen ) ) {
          continue;
        }

        $location_prefixes = array( 'side_', 'normal_', 'advanced_' );

        foreach ( get_class_methods( $screen ) as $box ) {

          // Set context and priority if specified for box

          $context = 'normal';

          if ( strpos( $box, "side_" ) === 0 || in_array( $box, $sidebar_boxes ) ) {
            $context = 'side';
          }

          if ( strpos( $box, "advanced_" ) === 0 ) {
            $context = 'advanced';
          }

          // Get name from slug
          $label = CRM_UD_F::slug_to_label( str_replace( $location_prefixes, '', $box ) );

          add_meta_box( $box, $label, array( $screen, $box ), $screen, $context, 'default' );
        }
      }
    }

    //** Handle actions */
    if ( isset( $_REQUEST[ 'quanly_daily_action' ] ) ) {

      $_wpnonce = $_REQUEST[ '_wpnonce' ];

      switch ( $_REQUEST[ 'quanly_daily_action' ] ) {

        case 'delete_user':
          $user_id = $_REQUEST[ 'user_id' ];

          if ( wp_verify_nonce( $_wpnonce, 'wp-crm-delete-user-' . $user_id ) ) {
            //** Get IDs of users posts */
            $post_ids = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_author = %d", $user_id ) );

            //** Delete user and reassign all their posts to the current user */
            if ( wp_delete_user( $user_id, $current_user->data->ID ) ) {

              //** Trash all posts */
              if ( is_array( $post_ids ) ) {
                foreach ( $post_ids as $trash_post ) {
                  wp_trash_post( $trash_post );
                }
              }

              wp_redirect( admin_url( 'admin.php?page=quanly_daily&message=user_deleted' ) );
            }
          }

          break;

      }

    }

    if ( $quanly_daily[ 'configuration' ][ 'replace_default_user_page' ] == 'true' ) {
      add_filter( 'admin_user_info_links', array( 'DaiLy_Core', 'admin_user_info_links' ), 10, 2 );
    }

    add_filter( 'admin_title', array( 'quanly_daily_F', 'admin_title' ) );

    quanly_daily_F::manual_activation();
  }

  /**
   * Handles current screen
   *
   * @author peshkov@UD
   */
  function current_screen( $current_screen ) {
    global $current_user, $current_screen;
    static $called = false;

    /** Determine if the current screen is profile we re-set it to 'edit user' (crm_page_quanly_daily_add_new) screen */
    if ( is_object( $current_screen ) && $current_screen->id == 'crm_page_quanly_daily_my_profile' ) {
      $called = true;
      if ( empty( $_REQUEST[ 'user_id' ] ) ) {
        $_GET[ 'user_id' ] = $_REQUEST[ 'user_id' ] = $current_user->id;
        /** Re-set global $quanly_daily_user. It was set earlier on admin_init action. */
        quanly_daily_F::maybe_load_profile( $current_user->id, true );
      }
      $_GET[ 'redirect_to' ] = $_REQUEST[ 'redirect_to' ] = urlencode( admin_url( 'admin.php?page=quanly_daily_my_profile' ) );
      set_current_screen( 'crm_page_quanly_daily_add_new' );
    }
    return $current_screen;
  }

  /**
   * Does user info links
   *
   * @author Reid Williams
   */
  function admin_user_info_links( $links, $current_user ) {
    $links[ 8 ] = '<a href="admin.php?page=quanly_daily_add_new&user_id=' . $current_user->ID . '" title="' . esc_attr__( 'Edit your profile', 'quanly_daily' ) . '">' . __( 'Your Profile', 'quanly_daily' ) . '</a>';
    return $links;
  }

  /**
   * Primary function for updating user profiles on back-end.
   *
   * Called before hearder on user editing page.
   *
   * @since 0.01
   *
   */
  function quanly_daily_save_user_data() {

    if ( wp_verify_nonce( $_REQUEST[ 'quanly_daily_update_user' ], 'quanly_daily_update_user' ) ) {
      $args = $_REQUEST[ 'quanly_daily' ][ 'args' ];

      $user_data = $_REQUEST[ 'quanly_daily' ][ 'user_data' ];

      //** Add extra user_data data */
      $user_data[ 'admin_color' ][ 0 ][ 'value' ] = $_REQUEST[ 'admin_color' ];
      $user_data[ 'show_admin_bar_front' ][ 0 ][ 'value' ] = $_REQUEST[ 'show_admin_bar_front' ];

      $args[ 'admin_save_action' ] = true;

      do_action( 'quanly_daily_before_save_user_data', $_REQUEST );
      quanly_daily_save_user_data( $user_data, $args );
    }

  }


  /**
   * Header functions
   *
   * Loads after admin_enqueue_scripts, admin_print_styles, and admin_head.
   * Loads before: favorite_actions, screen_meta
   *
   * @since 0.1
   */
  function admin_head() {
    global $current_screen, $wp_filter, $quanly_daily;

    do_action( "quanly_daily_header_{$current_screen->id}", $current_screen->id );

    switch ( $current_screen->id ) {

      case 'toplevel_page_quanly_daily':
        break;

      case 'crm_page_quanly_daily_add_new':
        break;

    }

    if ( $quanly_daily[ 'configuration' ][ 'developer_mode' ] == 'true' ) {
      echo '<script type="text/javascript">var quanly_daily_dev_mode = true;</script>';
    }

  }

  /**
   * Sets up plugin pages and loads their scripts
   *
   * @since 0.01
   * @todo Make position incriment by one to not override anything
   *
   */
  function daily_menu() {
    global $quanly_daily, $menu, $submenu, $current_user;

    do_action( 'naptien_admin_menu' );
    //** Replace default user management screen if set */
    $position = ( ( $quanly_daily[ 'configuration' ][ 'replace_default_user_page' ] == 'true' && current_user_can( 'manage_options' ) ) ? '70' : '33' );

    /** Setup main overview page */
    $quanly_daily[ 'system' ][ 'pages' ][ 'core' ] = add_menu_page('Đại lý', 'ĐẠI LÝ', '', 'quanly_daily', array( 'DaiLy_Core', 'page_loader' ), '', $position );
    //* Setup child pages (first one is used to be loaded in place of 'CRM' */
    $quanly_daily[ 'system' ][ 'pages' ][ 'naptien' ] = add_submenu_page( 'quanly_daily', __( 'All People', 'quanly_daily' ), __( 'Nạp thêm tiền', 'quanly_daily' ), 'WP-CRM: View Overview', 'napthemtien', array( 'DaiLy_Core', 'page_loader' ) );
    $quanly_daily[ 'system' ][ 'pages' ][ 'khachhang' ] = add_submenu_page( 'quanly_daily', __( 'All People', 'quanly_daily' ), __( 'Thêm mới khách hàng', 'quanly_daily' ), 'WP-CRM: View Overview', 'themmoikhachhang', array( 'DaiLy_Core', 'page_loader' ) );    

    if ( $quanly_daily[ 'configuration' ][ 'track_detailed_user_activity' ] == 'true' ) {
      $quanly_daily[ 'system' ][ 'pages' ][ 'user_logs' ] = add_submenu_page( 'quanly_daily', __( 'Activity Logs', 'quanly_daily' ), __( 'Activity Logs', 'quanly_daily' ), 'WP-CRM: View Detailed Logs', 'quanly_daily_detailed_logs', array( 'DaiLy_Core', 'page_loader' ) );
    }

    //** Migrate any pages that are under default user page */
    if ( $quanly_daily[ 'configuration' ][ 'replace_default_user_page' ] == 'true' ) {

      $quanly_daily_excluded_sub_pages = apply_filters( 'quanly_daily_excluded_sub_pages', array( 5, 10, 15 ) );
      if ( is_array( $submenu[ 'users.php' ] ) ) {

        foreach ( $submenu[ 'users.php' ] as $sub_key => $sub_pages_data ) {

          if ( in_array( $sub_key, $quanly_daily_excluded_sub_pages ) ) {
            continue;
          }

          //** Fix links (there may be a better way) */
          $sub_pages_data[ 2 ] = 'admin.php?page=' . $sub_pages_data[ 2 ];

          $submenu[ 'quanly_daily' ][ $sub_key ] = $sub_pages_data;
        }
      }

    }
    
    add_users_page('Tạo mới khách hàng', 'Tạo khách hàng', 1, "taoKH", array( 'DaiLy_Core', 'page_loader' ));
	add_users_page('Nạp tiền', 'Nạp tiền', 1, "dlNapTien", array( 'DaiLy_Core', 'page_loader' ));

  }
  
  function _menu() {
    global $quanly_daily, $menu, $submenu, $current_user;

    do_action( 'naptien_user_menu' );
    //** Replace default user management screen if set */
    $position = ( ( $quanly_daily[ 'configuration' ][ 'replace_default_user_page' ] == 'true' && current_user_can( 'manage_options' ) ) ? '70' : '33' );

    /** Setup main overview page */
    $quanly_daily[ 'system' ][ 'pages' ][ 'core' ] = add_menu_page('Đại lý', 'ĐẠI LÝ', '', 'quanly_daily', array( 'DaiLy_Core', 'page_loader' ), '', $position );
    //* Setup child pages (first one is used to be loaded in place of 'CRM' */
    $quanly_daily[ 'system' ][ 'pages' ][ 'naptien' ] = add_submenu_page( 'quanly_daily', __( 'All People', 'quanly_daily' ), __( 'Nạp tiền', 'quanly_daily' ), 'WP-CRM: View Overview', 'naptien', array( 'DaiLy_Core', 'page_loader' ) );
    $quanly_daily[ 'system' ][ 'pages' ][ 'quanly' ] = add_submenu_page( 'quanly_daily', __( 'All People', 'quanly_daily' ), __( 'Quản lý', 'quanly_daily' ), 'WP-CRM: View Profiles', 'quanly', array( 'DaiLy_Core', 'page_loader' ) );
    $quanly_daily[ 'system' ][ 'pages' ][ 'khachhang' ] = add_submenu_page( 'quanly_daily', __( 'All People', 'quanly_daily' ), __( 'Khách hàng', 'quanly_daily' ), 'WP-CRM: View Profiles', 'khachhang', array( 'DaiLy_Core', 'page_loader' ) );
    $quanly_daily[ 'system' ][ 'pages' ][ 'dangky' ] = add_submenu_page( 'quanly_daily', __( 'All People', 'quanly_daily' ), __( 'Đăng ký', 'quanly_daily' ), 'WP-CRM: View Profiles', 'dangky', array( 'DaiLy_Core', 'page_loader' ) );
    $quanly_daily[ 'system' ][ 'pages' ][ 'giahan' ] = add_submenu_page( 'quanly_daily', __( 'All People', 'quanly_daily' ), __( 'Gia hạn', 'quanly_daily' ), 'WP-CRM: View Profiles', 'giahan', array( 'DaiLy_Core', 'page_loader' ) );

    if ( $quanly_daily[ 'configuration' ][ 'track_detailed_user_activity' ] == 'true' ) {
      $quanly_daily[ 'system' ][ 'pages' ][ 'user_logs' ] = add_submenu_page( 'quanly_daily', __( 'Activity Logs', 'quanly_daily' ), __( 'Activity Logs', 'quanly_daily' ), 'WP-CRM: View Detailed Logs', 'quanly_daily_detailed_logs', array( 'DaiLy_Core', 'page_loader' ) );
    }

    //** Migrate any pages that are under default user page */
    if ( $quanly_daily[ 'configuration' ][ 'replace_default_user_page' ] == 'true' ) {

      $quanly_daily_excluded_sub_pages = apply_filters( 'quanly_daily_excluded_sub_pages', array( 5, 10, 15 ) );
      if ( is_array( $submenu[ 'users.php' ] ) ) {

        foreach ( $submenu[ 'users.php' ] as $sub_key => $sub_pages_data ) {

          if ( in_array( $sub_key, $quanly_daily_excluded_sub_pages ) ) {
            continue;
          }

          //** Fix links (there may be a better way) */
          $sub_pages_data[ 2 ] = 'admin.php?page=' . $sub_pages_data[ 2 ];

          $submenu[ 'quanly_daily' ][ $sub_key ] = $sub_pages_data;
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
  function page_loader() {
    global $quanly_daily, $screen_layout_columns, $current_screen, $wpdb, $crm_messages, $user_ID, $quanly_daily_user;

    $file_path = Nap_Tien_Path . "/core/ui/{$current_screen->base}.php";

    if ( file_exists( $file_path ) ) {
      include $file_path;
    } else {
      echo "<div class='wrap'><h2>Error</h2><p>Template not found:" . $file_path . "</p></div>";
    }

  }

  /**
   * Modify admin body class on CRM  pages for CSS
   *
   * Note: The white-space on the end of 'quanly_daily ' is intentional.
   *
   * @return string|$request a modified request to query listings
   * @since 0.5
   *
   */
  function admin_body_class() {
    global $current_screen, $quanly_daily_user, $current_user;

    switch ( $current_screen->id ) {

      case 'toplevel_page_quanly_daily':
      case 'crm_page_quanly_daily_settings':

        $classes[ ] = 'quanly_daily';

        break;

      case 'crm_page_quanly_daily_add_new':

        $classes[ ] = 'quanly_daily';

        if ( $quanly_daily_user ) {

          if ( $current_user->data->ID == $quanly_daily_user[ 'ID' ][ 'default' ][ 0 ] ) {
            $classes[ ] = 'quanly_daily_my_profile';
          }

          $classes[ ] = 'quanly_daily_existing_user';
        } else {

          $classes[ ] = 'quanly_daily_new_user';

        }

        break;

    }

    if ( is_array( $classes ) ) {
      echo implode( ' ', $classes );
    }

  }
}