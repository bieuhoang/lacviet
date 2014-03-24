<?php
/*
Plugin Name: AllWebMenus WordPress Menu Plugin
Plugin URI: http://www.likno.com/addins/wordpress-menu.html
Description: WordPress plugin for the AllWebMenus PRO Javascript Menu Maker - Create stylish drop-down menus or sliding menus for your blogs!
Version: 1.1.19
Author: Likno Software
Author URI: http://www.likno.com/ 
*/

/*

NOTE:

This plugin is licensed under the GNU General Public License (GPL).

As such, you may use the source code of this plugin as you wish.
This plugin is used as a bridge to a non-GPL licensed software (AllWebMenus PRO) that is a property of Likno Software.

The license of AllWebMenus PRO states that a WordPress Menu (a menu which structure is retrieved using this plugin and compiled with AllWebMenus PRO using the WordPress Add-In) can be used in **one single domain**.

Thus, the part of the code below that confirms that the menu is used only in one domain **cannot** be changed/removed.

*/

//globalization and initialization;
global $awmPluginInstance;
$awmPluginInstance = new AWM_Plugin();
/*
 *class: AWM_Plugin
 * class of plugin
 * has all the variables and the functions needed for the plugin
 *
 *  */

class AWM_Plugin
{

    var $optionsMessage, $databaseMessage, $AWM_ver , $awm_total_tabs, $awm_is_yarpp_enabled, $wpdb, $dataArray,$awm_table_name,$awm_wp_nav_array;
   
    function AWM_Plugin(){
    global $wpdb;
    $this->optionsMessage = "";
    $this->wpdb = $wpdb;
    add_action( 'init', array($this,'AWM_init_hook' ));
    add_action( 'admin_init',  array($this,'AWM_addmin_hook' ));
    add_action('admin_menu', array($this,'AWM_add_option_pages'));
    global $wp_version;
    if((float)$wp_version>=2.8){
		include_once WP_PLUGIN_DIR.'/allwebmenus-wordpress-menu-plugin/widgetClass.php';
		add_action('widgets_init', create_function('', 'return register_widget("Widget_AllWebMenus");'));
    }
}

/*
 * Initialization of plugin.
 */
function AWM_init_hook(){
	global $dataArray,$awm_table_name, $awm_total_tabs, $AWM_ver, $dataArray, $awm_is_yarpp_enabled;
	/*
	 * Load the include files
	 */
	include_once WP_PLUGIN_DIR.'/allwebmenus-wordpress-menu-plugin/menu_helper.php';
	include_once WP_PLUGIN_DIR.'/allwebmenus-wordpress-menu-plugin/include.php';
	
	$this->awm_table_name = $awm_table_name = $this->wpdb->prefix . "awm";
	$this->dataArray = $dataArray = array();
	$this->AWM_ver = $AWM_ver = '1.1.19';
	
	$this->awm_total_tabs = $awm_total_tabs = get_option("AWM_total_menus",(int) 0);
	//if ($_POST["AWM_selected_tab"]=="") $_POST["AWM_selected_tab"]="1";
	// set the first time options (if they do not already exist)
	awm_set_first_time_options();
	
	// add linking code to header.php file
	if (isset($_GET['theaction']) && $_GET['theaction']=='show_addcode') {
		update_option("AWM_code_check", 1);
	}
	$check = get_option("AWM_code_check",1);
	if ($check && !awm_add_code()) {
		$nonce= wp_create_nonce('my-nonce');
		$this->optionsMessage =
			"<div class=\"updated fade\" style=\"margin-top: 20px;\"><p><strong>Linking code could not be added automatically. You have to add it by yourself. Open the \"header.php\" file (found at \"SITEROOT/wp-content/themes/YourSiteTheme\") and add this code <br /><textarea readonly cols=50 rows=4><?php if (function_exists('AWM_generate_linking_code'))\nAWM_generate_linking_code(); ?></textarea><br /> right after the &lt;body&gt; tag.</strong></p>
				<form method=\"post\" id=\"the_add_code_form\" name=\"the_add_code_form\" action=\"".plugins_url('actions.php',__FILE__)."\">
					<input type=\"hidden\" name=\"theaction\" value=\"hide_addcode\"/>
					<input type=\"hidden\" name=\"_wpnonce\" value=\"$nonce\"/>
					<input type=\"hidden\" name=\"ref\" value=\" ". admin_url("options-general.php?page=allwebmenus-wordpress-menu-plugin/allwebmenus-wordpress-menu.php") ."\"/>
					<input type=\"submit\"  value=\"Hide notification\"/>
				</form>
			</div>
			";
	}
	// Check if already had the plugin when it was single-tab and convert values
	awm_convert_from_single_to_multi_tab();
	$this->databaseMessage  = awm_convert_to_database();
	if (!empty($this->databaseMessage))
		$this->databaseMessage = "<div class=\"updated fade\" style=\"margin-top: 20px;\"><strong>".$this->databaseMessage."</strong></div>";
	add_genre_column();
	add_revision_column();
	
	$this->awm_total_tabs = $awm_total_tabs;
	$this->awm_is_yarpp_enabled = $awm_is_yarpp_enabled = in_array('yet-another-related-posts-plugin/yarpp.php', get_option('active_plugins'));
	$awm_plugin = plugin_basename(__FILE__);
	add_filter("plugin_action_links_$awm_plugin", array($this, 'awm_wp_settings_link' ));
	
	// Check if you need to check for updates
	if ((get_option('AWM_Checked_Date') <= (date('d') - 15)) || (get_option('AWM_Checked_Date') === '00')) {
		update_option('AWM_Check_Show', TRUE);
	}
}
 /*
 * Adds link to settings page
 */
function awm_wp_settings_link($awm_links) {
	$awm_settings_link = '<a href="options-general.php?page=allwebmenus-wordpress-menu-plugin/allwebmenus-wordpress-menu.php">Settings</a>';
	array_unshift($awm_links, $awm_settings_link);
        $check = get_option("AWM_code_check",1);
	if (!$check){
            $awm_linking_link2 ='<a href="'.admin_url("options-general.php?page=allwebmenus-wordpress-menu-plugin/allwebmenus-wordpress-menu.php&theaction=show_addcode").'">Activate linking code check</a>';
             $awm_links[] = $awm_linking_link2;
        }
            return $awm_links;
}
/*
 * Initialization of the admin panel.
 */
function AWM_addmin_hook() {
	if (!session_id()) session_start();
	/* Register our stylesheet. */
	wp_register_script( 'AWMScript', plugins_url('/script.js', __FILE__) );
	wp_register_style( 'AWMStylesheet', plugins_url('/stylesheet.css',__FILE__ ));
}

/*
 * Add admin panel styles AND scripts
 */
function AWM_admin_styles() {
	/*
	 * It will be called only on your plugin admin page, enqueue our stylesheet here
	 */
	wp_enqueue_style( 'AWMStylesheet' );
	wp_enqueue_script( 'AWMScript' );
}
/*
 * Add options page
 */
function AWM_add_option_pages() {
	if (function_exists('add_options_page')) {
		$page = add_options_page('AllWebMenus WordPress Menu Plugin', 'AllWebMenus-WP-Menu', 8, __FILE__, array($this,'AWM_options_page'));
	}
   add_action( 'admin_print_styles-' . $page,  array($this,'AWM_admin_styles' ));
}

/*
 * Generate options page
 */
function AWM_options_page() {
    echo $this->databaseMessage;
	echo $this->optionsMessage;
	$locations = array();
	if (function_exists('get_registered_nav_menus'))
		$locations = (array) get_registered_nav_menus();
	$myrows = $this->wpdb->get_results( "SELECT * FROM $this->awm_table_name ORDER BY id ASC" );
?>
	<script>
		function awm_set_path(x) {
			for (var i=0; i<<?php echo $this->awm_total_tabs; ?>; i++) if (i!=x) document.getElementById('AWM_menu_path_'+i).value = document.getElementById('AWM_menu_path_'+x).value;
		}
	</script>
	<div style="max-width: 980px; margin-left: 15px;">

	<span class="wrap">
	<br>
	<h2>AllWebMenus WordPress Menu Plugin v<?php echo $this->AWM_ver; ?></h2>
	<div id="AWM_welcome_title" onclick="awm_show_welcome();" style="cursor: pointer;">
		Note: The plugin requires the use of the "AllWebMenus" commercial application (version 5.3.926+). <span id="AWM_welcome_title_info"><a href="javascript:void(0);">Click for more info.</a></span>
	</div>
		<p id="awm_upload_anchor">For information and updates, please visit:
		<a href="http://www.likno.com/addins/wordpress-menu.html">http://www.likno.com/addins/wordpress-menu.html</a></p>	

<?php
	/* Display a message in the Options page if the menu version is outdated
	NOTE: we do not check date as we have to recheck to display the message to the admin */
	if (get_option('AWM_Check_show')) {
		$AWM_buildText = AWM_check();
		if ($AWM_buildText != '') echo $AWM_buildText;
		else update_option('AWM_Check_show', FALSE);
	}
?>
	</span>
	<div id="AWM_welcome_screen">	<!-- START OF WELCOME SCREEN -->
		<h1>Welcome to our WordPress Menu plugin&nbsp;&nbsp;&nbsp;<button class="button" type="button" style="position: relative; top: -3px;" onclick="awm_show_welcome(false);">Move to Settings &raquo;</button></h1>
<p><br>This plugin acts as a <strong>"bridge"</strong> between...</p>
<table>
	<tr>
		<td valign="top" style="text-align: center;">
<p>
<span style="font-size: 14px;">...the <strong>AllWebMenus Pro application</strong>...</span><br />
 a powerful windows application for <br>creating any kind of navigation menu</p>
<img alt="javascript menu / css menu builder" height="203" src="<?php echo get_bloginfo('url');?>/wp-content/plugins/allwebmenus-wordpress-menu-plugin/awm5snap-wordpress-menu-plugin-info.jpg" style="padding-left: 28px;"width="270" />
<p><a href="http://www.likno.com/allwebmenusinfo.html">Features</a> &nbsp; <a href="http://www.likno.com/download.html">Download</a> &nbsp; <a href="http://www.likno.com/examples.html">Menu Examples</a> &nbsp; <a href="http://www.likno.com/awmstyles.php">Menu Themes</a> &nbsp; <a href="http://www.likno.com/awmregister.php">Purchase</a></p>
		</td>
		<td valign="top"><span style="font-size: 36px; padding: 35px;"><br>&amp;</span></td>
		<td valign="top" style="text-align: center;">
		<p>
		<span style="font-size: 14px;">...your <strong>WordPress blog</strong></span></p>
		<br><br>
<img alt="blog" height="203" src="<?php echo get_bloginfo('url');?>/wp-content/plugins/allwebmenus-wordpress-menu-plugin/v5_addins-wordpress-menu-plugin-info-blog.jpg" width="270"/>
		</td>
	</tr>
</table>

<p>
&nbsp;</p>
<p style="font-size: 14px; font-weight: bold;">How?</p>
<table style="width: 100%">
	<tr>
		<td valign="top" style="width: 530px">
<p><strong><br />
1.</strong> Use this plugin to <strong>retrieve items from your blog*.</strong><br>
&nbsp;&nbsp;&nbsp;&nbsp;(such as posts, pages, etc.)<br><br>
		<strong>2.</strong> <strong>Paste</strong> these blog items into AllWebMenus and create stylish, feature-rich navigation 
menus based on them. Fully customize these menus with styles, behaviors, effects, 
designs of your choice and <a href="http://www.likno.com/allwebmenusinfo.html">
many more!</a><br><br>
		<strong>3.</strong> Use this plugin to <strong>upload</strong> your menus 
(multiple menus also supported) to your blog. <strong>Done!</strong></p>
<br><br><p style="font-size: 11px;"><em>*Also add </em> <strong><em>non-wordpress menu items</em></strong><em> (i.e. your own "external" 
items, not posts or pages), that use external or internal links, html-rich 
content, etc. </em> </p>
		</td>
		<td><img alt="wordpress javascript menu css menu plugin" height="306" src="<?php echo get_bloginfo('url');?>/wp-content/plugins/allwebmenus-wordpress-menu-plugin/v5_addins-wordpress-menu-plugin-info.jpg" width="217" /></td>
	</tr>
</table>
		<div style="padding-left:200px;"><button class="button" type="button" onclick="awm_show_welcome(false);">Move to Settings &raquo;</button></div>
	</div>		<!-- END OF WELCOME SCREEN -->
	<br>

<?php
	if (isset($_SESSION['message'])) {
		echo $_SESSION['message']."<br>";
		unset ( $_SESSION['message'] );
	}
?>
	<div id="AWM_settings_publish_screen" style="display: none;">	<!-- START OF SETTINGS / PUBLISH SCREEN -->
	
	
<?php
	if (isset($_GET['generated'])){
			$awm_str_code = AWM_create_menu_structure($myrows[get_option('AWM_selected_tab')],"");
		$awm_up_path = get_bloginfo('url') . get_option('AWM_menu_path');
?>
		<!-- START OF PUBLISH SCREEN -->
		<div id="AWM_publish_screen">
		<div class="updated fade"><p><strong>Menu Structure Code generated!</strong></p></div>
		<div style="background-color: #FEFCF5; border: #E6DB55 solid 1px; padding-left:15px;">
			<table>
				<tr><td style="width: 800px; text-align: center; padding-top: 10px; padding-bottom: 10px;"><h3>Generated &quot;Menu Structure Code&quot;:</h3></td></tr>
				<tr><td style="width: 800px; text-align: center; padding-top: 0px; padding-bottom: 10px;">
					<textarea cols="100" rows="10" id="loginfo" name="loginfo"><?php echo $awm_str_code; ?></textarea>
				</td></tr>
			</table>
			<h3>STEP 1: &nbsp;Copy & Paste the above &quot;Menu Structure Code&quot; into AllWebMenus</h3>
			<table>
			<tr><td style="padding-left: 100px; width: 800px; text-align: left; padding-top: 10px;">
					- Select the generated &quot;Menu Structure Code&quot; above and <b>copy</b> it (press <strong>Ctrl+C</strong>)
					<br><br>- Switch to the <b>AllWebMenus</b> desktop application:
					</td></tr>
			<tr><td style="padding-left: 140px; width: 760px; text-align: left; padding-bottom: 10px;">									
					<br>- Open the<span style="background-color:#FFFFE0; font-weight:bold; padding:0px 5px;"><i>"Add-ins &nbsp;&gt;&nbsp; WordPress Menu &nbsp;&gt;&nbsp; Import/Update Menu Structure from WordPress"</i></span> form
					<br><br>- <b>Paste</b> the above copied "Menu Structure Code" into the import form
					<br><br>- Go to Style Editor to <b>configure</b> your menu appearance, behavior, etc. using the related AllWebMenus properties<br>
					<i>&nbsp;&nbsp;(if you use the &lt;"Static" Menu Type&gt; option, you may also change the menu structure & content using the Menu Editor)</i>
					<br><br>- <b>Compile</b> your menu using the<span style="background-color:#FFFFE0; font-weight:bold; padding:0px 5px;"><i>"Add-ins &nbsp;&gt;&nbsp; WordPress Menu &nbsp;&gt;&nbsp; Compile WordPress Menu"</i></span> form
					<br><br>- Your <b>compiled menu ZIP file</b> will be created. Now proceed to "Step 2" below to upload this ZIP file to your blog.<br>
					<i>&nbsp;&nbsp;Note: AllWebMenus version 5.3.926 (December 2013) or above is required</i>
				</td></tr>
				</td></tr>
			</table>
	<br>
	<h3>STEP 2: &nbsp;Upload the compiled menu ZIP file (produced by AllWebMenus)</h3>
	
	<table id="uploader">

		<tr><td width="250"><strong>Upload your compiled ZIP file "<span id='correct_filename'>awm<?php echo $myrows[get_option('AWM_selected_tab')]->name;?>.zip</span></i>":</strong></td>
		<td>
        <form method="post" enctype="multipart/form-data" id="theform1a" name="theform1a" action="<?php echo plugins_url('actions.php',__FILE__); ?>" >
			<?php $nonce= wp_create_nonce('my-nonce'); ?>
			<input type="hidden" name="ref" value="<?php echo admin_url("options-general.php?page=allwebmenus-wordpress-menu-plugin/allwebmenus-wordpress-menu.php"); ?>"/>
			<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>"/>
			<input type="hidden" name="theaction" value="zip_update"/>
			<input type="hidden" name="AWM_menu_id" value=""/>
			<input id="AWM_selected_tab_ca" name="AWM_selected_tab_c" type="hidden" value="<?php echo get_option('AWM_selected_tab');?>"/>
					<input id="AWM_menu_js" name="AWM_menu_js" type="file" />
					<button class="button" type="button" onclick="upload_zip();">Upload ZIP file</button>
		</form>
                </td></tr>
                <tr><td width="250">&nbsp;</td>
		<td class="awm_itemInfo">
					Use the "Browse" button to find and select this ZIP file (that contains the compiled files of your menu).<br>Click the "Upload ZIP file" button to upload it.<br />
		</td></tr>
	</table>
<br></div>
<br><div style="text-align: center;"><button class="button" type="button" onclick="document.getElementById('AWM_settings_screen').style.display='block';document.getElementById('AWM_publish_screen').style.display='none';">Go Back to Settings &raquo;</button></div>
</div>
<script type='text/javascript'>var t=document.getElementById('loginfo');t.select();t.focus();</script>
<?php
	} 
?>
<!-- END OF PUBLISH SCREEN -->


<!-- START OF SETTINGS SCREEN -->
	<div id="AWM_settings_screen" style="<?php if (isset($_GET['generated'])) echo "display: none;";?>">
        <form method="post" enctype="multipart/form-data" id="theform1" name="theform1" action="<?php echo plugins_url('actions.php',__FILE__); ?>" >
			<?php $nonce= wp_create_nonce('my-nonce'); ?>
			<input type="hidden" name="ref" value="<?php echo admin_url("options-general.php?page=allwebmenus-wordpress-menu-plugin/allwebmenus-wordpress-menu.php"); ?>"/>
			<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>"/>
			<input type="hidden" name="theaction" value=""/>
			<input type="hidden" name="AWM_menu_id" value=""/>
			<input id="AWM_selected_tab_c" name="AWM_selected_tab_c" type="hidden" value="<?php echo get_option('AWM_selected_tab');?>"/>
			<div style="text-align:right">
				<input class="button" type="button" name="info_update" value="Save settings" onclick="theform.theaction.value='info_update'; awm_form_validate();"/>
				<input class="button" type="button" name="generate_structure" value="Publish menu (also saves changes in settings) &raquo;" onclick="theform.theaction.value='generate_structure'; awm_form_validate();"/>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="button" type="button" onclick="theform1.theaction.value='createnew'; theform1.AWM_selected_tab_c.value=theform.AWM_selected_tab.value;theform1.submit();">Create Additional Menu</button>
				&nbsp;<button <?php if ($this->awm_total_tabs == 1) echo "disabled='disabled'"; ?>class="button" type="button" onclick="theform1.theaction.value='delete';theform1.AWM_menu_id.value = eval('theform.AWM_menu_id_'+theform.AWM_selected_tab.value + '.value'); theform1.AWM_selected_tab_c.value=theform.AWM_selected_tab.value;theform1.submit();">Delete Selected Menu</button>
			</div>
        </form >
<form method="post" enctype="multipart/form-data" id="theform" name="theform" action="<?php echo plugins_url('actions.php',__FILE__); ?>" >
<input type="hidden" name="ref" value="<?php echo admin_url("options-general.php?page=allwebmenus-wordpress-menu-plugin/allwebmenus-wordpress-menu.php"); ?>"/>
	<?php $nonce= wp_create_nonce('my-nonce'); ?>
	<input type="hidden" name="_wpnonce" value="<?php echo $nonce; ?>"/>
    <input id="AWM_selected_tab" name="AWM_selected_tab" type="hidden" value="<?php echo get_option('AWM_selected_tab');?>"/>
	
	<div id="AWM_tab_wrapper">
		<div id="AWM_tabHeaders">
<?php
	for ($awm_t=0; $awm_t<$this->awm_total_tabs; $awm_t++) {
		echo "<div class='awm_tab_header' style='color: #".($myrows[$awm_t]->active?"0099":"9900")."00;' id='AWM_tab_header_$awm_t' onclick='awm_show_tab($awm_t)'>".$myrows[$awm_t]->name."</div>";
	}
?>
		</div>
		<div id="AWM_tabBodies">
<?php
	for ($awm_t=0; $awm_t<$this->awm_total_tabs; $awm_t++) {
?>
			<div class='awm_tab_body' id='AWM_tab_body_<?php echo $awm_t;?>'>
				<div><input type="hidden" name="AWM_menu_id_<?php echo $awm_t?>" value="<?php echo $myrows[$awm_t]->id;?>"/><input id="AWM_menu_active_<?php echo $awm_t;?>" name="AWM_menu_active_<?php echo $awm_t;?>" onclick="awm_uncheck(<?php echo $awm_t;?>);" type="checkbox" value="true" <?php if ($myrows[$awm_t]->active) echo "checked='checked'"; ?> /> <strong>Show "<?php echo $myrows[$awm_t]->name;?>" in blog</strong>
				&nbsp;&nbsp;&nbsp;<?php if (!$myrows[$awm_t]->active) { ?><span id='AWM_unchecked_<?php echo $awm_t;?>' style='color:#990000;'>Unchecked! (this menu will not appear in your blog)</span><?php } else { ?><span id='AWM_unchecked_<?php echo $awm_t;?>' style='color:#009900;'>(this menu will appear in your blog)</span><?php } ?></div>
				<div style="padding-left: 19px; margin-top: 13px;"><strong>Menu name: </strong> <input name="AWM_menu_name_<?php echo $awm_t;?>" id="AWM_menu_name_<?php echo $awm_t;?>" type="text" size="30" value="<?php echo $myrows[$awm_t]->name ?>"/></div>
				<div style="padding-left: 19px;" class="awm_itemInfo">Please make sure that the "Menu name" value matches the value in the "Compiled Menu Name" property of the AllWebMenus project file (<i>Tools > Project Properties > Folders</i>). <a id='show_me_<?php echo $awm_t;?>' href="javascript:void(0)" onclick="show_awm_folder_info(<?php echo $awm_t;?>);">show me</a></div>
				<div id="AWM_folder_info_<?php echo $awm_t;?>" style="margin-top: 20px; display: none; background-color: #FEFCF5; border: #E6DB55 solid 1px;">
					<table>
						<tr><td style="width: 800px; text-align: center; padding-top: 10px; padding-bottom: 10px;"><strong>More info</strong></td></tr>
						<tr><td style="width: 800px; text-align: center; padding-top: 0px; padding-bottom: 10px;">
							<img src="<?php echo get_bloginfo('url');?>/wp-content/plugins/allwebmenus-wordpress-menu-plugin/more_info.jpg" width="527" height="513" alt="More info" title="More info"/>
						</td></tr>
						<tr><td style="width: 800px; text-align: center; padding-top: 0px; padding-bottom: 10px;">
							<a href="javascript:void(0)" onclick="show_awm_folder_info(<?php echo $awm_t;?>);">close</a>
					</table>
				</div>
				<div style="padding-left: 19px; margin-top: 13px;"><strong>Online folder for menu files<?php if ($this->awm_total_tabs>1) echo " (common for all menus)"; ?>: </strong> <input id="AWM_menu_path_<?php echo $awm_t;?>" name="AWM_menu_path_<?php echo $awm_t;?>" onkeyup="awm_set_path(<?php echo $awm_t;?>);" onchange="awm_set_path(<?php echo $awm_t;?>);" type="text" size="30" value="<?php echo get_option('AWM_menu_path'); ?>"/>&nbsp;&nbsp;(relative to blog's root folder)</div>
				<div style="padding-left: 19px;" class="awm_itemInfo">
					Based on your settings, this is the folder that should be created online: &nbsp;
					<span style="background-color:#FFFFE0; padding:0px 5px;">
					<?php echo get_bloginfo('url').get_option("AWM_menu_path")?>
					</span>
					<br>
					This is your blog's online folder where the AllWebMenus "compiled menu ZIP file" extracts its contents (menu engine, styles, etc.) every time you upload it through the "Publish Menu" action. It is created automatically during this action. If your server's settings do not permit this you will have to create this folder yourself (eg: FTP).
					</div>
				<br>
				<fieldset class="options">
                                        <fieldset id="AWM_menu_structure_fieldset_<?php echo $awm_t;?>">
					<div class="AWM_section">Menu Structure</div>
					<table width="100%" height="auto" style="padding-left: 20px;">
						<tr><td>&nbsp;</td></tr>
						<tr><td width="100%"><input onclick="awm_select_structure(true,<?php echo $awm_t;?>);" name="AWM_use_custom_menu_<?php echo $awm_t;?>" value="1" type="radio" <?php if ($myrows[$awm_t]->custom_menu) echo "checked='checked'"; ?> />&nbsp;Menu is populated from an existing "Wordpress menu" (check to choose menu, Wordpress 3+ only)</td></tr>
						<tr><td id="AWM_menu_structure_use_existing_<?php echo $awm_t;?>">
							<table width="100%" height="auto" style="padding-left: 20px;">
								<tr><td>
									Which WordPress menu do you want to use? <select id="AWM_use_custom_menu_id_<?php echo $awm_t;?>" name="AWM_use_custom_menu_id_<?php echo $awm_t;?>">
<?php
		$awm_available_custom_menus = (array)$this->wpdb->get_results("
			SELECT t.term_id as menu_ID, t.name as menu_name
			FROM {$this->wpdb->prefix}terms t, {$this->wpdb->prefix}term_taxonomy tt
			WHERE tt.taxonomy = 'nav_menu'
			AND t.term_id = tt.term_id
			ORDER BY menu_ID");
		if (count($awm_available_custom_menus)>0) {
//			echo "<option value='-1'>Please select a WordPress menu</option>";
			for ($awm_i=0; $awm_i<count($awm_available_custom_menus); $awm_i++) {
				echo "<option value='".$awm_available_custom_menus[$awm_i]->menu_ID."'".($awm_available_custom_menus[$awm_i]->menu_ID==$myrows[$awm_t]->custom_menu_id?" selected":"").">".$awm_available_custom_menus[$awm_i]->menu_name."</option>";
			}
		} else {
			echo "<option value='-1'>No WordPress menus found!</option>";
		}
?>
									</select>&nbsp;&nbsp;&nbsp;&nbsp;<span class="awm_itemInfo"><a href="nav-menus.php">Add/Edit WordPress menus</a></span
								</td></tr>
							</table>
						</td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td><input onclick="awm_select_structure(false,<?php echo $awm_t;?>);" name="AWM_use_custom_menu_<?php echo $awm_t;?>" value="0" type="radio"<?php if (!$myrows[$awm_t]->custom_menu) echo "checked='checked'"; ?> />&nbsp;Menu is populated with specific "pages", "posts", "categories", etc. (check to choose items)</td></tr>
						<tr><td id="AWM_menu_structure_use_own_<?php echo $awm_t;?>" width="100%">
							<table width="100%" height="auto" style="padding-left: 20px;">
								<tr><td colspan="2"><p>Please select the items you want to include/exclude in your menu structure:</p></td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td width="230"><input name="AWM_include_home_<?php echo $awm_t;?>" type="checkbox" value="true" <?php if ($myrows[$awm_t]->include_home) echo "checked='checked'"; ?> /> <strong>"Home"</strong></td>
								<td class="awm_itemInfo">A "Home" item that opens the blog's Home Page.</td></tr>

								<tr><td colspan="2">&nbsp;</td></tr>
								<tr><td colspan="2">&nbsp;</td></tr>

								<tr><td width="230" VALIGN="TOP"><input onclick="awm_show_field(this.checked,<?php echo $awm_t;?>,'pages');" name="AWM_pages_<?php echo $awm_t;?>" type="checkbox" <?php if ($myrows[$awm_t]->pages) echo "checked='checked'"; ?> /> <strong>Pages<span id="awm_pages_dots_<?php echo $awm_t;?>"><?php if ($myrows[$awm_t]->pages):?>:<?php else:?> ...<?php endif;?></span></strong></td>
								<td>
                                                                    <fieldset id="awm_pages_fieldset_<?php echo $awm_t?>" <?php if (!$myrows[$awm_t]->pages)echo 'style="display:none"';?>>
                                                                    <span style="color: #009900">Show all Pages</span> <span style="color: #990000">except the following:</span><br />
                                                                <input name="AWM_excluded_pages_<?php echo $awm_t;?>" type="text" size="55" value="<?php echo $myrows[$awm_t]->excluded_pages ?>"/>

								<br /><span class="awm_itemInfo">Page IDs, separated by commas (their sub-pages will also be excluded). Example: 34, 59, 140</span>

                                                                <br />
								<br />
                                                                <input onclick="awm_disable_input(false,<?php echo $awm_t;?>,'pages');" name="AWM_pages_ms_<?php echo $awm_t;?>" value="main" type="radio" <?php if ($myrows[$awm_t]->pages_ms == 'main') echo "checked='checked'"; ?> />&nbsp;Show Pages as Main Menu items
								<br /><input onclick="awm_disable_input(true,<?php echo $awm_t;?>,'pages');" name="AWM_pages_ms_<?php echo $awm_t;?>" value="sub" type="radio"<?php if ($myrows[$awm_t]->pages_ms != 'main') echo "checked='checked'"; ?> />&nbsp;Show a Main Menu item named <input <?php if ($myrows[$awm_t]->pages_ms == 'main') echo "disabled='disabled'"; ?> id="awm_pages_name_<?php echo $awm_t;?>" name="AWM_pages_name_<?php echo $awm_t;?>" type="text" size="10" value="<?php echo $myrows[$awm_t]->pages_name ?>"/>
								and show Pages as its submenu items
								<br/>

                                                                </fieldset>
                                                                    <br/>
                                                                <br/>

                                                                </td></tr>

								<tr><td valign="top" width="230"><input onclick="awm_show_field(this.checked,<?php echo $awm_t;?>,'posts');" name="AWM_posts_<?php echo $awm_t;?>" type="checkbox" <?php if ($myrows[$awm_t]->posts) echo "checked='checked'"; ?> /> <strong>Posts<span id="awm_posts_dots_<?php echo $awm_t;?>"><?php if ($myrows[$awm_t]->posts):?>:<?php else:?> ...<?php endif;?></span></strong></td>
								<td>
                                                                    <fieldset id="awm_posts_fieldset_<?php echo $awm_t?>" <?php if (!$myrows[$awm_t]->posts)echo 'style="display:none"';?>>
                                                                        <span style="display: block;color: #009900">Show the following Posts:</span>
								<input name="AWM_posts_ids_<?php echo $awm_t;?>" type="text" size="55" value="<?php echo $myrows[$awm_t]->posts_ids ?>"/>
								<br />
								<span class="awm_itemInfo">Post IDs, separated by commas. Example: 34, 59, 140</span>
                                                                <br />
								<br />
								<input onclick="awm_disable_input(false,<?php echo $awm_t;?>,'posts');" name="AWM_posts_ms_<?php echo $awm_t;?>" value="main" type="radio" <?php if ($myrows[$awm_t]->posts_ms == 'main') echo "checked='checked'"; ?> />&nbsp;Show Posts as Main Menu items
                                                                <br />

								<input onclick="awm_disable_input(true,<?php echo $awm_t;?>,'posts');" name="AWM_posts_ms_<?php echo $awm_t;?>" value="sub" type="radio"<?php if ($myrows[$awm_t]->posts_ms != 'main') echo "checked='checked'"; ?> />&nbsp;Show a Main Menu item named <input <?php if ($myrows[$awm_t]->posts_ms == 'main') echo "disabled='disabled'"; ?> id="awm_posts_name_<?php echo $awm_t;?>" name="AWM_posts_name_<?php echo $awm_t;?>" type="text" size="10" value="<?php echo $myrows[$awm_t]->posts_name ?>"/>
								and show Posts as its submenu items
								<br />

                                                                </fieldset>
                                                                    <br />
                                                                <br />
								</td></tr>
								<tr><td valign="top" width="230"><input onclick="awm_show_field(this.checked,<?php echo $awm_t;?>,'categories');" name="AWM_categories_<?php echo $awm_t;?>" type="checkbox" <?php if ($myrows[$awm_t]->categories) echo "checked='checked'"; ?> /> <strong>Categories<span id="awm_categories_dots_<?php echo $awm_t;?>"><?php if ($myrows[$awm_t]->categories):?>:<?php else:?> ...<?php endif;?></span></strong></td>
								<td>
                                                                    <fieldset id="awm_categories_fieldset_<?php echo $awm_t?>" <?php if (!$myrows[$awm_t]->categories)echo 'style="display:none"';?>>
                                                                        <span style="color: #009900">Show all Categories</span> <span style="color: #990000">except the following:</span><br/>

								<input name="AWM_excluded_cats_<?php echo $awm_t;?>" type="text" size="55" value="<?php echo $myrows[$awm_t]->excluded_cats ?>"/><br />
								<span class="awm_itemInfo">Category IDs, separated by commas (their sub-categories will also be excluded). Example: 34, 59, 140</span><br />
                                                                <input onclick="document.getElementById('awm_categories_subitems_no_<?php echo $awm_t;?>').disabled=!this.checked" name="AWM_categories_subitems_<?php echo $awm_t;?>" type="checkbox" <?php if ($myrows[$awm_t]->categories_subitems) echo "checked='checked'"; ?> /> Also show (up to) the <input onblur="awm_max_min_value(this , <?php echo $awm_t?>);" onkeyup="awm_max_min_value(this,  <?php echo $awm_t?> );" onkeypress="return awm_disable_value(this,event);" <?php if (!$myrows[$awm_t]->categories_subitems) echo "disabled='disabled'"; ?> id="awm_categories_subitems_no_<?php echo $awm_t;?>" name="AWM_categories_subitems_no_<?php echo $awm_t;?>" type="text" size="2" value="<?php echo $myrows[$awm_t]->categories_subitems_no ?>"/> newest posts of each Category as its submenu items<br />
								<span class="awm_itemInfo" id="awm_max_value_notice_<?php echo $awm_t;?>">Value must be between 1 and 50.</span>
								<br />
                                                                <br />
								<input onclick="awm_disable_input(false,<?php echo $awm_t;?>,'categories');"name="AWM_categories_ms_<?php echo $awm_t;?>" value="main" type="radio" <?php if ($myrows[$awm_t]->categories_ms == 'main') echo "checked='checked'"; ?> />&nbsp;Show Categories as Main Menu items<br />

								<input onclick="awm_disable_input(true,<?php echo $awm_t;?>,'categories');"name="AWM_categories_ms_<?php echo $awm_t;?>" value="sub" type="radio" <?php if ($myrows[$awm_t]->categories_ms != 'main') echo "checked='checked'"; ?> />&nbsp;Show a Main Menu item named <input <?php if ($myrows[$awm_t]->categories_ms == 'main') echo "disabled='disabled'"; ?> id="awm_categories_name_<?php echo $awm_t;?>" name="AWM_categories_name_<?php echo $awm_t;?>" type="text" size="10" value="<?php echo $myrows[$awm_t]->categories_name ?>"/>
								and show Categories as its submenu items<br />
								</fieldset>
								<br />
                                                                <br />



							</table>
						</td></tr>

						<tr><td>&nbsp;</td></tr></table>
                                                <div class="AWM_section">Other</div>

                                                <p>Here you can select other options regarding the menu items:</p>
                                                <fieldset class="awm_other">
							<input type="checkbox" name="AWM_hide_future_<?php echo $awm_t;?>" value="checkbox" <?php if ($myrows[$awm_t]->hide_future) echo "checked='checked'"; ?>/> Hide future-dated posts
							<input type="checkbox" name="AWM_hide_protected_<?php echo $awm_t;?>" value="checkbox" <?php if ($myrows[$awm_t]->hide_protected) echo "checked='checked'"; ?>/> Hide password-protected items
							<input type="checkbox" name="AWM_hide_private_<?php echo $awm_t;?>" value="checkbox" <?php if ($myrows[$awm_t]->hide_private) echo "checked='checked'"; ?>/> Hide private items
                                                 </fieldset>

                                                </fieldset>



                                    <div class="AWM_section">Menu Type</div>
					<p>Please select how you want your menu to behave:</p>

					<input type="hidden" id="awm_initial_menu_type_<?php echo $awm_t;?>" value="<?php echo $myrows[$awm_t]->type;?>"/>
					<table width="100%" height="auto" style="padding-left: 40px;">
						<tr><td colspan="2">&nbsp;</td></tr>
						<tr><td colspan="2" id="awm_changed_type_a_<?php echo $awm_t;?>" class="updated fade" style="display: none;">
								<span style="color: #990000;">&quot;Menu Type&quot; changed:</span> New behavior will take effect only when you perform the "Publish menu" action to generate an updated &quot;Menu Structure Code&quot; and re-import it to AllWebMenus.
						</td></tr>
						<tr><td colspan="2" id="awm_changed_type_b_<?php echo $awm_t;?>">&nbsp;</td></tr>

						<tr><td width="230" valign="top"><table>
							<tr><td><span style="cursor: pointer;" onclick="awm_select_menu_type('Dynamic',<?php echo $awm_t;?>);"><strong><input id="AWM_menu_type_<?php echo $awm_t;?>_Dynamic" name="AWM_menu_type_<?php echo $awm_t;?>" value="Dynamic" type="radio" <?php if ($myrows[$awm_t]->type == 'Dynamic') echo "checked='checked'"; ?> />&nbsp;"Dynamic" Menu Type</strong></span></td></tr>
							<tr><td><span style="cursor: pointer;" onclick="awm_select_menu_type('Mixed',  <?php echo $awm_t;?>);"><strong><input id="AWM_menu_type_<?php echo $awm_t;?>_Mixed" name="AWM_menu_type_<?php echo $awm_t;?>" value="Mixed" type="radio" <?php if ($myrows[$awm_t]->type == 'Mixed') echo "checked='checked'"; ?> />&nbsp;"Mixed" Menu Type</strong></span></td></tr>
							<tr><td><span style="cursor: pointer;" onclick="awm_select_menu_type('Static', <?php echo $awm_t;?>);"><strong><input id="AWM_menu_type_<?php echo $awm_t;?>_Static" name="AWM_menu_type_<?php echo $awm_t;?>" value="Static" type="radio" <?php if ($myrows[$awm_t]-> type == 'Static') echo "checked='checked'"; ?> />&nbsp;"Static" Menu Type</strong></span></td></tr>
						</table></td><td valign="top">
							<div class="awm_itemInfo" id="AWM_menu_type_<?php echo $awm_t;?>_Dynamic_info" <?php echo $myrows[$awm_t]->type=='Dynamic'?'':'style="display:none;"'; ?>>
								<p style="margin-top: 0px; padding-top: 0px;">You have selected to create a menu structure of "Dynamic Type".</p>
								<p>This means that the menu items in AllWebMenus will only be used for preview/styling purposes.</p>
								<p>In your actual blog these items will be ignored and the menu will be populated "dynamically" based on the plugin settings.</p>
								<p>The styles in AllWebMenus Style Editor will be used to form the actual menu items.</p>
								<p><a href="http://www.likno.com/blog/wordpress-javascript-menu/1184/" target="_blank">View short video explaining all your settings</a></p>
							</div>
							<div class="awm_itemInfo" id="AWM_menu_type_<?php echo $awm_t;?>_Mixed_info" <?php echo $myrows[$awm_t]->type =='Mixed'?'':'style="display:none;"'; ?>>
								<p style="margin-top: 0px; padding-top: 0px;">You have selected to create a menu structure of "Mixed Type".</p>
								<p>This means that your menu will contain both the items you create within AllWebMenus ("static") and the items you import from WordPress ("dynamic").</p>
								<p>The imported Wordpress items will use the styles of the AllWebMenus Style Editor but their actual content will be populated "dynamically" based on the plugin settings.</p>
								<p>The static items you create within AllWebMenus will be shown as is.</p>
								<p><a href="http://www.likno.com/blog/wordpress-javascript-menu/1184/" target="_blank">View short video explaining all your settings</a></p>
							</div>
							<div class="awm_itemInfo" id="AWM_menu_type_<?php echo $awm_t;?>_Static_info" <?php echo $myrows[$awm_t]->type=='Static'?'':'style="display:none;"'; ?>>
								<p style="margin-top: 0px; padding-top: 0px;">You have selected to create a menu structure of "Static Type".</p>
								<p>Your menu will be edited (addition/removal/customization of items) within AllWebMenus only.</p>
								<p>Any changes on your online blog will not affect the menu items unless you perform the "Publish menu" action to generate an updated &quot;Menu Structure Code&quot; and re-import it to AllWebMenus.</p>								
								<p>This allows for maximum customization, as your online menu will show all items and styles customized within AllWebMenus.</p>
								<p><a href="http://www.likno.com/blog/wordpress-javascript-menu/1184/" target="_blank">View short video explaining all your settings</a></p>
							</div>
						</td></tr>

						<tr><td colspan="2">&nbsp;</td></tr>
						<tr><td colspan="2">&nbsp;</td></tr>
					</table>



                                        <div class="AWM_section">Menu Positioning Method</div>


                                            
				<div style="padding: 20px 0;">Please select a menu position that your menu wants to appear:
				<select style="margin-left: 20px;" onchange="awm_menu_position(this.value , <?php echo $awm_t;?>)" name="awm_menu_position_<?php echo $awm_t;?>" id="awm_menu_position_<?php echo $awm_t;?>">
                                            <option value="0" <?php selected( isset( $myrows[$awm_t]->position ) && $myrows[$awm_t]->position == 0 ); ?>>custom position</option>
                                            <?php global $wp_registered_sidebars;
                                            global $wp_version;
                                            if ((float)$wp_version>=2.2 &&  count($wp_registered_sidebars > 1)):?>
                                            <option value="awm_widget" <?php selected( isset( $myrows[$awm_t]->position ) && $myrows[$awm_t]->position == "awm_widget" ); ?>>widget</option>
                                            <?php endif;?>
                                            <?php  if (count($locations)):?>
                 <?php
                                        foreach ( $locations as $location => $description ) {
		?>
					<option<?php selected( isset( $myrows[$awm_t]->position ) && $myrows[$awm_t]->position == $location ); ?>
						value="<?php echo $location; ?>">Theme Menu Location: <?php
						echo $location . '&hellip;';
					?></option>
	<?php
	}
	?>
                                        </select>

                                    <div class="awm_menu_position" id="awm_custom_menu_position_<?php echo $awm_t;?>" <?php if ($myrows[$awm_t]->position != '0') echo 'style="display: none;"';?>>You have selected the <em>Custom</em> positioning method. This means that you should manually add a positioning DIV element (or image) into your blog's HTML code, based on the settings that you specified within AllWebMenus ("Positioning" property). In case you selected the "Relative to Window" option there, no positioning element needs to be added to your blog's HTML.</div>
                                    <div class="awm_menu_position" id="awm_widget_menu_position_<?php echo $awm_t;?>" <?php if ($myrows[$awm_t]->position !="awm_widget") echo 'style="display: none;"';?>>You have selected the <em>Widget</em> positioning method. ***Note: this method applies ONLY when you ALSO use the "Relative to Element" positioning option within your AllWebMenus project (without changing its "Default ID" selection)***<br />
                                        A widget instance for this menu is initially created and found in the <a href="<?php echo admin_url('widgets.php');?>#wp_inactive_widgets">widgets administration page</a>, under the "Inactive Widgets" section. Just drag this widget instance and drop it inside the Widget Area that you want.</div>
                                    <div class="awm_menu_position" id="awm_theme_menu_position_<?php echo $awm_t;?>" <?php if ($myrows[$awm_t]->position =="awm_widget" || $myrows[$awm_t]->position == '0') echo 'style="display: none;"';?>>
                                        You have selected the <em>Theme Menu Location</em> positioning method. ***Note: this method applies ONLY when you ALSO use the "Relative to Element" positioning option within your AllWebMenus project (without changing its "Default ID" selection)***<br />
                                        This means that your menu will be positioned based on the "Menu Location" that your Blog's Theme provides.</div>
                               


		</div>
                                        <?php endif;?>
					

					<div class="AWM_section">Collaboration with external plugins</div>
					<table width="100%" height="auto" style="padding-left: 40px;">
						<tr><td colspan="2">&nbsp;</td></tr>

						<tr><td width="230" valign="top"><strong>Related Posts (YARPP):</strong>
						<?php if (!$this->awm_is_yarpp_enabled) {?><br><span class='awm_itemInfo' style='color:#990000;'>Currently not installed and activated</span>
						<br><span class="awm_itemInfo"><a href="http://wordpress.org/extend/plugins/yet-another-related-posts-plugin/" target="_blank">get it here</a></span>
                                                    <?php
                                                }?>
                                                    </td>
						<td valign="top"><input type="checkbox" name="AWM_Related_<?php echo $awm_t;?>" value="checkbox" <?php if ($myrows[$awm_t]->related) echo "checked='checked'"; if (!$this->awm_is_yarpp_enabled) echo "disabled='disabled'";else echo "onclick=\"awm_disable_input(this.checked,$awm_t,'related');\""; ?>/>
						Show a "Related Posts" item when viewing a post</td>
						<tr><td width="230" valign="top">&nbsp;</td>
						<td >
							<?php if ($this->awm_is_yarpp_enabled) { ?>
                                                                <br>
                                                                <label for="AWM_Related_name_<?php echo $awm_t;?>" >Name of the 'Related Posts' item: </label><input name="AWM_Related_name_<?php echo $awm_t;?>" id="awm_related_name_<?php echo $awm_t;?>" value="<?php echo $myrows[$awm_t]->related_name?>"<?php if (!$myrows[$awm_t]->related) echo "disabled='disabled'"; ?>/>
								<div class="awm_itemInfo">
                                                                <p style="margin-top: 0px; padding-top: 0px;" >This feature uses the 3rd-party "Related Posts (YARPP)" plugin, which needs to be installed separately.</p>
								<p>It dynamically adds a "Related Posts" item at the end of the Main Menu, with a submenu that contains posts related to the post you are currently viewing, regardless of the menu type you have selected.</p>
								<p>The "Related Posts" item appears only when viewing a single post.</p>
                                                                </div>
							<?php } else { ?>
                                                                <div class="awm_itemInfo">
								<p style="margin-top: 0px; padding-top: 0px;" ><span style="color: #990000;">Note!</span> It seems that the "Related Posts (YARPP)" Plugin is not installed and activated so this option is disabled.</p>
								<p>This feature uses the 3rd-party "Related Posts (YARPP)" plugin, which needs to be installed separately.</p>
								<p>It dynamically adds a "Related Posts" item at the end of the Main Menu, with a submenu that contains posts related to the post you are currently viewing, regardless of the menu type you have selected.</p>
								<p>The "Related Posts" item appears only when viewing a single post.</p>
                                                                </div>
							<?php } ?>
						</td></tr>

						<tr><td colspan="2">&nbsp;</td></tr>
						<tr><td colspan="2">&nbsp;</td></tr>
					</table>
				</fieldset>
			</div>
			<script type="text/javascript">awm_select_structure(<?php echo $myrows[$awm_t]->custom_menu?"true":"false";?>,<?php echo $awm_t;?>);</script>
<?php
	}	// end of tab body for loop
?>
		</div>	<!-- tab_bodies -->
	</div>	<!-- tab_wrapper -->

	<br>
	<br>
	<div style="text-align: center;" class="awm_itemInfo"><span style="color:#990000;">Note:</span> Always click the "Save settings" button below to apply your changes. If you leave this page without saving you will lose your unsaved changes.</div>
	<div class="submit" style="text-align: center;">
		<input type="button" name="info_update" value="Save settings" onclick="theform.theaction.value='info_update'; awm_form_validate();"/>
		&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
		<input type="button" name="generate_structure" value="Publish menu (also saves changes in settings) &raquo;" onclick="theform.theaction.value='generate_structure'; awm_form_validate();"/>
	</div>

	<input type="hidden" name="theaction" value="" />

        <?php if ($this->awm_total_tabs) :?>
	<script type="text/javascript">
		AWM_TOTAL_TABS_JS = <?php echo $this->awm_total_tabs;?>;
		awm_show_tab(<?php echo get_option('AWM_selected_tab'); ?>);
		awm_show_welcome(<?php echo get_option('AWM_show_welcome')?"true":"false"; ?>);
	</script>
        <?php endif;?>

</div>
</form>

</div>	<!-- END OF SETTINGS SCREEN -->
</div>	<!-- END OF PUBLISH/SETTINGS SCREEN -->
<?php

}	// END of AWM_options_page()

/*
 * Special filter to work with wordpress menu system
 */
function awm_menu_position( $items, $args) {
    if (isset($this->awm_wp_nav_array[$args->theme_location]))
        return $items."<div id='".$this->awm_wp_nav_array[$args->theme_location]."'>&nbsp;</div>";
    else
        return $items;
}

function awm_print_menu_position($args) {
   $myrows =  $this->wpdb->get_results("SELECT name FROM $this->awm_table_name WHERE position LIKE '". $args['theme_location']."' && active = 1 ORDER BY id ASC" );
   foreach ($myrows as $myrow)
       echo "<div id=awmAnchor-".$myrow->name.">&nbsp;</div>";
}

function awm_menu_args($args) {
	$args = (object) $args;
	$args->fallback_cb = 'AWM_print_menu_position';
	return $args;
}
/*
 * Generate Linking Code
 */
function AWM_generate_linking_code() {
	$filter_flag = false;
	$myrows = $this->wpdb->get_results( "SELECT * FROM $this->awm_table_name WHERE active=1 order by id ASC" );
	for ($awm_t=0; $awm_t<count($myrows); $awm_t++) {
		$gnr = $myrows[$awm_t]->menu_genre;
		$tp = $myrows[$awm_t]->type;
		$revs = $myrows[$awm_t]->menu_revisions;
		if ( $myrows[$awm_t]->position !=  "0" && $myrows[$awm_t]->position !=  "awm_widget") {
			$this->awm_wp_nav_array[$myrows[$awm_t]->position] = "awmAnchor-".$myrows[$awm_t]->name;
			$filter_flag = true;
		}
		$awm_name = $myrows[$awm_t]->name;
		
		echo "<!-- ******** BEGIN ALLWEBMENUS CODE FOR " . $awm_name . " ($gnr MENU)******** -->\n";
		if ($gnr=="JS" || $gnr=="ULLI") {
			echo "<script type='text/javascript'>var MenuLinkedBy='AllWebMenus [4]',awmMenuName='" . $awm_name . "',awmBN='WP';awmAltUrl='';</script>\n";
			echo "<script charset='UTF-8' src='" . get_bloginfo('url') . get_option('AWM_menu_path') . $awm_name . ".js' type='text/javascript'></script>\n";
			echo "<script type='text/javascript'>".($gnr=="JS"?"if (typeof(Menu)!='undefined') ":"")."awmBuildMenu();\n";
			echo "<!-- -------  Add your Server-Side code right after this comment  ---------- -->\n";
			if ($gnr=="ULLI") echo "</script>\n";
		} elseif ($gnr=="CSS") {
			echo "<link href='". get_bloginfo('url') . get_option('AWM_menu_path') .$awm_name.".css' rel='stylesheet' type='text/css' />\n";
			echo "<script charset='UTF-8' src='". get_bloginfo('url') . get_option('AWM_menu_path') .$awm_name.".js' type='text/javascript'></script>\n";
		}

		if ($gnr=="JS") {
			for ($i=1; $i<=$revs; $i++) {
				if ($tp=='Dynamic') {
					AWM_create_dynamic_menu($myrows[$awm_t],false, ($i>1)?"_rm".$i:"");
				} elseif ($tp=='Mixed') {
					AWM_create_dynamic_menu($myrows[$awm_t],true, ($i>1)?"_rm".$i:"");
				}
			}
		} elseif ($gnr=="ULLI" || $gnr=="CSS") {
			$dyn_code = "";
			if ($tp=='Dynamic' || $tp=='Mixed') $dyn_code = AWM_create_ULLI_dynamic_menu($myrows[$awm_t], "");
//			echo $myrows[$awm_t]->menu_structure;
			echo str_replace("<!--DYNAMIC STRUCTURE CODE-->",$dyn_code,$myrows[$awm_t]->menu_structure);
		}
		
		
		if ($gnr=="JS") {
			// only if we are viewing a single post & want related (and not custom menu)
			if ($this->awm_is_yarpp_enabled && $myrows[$awm_t]->related && is_single() && !$myrows[$awm_t]->custom_menu) {
				// convert quotes to add code to item's <Text> property
				$awm_related = related_posts(array('before_related'=> '<p>'.$myrows[$awm_t]->related_name.'</p><ol>' ), false);
				$awm_related = str_replace('"', "'", $awm_related);
				$awm_related = str_replace("'", "\'", $awm_related);
				$awm_related = str_replace(chr(10), "", $awm_related);
				$awm_related = str_replace(chr(13), "", $awm_related);
				$awm_related = str_replace('\n', '', $awm_related);
				$awm_related = str_replace('\r', '', $awm_related);
				$awm_related = trim($awm_related);
				
				$awm_parentgroup = "wpgroup";
				echo "IRP=$awm_parentgroup.newItem('style=". $awm_name ."_'+(wplevel==0?'main_item_style':'sub_item_style')+';visible=0');\n";
				echo "IRP.visible=1; IRP.text0='".$myrows[$awm_t]->related_name."'; IRP.text1='".$myrows[$awm_t]->related_name."'; IRP.text2='".$myrows[$awm_t]->related_name."';\n";
				echo "IRP1=IRP.newGroup('style=". $awm_name ."_'+(wplevel==0?'sub_group_style':'sub_group_plus_style'));\n";
				echo "IRP2=IRP1.newItem('style=". $awm_name ."_'+(wplevel==0?'sub_item_style':'sub_item_plus_style')+';text0=" . $awm_related . ";htmlMode=1;');\n";
			}
			echo "if (typeof(" . $awm_name . ")!='undefined') ProduceMenu(" . $awm_name . ");\n";
			echo "</script>\n";
		}
		echo "<!-- ******** END ALLWEBMENUS CODE FOR " . $awm_name . " ($gnr MENU)******** -->\n\n";
	}
	if ($filter_flag) {
		add_filter('wp_nav_menu', array($this,'awm_menu_position'), 10, 2);
		add_filter('wp_nav_menu_args',array($this,'awm_menu_args'), 10 ,1);
	}
}



/* 
 * Initialize query var for sitemap permalinks
 */
function AWM_query_vars ( $awm_vars ) {
	$awm_vars[] = "pg";
	return $awm_vars;
}

//add_filter('query_vars', 'AWM_query_vars');


}	// END of Class AWM_Plugin


//this functions calls the object's generate linking code
function AWM_generate_linking_code(){
    global $awmPluginInstance;
    $awmPluginInstance->AWM_generate_linking_code();
}
//this functions is the callback function that calls the function that prints the div at the Menu Position
function AWM_print_menu_position($args){
    global $awmPluginInstance;
    $awmPluginInstance->awm_print_menu_position($args);
}
?>