<?php
/*
Plugin Name: Nap tien (III)
Plugin URI: http://
Description: Plugin thuc hien chuc nang Dai ly thong bao nap tien vao tai khoan den he thong, He thong kiem tra va kich hoat thong tin nap tien cua dai ly.
Version: 1.0.0
Author: BieuHv
Author URI: http://
License: Lacviet pay by the hour
*/

global $wp_version;
if(version_compare($wp_version, '3.8.0', '<')){
	exit("Plugin này chỉ hoạt động tốt ở phiên bản 3.8.0 trở lên.<br> Phiên bản hiện tại của ban: $wp_version .<br> Làm ơn cập nhật phiên bản mới hơn.");
}

function bhvFillterBadWord($contents){
	$bad = array("Cuc", "dm", "dkm", "b");
	return str_ireplace($bad, "*BIEU*", $contents);
}



function bhvEmailComments(){
	$email = $_POST['email'];
	$name = $_POST['author'];
	$message = "Chao ong $name, Thanks";
	wp_mail($email, 'post from WP', $message);
}


add_filter('the_content', 'bhvFillterBadWord');
add_filter('the_content', strtolower);
add_action('comment_post', 'bhvEmailComments');
?>
