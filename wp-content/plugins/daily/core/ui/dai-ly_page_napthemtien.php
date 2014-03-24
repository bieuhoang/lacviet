<?php
$sotien = $_REQUEST['sotien'];
$noidung = $_REQUEST['noidung'];
$data= array();
$data["tien"] = $sotien;
$data["noidung"] = $noidung;
DaiLy_F::add_naptienLog($data);

?>
<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=napthemtien'); ?>"  enctype="multipart/form-data" >
<div class="formTitle">Số tiền nạp : </div><input type="text" name="sotien"/> (Đồng)<br>
<div class="formTitle">Nội dung: </div>
<textarea rows="4" cols="50" name="noidung">
</textarea>
<p class="wp_crm_save_changes_row">
<input type="submit" value="GỬI THÔNG TIN NẠP TIỀN" class="button-primary" name="Submit">
</p>
</form>