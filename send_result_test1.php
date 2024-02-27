<?php
// Test Page : 20.08.28
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once './includes/auth_validate.php';

// 전송 결과 화면

use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";

require_once 'includes/header.php'; 
	
$send_result = '';
$to_name = '';
$virtual = '';
$send_sms_r = '';

if ($_SERVER['REQUEST_METHOD'] == 'GET' )	{
	$txid = !empty($_GET['txid']) ? $_GET['txid'] : '';
	$type = !empty($_GET['type']) ? $_GET['type'] : ''; // send
	$virtual_account_tx1 = '';

	if ( empty($txid) ) { // 전송실패
		$send_result = 'F';
	} else {


		$db = getDbInstance();
		$db->where("id", $txid);
		$row = $db->get('user_transactions_all');


		// 200828
		$send_result_store = '';
		if ( !empty($row[0]['id']) && $row[0]['send_type'] == 'send' ) {
			$db = getDbInstance();
			$db->where("wallet_address", $row[0]['to_address']);
			$kiosk_row = $db->getOne('kiosk_config');

			if ( empty($row[0]['transactionId']) ) { // 전송 실패
				$send_result_store = 'F';
			} else {
				$send_result_store = 'Y';
			}
			if ( !empty($kiosk_row['name']) ) {
					$db = getDbInstance();
					$db->where("id", $row[0]['id']);
					$updateArr = [] ;
					$updateArr['store_name'] = $kiosk_row['name'];
					$updateArr['store_result'] = $send_result_store;
					$last_id = $db->update('user_transactions_all', $updateArr);
			}
		}






	} // if

}

?>

<link  rel="stylesheet" href="css/send.css"/>
</head>

<body>

<div id="page-wrapper">
	
	<div id="send_result">
		
	</div>

</div>

</body>
</html>


<?php include_once 'includes/footer.php'; ?>
