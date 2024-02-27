<?php 

include "../config/new_config.php";


$ip = new_getUserIpAddr();
//if ( $ip == '211.117.42.16' ) {

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		$mode = $_POST['mode'];
		$passwd = $_POST['passwd'];
		if ( $passwd != 'cybertronchain' ) {
			header('location: set.php');
			exit();
		}


		if ( $mode == 'set_passcode' ) {
			
			if ( !empty($_POST['passcode1']) && !empty($_POST['key1']) ) {
				$passcode = $_POST['passcode1'].$n_wallet_pass_key;
				$userpvtkey = $_POST['key1'];

				$curl = curl_init();

				curl_setopt_array($curl, array(
					CURLOPT_PORT => "3000",
					CURLOPT_URL => "http://195.201.168.34:3000/generate_passcode",
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_ENCODING => "",
					CURLOPT_MAXREDIRS => 10,
					CURLOPT_TIMEOUT => 60,
					CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
					CURLOPT_CUSTOMREQUEST => "POST",
					CURLOPT_POSTFIELDS => "{\n\t\"passcode\":\"".$passcode."\",\n\t\"pvtkey\":\"".$userpvtkey."\"\t\n}",
					CURLOPT_HTTPHEADER => array(
						"cache-control: no-cache",
						"content-type: application/json",
						"postman-token: eb0783a3-f404-9d7c-b9ba-32ebeefe2c65"
					),
				));
				
				echo '처리 결과 : <br />';
				echo $response = curl_exec($curl);
				echo $err = curl_error($curl);
			}

		}
		else if ( $mode == 'get_key' ) {

			$getPvtKey="";
			
			if ( !empty($_POST['wallet_address1']) && !empty($_POST['passcode2']) ) {
				$userWalletAddress = $_POST['wallet_address1'];
				$userWalletPass = $_POST['passcode2'].$n_wallet_pass_key;

				$curl = curl_init();

				curl_setopt_array($curl, array(
					CURLOPT_PORT => "3000",
					CURLOPT_URL => "http://195.201.168.34:3000/getpvtkey",
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
				if($response == 'message authentication code mismatch'){
					echo $response;
				}
				else{
					$decodeResp = json_decode($response,true);
					if(!empty($decodeResp)){
						$getPvtKey = $decodeResp['pvtKey'];
					}
				
					echo $err = curl_error($curl);
				}
	
			}
			
			echo '<br />Key : <br />';
			echo $getPvtKey;
			
		}

	}

//}

?>