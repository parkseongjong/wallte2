<?php 

//return; ////////////// (MJY)
//exit;

	$getPvtKey="";
	
		$passcode = $_GET['passcode'];
		$userpvtkey = $_GET['userpvtkey'];
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_PORT => "3000",
		 CURLOPT_URL => "http://195.201.168.34:3000/generate_passcode",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "{\n\t\"passcode\":\"".$passcode."\",\n\t\"pvtkey\":\"".$userpvtkey."\"\t\n}",
		  CURLOPT_HTTPHEADER => array(
			"cache-control: no-cache",
			"content-type: application/json",
			"postman-token: eb0783a3-f404-9d7c-b9ba-32ebeefe2c65"
		  ),
		));

		echo $response = curl_exec($curl);
		//$decodeResp = json_decode($response,true);
		//if(!empty($decodeResp)){
		//	$getPvtKey = $decodeResp['pvtKey'];
		//}
		echo $err = curl_error($curl);
	
	die;

?>