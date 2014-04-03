<div style="text-transform:uppercase; text-transform: uppercase;width: 90%;text-align: center;font-size: 35px;margin-top: 30px;border-bottom: 1px solid;padding-bottom: 20px;">
Thêm mới dịch vụ cung cấp
</div>
<?php
$id = 0;
$thisDv = array();
if(isset($_GET['id']) && $_GET['id']> 0){
	$id = $_GET['id'];
	$thisDv = quantri_F::qt_getDvuById($id);
}
$tenDv = trim($_REQUEST['tenDv']);
$tinhtrang = trim($_REQUEST['tinhtrang']);
if($tenDv != null && $tenDv != ""){
	$data= array();
	$data["name"] = $tenDv;
	$data["status"] = $tinhtrang;
	$them = quantri_F::qt_themDichVu($data, $id);
	if($them != null){
		echo "<span style='color: red'>Tên dịch vụ đã được sử dụng trên hệ thống</span>";
	}else{
		echo "Thêm mới thành công";
	}
}
$listStatuss = quantri_F::qt_getListStatusDichVu();
?>
<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=themdv&id=').$id; ?>"  enctype="multipart/form-data" >
<div class="formTitle">Tên dịch vụ : </div>
<span id="erorTen" style="color: red"></span><br>
<input type="text" name="tenDv" id="tenDv" value="<?php echo $thisDv[0]->name;?>"/><br>
<div class="formTitle">Tình trạng: </div>
<select name="tinhtrang">
<?php
foreach ($listStatuss as $listStatus){
	echo "<option value='$listStatus->num'>$listStatus->name</option>";
}
?>
</select>
<p class="wp_crm_save_changes_row">
<input type="button" value="THÊM MỚI DỊCH VỤ" class="button-primary" onclick="themdv()">
</p>
</form>
<script type="text/javascript">
	function themdv(){
		var tendv = document.getElementById("tenDv").value;
		if(tendv === null || tendv === ""){
			document.getElementById("erorTen").innerHTML ="Phải nhập tên dịch vụ";
			return false;
		}else{
			document.getElementById("erorTen").innerHTML ="";
		}
		document.getElementById("wp_crm_settings").submit();
	}
</script>