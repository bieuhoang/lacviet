<?php
$sotien = trim($_REQUEST['sotien']);
$noidung = trim($_REQUEST['noidung']);
if($noidung != null && $noidung != "" && $sotien != null && $sotien != "" && is_numeric($sotien)){
	$data= array();
	$data["tien"] = $sotien;
	$data["noidung"] = $noidung;
	DaiLy_F::add_themKhachHang($data);
}
?>
<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=napthemtien'); ?>"  enctype="multipart/form-data" >
<div class="formTitle">Tên khách hàng : </div>
<span id="errorten" style="color: red"></span><br>
<input type="text" name="sotien" id="sotien"/> (Đồng)<br>
<div class="formTitle">Nội dung: </div>
<span id="errornd" style="color: red"></span><br>
<textarea rows="4" cols="50" name="noidung" id="noidung">
</textarea>
<p class="wp_crm_save_changes_row">
<input type="button" value="GỬI THÔNG TIN NẠP TIỀN" class="button-primary" onclick="nap()">
</p>
</form>
<script type="text/javascript">
	function nap(){
		var tien = document.getElementById("sotien").value;
		var noidung = document.getElementById("noidung").value;
		if(tien === null || tien === "" || isNaN(tien)){
			document.getElementById("errortien").innerHTML ="Số tiền nạp phải là một số";
			return false;
		}else{
			document.getElementById("errortien").innerHTML ="";
		}
		if(noidung === null || noidung === ""){
			document.getElementById("errornd").innerHTML = "Nhập nội dung(Lý do) nạp tiền";
			return false;
		}else{
			document.getElementById("errornd").innerHTML = "";
		}
		document.getElementById("wp_crm_settings").submit();
	}
</script>