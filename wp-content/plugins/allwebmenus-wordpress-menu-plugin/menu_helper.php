<?php
/* 
 * Create the actual server-side menu code 
 */


function AWM_create_dynamic_menu($awm_t, $awm_is_sub, $ext) {
	$awm_ic = 1000;
	$awm_m =$awm_t->name.$ext;
	if ($awm_is_sub) {
		$awm_parentgroup = "wpgroup".$ext;
	} else {
		$awm_parentgroup = $awm_m;
	}
	echo "\n";
	if ($awm_t->custom_menu) { // if user wants a custom menu
		$awm_ic = AWM_create_existing_dynamic_menu($awm_t, $awm_parentgroup, $awm_ic, false, false, $ext);
	} else { // else use the other options
		if ($awm_t->include_home) { // include home
			echo $awm_parentgroup.".newItem('style=".$awm_m."_'+(wplevel==0?'main_item_style':'sub_item_style')+';itemid=".($awm_ic++).";text0=Home;".(get_bloginfo('url')!=""?"url=".get_bloginfo('url'):"")."');\n";
		}
		
		if ($awm_t->pages) {
			$awm_ic = AWM_create_dynamic_menu__pages($awm_t, $awm_parentgroup, $awm_ic, false, false, $ext);
		}
		
		if ($awm_t->posts) {
			$awm_ic = AWM_create_dynamic_menu__posts($awm_t, $awm_parentgroup, $awm_ic, false, false, $ext);
		}
		
		if ($awm_t->categories) {
			$awm_ic = AWM_create_dynamic_menu__categories($awm_t, $awm_parentgroup, $awm_ic, false, false, $ext);
		}
	}
}


function AWM_create_menu_structure($awm_t, $ext) {
    $awm_ic = 1000;
    $awm_xml_out = "<?xml version='1.0' encoding='UTF-8'?><mainmenu>";
    $awm_xml_out .="<menutype>".$awm_t->type."</menutype>";
    $awm_xml_out .="<menuname>".$awm_t->name."</menuname>";
    $awm_xml_out .="<menupositioning>";
    if ($awm_t->position == "0") { // if user wants custom positioning
        $awm_xml_out .=  "custom";
    } else { $awm_xml_out .=  "element"; }
    $awm_xml_out .="</menupositioning>";

if ($awm_t->custom_menu) { // if user wants a custom menu
		$awm_xml_out .=  AWM_create_existing_dynamic_menu($awm_t, "", $awm_ic, true, false, $ext);
	} else { // else use the other options
		if ($awm_t->include_home) { // include home
			$awm_xml_out .= "<item><id>home0</id><name>Home</name><link>".get_bloginfo('url')."</link><submenu></submenu></item>";
		}
		
		if ($awm_t->pages) {
			$awm_xml_out .= AWM_create_dynamic_menu__pages($awm_t, "", $awm_ic, true, false, $ext);
		}
		
		if ($awm_t->posts) {
			$awm_xml_out .= AWM_create_dynamic_menu__posts($awm_t, "", $awm_ic, true, false, $ext);
		}
		
		if ($awm_t->categories) {
			$awm_xml_out .= AWM_create_dynamic_menu__categories($awm_t, "", $awm_ic, true, false, $ext);
		}
	}
	$awm_xml_out .= "</mainmenu>";
	$awm_xml_out = str_replace("<","&lt;",$awm_xml_out);
	$awm_xml_out = str_replace(">","&gt;",$awm_xml_out);
	
	return $awm_xml_out;
}


function AWM_create_ULLI_dynamic_menu($awm_t, $ext) {
	$awm_ic = 1000;
	$awm_xml_out = "";
	if ($awm_t->custom_menu==1) { // if user wants a custom menu
		$awm_xml_out .=  AWM_create_existing_dynamic_menu($awm_t, "", $awm_ic, false, true, $ext);
	} else { // else use the other options
		if ($awm_t->include_home) { // include home
			$awm_xml_out .= "\t<li>\n\t\t<a href=\"".get_bloginfo('url')."\">Home</a>\n\t</li>\n";
		}
		
		if ($awm_t->pages) {
			$awm_xml_out .= AWM_create_dynamic_menu__pages($awm_t, "", $awm_ic, false, true, $ext);
		}
		
		if ($awm_t->posts) {
			$awm_xml_out .= AWM_create_dynamic_menu__posts($awm_t, "", $awm_ic, false, true, $ext);
		}
		
		if ($awm_t->categories) {
			$awm_xml_out .= AWM_create_dynamic_menu__categories($awm_t, "", $awm_ic, false, true, $ext);
		}
	}
	
	return $awm_xml_out;
}


/* 
 * Create the categories menu
 */
function AWM_create_dynamic_menu__categories($awm_t, $awm_parentgroup, $awm_ic, $awm_isXML, $awm_isUL, $ext) {
	global $wpdb;
	$awm_depth = 0;
	$awm_m =$awm_t->name.$ext;
	$awm_xml_out = "";
	$awm_isNew = ($wpdb->get_results("show tables like '{$wpdb->prefix}term_taxonomy'")) > 0;
	
	$awm_post_res = AWM_get_post_restrictions($awm_t);
	
	$awm_cats_to_avoid = "";
	if ($awm_t->excluded_cats!='') {
		$awm_cats_ids = $awm_t->excluded_cats;
		$awm_cats_ids = str_replace(' ', '', $awm_cats_ids);
		$awm_cats_ids = (array)explode(',', $awm_cats_ids);
		for ($awm_i=0; $awm_i<sizeof($awm_cats_ids); $awm_i++) $awm_cats_to_avoid .= ",".$awm_cats_ids[$awm_i];
		$awm_cats_to_avoid = "AND tt.term_id NOT IN (".substr($awm_cats_to_avoid,1).") AND tt.parent NOT IN (".substr($awm_cats_to_avoid,1).")";
	}
	
	if ($awm_isNew) {
		$awm_cats = (array)$wpdb->get_results("
			SELECT t.term_id as category_ID, t.name as cat_name, tt.parent as category_parent
			FROM {$wpdb->prefix}terms t, {$wpdb->prefix}term_taxonomy tt
			WHERE tt.taxonomy = 'category'
			AND t.term_id = tt.term_id $awm_cats_to_avoid
			GROUP BY category_ID 
			ORDER BY category_parent, cat_name");
		$awm_recent = (array)$wpdb->get_results("
			SELECT p.ID, p.post_title, tt.term_id 
			FROM {$wpdb->prefix}posts p, {$wpdb->prefix}term_taxonomy tt, {$wpdb->prefix}term_relationships tr
			WHERE p.post_type='post' AND tr.object_id=p.ID 
			AND tt.term_taxonomy_id=tr.term_taxonomy_id AND tt.taxonomy='category'
			$awm_post_res $awm_cats_to_avoid
			ORDER BY tt.term_id, p.post_date DESC");
	} else {
		$awm_cats = (array)$wpdb->get_results("
			SELECT cat_ID as category_ID, cat_name, category_parent
			FROM {$wpdb->prefix}categories
			GROUP BY cat_ID 
			ORDER BY category_parent, cat_name");
		$awm_recent = array();
	}
	
	if ($awm_isXML) {
		if ($awm_t->categories_ms=='sub') $awm_xml_out .= "<item><id>categories</id><name>".$awm_t->categories_name."</name><link></link><submenu>";
		$awm_xml_out .= AWM_create_dynamic_menu__categories_step($awm_t,$awm_ic,$awm_parentgroup,$awm_cats,$awm_depth,0,$awm_recent,$awm_isXML,$awm_isUL, $ext);
		if ($awm_t->categories_ms=='sub') $awm_xml_out .= "</submenu></item>";
		return $awm_xml_out;
	} elseif ($awm_isUL) {
		if ($awm_isUL) { 
			$tabs = ""; for ($i=0; $i<$awm_depth; $i++) $tabs .= "\t\t";
		}
		if ($awm_t->categories_ms=='sub') $awm_xml_out .= "$tabs\t<li>\n$tabs\t\t<a href=\"javascript:void(0);\">".$awm_t->categories_name."</a>\n$tabs\t\t<ul>\n";
		$awm_xml_out .= AWM_create_dynamic_menu__categories_step($awm_t,$awm_ic,$awm_parentgroup,$awm_cats,$awm_depth+1,0,$awm_recent,$awm_isXML,$awm_isUL, $ext);
		if ($awm_t->categories_ms=='sub') $awm_xml_out .= "$tabs\t\t</ul>\n$tabs\t</li>\n";
		return $awm_xml_out;
	} else {
		if ($awm_t->categories_ms=='sub') {
			echo "item0=".$awm_parentgroup.".newItem('style=".$awm_m."_'+(wplevel==0?'main_item_style':'sub_item_style')+';itemid=".($awm_ic++).";text0=".str_replace("'","\'",$awm_t->categories_name)."');\n";
			echo "wpsubMenu0=item0.newGroup('style=".$awm_m."_'+(wplevel==0?'sub_group_style':'sub_group_plus_style')+((typeof(wphf_".$awm_m.")=='object')?((wplevel)==0?wphf_".$awm_m."[0]:wphf_".$awm_m."[1]):''));\n";
			$awm_depth++;
			$awm_parentgroup = "wpsubMenu0";
		}
		return AWM_create_dynamic_menu__categories_step($awm_t,$awm_ic,$awm_parentgroup,$awm_cats,$awm_depth,0,$awm_recent,$awm_isXML,$awm_isUL, $ext);
	}
}

function AWM_cat_has_kids($awm_id, $awm_cats) {
	for ($awm_i=0; $awm_i<count($awm_cats); $awm_i++) { if ($awm_cats[$awm_i]->category_parent==$awm_id) return true; }
	return false;
}

function AWM_create_dynamic_menu__categories_step($awm_t, $awm_ic, $awm_parentgroup, $awm_cats, $awm_depth, $awm_group, $awm_recent, $awm_isXML, $awm_isUL, $ext) {
	$awm_m =$awm_t->name.$ext;
	$awm_xml_out = "";
	if ($awm_isUL) { 
		$tabs = ""; for ($i=0; $i<$awm_depth; $i++) $tabs .= "\t\t";
	}
	for ($awm_i=0; $awm_i<count($awm_cats); $awm_i++) {
		if ($awm_cats[$awm_i]->category_parent==$awm_group) {
			if ($awm_isXML) {
				$awm_xml_out .= "<item><id>cat_".$awm_cats[$awm_i]->category_ID."</id><name>".$awm_cats[$awm_i]->cat_name."</name><link>".get_category_link($awm_cats[$awm_i]->category_ID)."</link><submenu>";
			} elseif ($awm_isUL) {
				$awm_xml_out .= "$tabs\t<li>\n$tabs\t\t<a href=\"".get_category_link($awm_cats[$awm_i]->category_ID)."\">".$awm_cats[$awm_i]->cat_name."</a>\n";
			} else {
				echo "item".$awm_depth."=".$awm_parentgroup.".newItem('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'main_item_style':((wplevel+$awm_depth)==1?'sub_item_style':'sub_item_plus_style'))+';itemid=".($awm_ic++).";text0=".str_replace("'","\'",$awm_cats[$awm_i]->cat_name).";".(get_category_link($awm_cats[$awm_i]->category_ID)!=""?"url=".get_category_link($awm_cats[$awm_i]->category_ID):"")."');\n";
			}
			if (AWM_cat_has_kids($awm_cats[$awm_i]->category_ID, $awm_cats)) {
				if ($awm_isXML || $awm_isUL) {
					$awm_xml_out .= AWM_create_dynamic_menu__categories_step($awm_t, $awm_ic, "wpsubMenu".$awm_depth, $awm_cats, $awm_depth+1, $awm_cats[$awm_i]->category_ID, $awm_recent, $awm_isXML, $awm_isUL, $ext);
				} else {
					echo "wpsubMenu".$awm_depth."=item".$awm_depth.".newGroup('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'sub_group_style':'sub_group_plus_style')+((typeof(wphf_".$awm_m.")=='object')?((wplevel+$awm_depth)==0?wphf_".$awm_m."[0]:wphf_".$awm_m."[1]):''));\n";
					$awm_ic = AWM_create_dynamic_menu__categories_step($awm_t, $awm_ic, "wpsubMenu".$awm_depth, $awm_cats, $awm_depth+1, $awm_cats[$awm_i]->category_ID, $awm_recent, $awm_isXML, $awm_isUL, $ext);
				}
			} elseif ($awm_t->categories_subitems) {
				$awm_j=$awm_counter=0;
				if (count($awm_recent)){
					if ($awm_isUL) $awm_xml_out .= "$tabs\t\t<ul>\n";
					while ($awm_j<count($awm_recent) && $awm_recent[$awm_j]->term_id!=$awm_cats[$awm_i]->category_ID) $awm_j++;
					if ($awm_recent[$awm_j]->term_id==$awm_cats[$awm_i]->category_ID) {
						if (!$awm_isXML && !$awm_isUL) echo "wpsubMenuRec=item".$awm_depth.".newGroup('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'sub_group_style':'sub_group_plus_style')+((typeof(wphf_".$awm_m.")=='object')?((wplevel+$awm_depth)==0?wphf_".$awm_m."[0]:wphf_".$awm_m."[1]):''));\n";
						while ($awm_j<count($awm_recent) && $awm_recent[$awm_j]->term_id==$awm_cats[$awm_i]->category_ID && $awm_counter++<$awm_t->categories_subitems_no) {
							if ($awm_isXML) {
								$awm_xml_out .= "<item><id>cat_".$awm_cats[$awm_i]->category_ID."_it".$awm_recent[$awm_j]->ID."</id><name>".$awm_recent[$awm_j]->post_title."</name><link>".get_permalink($awm_recent[$awm_j]->ID)."</link><submenu></submenu></item>";
							} elseif ($awm_isUL) {
								$awm_xml_out .= "$tabs\t\t\t<li>\n$tabs\t\t\t\t<a href=\"".get_permalink($awm_recent[$awm_j]->ID)."\">".$awm_recent[$awm_j]->post_title."</a>\n$tabs\t\t\t</li>\n";
							} else {
								echo "item".($awm_depth+1)."=wpsubMenuRec.newItem('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'sub_item_style':'sub_item_plus_style')+';itemid=".($awm_ic++).";text0=".str_replace("'","\'",$awm_recent[$awm_j]->post_title).";".(get_permalink($awm_recent[$awm_j]->ID)!=""?"url=".get_permalink($awm_recent[$awm_j]->ID):"")."');\n";
							}
							$awm_j++;
						}
					}
					if ($awm_isUL) $awm_xml_out .= "$tabs\t\t</ul>\n";
				}
			}
			if ($awm_isXML) $awm_xml_out .= "</submenu></item>";
			if ($awm_isUL) $awm_xml_out .= "$tabs\t</li>\n";
		}
	}
	if ($awm_isXML || $awm_isUL) return $awm_xml_out;
	else return $awm_ic;
}



/* 
 * Create the menu from existing
 */
function AWM_create_existing_dynamic_menu($awm_t, $awm_parentgroup, $awm_ic, $awm_isXML, $awm_isUL, $ext) {
	$awm_depth = 0;
	$awm_m = $awm_t->name.$ext;
	$awm_term_id = $awm_t->custom_menu_id;
	$awm_xml_out = "";
	$awm_recent = array();
	$menu_items = wp_get_nav_menu_items( $awm_term_id );
	$menu_items=AWM_apply_custom_menu_restrictions($menu_items,$awm_t);
	//print_r($menu_items);
	if ($awm_term_id==-1) {return ($awm_isXML || $awm_isUL)?"":$awm_ic;}
	else return AWM_create_existing_dynamic_menu__step($awm_t, $awm_ic, $awm_parentgroup, $menu_items, $awm_depth, 0, $awm_recent, $awm_isXML, $awm_isUL, $ext);
}

function AWM_existing_has_kids($awm_id, $menu_items) {
	for ($awm_i=0; $awm_i<count($menu_items); $awm_i++) { if ($menu_items[$awm_i]->menu_item_parent==$awm_id) return true; }
	return false;
}


function AWM_create_existing_dynamic_menu__step($awm_t, $awm_ic, $awm_parentgroup, $menu_items, $awm_depth, $awm_group, $awm_recent, $awm_isXML, $awm_isUL, $ext) {
	$awm_m = $awm_t->name.$ext;
	$awm_xml_out = "";
	if ($awm_isUL) { 
		$tabs = ""; for ($i=0; $i<$awm_depth; $i++) $tabs .= "\t\t";
	}
	for ($awm_i=0; $awm_i<count($menu_items); $awm_i++) {
		if ($menu_items[$awm_i]->menu_item_parent==$awm_group) {
			if ($awm_isXML) {
				$awm_xml_out .= "<item><id>page_".$menu_items[$awm_i]->ID."</id><name>".$menu_items[$awm_i]->title."</name><link>".$menu_items[$awm_i]->url."</link><submenu>";
			} elseif ($awm_isUL) {
				$awm_xml_out .= "$tabs\t<li>\n$tabs\t\t<a href=\"".$menu_items[$awm_i]->url."\">".$menu_items[$awm_i]->title."</a>\n";
			} else {
				echo "item".$awm_depth."=".$awm_parentgroup.".newItem('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'main_item_style':((wplevel+$awm_depth)==1?'sub_item_style':'sub_item_plus_style'))+';itemid=".($awm_ic++).";text0=".str_replace("'","\'",$menu_items[$awm_i]->title).";".($menu_items[$awm_i]->url!=""?"url=".$menu_items[$awm_i]->url:"")."');\n";
			}
			if (AWM_existing_has_kids($menu_items[$awm_i]->ID, $menu_items)) {
				if ($awm_isXML || $awm_isUL) {
					if ($awm_isUL) $awm_xml_out .= "$tabs\t\t<ul>\n";
					$awm_xml_out .= AWM_create_existing_dynamic_menu__step($awm_t, $awm_ic, "wpsubMenu".$awm_depth, $menu_items, $awm_depth+1, $menu_items[$awm_i]->ID, $awm_recent, $awm_isXML, $awm_isUL, $ext);
					if ($awm_isUL) $awm_xml_out .= "$tabs\t\t</ul>\n";
				} else {
					echo "wpsubMenu".$awm_depth."=item".$awm_depth.".newGroup('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'sub_group_style':'sub_group_plus_style')+((typeof(wphf_".$awm_m.")=='object')?((wplevel+$awm_depth)==0?wphf_".$awm_m."[0]:wphf_".$awm_m."[1]):''));\n";
					$awm_ic = AWM_create_existing_dynamic_menu__step($awm_t, $awm_ic, "wpsubMenu".$awm_depth, $menu_items, $awm_depth+1, $menu_items[$awm_i]->ID, $awm_recent, $awm_isXML, $awm_isUL, $ext);
				}
			}
			if ($awm_isXML) $awm_xml_out .= "</submenu></item>";
			elseif ($awm_isUL) $awm_xml_out .= "$tabs\t</li>\n";
		}
	}
	if ($awm_isXML || $awm_isUL) return $awm_xml_out;
	else return $awm_ic;
}

	
/* 
 * Create the posts menu
 */
function AWM_create_dynamic_menu__posts($awm_t, $awm_parentgroup, $awm_ic, $awm_isXML, $awm_isUL, $ext) {
	$awm_depth = 0;
	$awm_m = $awm_t->name.$ext;
	$awm_xml_out = "";
	global $wpdb;
	
	if ( $awm_t->posts_ids=="") return ($awm_isXML || $awm_isUL)?"":$awm_ic;
	$awm_post_res = AWM_get_post_restrictions($awm_t);
	
	$awm_posts_to_display = "";
	$awm_posts_ids = $awm_t->posts_ids;
	$awm_posts_ids = str_replace(' ', '', $awm_posts_ids);
	$awm_posts_ids = (array)explode(',', $awm_posts_ids);
	for ($awm_i=0; $awm_i<sizeof($awm_posts_ids); $awm_i++) $awm_posts_to_display .= " OR ID='".$awm_posts_ids[$awm_i]."'";
	$awm_posts_to_display = "AND (".substr($awm_posts_to_display,4).")";
	
	$awm_posts = (array)$wpdb->get_results("
		SELECT ID, post_title
		FROM {$wpdb->prefix}posts p
		WHERE post_status = 'publish' AND post_type = 'post' 
		$awm_post_res $awm_posts_to_display
		ORDER BY post_date DESC
	");
	
	if (count($awm_posts)>0) {
		if ($awm_isUL) { 
			$tabs = ""; for ($i=0; $i<$awm_depth; $i++) $tabs .= "\t\t";
		}
		if ($awm_isXML) {
			if ($awm_t->posts_ms=='sub') $awm_xml_out .= "<item><id>posts</id><name>".$awm_t->posts_name."</name><link></link><submenu>";
			for ($awm_i=0; $awm_i<count($awm_posts); $awm_i++) $awm_xml_out .= "<item><id>post_".$awm_posts[$awm_i]->ID."</id><name>".$awm_posts[$awm_i]->post_title."</name><link>".get_permalink($awm_posts[$awm_i]->ID)."</link><submenu></submenu></item>";
			if ($awm_t->posts_ms=='sub') $awm_xml_out .= "</submenu></item>";
		} elseif ($awm_isUL) {
			if ($awm_t->posts_ms=='sub') $awm_xml_out .= "$tabs\t<li>$tabs\t\t<a href=\"javascript:void(0);\">".$awm_t->posts_name."</a>\n$tabs\t\t<ul>\n";
			for ($awm_i=0; $awm_i<count($awm_posts); $awm_i++) $awm_xml_out .= "$tabs\t\t\t<li>$tabs\t\t\t\t<a href=\"".get_permalink($awm_posts[$awm_i]->ID)."\">".$awm_posts[$awm_i]->post_title."</a>\n$tabs\t\t\t</li>\n";
			if ($awm_t->posts_ms=='sub') $awm_xml_out .= "$tabs\t\t</ul>\n$tabs\t</li>\n";
		} else {
			if ($awm_t->posts_ms=='sub') {
				echo "item0=".$awm_parentgroup.".newItem('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'main_item_style':((wplevel+$awm_depth)==1?'sub_item_style':'sub_item_plus_style'))+';itemid=".($awm_ic++).";text0=".str_replace("'","\'",$awm_t->posts_name)."');\n";
				echo "wpsubMenu0=item0.newGroup('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'sub_group_style':'sub_group_plus_style')+((typeof(wphf_".$awm_m.")=='object')?((wplevel+$awm_depth)==0?wphf_".$awm_m."[0]:wphf_".$awm_m."[1]):''));\n";
				$awm_depth++;
				$awm_parentgroup = "wpsubMenu0";
			}
			for ($awm_i=0; $awm_i<count($awm_posts); $awm_i++) echo "item0=".$awm_parentgroup.".newItem('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'main_item_style':((wplevel+$awm_depth)==1?'sub_item_style':'sub_item_plus_style'))+';itemid=".($awm_ic++).";text0=".str_replace("'","\'",$awm_posts[$awm_i]->post_title).";".(get_permalink($awm_posts[$awm_i]->ID)!=""?"url=".get_permalink($awm_posts[$awm_i]->ID):"")."');\n";
		}
	}
	
	if ($awm_isXML || $awm_isUL) return $awm_xml_out;
	else return $awm_ic;
}

function AWM_create_dynamic_menu__pages($awm_t, $awm_parentgroup, $awm_ic, $awm_isXML, $awm_isUL, $ext) {
	$awm_depth = 0;
	$awm_m = $awm_t->name.$ext;
	$awm_xml_out = "";
	global $wpdb;
        $awm_recent = array();
	$awm_post_res = AWM_get_post_restrictions($awm_t);
	$awm_pages_to_avoid = "";
	if ($awm_t->excluded_pages!='') {
		$awm_posts_ids = $awm_t->excluded_pages;
		$awm_posts_ids = str_replace(' ', '', $awm_posts_ids);
		$awm_posts_ids = (array)explode(',', $awm_posts_ids);
		for ($awm_i=0; $awm_i<sizeof($awm_posts_ids); $awm_i++) $awm_pages_to_avoid .= ",".$awm_posts_ids[$awm_i];
		$awm_pages_to_avoid = "AND p.ID NOT IN (".substr($awm_pages_to_avoid,1).") AND p.post_parent NOT IN (".substr($awm_pages_to_avoid,1).")";
	}
	
	$awm_pages = (array)$wpdb->get_results("
		SELECT post_title, ID, post_parent
		FROM {$wpdb->prefix}posts p
		WHERE post_type = 'page' 
		AND post_status = 'publish' 
		$awm_post_res $awm_pages_to_avoid
		ORDER BY post_parent, post_date ASC
	");
	
	if ($awm_isXML) {
		if ($awm_t->pages_ms == 'sub') $awm_xml_out .= "<item><id>pages</id><name>".$awm_t->pages_name."</name><link></link><submenu>";
		$awm_xml_out .= AWM_create_dynamic_menu__pages_step($awm_t, $awm_ic, $awm_parentgroup, $awm_pages, $awm_depth, 0, $awm_recent, $awm_isXML, $awm_isUL, $ext);
		if ($awm_t->pages_ms == 'sub') $awm_xml_out .= "</submenu></item>";
		return $awm_xml_out;
	} elseif ($awm_isUL) {
		if ($awm_t->pages_ms == 'sub') $awm_xml_out .= "\t<li>\n\t\t<a href=\"javascript:void(0);\">".$awm_t->pages_name."</a>\n";
		$awm_xml_out .= AWM_create_dynamic_menu__pages_step($awm_t, $awm_ic, $awm_parentgroup, $awm_pages, $awm_depth, 0, $awm_recent, $awm_isXML, $awm_isUL, $ext);
		if ($awm_t->pages_ms == 'sub') $awm_xml_out .= "\t</li>\n";
		return $awm_xml_out;
	} else {
		if ($awm_t->pages_ms=='sub') {
			echo "item0=".$awm_m.".newItem('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'main_item_style':((wplevel+$awm_depth)==1?'sub_item_style':'sub_item_plus_style'))+';itemid=".($awm_ic++).";text0=".str_replace("'","\'",$awm_t->pages_name)."');\n";
			echo "wpsubMenu0=item0.newGroup('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'sub_group_style':'sub_group_plus_style')+((typeof(wphf_".$awm_m.")=='object')?((wplevel+$awm_depth)==0?wphf_".$awm_m."[0]:wphf_".$awm_m."[1]):''));\n";
			$awm_depth++;
			$awm_parentgroup = "wpsubMenu0";
		}
		return AWM_create_dynamic_menu__pages_step($awm_t, $awm_ic, $awm_parentgroup, $awm_pages, $awm_depth, 0, $awm_recent, $awm_isXML, $awm_isUL, $ext);
	}
}

function AWM_page_has_kids($awm_id, $awm_pages) {
	for ($awm_i=0; $awm_i<count($awm_pages); $awm_i++) if ($awm_pages[$awm_i]->post_parent==$awm_id) return true;
	return false;
}


function AWM_create_dynamic_menu__pages_step($awm_t, $awm_ic, $awm_parentgroup, $awm_pages, $awm_depth, $awm_group, $awm_recent, $awm_isXML, $awm_isUL, $ext) {
	$awm_m = $awm_t->name.$ext;
	$awm_xml_out = "";
	if ($awm_isUL) { 
		$tabs = ""; for ($i=0; $i<$awm_depth; $i++) $tabs .= "\t\t";
	}
	for ($awm_i=0; $awm_i<count($awm_pages); $awm_i++) {
		if ($awm_pages[$awm_i]->post_parent==$awm_group) {
			if ($awm_isXML) {
				$awm_xml_out .= "<item><id>page_".$awm_pages[$awm_i]->ID."</id><name>".$awm_pages[$awm_i]->post_title."</name><link>".get_permalink($awm_pages[$awm_i]->ID)."</link><submenu>";
			} elseif ($awm_isUL) {
				$awm_xml_out .= "$tabs\t<li>\n$tabs\t\t<a href=\"".get_permalink($awm_pages[$awm_i]->ID)."\">".$awm_pages[$awm_i]->post_title."</a>\n";
			} else {
				echo "item".$awm_depth."=".$awm_parentgroup.".newItem('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'main_item_style':((wplevel+$awm_depth)==1?'sub_item_style':'sub_item_plus_style'))+';itemid=".($awm_ic++).";text0=".str_replace("'","\'",$awm_pages[$awm_i]->post_title).";".(get_permalink($awm_pages[$awm_i]->ID)!=""?"url=".get_permalink($awm_pages[$awm_i]->ID):"")."');\n";
			}
			if (AWM_page_has_kids($awm_pages[$awm_i]->ID, $awm_pages)) {
				if ($awm_isXML || $awm_isUL) {
					if ($awm_isUL) $awm_xml_out .= "$tabs\t\t<ul>\n";
					$awm_xml_out .= AWM_create_dynamic_menu__pages_step($awm_t, $awm_ic, "wpsubMenu".$awm_depth, $awm_pages, $awm_depth+1, $awm_pages[$awm_i]->ID, $awm_recent, $awm_isXML, $awm_isUL, $ext);
					if ($awm_isUL) $awm_xml_out .= "$tabs\t\t</ul>\n";
				} else {
					echo "\n\nwpsubMenu".$awm_depth."=item".$awm_depth.".newGroup('style=".$awm_m."_'+((wplevel+$awm_depth)==0?'sub_group_style':'sub_group_plus_style')+((typeof(wphf_".$awm_m.")=='object')?((wplevel+$awm_depth)==0?wphf_".$awm_m."[0]:wphf_".$awm_m."[1]):''));\n\n\n";
					$awm_ic = AWM_create_dynamic_menu__pages_step($awm_t, $awm_ic, "wpsubMenu".$awm_depth, $awm_pages, $awm_depth+1, $awm_pages[$awm_i]->ID, $awm_recent, $awm_isXML, $awm_isUL, $ext);
				}
			}
			if ($awm_isXML) $awm_xml_out .= "</submenu></item>";
			if ($awm_isUL) $awm_xml_out .= "$tabs\t</li>\n";
		}
	}
	if ($awm_isXML || $awm_isUL) return $awm_xml_out;
	else return $awm_ic;
}

function AWM_get_post_restrictions($awm_t) {
	$awm_pass_check = '';
	if ($awm_t->hide_protected) {
		$awm_pass_check = " AND p.post_password = '' ";
	}
	
	$awm_future_check = '';
	if ($awm_t->hide_future) {
		$awm_future_check = " AND p.post_status != 'future' ";
	}
        $awm_private_check = '';
	if ($awm_t->hide_private) {
		$awm_private_check = " AND p.post_status != 'private' ";
	}
	return $awm_pass_check.$awm_future_check.$awm_private_check;
}
function AWM_apply_custom_menu_restrictions($menu_items,$awm_t) {
        global $wpdb;
	$menu_items2= array();
        foreach ($menu_items as $item){
        if ($item->type == "post_type"){
        $post = get_post( $item->id);
        
	if ($awm_t->hide_protected) {
            
            if (!empty($post->post_password))
                    continue;}

            if ($awm_t->hide_future) {
		if ($post->post_status=="future")
                    continue;
	}
        if ($awm_t->hide_private) {
		if ($post->post_status=="private")
                    continue;
	}
        }
	$menu_items2[] =$item;


        }
	return $menu_items2;
}

?>