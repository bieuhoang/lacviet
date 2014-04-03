<div style="text-transform:uppercase; text-transform: uppercase;width: 90%;text-align: center;font-size: 35px;margin-top: 30px;border-bottom: 1px solid;padding-bottom: 20px;">
Danh sách các dịch vụ đang cung cấp
</div>
<?php
if(isset($_GET['idx']) && $_GET['idx']> 0){
	quantri_F::qt_xoaDvuById($_GET['idx']);
}
$tenDv = trim($_REQUEST['tenDv']);
$tinhtrang = trim($_REQUEST['tinhtrang']);
if($tenDv != null && $tenDv != ""){
	$data= array();
	$data["name"] = $tenDv;
	$data["status"] = $tinhtrang;
	quantri_F::qt_themDichVu($data);
}
$listDvs = quantri_F::qt_getListDichVuCungcap();
$dsStatusDvs = quantri_F::qt_getListStatusDichVu();
$dataDs = array();
foreach($dsStatusDvs as $dsStatusDv){
	$dataDs[$dsStatusDv->num] = $dsStatusDv->name;
}
?>
<table border="1">
	<tbody><tr>
		<th>Tên Dịch vụ</th>
		<th>Tình trạng</th>		
		<th>Sửa</th>
		<th>Xóa</th>
	</tr>
	<?php foreach($listDvs as $listDv){
		$tinhtrangDv = $dataDs[$listDv->status];?>
	<tr>
		<td><?php echo $listDv->name?></td>
		<td><?php echo $tinhtrangDv;?></td>		
		<td><a href="<?php echo admin_url('admin.php?page=themdv&id=').$listDv->id; ?>"><input type="button" value="Sửa" class="button-primary""></a></td>
		<td><form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=dsdv&idx=').$listDv->id; ?>" enctype="multipart/form-data" >
			<input type="submit" style="background: red;" value="Xóa" class="button-primary"">
		</form></td>
	</tr>
	<?php }?>
</tbody></table>