<style>
.page-header {
	display:inline-block;
}
.head-nam-id {
	display:inline-block;
	font-weight: bold;
	margin-top: 46px;
    float: right;
}
.head-nam-id p {
	margin:0px;
}

.panel.panel-primary > .panel-heading {
	color: #000;
	background-color: #ffd602;
	border-color: #ffd602;
}

.panel.panel-green > .panel-heading {
	border-color: #d93233;
	color: white;
	background-color: #d93233;
}

.panel.panel-green {
	border:none;
}

.panel.panel-primary {
	border:none;
}

.panel-footer {
	color: #000;
	background-color: #fff;
}





</style>
<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';
require('includes/web3/vendor/autoload.php');

use Web3\Web3;
use Web3\Contract;

require('vendor/autoload.php');
use EthereumRPC\EthereumRPC;
use ERC20\ERC20;
$geth = new EthereumRPC('127.0.0.1', 8545);
// Instantiate ERC20 lib by passing Instance of EthereumRPC lib as constructor argument
$erc20 = new ERC20($geth);

$getBalance = 0;
$coinBalance = 0;
$EthCoinBalance=0;

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$walletAddress = $row[0]['wallet_address'];
$email = $row[0]['email'];
$username = $row[0]['name'];
$registerWith = $row[0]['register_with'];
$showHeader = ($registerWith=="email") ? $email : $row[0]['phone'];


if(!empty($walletAddress)) {

	$web3 = new Web3('http://127.0.0.1:8545/');
	$eth = $web3->eth;

	$eth->getBalance($walletAddress, function ($err, $balance) use (&$getBalance) {
		
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		$getBalance = $balance->toString();
		//echo 'Balance: ' . $balance . PHP_EOL;
	});
	
	$functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
		$coinBalance = reset($result)->toString();
	});
	
	
	

}


$getVal = $getBalance/1000000000000000000;
$_SESSION['eth_balance']	=	$getVal;


$coinBalance = $coinBalance/1000000000000000000;
$_SESSION['Token_balance']	=	$coinBalance;




//Get DB instance. function is defined in config.php
$db = getDbInstance();

//Get Dashboard information
$numCustomers = $db->getValue ("customers", "count(*)");


//Get DB instance. function is defined in config.php
$db = getDbInstance();
$db->where("user_id", $_SESSION['user_id']);
$pointSum = $db->getValue("store_transactions", "sum(points)");


$tokenPay = $erc20->token($tokenPayContractAddress);
$tokenPayBalance = $tokenPay->balanceOf($walletAddress);

include_once('includes/header.php');
?>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><?php echo !empty($langArr['dashboard']) ? $langArr['dashboard'] : "Dashboard"; ?></h1>
			<div class="head-nam-id"><p><?php echo $showHeader;?></p><span><?php echo $username;?></span></div>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <div class="row">
	<?php include('./includes/flash_messages.php') ?>
	
	  <!--<div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/logo3.png" class="dash-img" style="max-height:80px;">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;"><?php //echo number_format($coinBalance,8); ?></div>
                            <div><?php //echo !empty($langArr['tp_balance']) ? $langArr['tp_balance'] : "CTC Balance"; ?></div>
                        </div>
						
                    </div>
                </div>
                <a href="send_token.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php //echo !empty($langArr['send_tp_token']) ? $langArr['send_tp_token'] : "Send TP Token"; ?></span>
                        <span class="pull-right"><i class="fa fa-share"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>-->
	
	
        <div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/logo3.png" class="dash-img" style="max-height:80px;">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;"><?php echo number_format($coinBalance,8); ?></div>
                            <div><?php echo !empty($langArr['ctc_balance']) ? $langArr['ctc_balance'] : "CTC Balance"; ?></div>
                        </div>
						
                    </div>
                </div>
                <a href="send_token.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo !empty($langArr['send_ctc_token']) ? $langArr['send_ctc_token'] : "Send CTC Token"; ?></span>
                        <span class="pull-right"><i class="fa fa-share"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
        <div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/1321_64.png" class="dash-img" style="max-height:80px;">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;"><?php echo number_format($getVal,8); ?></div>
                            <div><?php echo !empty($langArr['eth_balance']) ? $langArr['eth_balance'] : "ETH Balance"; ?></div>
                        </div>
                    </div>
                </div>
                <a href="receive_token.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo !empty($langArr['receive']) ? $langArr['receive'] : "Receive"; ?></span>
                        <span class="pull-right"><i class="fa fa-download"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
       
	   
	   <div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/bee_points.png" class="dash-img" style="max-height:80px;">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;">? <?php echo number_format($pointSum,2); ?></div>
                            <div><?php echo !empty($langArr['bee_points']) ? $langArr['bee_points'] : "Bee Points"; ?></div>
                        </div>
                    </div>
                </div>
                <a href="store_transactions_user.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo !empty($langArr['transactions']) ? $langArr['transactions'] : "Transactions"; ?></span>
                        <span class="pull-right"><i class="fa fa-share"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
	   
        <div class="col-lg-6 col-md-6">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/exchange.png" class="dash-img" style="max-height:80px;">
                        </div>
                    </div>
                </div>
                <a href="ctckrw.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo !empty($langArr['ctc_to_krw']) ? $langArr['ctc_to_krw'] : "CTC to KRW"; ?></span>
                        <span class="pull-right"><i class="fa fa-share"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
		
		
		
		
		 <div class="col-lg-6 col-md-6" style="display:none;">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                           <img src="images/logo3.png" class="dash-img" style="max-height:80px;">
                        </div>
                        <div class="col-xs-9 text-right">
                            <div class="huge" style="font-size:15px;"><?php echo number_format($tokenPayBalance,8); ?></div>
                            <div><?php echo !empty($langArr['tp_balance']) ? $langArr['tp_balance'] : "TP Balance"; ?></div>
                        </div>
						
                    </div>
                </div>
                <a href="send_token.php">
                    <div class="panel-footer">
                        <span class="pull-left"><?php echo !empty($langArr['send_tp_token']) ? $langArr['send_tp_token'] : "Send TP Token"; ?></span>
                        <span class="pull-right"><i class="fa fa-share"></i></span>
                        <div class="clearfix"></div>
                    </div>
                </a>
            </div>
        </div>
		
    </div>
   
</div>
<!-- /#page-wrapper -->

<?php include_once('includes/footer.php'); ?>
