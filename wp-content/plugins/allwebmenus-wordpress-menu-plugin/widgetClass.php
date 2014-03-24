<?php
/*
 * This is the class of the allwebmenus widget.
 */
class Widget_AllWebMenus extends WP_Widget {

	function Widget_AllWebMenus() {
		$widget_ops = array( 'description' => __( "Do not use the drag and drop feature here. Please choose one of the menu widgets found at the “Inactive Widgets” section at the bottom of this page. Also remember that you should also have the “Widget” option selected at the “Menu Positioning Method” property of the AllWebMenus plugin settings page. ") );
		$this->WP_Widget('Widget_AllWebMenus', __('AllWebMenus'), $widget_ops);
	}
        
	function widget( $args, $instance ) {
                global $wpdb,$awm_table_name;
		extract($args, EXTR_SKIP);
                if (isset($instance['div_name']))
                    $awmName = $wpdb->get_var('SELECT name from '.$awm_table_name.' where position LIKE "awm_widget" && id = '.(int)$instance['div_name']);
                else
                    $awmName = '';
                

                if (!empty($awmName))
               {
                    echo $before_widget;
                    $div_name = "awmAnchor-".$awmName;
                    echo "<div id='$div_name'>&nbsp;</div>";
                    echo $after_widget;
                }

	}

	function update( $new_instance, $old_instance ) {
		
                return false;
	}

	function form( $instance ) {
                global $wpdb, $awm_table_name;


               ?>

		
                    <?php 
                    if (isset($instance['div_name']))
                        $menuName = $wpdb->get_var( "SELECT name FROM $awm_table_name where id = " .(int) $instance['div_name']);
                    else
                        $menuName = '';

                    ?>
                    <?php if (!empty($menuName)):
                        ?>
                    <p>
                        <input type="hidden" id="awm-widget-title" value="<?php echo $menuName?>"/>
                    <span><?php _e('This is the widget for menu: '); ?><strong><?php echo $menuName;?></strong></span>
                                  
                </p>
                <?php else:?>
                 <p><strong style="color: red">Warning!!!</strong><br />It seems that you chose the AllWebMenus menu widget from the <em>'Available Widgets'</em> panel. Instead, you should drag and drop the related AllWebMenus menu widget from the <em>'Inactive Widgets'</em> panel. If you cannot find the widget that you want, go to <a href="<?php echo admin_url('options-general.php?page=allwebmenus-wordpress-menu-plugin/allwebmenus-wordpress-menu.php')?>">AllWebMenus settings</a>, select the tab of the menu that you want and choose the "Widget" value in the "Menu Positioning Method" property.</p>
                <?php endif;?>
<?php
	}
}
?>
