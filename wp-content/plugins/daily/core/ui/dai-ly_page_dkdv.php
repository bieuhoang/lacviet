<div style="text-transform:uppercase; text-transform: uppercase;width: 90%;text-align: center;font-size: 35px;margin-top: 30px;border-bottom: 1px solid;padding-bottom: 20px;">
Đăng ký mới dịch vụ
</div>
<?php
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	$data= array();
	$data["dichvu"] = $_REQUEST['dv'];
	$data["start"] = $_REQUEST['start'];
	$data["end"] = $_REQUEST['end'];	
if($data["start"] != "" && $data["end"] != ""){
	$insert = DaiLy_F::add_dangKyDichVu($data);
	if($insert != null){
		echo "<span style='color: red'>Dịch vụ đã được đăng ký. Bạn muốn <a href='#'> Gia gạn </a>?</span>";
	}else{
		echo "Thêm mới thành công. Xem <a href='admin.php?page=dsdvdk'>Danh sách Dịch vụ đã đăng ký</a>";
	}
}	
//$listDv = DaiLy_F::getDichVuDaiLy();
// print_r($listDv);
?>
<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=dkdv'); ?>"  enctype="multipart/form-data" >
<div class="formTitle">Dịch vụ: </div>
<select name="dv">
<?php
$listDvs = DaiLy_F::getDichVuDaiLy();
foreach($listDvs as $listDv){?>
	<option value="<?php echo $listDv->id;?>"><?php echo $listDv->name;?></option>
<?php } ?>
</select>
<div class="formTitle">Ngày bắt đầu: </div>
<input type="text" id="start" name="start" value=""/>
<div class="formTitle">Ngày Kết thúc đầu: </div>
<input type="text" id="end" name="end" value=""/>
<p class="wp_crm_save_changes_row">
<input type="button" value="THÊM DỊCH VỤ" class="button-primary" onclick="nap()">
</p>
</form>
<script type="text/javascript">
	function nap(){
		document.getElementById("wp_crm_settings").submit();
	}
	var myDate = new Date();
	var prettyDate =myDate.getDate() + '-'+(myDate.getMonth()+1)+'-'+ myDate.getFullYear();
	jQuery(document).ready(function() {
    jQuery('#end').datepicker({
        dateFormat : 'dd-mm-yy'
    });
    jQuery('#end').val(prettyDate);3
    jQuery('#start').val(prettyDate);
    jQuery('#start').datepicker({
        dateFormat : 'dd-mm-yy'
    });
});
</script>