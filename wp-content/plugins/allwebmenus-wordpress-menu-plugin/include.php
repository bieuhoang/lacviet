<?php
/* automatically adds code to header.php file*/
function awm_add_code(){
    if (!defined ('TEMPLATEPATH') || !defined('STYLESHEETPATH') )
		wp_templating_constants();
	$file_path = locate_template(array("header.php"));
	if (!empty($file_path))
		$file = @file_get_contents( $file_path );
	else
		return false;
	//checking if htmp.tpl.php is writable
	$wantedPerms = octdec("0755");
	$actualPerms = octdec(substr(sprintf("%o",fileperms($file_path)),-4));
	
	if($actualPerms < $wantedPerms){
		if (!chmod ( $file_path , $wantedPerms )){
			return false;
		}
	}
	if (substr_count($file,'AWM_generate_linking_code')==0) {
		preg_match ( "/<body([^\?>])*(<\?([^>])*\?>([^>\?])*)*>/", $file, $matches );
		if (count($matches)>0) {
			$pieces = explode($matches[0],$file);
			if (count($pieces) == 2) {
				$file =$pieces[0].$matches[0]."\n<?php if (function_exists('AWM_generate_linking_code'))AWM_generate_linking_code(); ?>".$pieces[1];
				$fp = fopen($file_path, 'w');
				if (!$fp) {
					if ($actualPerms < $wantedPerms) @chmod ( $file_path , $actualPerms );
					return false;
				}
			   fwrite($fp, $file);
			   fclose($fp);
				if ($actualPerms < $wantedPerms) @chmod ( $file_path , $actualPerms );
			   return true;
			}
		} else {
			if ($actualPerms < $wantedPerms) @chmod ( $file_path , $actualPerms );
            return false;
        }
	}
	if ($actualPerms < $wantedPerms) @chmod ( $file_path , $actualPerms );
	return true;
}

/* This code sets the plugin up for first-time-run */
function awm_set_first_time_options() {
	add_option('AWM_menu_path', '/wp-content/plugins/allwebmenus-wordpress-menu-plugin/menu/');
	add_option('AWM_Checked', FALSE);
	add_option('AWM_Check_show', TRUE);
	add_option('AWM_show_welcome', TRUE);
	add_option('AWM_Checked_Date', '00');
	add_option('AWM_selected_tab', '0');
}

/* This code sets the default option values for a given tab */
function awm_set_default_option_values($awm_t) {
	global $wpdb, $awm_table_name;
    $wpdb->update( $awm_table_name, array( 'name' => (string) "menu".$awm_t ,'pages_name'=>'Pages','pages_ms'=>'main','posts_name'=>'Posts','posts_ms'=>'sub','categories_ms'=>'sub','categories_name'=>'Categories','type'=>'Dynamic','genre'=>'JS' ),array('id'=>$_POST['AWM_menu_id_'.$awm_t]) );
//	update_option('AWM_menu_path', '/menu/');
}

/* This code converts the options from old single-tab version to multi-tab */
function awm_convert_from_single_to_multi_tab() {
	global $awm_total_tabs;
	add_option('AWM_menu_name','nowayyouhavethisvalue');	// first create an impossible value
	if (get_option('AWM_menu_name')=='nowayyouhavethisvalue') {	// if the option now has this value, it did not exist (this means you already have the new version
		delete_option('AWM_menu_name');
	} else {										// else you had the old so you need to convert
		for ($awm_t=0; $awm_t<$awm_total_tabs; $awm_t++) {
			update_option('AWM_include_home_'.$awm_t, get_option('AWM_include_home'));
			update_option('AWM_pages_'.$awm_t, get_option('AWM_pages'));
			update_option('AWM_pages_ms_'.$awm_t, get_option('AWM_pages_ms'));
			update_option('AWM_pages_name_'.$awm_t, get_option('AWM_pages_name'));
			update_option('AWM_posts_'.$awm_t, get_option('AWM_posts'));
			update_option('AWM_posts_ms_'.$awm_t, get_option('AWM_posts_ms'));
			update_option('AWM_posts_name_'.$awm_t, get_option('AWM_posts_name'));
			update_option('AWM_posts_ids_'.$awm_t, get_option('AWM_posts_ids'));
			update_option('AWM_categories_'.$awm_t, get_option('AWM_categories'));
			update_option('AWM_categories_ms_'.$awm_t, get_option('AWM_categories_ms'));
			update_option('AWM_categories_name_'.$awm_t, get_option('AWM_categories_name'));
			update_option('AWM_categories_subitems_'.$awm_t, get_option('AWM_categories_subitems'));
			update_option('AWM_categories_subitems_no_'.$awm_t, get_option('AWM_categories_subitems_no'));
			update_option('AWM_hide_future_'.$awm_t, get_option('AWM_hide_future'));
			update_option('AWM_hide_protected_'.$awm_t, get_option('AWM_hide_protected'));
			update_option('AWM_excluded_cats_'.$awm_t, get_option('AWM_excluded_cats'));
			update_option('AWM_excluded_pages_'.$awm_t, get_option('AWM_excluded_pages'));
			update_option('AWM_Related_'.$awm_t, get_option('AWM_Related'));
		}

		$awm_mn = explode(",", get_option('AWM_menu_name'));
		for ($awm_i=0; $awm_i<count($awm_mn) && $awm_i<$awm_total_tabs; $awm_i++) {
			$awm_n = awm_fix_menu_name(trim($awm_mn[$awm_i]));
			update_option('AWM_use_custom_menu_'.$awm_t, FALSE);
			update_option('AWM_use_custom_menu_id_'.$awm_t, '');
			update_option('AWM_menu_name_'.$awm_i, $awm_n);
			update_option('AWM_menu_active_'.$awm_i, TRUE);
		}

		delete_option('AWM_include_home');
		delete_option('AWM_pages');
		delete_option('AWM_pages_ms');
		delete_option('AWM_pages_name');
		delete_option('AWM_posts');
		delete_option('AWM_posts_ms');
		delete_option('AWM_posts_name');
		delete_option('AWM_posts_ids');
		delete_option('AWM_categories');
		delete_option('AWM_categories_ms');
		delete_option('AWM_categories_name');
		delete_option('AWM_categories_subitems');
		delete_option('AWM_categories_subitems_no');
		delete_option('AWM_archives');
		delete_option('AWM_hide_future');
		delete_option('AWM_new_window');
		delete_option('AWM_show_post_date');
		delete_option('AWM_date_format');
		delete_option('AWM_hide_protected');
		delete_option('AWM_excluded_cats');
		delete_option('AWM_excluded_pages');
		delete_option('AWM_menu_name');
		delete_option('AWM_Related');
		delete_option('AWM_Related_name');
	}
}


/* This code converts the plugin to database version */
function awm_convert_to_database() {
    global $dataArray,$awm_table_name,$wpdb,$awm_total_tabs;
	if ( isset($awm_total_tabs) ) {
		if($wpdb->get_var("SHOW TABLES LIKE '$awm_table_name'") != $awm_table_name) {
			$sql = "CREATE TABLE " . $awm_table_name . " (
				id mediumint(9) NOT NULL AUTO_INCREMENT,
				name tinytext NOT NULL,
				active BOOLEAN NOT NULL DEFAULT  '0',
				custom_menu BOOLEAN NOT NULL DEFAULT  '0',
				custom_menu_id mediumint(9) NOT NULL DEFAULT  '0',
				position tinytext NOT NULL,
				type tinytext NOT NULL,
				include_home BOOLEAN NOT NULL DEFAULT  '1',
				pages BOOLEAN NOT NULL DEFAULT  '1',
				pages_ms tinytext NOT NULL DEFAULT  '',
				pages_name tinytext NOT NULL DEFAULT  '',
				excluded_pages tinytext,
				posts BOOLEAN NOT NULL DEFAULT  '0',
				posts_ms tinytext NOT NULL DEFAULT  '',
				posts_name tinytext NOT NULL DEFAULT  '',
				posts_ids tinytext,
				categories BOOLEAN NOT NULL DEFAULT  '0',
				categories_ms tinytext NOT NULL DEFAULT  '',
				categories_name tinytext NOT NULL DEFAULT  '',
				categories_subitems BOOLEAN NOT NULL DEFAULT  '1',
				categories_subitems_no tinyint NOT NULL DEFAULT  5,
				excluded_cats tinytext,
				hide_future BOOLEAN NOT NULL DEFAULT  '1',
				hide_protected BOOLEAN NOT NULL DEFAULT  '1',
				hide_private BOOLEAN NOT NULL DEFAULT  '1',
				related BOOLEAN NOT NULL DEFAULT '0',
				related_name tinytext NOT NULL DEFAULT '',
				UNIQUE KEY id (id)
			);";
			
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			dbDelta($sql);
			
			if ($wpdb->get_var("SHOW TABLES LIKE '$awm_table_name'") != $awm_table_name) {
				return "Could not create table. Please check your privileges";
			}
			if ( $awm_total_tabs == 0) {
				awm_create_new_menu(true);
				update_option('AWM_selected_tab', 0);
			}
		}
		for ($awm_t=0; $awm_t<$awm_total_tabs; $awm_t++) {
			$dataArray[$awm_t]['custom_menu'] = get_option('AWM_use_custom_menu_'.$awm_t);
			$dataArray[$awm_t]['custom_menu_id'] = get_option('AWM_use_custom_menu_id_'.$awm_t);
			$dataArray[$awm_t]['active'] = get_option('AWM_menu_active_'.$awm_t);
			$dataArray[$awm_t]['type'] = get_option('AWM_menu_type_'.$awm_t);
			$dataArray[$awm_t]['name'] = get_option('AWM_menu_name_'.$awm_t);
			$dataArray[$awm_t]['include_home'] = get_option('AWM_include_home_'.$awm_t);
			$dataArray[$awm_t]['pages'] = get_option('AWM_pages_'.$awm_t);
			$dataArray[$awm_t]['pages_ms'] =  get_option('AWM_pages_ms_'.$awm_t);
			$dataArray[$awm_t]['pages_name'] = get_option('AWM_pages_name_'.$awm_t);
			$dataArray[$awm_t]['excluded_pages'] =  get_option('AWM_excluded_pages_'.$awm_t);
			$dataArray[$awm_t]['posts'] = get_option('AWM_posts_'.$awm_t);
			$dataArray[$awm_t]['posts_ms'] = get_option('AWM_posts_ms_'.$awm_t);
			$dataArray[$awm_t]['posts_name'] = get_option('AWM_posts_name_'.$awm_t);
			$dataArray[$awm_t]['posts_ids'] = get_option('AWM_posts_ids_'.$awm_t);
			$dataArray[$awm_t]['categories'] = get_option('AWM_categories_'.$awm_t);
			$dataArray[$awm_t]['categories_ms'] =  get_option('AWM_categories_ms_'.$awm_t);
			$dataArray[$awm_t]['categories_name'] =  get_option('AWM_categories_name_'.$awm_t);
			$dataArray[$awm_t]['categories_subitems'] = get_option('AWM_categories_subitems_'.$awm_t);
			$dataArray[$awm_t]['categories_subitems_no'] = get_option('AWM_categories_subitems_no_'.$awm_t);
			$dataArray[$awm_t]['excluded_cats'] =  get_option('AWM_excluded_cats_'.$awm_t);
			$dataArray[$awm_t]['hide_future'] = get_option('AWM_hide_future_'.$awm_t);
			$dataArray[$awm_t]['hide_protected'] = get_option('AWM_hide_protected_'.$awm_t);
			$dataArray[$awm_t]['hide_private'] = get_option('AWM_hide_private_'.$awm_t);
			$dataArray[$awm_t]['Related'] =  get_option('AWM_Related_'.$awm_t);
			$dataArray[$awm_t]['position'] =  0;
					
			if ($wpdb->insert( $awm_table_name, $dataArray[$awm_t])) {
				delete_option('AWM_include_home_'.$awm_t);
				delete_option('AWM_pages_'.$awm_t);
				delete_option('AWM_pages_ms_'.$awm_t);
				delete_option('AWM_pages_name_'.$awm_t);
				delete_option('AWM_exluded_pages_'.$awm_t);
				delete_option('AWM_posts_'.$awm_t);
				delete_option('AWM_posts_ms_'.$awm_t);
				delete_option('AWM_posts_name_'.$awm_t);
				delete_option('AWM_posts_ids_'.$awm_t);
				delete_option('AWM_categories_'.$awm_t);
				delete_option('AWM_categories_ms_'.$awm_t);
				delete_option('AWM_categories_name_'.$awm_t);
				delete_option('AWM_categories_subitems_'.$awm_t);
				delete_option('AWM_categories_subitems_no_'.$awm_t);
				delete_option('AWM_archives_'.$awm_t);
				delete_option('AWM_hide_future_'.$awm_t);
				delete_option('AWM_new_window_'.$awm_t);
				delete_option('AWM_show_post_date_'.$awm_t);
				delete_option('AWM_date_format_'.$awm_t);
				delete_option('AWM_hide_protected_'.$awm_t);
				delete_option('AWM_excluded_cats_'.$awm_t);
				delete_option('AWM_menu_name_'.$awm_t);
				delete_option('AWM_Related_'.$awm_t);
			}
		}
		
		delete_option('AWM_total_menus');
		if($wpdb->get_var("show columns from $awm_table_name LIKE 'related_name'")!= 'related_name'){
			$wpdb->query("ALTER TABLE ".$awm_table_name." ADD related_name tinytext NOT NULL DEFAULT ''");
			$wpdb->query("UPDATE $awm_table_name SET related_name='Related Posts'");
		}
		$awm_total_tabs = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $awm_table_name",null));
	}
	return "";
}

function add_genre_column() {
	global $awm_total_tabs, $awm_table_name, $wpdb;
	if($wpdb->get_var("show columns from $awm_table_name LIKE 'menu_genre'")!= 'menu_genre'){
		$wpdb->query("ALTER TABLE ".$awm_table_name." ADD menu_genre tinytext NOT NULL DEFAULT ''");
		$wpdb->query("ALTER TABLE ".$awm_table_name." ADD menu_structure text NOT NULL DEFAULT ''");
		$wpdb->query("UPDATE $awm_table_name SET menu_genre='JS'");
	}
}

function add_revision_column() {
	global $awm_total_tabs, $awm_table_name, $wpdb;
	if($wpdb->get_var("show columns from $awm_table_name LIKE 'menu_revisions'")!= 'menu_revisions'){
		$wpdb->query("ALTER TABLE ".$awm_table_name." ADD menu_revisions tinytext NOT NULL DEFAULT ''");
		$wpdb->query("UPDATE $awm_table_name SET menu_revisions='1'");
	}
}

/* This code creates new menu */
function awm_create_new_menu($firstTime = false) {
	update_option('AWM_show_welcome', FALSE);
	global $wpdb,$awm_table_name;
	$rows_affected = $wpdb->insert($awm_table_name, array('name' => ""));
	$i = 1;
	while ($wpdb->get_var("SELECT COUNT(*) as cnt from $awm_table_name where name LIKE 'menu".$i."'")) {
		$i++;
	}
	$wpdb->update( $awm_table_name, array( 'active' => $firstTime,'name' => (string) "menu".$i ,'pages_name'=>'Pages','pages_ms'=>'main','posts_name'=>'Posts','posts_ms'=>'sub','categories_ms'=>'sub','categories_name'=>'Categories','type'=>'Dynamic' ),array('id'=>$wpdb->insert_id) );
	return '<div class="updated fade"><p><strong>Additional menu succesfully created.</strong></p></div>';
}

function awm_delete_menu(){
	update_option('AWM_show_welcome', FALSE);
    global $wpdb,$awm_table_name, $awm_total_tabs;
    if ($awm_total_tabs == 1)
        return  "This is the last menu. You cannot delete it.";
    $wpdb->query("DELETE from $awm_table_name where id =".(int) $_POST['AWM_menu_id']);
    awm_delete_widget_instance((int) $_POST['AWM_menu_id'], true);
	if ((int) $_POST["AWM_selected_tab_c"] == ($awm_total_tabs-1) &&  get_option('AWM_selected_tab') == (int) $_POST["AWM_selected_tab_c"])
		update_option('AWM_selected_tab', ((int) $_POST["AWM_selected_tab_c"])-1);
	return  "Menu deleted succesfully.";
}           

/* This code saves the form values */
function awm_update_option_values() {
	update_option('AWM_show_welcome', FALSE);
	global $awm_total_tabs,$awm_table_name,$wpdb;
	
	if ($awm_total_tabs){
        $checkNames = array();
        $duplArray = array();
        $message = "";
        //checking for name duplication
        for ($awm_t=0; $awm_t<$awm_total_tabs; $awm_t++) {
            $fndIndex = array_search((string) $_POST["AWM_menu_name_".$awm_t], $checkNames);
            
			if ($fndIndex===false) {
                $checkNames[$awm_t] = (string) $_POST["AWM_menu_name_".$awm_t];
			} else {
				if (array_key_exists((string)$_POST["AWM_menu_name_".$awm_t],  $duplArray)) {
					$duplArray[(string)$_POST["AWM_menu_name_".$awm_t]][] = $awm_t;
				} else {
					$duplArray[(string)$_POST["AWM_menu_name_".$awm_t]] =  array($fndIndex, $awm_t);
				}
			}
		}
        
		if (count($duplArray)) {
			foreach ($duplArray as $name => $keys) {
				$message .= "Name ".$name." exists in ".count($keys)." menus. Check name field in ";
				foreach ($keys as  $i=>$tab) {
					if ($i != count($keys)-1)
						$message .= "<a href=\"javascript:void(0)\" onclick=\"awm_show_tab(".$tab.");window.location.hash = '#AWM_tab_header_".$tab."';\">".($tab + 1)."</a>, ";
					else
						$message .= "<a href=\"javascript:void(0)\" onclick=\"awm_show_tab(".$tab.");window.location.hash =  '#AWM_tab_header_".$tab."';\">".($tab + 1)."</a> ";
				}
				$message .="menu tabs.<br />";
			}
			$message .= "Names must be unique for each menu.";
			return $message;
        }
		for ($awm_t=0; $awm_t<$awm_total_tabs; $awm_t++) {
			$dataArray[$awm_t]['custom_menu'] = (bool) $_POST["AWM_use_custom_menu_".$awm_t];
			$dataArray[$awm_t]['custom_menu_id'] = (string) $_POST["AWM_use_custom_menu_id_".$awm_t];
			$dataArray[$awm_t]['active'] =  isset($_POST["AWM_menu_active_".$awm_t])?true:false;
			$dataArray[$awm_t]['position'] = (string) $_POST["awm_menu_position_".$awm_t];
			//if user choses awm_widget position, we should create a new 'inactive' widget instance
			if ($dataArray[$awm_t]['position'] == 'awm_widget' && $dataArray[$awm_t]['active'] ){
				awm_create_widget_instance($awm_t);
			} else {
				awm_delete_widget_instance($awm_t);
			}
			$dataArray[$awm_t]['type'] =  (string) $_POST["AWM_menu_type_".$awm_t];
			$dataArray[$awm_t]['name'] =  (string) $_POST["AWM_menu_name_".$awm_t];
			$dataArray[$awm_t]['include_home'] =  isset( $_POST["AWM_include_home_".$awm_t])?true:false;
			$dataArray[$awm_t]['pages'] =  isset( $_POST["AWM_pages_".$awm_t])?true:false;
			$dataArray[$awm_t]['pages_ms'] =  (string) $_POST["AWM_pages_ms_".$awm_t];
			if (isset ($_POST["AWM_pages_name_".$awm_t]))
                $dataArray[$awm_t]['pages_name'] =  (string) $_POST["AWM_pages_name_".$awm_t];
			$dataArray[$awm_t]['excluded_pages'] =  (string) $_POST["AWM_excluded_pages_".$awm_t];
			$dataArray[$awm_t]['posts'] =  isset($_POST["AWM_posts_".$awm_t])?true:false;
			$dataArray[$awm_t]['posts_ms'] =  (string) $_POST["AWM_posts_ms_".$awm_t];
			if (isset ($_POST["AWM_posts_name_".$awm_t]))
				$dataArray[$awm_t]['posts_name'] =  (string) $_POST["AWM_posts_name_".$awm_t];
			$dataArray[$awm_t]['posts_ids'] =  (string) $_POST["AWM_posts_ids_".$awm_t];
			$dataArray[$awm_t]['categories'] =  isset ($_POST["AWM_categories_".$awm_t])? true :false;
			$dataArray[$awm_t]['categories_ms'] =  (string) $_POST["AWM_categories_ms_".$awm_t];
			if (isset ($_POST["AWM_categories_name_".$awm_t]))
				$dataArray[$awm_t]['categories_name'] =  (string) $_POST["AWM_categories_name_".$awm_t];
			$dataArray[$awm_t]['categories_subitems'] =  isset ($_POST["AWM_categories_subitems_".$awm_t])? true :false;
			if (isset ($_POST["AWM_categories_subitems_no_".$awm_t]))
				$dataArray[$awm_t]['categories_subitems_no'] =  (string) $_POST["AWM_categories_subitems_no_".$awm_t];
			$dataArray[$awm_t]['excluded_cats'] =  (string) $_POST["AWM_excluded_cats_".$awm_t];
			$dataArray[$awm_t]['hide_future'] =  isset($_POST["AWM_hide_future_".$awm_t])? true : false;
			$dataArray[$awm_t]['hide_protected'] =  isset($_POST["AWM_hide_protected_".$awm_t])?true : false;
			$dataArray[$awm_t]['hide_private'] =  isset($_POST["AWM_hide_private_".$awm_t])?true : false;
			$dataArray[$awm_t]['related'] = isset( $_POST["AWM_Related_".$awm_t])?true:false;
			if (isset ($_POST["AWM_Related_name_".$awm_t]))
				$dataArray[$awm_t]['related_name'] = $_POST["AWM_Related_name_".$awm_t];
			$wpdb->update($awm_table_name,$dataArray[$awm_t],array( 'id' => (int) $_POST["AWM_menu_id_".$awm_t] ));
		}
		update_option('AWM_menu_path', (string) $_POST["AWM_menu_path_0"]);
		update_option('AWM_selected_tab', (string) $_POST["AWM_selected_tab"]);
        return "Settings updated!";
	} else return "There are no menus. You can create one using the appropriate button.";
}

/* 
 * Helper function that creates AllWebMenu Widget Instances
 */
function awm_create_widget_instance($awm_t) {
	//retrieve all instances of widget.
	$a = get_option('widget_widget_allwebmenus');
	//a flag to find if this instance is already setted
	$a2 = $a;
	$notSet = true;
	//variable to hold where is the last instance saved
	$_keyPos = count($a) - 2;
	$i = 0;
	$keyToSave = -1;
	$keyThatExists = -1;
	foreach ( (array) $a as $_key=>$m_instance) {
		//save the key of the last instance
		if ($i == $_keyPos) { $keyToSave =  $_key; }
		$i++;
		
		if (!empty($m_instance['div_name']) )
			if ($m_instance['div_name'] == (int) $_POST["AWM_menu_id_".$awm_t]) {
				//if it's already defined set the flag to false
				$notSet = false;
				$keyThatExists = $_key;
				break;
			}
	}
	if (count($a) == 1) { $keyToSave = 1; }
	if ($notSet && $keyToSave!=-1) {
		//create the new instace
		$a2[ $keyToSave + 1 ] = array ('div_name' => (int) $_POST["AWM_menu_id_".$awm_t] );
		ksort($a2 , SORT_STRING);
		update_option('widget_widget_allwebmenus',$a2);
		//adds the new instance to inactive widgets
		$sidebar_widgets = (get_option('sidebars_widgets'));
		$sidebar_widgets['wp_inactive_widgets'][count($sidebar_widgets['wp_inactive_widgets'])] = 'widget_allwebmenus-'.($keyToSave + 1);
		update_option('sidebars_widgets', $sidebar_widgets);
	}
	/*
	else if ($notSet && $keyThatExists!=-1){

	$alreadyFlag = true;
	$sidebar_widgets = (get_option('sidebars_widgets'));
	$sidebar_widgets2 = $sidebar_widgets = (array)$sidebar_widgets;
	//loop through sidebats to find instances of the widghet
	foreach ($sidebar_widgets as $name_of_sidebar=>$sidebar){
	foreach ((array)$sidebar as $sidebar_i=>$widget_instance_name){
	if ($widget_instance_name == 'widget_allwebmenus-'.$keyThatExists){
	$alreadyFlag = false;
	}
	}
	}

	if ($alreadyFlag)
	{
	$sidebar_widgets = (get_option('sidebars_widgets'));
	$sidebar_widgets['wp_inactive_widgets'][count($sidebar_widgets['wp_inactive_widgets'])] = 'widget_allwebmenus-'.($keyThatExists);
	update_option('sidebars_widgets', $sidebar_widgets);
	}

	}

	*/
}
/*
 * Helper function that deletes a widget instance
 */
function awm_delete_widget_instance($awm_t, $isId = false) {
	//if not an awm_widget we should check if this menu
	//widget instance exists so to remove it from the instances and sidebars
	$id = -1;
	if ($isId) $id = $awm_t;
	else $id = (int) $_POST["AWM_menu_id_".$awm_t];
	
	$notSet = TRUE;
	$notSet2 = TRUE;
	$a = get_option('widget_widget_allwebmenus');
	$a2 = $a = (array) $a;
	//array to hold keys to remove in case we find one or more instances
	$keysToRemove = array();
	
	foreach ( $a as $_key=>$m_instance) {
		if (!empty($m_instance['div_name']) )
			if ($m_instance['div_name'] == $id) {
				$notSet = false;
				unset ($a2[$_key])  ;
				$keysToRemove[] = $_key ;
			}
	}
	if (!$notSet) {
		ksort($a2 , SORT_STRING);
		update_option('widget_widget_allwebmenus',$a2);
		//retrieve the sidebars
		$sidebar_widgets = (get_option('sidebars_widgets'));
		$sidebar_widgets2 = $sidebar_widgets = (array)$sidebar_widgets;
		//loop through sidebats to find instances of the widghet
		foreach ($sidebar_widgets as $name_of_sidebar=>$sidebar) {
			foreach ((array)$sidebar as $sidebar_i=>$widget_instance_name) {
				foreach ($keysToRemove as $keyToRemove)
					if ($widget_instance_name == 'widget_allwebmenus-'.$keyToRemove) {
						unset($sidebar_widgets2[$name_of_sidebar][$sidebar_i]);
						$notSet2 = false;
					}
			}
		}
		if (!$notSet2) {
			ksort($sidebar_widgets2, SORT_STRING);
			update_option('sidebars_widgets', $sidebar_widgets2);
		}
	}
}

/* The function that uploads the zip file */
function awm_update_zip() {
 	global $awm_table_name,$wpdb;
	global $awm_total_tabs;
	
	add_filter('upload_mimes', 'addUploadMimes');
	function addUploadMimes($mimes) {
		$mimes = array_merge($mimes, array(
			'zip' => 'application/zip'
		));
		return $mimes;
	}

	update_option('AWM_selected_tab', (string) $_POST["AWM_selected_tab_c"]);
	foreach ( $_FILES as $src ) {
		if ($src['size']) {
			$folder = get_option( 'AWM_menu_path' );
			$or_name = $wpdb->get_var("SELECT name from $awm_table_name where id = ".(int) $_POST["AWM_menu_id"]);
			$or_name_full = "awm".$or_name.".zip";
			if ($src['name'] != $or_name_full) return "Error: Wrong filename (".$src['name']."). It should be: '".$or_name_full."'.";
			if (file_exists (ABSPATH.$folder.$src['name'])) unlink ( ABSPATH.$folder.$src['name'] );
			if (!file_exists(ABSPATH.$folder)) {
				if (!mkdir(ABSPATH.$folder))
					return "Error: The folder '".$folder."' does not exist and could not be automatically created. <br>You should create it by yourself and make sure that it has '757' permissions.";
			}
			$wantedPerms = octdec("0755");
			$actualPerms = octdec(substr(sprintf("%o",fileperms(ABSPATH.$folder)),-4));
			if($actualPerms < $wantedPerms) {
				if (!chmod ( ABSPATH.$folder , $wantedPerms )) {
					return "Error: Cannnot extract files to folder: '".$folder."'. Please make sure that this folder has '757' permissions";
				}
			}
			define('UPLOADS', $folder);
			$overrides = array( 'test_form' => false);
			if ($uploads_use_yearmonth_folders = get_option( 'uploads_use_yearmonth_folders' ))
				update_option('uploads_use_yearmonth_folders', 0);
			define('FS_METHOD', 'direct');
			WP_Filesystem();
			$file = wp_handle_upload( $src, $overrides );
			if ($uploads_use_yearmonth_folders == 1)
				update_option('uploads_use_yearmonth_folders', 1);
			if (function_exists(unzip_file)) {
				$result = unzip_file($file['file'], ABSPATH.$folder );
				if (is_wp_error($result)){
					@unlink($file['file']);
					if ($actualPerms < $wantedPerms) @chmod ( ABSPATH.$folder , $actualPerms );
					return "Error: Unzipping file failed.";
				}
				$struct = "";
				$gen = "JS";
				$revs = "1";
				// if the zip contains the "info.txt" then read-in the genre, else set as "JS"
				if (file_exists (ABSPATH.$folder."info.txt")) {
					if ($awm_menuinfofile = fopen(ABSPATH.$folder."info.txt", 'r')) {
						while($tmp = fgets($awm_menuinfofile, filesize(ABSPATH.$folder."info.txt"))) {
							if (substr($tmp,0,7)=="Genre: ") $gen = substr($tmp,7);
							if (trim($tmp)=="***Start Structure Code***") while (trim($tmp=fgets($awm_menuinfofile, filesize(ABSPATH.$folder."info.txt")))!="***End Structure Code***") $struct .= $tmp;
							if (substr($tmp,0,12)=="Responsive: ") $revs = substr($tmp,12);
						}
					}
				}
				$gen = trim($gen);
				$wpdb->query("UPDATE $awm_table_name SET menu_genre='$gen', menu_structure='$struct', menu_revisions='$revs' WHERE name='$or_name'");
			} else {
				@unlink($file['file']);
				if ($actualPerms < $wantedPerms) @chmod ( ABSPATH.$folder , $actualPerms );
				return "ZIP upload requires WordPress version 2.5 or greater.";
			}
			@unlink($file['file']);
			if ($actualPerms < $wantedPerms) @chmod ( ABSPATH.$folder , $actualPerms );
			return "Menu files successfully uploaded.";
		}
	}
	update_option('AWM_selected_tab', (string) $_POST["AWM_selected_tab_c"]);
}

/* This code corrects the menu name if it has paths or extension */
function awm_fix_menu_name($awm_m) {
	$awm_name = $awm_m;
	if (strpos($awm_name,"/")>-1 || strpos($awm_name,".js")>-1 ) {
		$awm_nm = explode("/", $awm_name);
		$awm_name = $awm_nm[1];
		$awm_nm = explode(".", $awm_name);
		$awm_name = $awm_nm[0];
	}
	return $awm_name;
}

/* This code checks for updated versions of the AllWebMenus software and informs the user if necessary */
function AWM_check()
{
	global $awm_url, $awm_total_tabs, $wpdb, $awm_table_name;
	$awm_the_msg = array();
	$awm_realpath = ABSPATH. get_option('AWM_menu_path');
	$awm_path = get_bloginfo('url') . get_option('AWM_menu_path');
	
	error_reporting(0);
	$myrows = $wpdb->get_results( "SELECT * FROM $awm_table_name ORDER BY id ASC" );
	for ($awm_t=0; $awm_t<$awm_total_tabs; $awm_t++) {
		$awm_the_msg[$awm_t] = "";
		if (!$myrows[$awm_t]->active) continue;
		$awm_name = trim($myrows[$awm_t]->name).".js";
		
		if (! ($awm_menufile = fopen($awm_realpath . $awm_name, 'r'))) {
			$awm_the_msg[$awm_t] = "Menu ".$myrows[$awm_t]->name." was not found at: ". $awm_path . $awm_name;
			continue;
		} elseif (! ($awm_mfile = fread($awm_menufile, filesize($awm_realpath . $awm_name)))) {
			$awm_the_msg[$awm_t] = "Could not read menu at: ". $awm_path . $awm_name;
			continue;
		}
		$awm_bNo = explode('awmLibraryBuild=', $awm_mfile);
		if ($awm_bNo[1]==null) {
			$awm_the_msg[$awm_t] = "Could not read menu at: ". $awm_path . $awm_name;
			continue;
		}
		$awm_bNo = explode(';', $awm_bNo[1]);
		$awm_buildNo = $awm_bNo[0];
		$awm_hNo = explode('awmHash=\'', $awm_mfile);
		if ($awm_hNo[1]==null) {
			$awm_the_msg[$awm_t] = "Could not read menu at: ". $awm_path . $awm_name;
			continue;
		}
		$awm_hNo = explode('\'', $awm_hNo[1]);
		$awm_HashNo = $awm_hNo[0];
		
		$awm_params = "plugin=wordpress&build=$awm_buildNo&hash=$awm_HashNo&rand=". rand(1,10000) ."&domain=". get_bloginfo('url');
		
		if (function_exists('curl_init')) {
			if (! ($awm_tmp = awm_geturl($awm_params))) {
				$awm_the_msg[$awm_t] = "Could not retrieve version information for ".$myrows[$awm_t]->name.". Please <a href='mailto:support@likno.com?subject=WordPress: Error while retrieving version info'>contact Likno</a> for more information.";
			} else {
				$awm_the_msg[$awm_t] = $awm_tmp;
			}
			continue;
		} else {
			$awm_the_msg[$awm_t] = '<iframe src='. $awm_url .'?'. $awm_params .' width="600px" height="80px"></iframe>';
		}
	}
	
	$awm_has_msg = false;
	for ($awm_t=0; $awm_t<$awm_total_tabs; $awm_t++) { if ($awm_the_msg[$awm_t] != "") {$awm_has_msg = true; break;} }
	
	$awm_the_full_msg = "";
	if ($awm_has_msg) {
		$awm_the_full_msg = "<div class='updated fade'>";
		for ($awm_t=0; $awm_t<$awm_total_tabs; $awm_t++) if ($awm_the_msg[$awm_t] != "") $awm_the_full_msg .= "<br><strong>Note about ".$myrows[$awm_t]->name.": </strong><br>".$awm_the_msg[$awm_t]."<br>";
		$awm_the_full_msg .= "<br><input type='button' value='Hide Notifications' onclick='theform.theaction.value=\"hide_msg\"; theform.submit();'/><br>&nbsp;</div>";
	}
	
	update_option('AWM_Checked', TRUE);
	update_option('AWM_Checked_Date', date(d));
	
	error_reporting(1);
	
	return $awm_the_full_msg;
}

/* Helper code for above function */
global $awm_url;
$awm_url ="http://www.likno.com/addins/plugin-check.php";
function awm_geturl($awm_params)
{
    global $awm_url;
    $awm_ch = curl_init();
    curl_setopt ($awm_ch, CURLOPT_URL,$awm_url);
	curl_setopt($awm_ch, CURLOPT_POST, 1);
    curl_setopt($awm_ch, CURLOPT_POSTFIELDS, $awm_params);
    
    curl_setopt($awm_ch, CURLOPT_RETURNTRANSFER, 1);
    $awm_postResult = curl_exec($awm_ch);

    return $awm_postResult;
}

?>