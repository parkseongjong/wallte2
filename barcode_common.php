<?php
// Page in use
if ( !empty($barCodeUrl) && !empty($walletAddress) ) {
	?>
	<div class="modal fade" id="myReceive" role="dialog">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><img src="images/icons/tmenu_qrcorde.png" alt="barcode" /></button>
					<!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
				</div>
				<div class="receive_new">
					<div class="barcode">
						<img id="barcodeimage" src="<?php echo $barCodeUrl; ?>" />
						<p><?php echo !empty($langArr['token_barcode_text1']) ? $langArr['token_barcode_text1'] : "My Wallet Address"; ?></p>
						<span class="showtxtpop"><?php echo $walletAddress;?></span>
					</div>
					<div class="btn1" onclick="myFunctionPop()"><?php echo !empty($langArr['token_barcode_text2']) ? $langArr['token_barcode_text2'] : "Copy Address"; ?></div>
				</div>
			</div>
		</div>
	</div>
	<script>
	function myFunctionPop() {
		var $temp = $("<input>");
		$("body").append($temp);
		$temp.val($("#myReceive .showtxtpop").text()).select();
		document.execCommand("copy");
		$temp.remove();
	
		//if (navigator.userAgent == 'android-web-view') {
		if (navigator.userAgent.indexOf("android-web-view") > - 1 || navigator.userAgent.indexOf("ios-web-view") > - 1){
			pop_message("<?php echo !empty($langArr['copy_finished']) ? $langArr['copy_finished'] : 'Copied!'; ?>");
		}
	}
	function showBarcode() {
		$("#myReceive").modal('show');
	}
	</script>
	<!--
	<div class="barcode_top">
	<img src="images/icons/barcode_open.png" alt="barcode" class="barcode_open" onclick="showBarcode()" />
	</div>
	-->
<?php } ?>