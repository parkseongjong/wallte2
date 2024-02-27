<?php 
require_once './config/config.php';
session_start();

//echo "ajay";
//echo md5(1);
require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;



$db = getDbInstance();

$adminAccountWalletAddress = "0xcea66e2f92e8511765bc1e2a247c352a7c84e895";
$adminAccountWalletPassword = "michael@cybertronchain.comZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";

if(empty($_SESSION['lang'])) {
	$_SESSION['lang'] = "ko";
}
$langFolderPath = file_get_contents("lang/".$_SESSION['lang']."/index.json");
$langArr = json_decode($langFolderPath,true);

function dec2hex($number)
{
    $hexvalues = array('0','1','2','3','4','5','6','7',
               '8','9','A','B','C','D','E','F');
    $hexval = '';
     while($number != '0')
     {
        $hexval = $hexvalues[bcmod($number,'16')].$hexval;
        $number = bcdiv($number,'16',0);
    }
    return $hexval;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') 
{
    $email 	= filter_input(INPUT_POST, 'email');
    $passwd = filter_input(INPUT_POST, 'passwd');
    $phone = filter_input(INPUT_POST, 'phone');
    $remember = filter_input(INPUT_POST, 'remember');
    $passwd5	=  md5($passwd);
   	$phone = str_replace("-","",$phone);
	$phone =  str_replace(" ","",$phone); //filter_input(INPUT_POST, 'passwd');
    //Get DB instance. function is defined in config.php
    $db = getDbInstance();
	
	
	/* if($email !="jkgbiolife@gmail.com"){
		header('Location:login.php');
		exit;
	} */
	
	$email = !empty($email) ? $email : $phone;
	
    $db->where ("email", $email);
    $db->where ("passwd", $passwd5);
    $row = $db->get('admin_accounts');
	

    if ($db->count >= 1) {
		$emailVerify = $row[0]['email_verify'];
		$registerWith = $row[0]['register_with'];
		if($emailVerify=="N"){
			if($registerWith=="email") {
				$_SESSION['login_failure'] = $langArr['plz_v_em_lon'];
			}
			else {
				$_SESSION['login_failure'] = $langArr['plz_v_ph_lon']."<a href='phoneverify.php'>".$langArr['cli_to_verify']."</a>";
			}
			header('Location:login.php');
			exit;
		}
        $_SESSION['user_logged_in'] = TRUE;
        $_SESSION['admin_type'] = $row[0]['admin_type'];
		$_SESSION['user_id'] = $row[0]['id'];
		$userId = $row[0]['id'];
		$userRole = $row[0]['admin_type'];
		$userDbEmail =  $row[0]['email'];
       	if($remember)
       	{
       		setcookie('username',$email , time() + (86400 * 90), "/");
       		setcookie('password',$passwd5 , time() + (86400 * 90), "/");
       	}
		
		/* check for ctc token available */
		$walletAddress = $row[0]['wallet_address'];
		$sendApproved = $row[0]['sendapproved'];
		$asmSendApproved = $row[0]['asm_send_approved'];
		$getEthBalance = 0;
		$coinBalance = 0;
		$web3 = new Web3('http://127.0.0.1:8545/');
		$eth = $web3->eth;
		
		// create walletAddress if not exists start
		$personal = $web3->personal;
		if(empty($walletAddress)){
			$walletAddress = '';
			$personal->newAccount($email.'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM', function ($err, $account) use (&$walletAddress) {
				if ($err !== null) {
					echo 'Error: ' . $err->getMessage();
					
				}
				else {
					$walletAddress = $account;
				}
			});
			$db = getDbInstance();
			$db->where("id", $userId);
			$updateArr = [] ;
			$updateArr['wallet_address'] =  $walletAddress;
			$last_id = $db->update('admin_accounts', $updateArr);
		}
		// create walletAddress if not exists end
	
		
		//$amountToSend = 0.0002;
		 $amountToSend = 0.0009;
		 if($sendApproved=='N' && $userRole!='admin' && $registerWith!="email") {
			
			
			$db = getDbInstance();
			$db->where ("user_id", $userId);
			$db->where ("coin_type", 'ctc');
			$db->where ("ethmethod", 'sendTransaction');
			$ethSendRow = $db->get('ethsend');
			
			$sendTransactionCoundShouldBe = ($userId <=167) ? 1 : 0 ;
			
			if($db->count <= $sendTransactionCoundShouldBe){
			
				
				
				$functionName = "balanceOf";
				$contract = new Contract($web3->provider, $testAbi);
				$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
					$coinBalance = reset($result)->toString();
				});
				 
				
			
					
					$getTxId = '';
					$fromAccount = $adminAccountWalletAddress;
					$fromAccountPassword = $adminAccountWalletPassword;
					$toAccount = $walletAddress;
					
					// unlock account
					$personal = $web3->personal;
					$personal->unlockAccount($fromAccount, $fromAccountPassword, function ($err, $unlocked) {
						
					});
					
					
					// send transaction
					$eth->sendTransaction([
						'from' => $fromAccount,
						'to' => $toAccount,
						//'value' => '0x27CA57357C000'
						'value' => '0x3328B944C4000',
						'gas' => '0x186A0',   //100000
						'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
						
					], function ($err, $transaction) use ($eth, $fromAccount, $toAccount, &$getTxId) {
						if ($err !== null) {
							echo 'Error: ' . $err->getMessage();
							//die;
						}
						else {
							$getTxId = $transaction;
						}

					});
					
					if(!empty($getTxId)) {
						$db = getDbInstance();
						$data_to_store = [];
						$data_to_store['user_id'] = $userId;
						$data_to_store['coin_type'] = 'ctc';
						$data_to_store['tx_id'] = $getTxId;
						$data_to_store['ethmethod'] = "sendTransaction";
						$data_to_store['amount'] = $amountToSend;
						$data_to_store['to_address'] = $toAccount;
						$data_to_store['from_address'] = $fromAccount;
						$last_id = $db->insert('ethsend', $data_to_store);
						//die;
					}
					
				
			}
			else {
				
				$db = getDbInstance();
				$db->where ("user_id", $userId);
				$db->where ("coin_type", 'ctc');
				$db->where ("ethmethod", 'sendTransaction');
				$ethSendRow = $db->get('ethsend');
				
				
					
					
					$approveTxId = '';
					$contract = new Contract($web3->provider, $testAbi);
					$senderAccount = $adminAccountWalletAddress;
					$ownerAccount = $walletAddress;
					$ownerAccountPassword = $userDbEmail."ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
					
					$personal = $web3->personal;
						$personal->unlockAccount($ownerAccount, $ownerAccountPassword, function ($err, $unlocked) {
							if ($err !== null) {
								echo 'Unlock Error: ' . $err->getMessage();
								
							}
							
						});
					
					$contract->at($contractAddress)->send('approve',$senderAccount, 5000000000000000000000000000, [
								'from' => $ownerAccount,
								'gas' => '0x186A0',   //100000
								'gasprice' =>'0x6FC23AC00'    //30000000000wei // 9 gwei
							], function ($err, $result) use ($contract, $senderAccount, &$approveTxId) {
								if ($err !== null) {
									echo 'Approval Error: ' . $err->getMessage();
									die;
								}
								else {
									$approveTxId = $result;
									//print_r($result);
								}
								
							});
					
					if(!empty($approveTxId)) {		
						$db = getDbInstance();
						$data_to_store = [];
						$data_to_store['user_id'] = $userId;
						$data_to_store['coin_type'] = 'ctc';
						$data_to_store['tx_id'] = $approveTxId;
						$data_to_store['ethmethod'] = "approve";
						$data_to_store['amount'] = 0;
						$data_to_store['to_address'] = $senderAccount;
						$data_to_store['from_address'] = $ownerAccount;
						$last_id = $db->insert('ethsend', $data_to_store);	
						
						$db = getDbInstance();
						$db->where("id", $userId);
						$last_id = $db->update('admin_accounts', ['sendapproved'=>"Y"]);
					}	
					
					 
				
			}
		}
		
		
		
		 
		
		/* check for ctc token available */
		
		//die("t2");
        header('Location:index.php');
        exit;
    } else {
		//die("t3");
        $_SESSION['login_failure'] = $langArr['invalid_ur_pass'];
        header('Location:login.php');
        exit;
    }
  
}