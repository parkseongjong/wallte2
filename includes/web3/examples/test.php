<?php

require('./config.php');

 $transactionId = "0x4fb81ea5dc4528bd3abe894a27188ec17a586ef7c5985ed327b30ffc6ff4018f";
 
$web3->eth->getTransactionReceipt($transactionId, function ($err, $transaction) {
						if ($err !== null) {
							throw $err;
						}
						if ($transaction) {
						print_r($transaction); die;
							
						}
					});
?>