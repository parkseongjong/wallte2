<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

require_once 'includes/header.php'; 
?>
<link  rel="stylesheet" href="css/send.css?ver=2.1.1"/>

<div id="page-wrapper">
	<div id="exchange_tabs" class="send_common">

		<?php include('./includes/flash_messages.php') ?>
		<div class="row">
	
			<div class="col-sm-12 col-md-12 form-part-token">
				<div class=""><!-- panel -->
				<!-- main content -->
					<div id="main_content" class="panel-body">
					   <!-- page heading -->


						<div class="charging_tab">
							<!--<p class="profile_subject"><?php echo !empty($langArr['charge_menu_tab']) ? $langArr['charge_menu_tab'] : "Transformation"; ?></p>-->
							<ul>
								<li><a href="exchange.php" alt="exchange"><?php echo !empty($langArr['buy_ctc']) ? $langArr['buy_ctc'] : "Charging CTC"; ?> (ETH -&gt; CTC)</a></li>
								<li><a href="exchange_tp3.php" alt="exchange"><?php echo !empty($langArr['buy_tp3']) ? $langArr['buy_tp3'] : "Charging TP3"; ?> (ETH -&gt; TP3)</a></li>
								<li><a href="exchange_etoken_re.php?token=ectc" alt="exchange"><?php echo !empty($langArr['buy_ctc']) ? $langArr['buy_ctc'] : "Charging CTC"; ?> (E-CTC -&gt; CTC)</a></li>
								<li><a href="exchange_etoken_re.php?token=etp3" alt="exchange"><?php echo !empty($langArr['buy_tp3']) ? $langArr['buy_tp3'] : "Charging TP3"; ?> (E-TP3 -&gt; TP3)</a></li>
								<li><a href="exchange_etoken_re.php?token=emc" alt="exchange"><?php echo !empty($langArr['buy_mc']) ? $langArr['buy_mc'] : "Charging MC"; ?> (E-MC -&gt; MC)</a></li>
							</ul>
						</div>



					</div><!-- main_content -->
				</div>
			</div><!-- col-sm-12 col-md-12 form-part-token -->
		</div><!-- row -->
	</div>
</div>
	
	
	

<?php include_once 'includes/footer.php'; ?>