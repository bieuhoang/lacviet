<?php
/*
Plugin Name: Dai ly
Plugin URI: http://
Description: Plugin thuc hien chuc nang Dai ly thong bao nap tien vao tai khoan den he thong va chuc nang dang ky khach hang moi.
Version: 1.0.0
Author: BieuHv
Author URI: http://
License: Lacviet pay by the hour
*/


//add_action('admin_menu', 'wpautop_control_menu');

add_action('admin_menu', 'your_menu');

function your_menu () {
	add_users_page('Tạo mới khách hàng', 'Tạo khách hàng', 1, "", 'some_function');
	add_users_page('Nạp tiền', 'Nạp tiền', 1, "", 'some_function');
}
?>
