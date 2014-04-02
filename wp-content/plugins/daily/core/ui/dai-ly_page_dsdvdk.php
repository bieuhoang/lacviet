<div style="text-transform:uppercase; text-transform: uppercase;width: 90%;text-align: center;font-size: 35px;margin-top: 30px;border-bottom: 1px solid;padding-bottom: 20px;">
Danh sách các dịch vụ đã đăng ký
</div>
<?php 
if(isset($_GET['iddv']) && $_GET['iddv']> 0 && isset($_GET['type'])){
	DaiLy_F ::dl_updateStatusDichVu($_GET['iddv'], $_GET['type']);
}
$listStatuss = DaiLy_F ::dl_getListStatusDichVu();
$op = null;
if(isset($_GET['stt']) && $_GET['stt']> 0){
	$op[stt] = $_GET['stt'];
}
$dsdvs = DaiLy_F ::dl_getListDichVu($op);
?>
Lọc tình trạng
<select name="tinhtrang" onchange="reloadStatus()" id="ttslt">
<option value=''>TẤt cả</option>;
<?php
$dataDs = array();
$nameDv = array();
$listAllDvs = DaiLy_F ::dl_listAllDichVu();
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
		$user = DaiLy_F ::dl_userNameById($listDv->daiLy);
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
		<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=dsdvdk&type=2&iddv=').$listDv->id; ?>" enctype="multipart/form-data" >
			<input type="button" value="Sửa" class="button-primary"">
		</form>		
			<?php }else{?>
		<a href="<?php echo admin_url('admin.php?page=ghdv&id=').$listDv->id; ?>">
			<input type="button" style="background: #888;" value="Gia hạn" class="button-primary"">
		</a>		
			<?php }?></td>
		<td>
		<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=dsdvdk&type=0&iddv=').$listDv->id; ?>" enctype="multipart/form-data" >
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
	window.location = "<?php echo admin_url('admin.php?page=dsdvdk&stt='); ?>"+tt;
}
</script>