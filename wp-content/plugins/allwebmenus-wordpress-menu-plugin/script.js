 /*
Plugin Name: AllWebMenus WordPress Menu Plugin
Plugin URI: http://www.likno.com/addins/wordpress-menu.html
Description: WordPress plugin for the AllWebMenus PRO Javascript Menu Maker - Create stylish drop-down menus or sliding menus for your blogs!
Version: 1.1.17
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
/*
 *These are all the javascript functions that the software uses.
 *
 **/


var AWM_TOTAL_TABS_JS;

if(!Array.indexOf){
	Array.prototype.indexOf = function(obj){
		for(var i=0; i<this.length; i++) if(this[i]==obj) return i;
		return -1;
	}
}

function awm_show_welcome(x) {
	if (typeof(x)=="undefined") x = document.getElementById('AWM_welcome_screen').style.display=="none";
	document.getElementById('AWM_welcome_screen').style.display=x?"block":"none";
	document.getElementById('AWM_settings_publish_screen').style.display=x?"none":"block";
	document.getElementById('AWM_welcome_title_info').style.display=x?"none":"inline-block";
}
function upload_zip() {
	var tmp = document.getElementById('AWM_menu_js').value;
	tmp = tmp.substring(tmp.lastIndexOf("\\")+1);
	if (tmp==document.getElementById('correct_filename').innerHTML) {
		theform1a.AWM_menu_id.value = eval('theform.AWM_menu_id_'+theform.AWM_selected_tab.value + '.value');theform1a.AWM_selected_tab_c.value=theform.AWM_selected_tab.value;theform1a.submit();
	} else {
		alert("Wrong filename! The filename should be '"+document.getElementById('correct_filename').innerHTML+"' and it now is '"+tmp+"'.");
	}
}
function awm_show_tab(x) {
	if (typeof(x)=="undefined") x = 0;
	var i;
	for (i=0; i<AWM_TOTAL_TABS_JS; i++) {
		document.getElementById('AWM_tab_body_'+i).style.display="none";
		document.getElementById('AWM_tab_header_'+i).className = "awm_tab_header";
	}
	if (typeof (document.getElementById('AWM_tab_header_'+x))=="undefined") x = 0;
	document.getElementById('AWM_tab_header_'+x).className = "awm_tab_header_selected";
	document.getElementById('AWM_tab_body_'+x).style.display="";
	document.getElementById('AWM_selected_tab').value = x;
//			document.getElementById("upload_form_menu_name").innerHTML = eval('document.theform.AWM_menu_name_'+x+'.value;');
}
function awm_select_menu_type(x,t) {
	document.getElementById('AWM_menu_type_'+t+'_Dynamic_info').style.display="none";
	document.getElementById('AWM_menu_type_'+t+'_Mixed_info').style.display="none";
	document.getElementById('AWM_menu_type_'+t+'_Static_info').style.display="none";
	document.getElementById('AWM_menu_type_'+t+'_'+x+'_info').style.display="";
	document.getElementById('AWM_menu_type_'+t+'_'+x).checked=true;
	if (x != document.getElementById('awm_initial_menu_type_'+t).value) {
		document.getElementById('awm_changed_type_a_'+t).style.display = "";
		document.getElementById('awm_changed_type_b_'+t).style.display = "";
	} else {
		document.getElementById('awm_changed_type_a_'+t).style.display = "none";
		document.getElementById('awm_changed_type_b_'+t).style.display = "none";
	}

}
function awm_uncheck(x) {
	if (document.getElementById('AWM_menu_active_'+x).checked) {
		document.getElementById('AWM_unchecked_'+x).style.color="#009900";
		document.getElementById('AWM_unchecked_'+x).innerHTML = "(this menu will appear in your blog)";
	} else {
		document.getElementById('AWM_unchecked_'+x).style.color="#990000";
		document.getElementById('AWM_unchecked_'+x).innerHTML = "Unchecked! (this menu will not appear in your blog)";
	}
}
function show_awm_folder_info(t) {
	x = document.getElementById('AWM_folder_info_'+t).style.display == 'none';
	document.getElementById('AWM_folder_info_'+t).style.display = x?'':'none';
	document.getElementById('show_me_'+t).innerHTML = x?'hide me':'show me';
}
function awm_select_structure(x,t) {
	document.getElementById("AWM_menu_structure_use_existing_"+t).style.display = x?"":"none";
	document.getElementById("AWM_menu_structure_use_own_"+t).style.display = x?"none":"";
}
function awm_show_field(x,t,field) {
	elem = document.getElementById("awm_"+field+"_fieldset_"+t);
	elem.style.display = x?"":"none";
	document.getElementById("awm_"+field+"_dots_"+t).innerHTML = x?":":" ...";
}
function awm_disable_input(x,t,field) {
	elem = document.getElementById("awm_"+field+"_name_"+t);
	elem.disabled = x?false:true;
}

var tI;

function awm_max_min_value(elem, i){
	tI=i;
	if ((isNaN(elem.value) || elem.value<1 || elem.value >50) && elem.value.length > 0 ){
		if (elem.value<1) elem.value = 1
		if (elem.value>50) elem.value = 50
		
		document.getElementById('awm_max_value_notice_'+i).style.color = "red";
		setTimeout("document.getElementById('awm_max_value_notice_'+tI).style.color = '';" ,3000);
	}
	if ( elem.value.length == 0){
		document.getElementById('awm_max_value_notice_'+i).style.color = "red";
		setTimeout("document.getElementById('awm_max_value_notice_'+tI).style.color = '';" ,3000);
	}
}

function awm_disable_value(elem,e){
	var key;
	if (window.event) {
		key = e.keyCode;
		isCtrl = window.event.ctrlKey
	} else if(e.which) {
		key = e.which;
		isCtrl = e.ctrlKey;
	}
	
	if (isNaN(String.fromCharCode(key))) return false;
	return true;
}

function awm_menu_position(value, i){
	document.getElementById('awm_custom_menu_position_' + i).style.display = "none";
	document.getElementById('awm_widget_menu_position_' + i).style.display = "none";
	document.getElementById('awm_theme_menu_position_' + i).style.display = "none";
	if (value == 0 )
		document.getElementById('awm_custom_menu_position_' + i).style.display = "block";
	else if (value == "awm_widget")
		document.getElementById('awm_widget_menu_position_' + i).style.display = "block";
	else
		document.getElementById('awm_theme_menu_position_' + i).style.display = "block";
}


function awm_form_validate(){
	var i=0;
	var j=0;
	var errorMsg='';
	var exclude = new Array();
	for (i=0; i<AWM_TOTAL_TABS_JS; i++) {
		var message = "Name: \"" + document.getElementById("AWM_menu_name_"+i).value + "\" exists in menu tabs: " + (i+1);
		var message2= '';
		
		if (exclude.indexOf(document.getElementById("AWM_menu_name_"+i).value)==-1)
			for (j = i+1; j<AWM_TOTAL_TABS_JS; j++)
				if (document.getElementById("AWM_menu_name_"+i).value == document.getElementById("AWM_menu_name_"+j).value) 
					message2 += ", " + (j+1);
		
		if (message2.length > 0){
			errorMsg+= message + message2 + ". Names must be unique for each menu.\n";
			exclude[exclude.length] = document.getElementById("AWM_menu_name_"+i).value;
		}
	}
	if (errorMsg.length>0) alert("The following error(s) occured: \n" +errorMsg)
	else document.theform.submit();
}
