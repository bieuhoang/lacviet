<div style="text-transform:uppercase; text-transform: uppercase;width: 90%;text-align: center;font-size: 35px;margin-top: 30px;border-bottom: 1px solid;padding-bottom: 20px;">
Quản lý các dịch vụ đại lý đăng ký
</div>
<?php 
if(isset($_GET['iddv']) && $_GET['iddv']> 0 && isset($_GET['type'])){
	quantri_F::qt_updateStatusDichVu($_GET['iddv'], $_GET['type']);
}
$listStatuss = quantri_F::qt_getListStatusDichVu();
$op = null;
if(isset($_GET['stt']) && $_GET['stt']> 0){
	$op[stt] = $_GET['stt'];
}
$dsdvs = quantri_F::qt_getListDichVu($op);
?>
Lọc tình trạng
<select name="tinhtrang" onchange="reloadStatus()" id="ttslt">
<option value=''>TẤt cả</option>;
<?php
$dataDs = array();
$nameDv = array();
$listAllDvs = quantri_F::qt_listAllDichVu();
foreach($listAllDvs as $listAllDv){
	$nameDv[$listAllDv->id] = $listAllDv->name;
}
foreach ($listStatuss as $listStatus){	
	$dataDs[$listStatus->num] = $listStatus->name;
	echo "<option value='$listStatus->num'>$listStatus->name</option>";
}
?>
</select>
<table border="1">
	<tbody><tr>
		<th>STT</th>
		<th>Khách hàng</th>		
		<th>Dịch vụ</th>
		<th>Tình trạng</th>
		<th>Ngày đăng ký</th>
		<th>Thời gian bắt đầu</th>
		<th>Thời gian kết thúc</th>
		<th>Kích hoạt</th>
		<th>Xóa</th>
	</tr>
	<?php $i = 0;
	foreach($dsdvs as $listDv){
		$i++;
		$tinhtrangDv = $dataDs[$listDv->status];
		$user = quantri_F::qt_userNameById($listDv->daiLy);
	?>
	<tr>
		<td><?php echo $i; ?></td>
	    <td><?php echo $user[0]->user_nicename;?></td>		
		<td><?php echo $nameDv[$listDv->dichVu]?></td>
		<td><?php echo $tinhtrangDv;?></td>		
		<td><?php echo $listDv->created;?></td>
		<td><?php echo $listDv->start;?></td>
		<td><?php echo $listDv->end;?></td>
		<td><?php if($listDv->status == 1){?>
		<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=qldvKh&type=2&iddv=').$listDv->id; ?>" enctype="multipart/form-data" >
			<input type="submit" value="Kích hoạt" class="button-primary"">
		</form>		
			<?php }else{?>
		<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=qldvKh&type=1&iddv=').$listDv->id; ?>" enctype="multipart/form-data" >
			<input type="submit" style="background: #888;" value="Tạm dừng" class="button-primary"">
		</form>		
			<?php }?></td>
		<td>
		<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=qldvKh&type=0&iddv=').$listDv->id; ?>" enctype="multipart/form-data" >
			<input type="submit" style="background: red;" value="Xóa" class="button-primary"">
		</form>	
		</td>
	</tr>
	<?php }?>
</tbody></table>

<script type="text/javascript">
function reloadStatus(){
	var tt= document.getElementById("ttslt").value;
	console.log(tt);
	window.location = "<?php echo admin_url('admin.php?page=qldvKh&stt='); ?>"+tt;
}
</script>