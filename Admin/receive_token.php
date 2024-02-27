<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';

require('includes/web3/vendor/autoload.php');

use Web3\Web3;

//$web3 = new Web3('http://192.168.99.100:8545/');
$web3 = new Web3('http://139.162.29.60:8545/');

//Get DB instance. function is defined in config.php
$db = getDbInstance();

//Get Dashboard information
$numCustomers = $db->getValue ("customers", "count(*)");

include_once('includes/header.php');



$personal = $web3->personal;
/* $newAccount = '';
// create account
$personal->newAccount('123456', function ($err, $account) use (&$newAccount) {
	if ($err !== null) {
	    echo 'Error: ' . $err->getMessage();
		return;
	}
	$newAccount = $account;
	echo 'New account: ' . $account . PHP_EOL;
});

$personal->unlockAccount($newAccount, '123456', function ($err, $unlocked) {
	if ($err !== null) {
		echo 'Error: ' . $err->getMessage();
		return;
	}
	if ($unlocked) {
        echo 'New account is unlocked!' . PHP_EOL;
	} else {
	    echo 'New account isn\'t unlocked' . PHP_EOL;
	}
}); */

//$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|2&cht=qr&chl=bitcoin:".$wallertAddress."?amount=".$btcCoins;
$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|2&cht=qr&chl=bitcoin:0x2f8f93154510d8a868c4602ba4a5461640191a6d?amount=100";
?>

<style>
.showtxt{ text-align:left;}
</style>
<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Receive Token</h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-lg-3 col-md-6">
            <div class="panel panel-primarys">
                <div class="panel-heading">
                    <div class="row">
                       <div class="col-md-12">
					   <img src="<?php echo $barCodeUrl; ?>" />
					   </div>
                        <div class="col-xs-9 text-right">
                            <div class="showtxt">Wallet Address</div>
                            <div class="showtxt">0x2f8f93154510d8a868c4602ba4a5461640191a6d </div>
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
