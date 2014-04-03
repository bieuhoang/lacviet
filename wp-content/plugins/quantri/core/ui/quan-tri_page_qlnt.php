<div style="text-transform:uppercase; text-transform: uppercase;width: 90%;text-align: center;font-size: 35px;margin-top: 30px;border-bottom: 1px solid;padding-bottom: 20px;">
Quản lý thông tin nạp tiền từ Đại lý
</div>
<?php
/*
 * Created on Mar 26, 2014
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
?>
<table border="1">
	<tbody><tr>
		<th>STT</th>
		<th>Khách hàng</th>		
		<th>Số tiền nạp</th>
		<th>Nội dung</th>
		<th>Ngày nạp</th>
		<th>Kích hoạt</th>
		<th>Xóa</th>
	</tr>
	<tr>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td></td>
		<td>
		<input type="button" style="background: #888;" value="Kích hoạt" class="button-primary"">
		</td>
		<td>
		<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=qlnt'); ?>" enctype="multipart/form-data" >
			<input type="submit" style="background: red;" value="Xóa" class="button-primary"">
		</form>
		</td>
	</tr>
</tbody></table>