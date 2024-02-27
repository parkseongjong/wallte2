<?php
// Previously used pages (not used now)
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';


if(empty( $_SESSION['user_id'] )) {
	return;
	exit;
}


require('includes/web3/vendor/autoload.php');

use Web3\Web3;

$wallertAddress = '';


// for check wallertAddress is empty or not start 
$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
$userEmail = $row[0]['email'];
if ($db->count > 0) {
	$wallertAddress = $row[0]['wallet_address'];
}
else
{
	return;
	exit;
}

// for check wallertAddress is empty or not end


//Get Dashboard information
$numCustomers = $db->getValue ("customers", "count(*)");
include_once('includes/header.php');


if(empty($wallertAddress)){
	$web3 = new Web3('http://139.162.29.60:8545/');
	$personal = $web3->personal;
	$newAccount = '';
	// create account
	$personal->newAccount($userEmail, function ($err, $account) use (&$newAccount) {
		/* if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		} */
		$newAccount = $account;
		//echo 'New account: ' . $account . PHP_EOL;
	});

	$personal->unlockAccount($newAccount, $userEmail, function ($err, $unlocked) {
		/* if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		if ($unlocked) {
			echo 'New account is unlocked!' . PHP_EOL;
		} else {
			echo 'New account isn\'t unlocked' . PHP_EOL;
		} */
	});
	$wallertAddress = $newAccount;
	// update walletAddress into database
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$row = $db->update('admin_accounts',['wallet_address'=>$wallertAddress]);
}

//$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=ethereum:".$wallertAddress;
$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=".$wallertAddress;
?>

<style>
.showtxt{ text-align:center;}
.showtxt1{ text-align:center; font-size:25px;}
.panel.panel-primarys {
	text-align: center;
	color: #000;
	box-shadow: 1px 1px 5px #0000009c;
	background:#f1ecf7;
}

.panel {

}
</style>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header"><?php echo !empty($langArr['receive_token']) ? $langArr['receive_token'] : "Receive Token"; ?></h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <div class="row">
    <div class="col-lg-3"></div>
        <div class="col-lg-6 col-md-6">
            <div class="panel panel-primarys">
                <div class="panel-heading">
                    <div class="row">
                       <div class="col-md-12">
					   <img src="<?php echo $barCodeUrl; ?>" />
					   </div>
                        <div class="col-xs-12 text-right">
                            <div class="showtxt1"><?php echo !empty($langArr['wallet_address']) ? $langArr['wallet_address'] : "Wallet Address"; ?></div>
                            <div style="word-break:break-all" class="showtxt"><?php echo $wallertAddress; ?> </div>
                        </div>
                    </div>
                </div>
               
            </div>
        </div>
   
        <div class="col-lg-3 col-md-6">
        
        </div>
        <div class="col-lg-3 col-md-6">
            
        </div>
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-lg-8">


            <!-- /.panel -->
        </div>
        <!-- /.col-lg-8 -->
        <div class="col-lg-4">

            <!-- /.panel .chat-panel -->
        </div>
        <!-- /.col-lg-4 -->
    </div>
    <!-- /.row -->
</div>
<!-- /#page-wrapper -->

<?php include_once('includes/footer.php'); ?>
