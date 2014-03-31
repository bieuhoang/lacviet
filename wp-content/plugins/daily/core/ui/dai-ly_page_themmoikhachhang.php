<div style="text-transform:uppercase; text-transform: uppercase;width: 90%;text-align: center;font-size: 35px;margin-top: 30px;border-bottom: 1px solid;padding-bottom: 20px;">
Thêm mới khách hàng
</div>
<?php
$ten = trim($_REQUEST['tenkh']);
$dv = trim($_REQUEST['dv']);
if($ten != null && $ten != ""){
	$data= array();
	$data["name"] = $ten;
	$data["dichvu"] = $dv;
	DaiLy_F::add_themKhachHang($data);
}
?>

<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=themmoikhachhang'); ?>"  enctype="multipart/form-data" >
<div class="formTitle">Tên khách hàng : </div>
<span id="errorten" style="color: red"></span><br>
<input type="text" name="tenkh" id="tenkh"/><br>
<div class="formTitle">Dịch vụ: </div>
<select name="dv">
<?php
$listDvs = DaiLy_F::getDichVuDaiLy();
foreach($listDvs as $listDv){?>
	<option value="<?php echo $listDv->id?>"><?php echo $listDv->name;?></option>
<?php } ?>
</select>
<p class="wp_crm_save_changes_row">
<input type="button" value="THÊM KHÁCH HÀNG" class="button-primary" onclick="nap()">
</p>
</form>
<script type="text/javascript">
	function nap(){
		var ten = document.getElementById("tenkh").value;
		if(ten === null || ten === ""){
			document.getElementById("errorten").innerHTML ="Tên khách hàng không được để trống";
			return false;
		}else{
			document.getElementById("errorten").innerHTML ="";
		}
		document.getElementById("wp_crm_settings").submit();
	}
</script>