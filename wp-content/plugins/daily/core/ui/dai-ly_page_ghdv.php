<div style="text-transform:uppercase; text-transform: uppercase;width: 90%;text-align: center;font-size: 35px;margin-top: 30px;border-bottom: 1px solid;padding-bottom: 20px;">
Gia hạn dịch vụ
</div>
<?php
wp_enqueue_script('jquery-ui-datepicker');
wp_enqueue_style('jquery-style', 'http://ajax.googleapis.com/ajax/libs/jqueryui/1.8.2/themes/smoothness/jquery-ui.css');
	$data= array();
	if(isset($_GET['id']) && $_GET['id']> 0){		
		$data[id] = $_GET['id'];
	}
	$data["end"] = $_REQUEST['end'];	
if($data["end"] != null && $data["end"] != "" && isset($_GET['id']) && $_GET['id']> 0){
	$insert = DaiLy_F::dl_giahan_DichVu($data);
}
$listDvs = DaiLy_F::getDichVuDaiLy();
$nameDv = array();
foreach($listDvs as $listDv){
	$nameDv[$listAllDv->id] = $listAllDv->name;
} ?>
<form id="wp_crm_settings" method="post" action="<?php echo admin_url('admin.php?page=dkdv'); ?>"  enctype="multipart/form-data" >
<div class="formTitle">Dịch vụ: </div>

<div class="formTitle">Ngày bắt đầu: </div>
<input disabled type="text" id="start" name="start" value=""/>
<div class="formTitle">Ngày Kết thúc: </div>
<input type="text" id="end" name="end" value=""/>
<p class="wp_crm_save_changes_row">
<input type="button" value="THÊM DỊCH VỤ" class="button-primary" onclick="nap()">
</p>
</form>
<script type="text/javascript">
	if(getURLParameter("id") == null || getURLParameter("id") == ""){
		window.location = "<?php echo admin_url('admin.php?page=dsdvdk'); ?>";
	}
	var myDate = new Date();
	var prettyDate =myDate.getDate() + '-'+(myDate.getMonth()+1)+'-'+ myDate.getFullYear();
	jQuery(document).ready(function() {
    jQuery('#end').datepicker({
        dateFormat : 'dd-mm-yy'
    });
    jQuery('#end').val(prettyDate);
    jQuery('#start').val(prettyDate);
   // jQuery('#start').datepicker({
    //    dateFormat : 'dd-mm-yy'
   // });
});
function getURLParameter(name) {
  return decodeURIComponent((new RegExp('[?|&]' + name + '=' + '([^&;]+?)(&|#|;|$)').exec(location.search)||[,""])[1].replace(/\+/g, '%20'))||null
}
</script>