<?php
$msglist=array();
function addmsgbox($tpye="danger",$message="Something went wrong.",$dismissible=true){
	global $msglist;
	$msglist[]=array("type"=>$tpye,"message"=>$message,"dismissible"=>$dismissible);
}
function showmsgbox(){
	global $msglist;
	foreach($msglist as $temp){
		if($temp["dismissible"]){
			?>
			<div class="alert alert-<?php echo $temp["type"]; ?> alert-dismissible" role="alert" style="text-align: center; margin: 0;">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
				<?php echo $temp["message"];?>
			</div>
			<?php
		}else {
	?>
		<div class="alert alert-<?php echo $temp["type"]; ?>" role="alert" style="text-align: center; margin: 0;">
			<?php echo $temp["message"];?>
		</div>
	<?php
		}
	}
}