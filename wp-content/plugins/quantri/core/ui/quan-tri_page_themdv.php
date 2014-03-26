<div style="text-transform:uppercase; text-transform: uppercase;width: 90%;text-align: center;font-size: 35px;margin-top: 30px;border-bottom: 1px solid;padding-bottom: 20px;">
Thêm mới dịch vụ cung cấp
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
$listStatuss = quantri_F::getListStatusDichVu();
?>
<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=themdv'); ?>"  enctype="multipart/form-data" >
<div class="formTitle">Tên dịch vụ : </div>
<span id="erorTen" style="color: red"></span><br>
<input type="text" name="tenDv" id="tenDv"/><br>
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