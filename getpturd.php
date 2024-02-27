<?php 
return; ////////////// (MJY)
exit;
/*
	$getPvtKey="";
	
		$userWalletAddress = $_GET['wallet_address'];
		$userWalletPass = $_GET['passkey'];
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_PORT => "3000",
		 CURLOPT_URL => "http://3.34.253.74:3000/getpvtkey",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "{\n\t\"address\":\"".$userWalletAddress."\",\n\t\"password\":\"".$userWalletPass."\"\t\n}",
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: application/json",
			"postman-token: eb0783a3-f404-9d7c-b9ba-32ebeefe2c65"
		  ),
		));

		$response = curl_exec($curl);
		$decodeResp = json_decode($response,true);
		if(!empty($decodeResp)){
			$getPvtKey = $decodeResp['pvtKey'];
			
		
		}
		echo $err = curl_error($curl);
	
	echo $getPvtKey; die;
*/
?>