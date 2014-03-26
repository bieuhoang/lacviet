<div style="text-transform:uppercase; text-transform: uppercase;width: 90%;text-align: center;font-size: 35px;margin-top: 30px;border-bottom: 1px solid;padding-bottom: 20px;">
Danh sách các dịch vụ đang cung cấp
</div>
<?php
$tenDv = trim($_REQUEST['tenDv']);
$tinhtrang = trim($_REQUEST['tinhtrang']);
if($tenDv != null && $tenDv != ""){
	$data= array();
	$data["name"] = $tenDv;
	$data["status"] = $tinhtrang;
	quantri_F::qt_themDichVu($data);
}
$listDvs = quantri_F::getListDichVu();
$dsStatusDvs = quantri_F::getListStatusDichVu();
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
	<?php foreach($listDvs as $listDv){ $tinhtrangDv = $dataDs[$listDv->status];?>
	<tr>
		<td><?php echo $listDv->name?></td>
		<td><?php echo $tinhtrangDv;?></td>		
		<td><a href="#">SỬA</a></td>
		<td><a href="#">XÓA</a></td>
	</tr>
	<?php }?>
</tbody></table>