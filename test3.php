<?php
exit();
require_once './config/config.php';

require_once(__DIR__ . '/messente_api/vendor/autoload.php');



	
$wallet_array = array(


"0x08934a0956415c3c9a0da603fa94d8daf0a353bf",
"0x441bac29b271c0738d219832a486f99f00ca7f81",
"0xbb49dd066f1841bc0a6fa3bf06d76e0a6e94e438",
"0xd35127efa1f5370e4b56d5ede16879003da1003a",
"0x0375fd02d66a02b2e338cc7e0e70a59fe7bb0753",
"0x3b794bfc219f8e7e5f3b1f2c6a02bc38aafa6681",
"0xf09e1298782bdd034d5629f44f874c281a448479",
"0x63a97472d9456265444ded924ee1976986820825",
"0x8b93557c84ae9dac705a4bad63672d586b069e7e",
"0x2098587d3f69f719369929db4e107e2c2eae7ea6",
"0x4e6b666014551472506d657d341a966e3c1994eb",
"0x7966737d5d1901062380edc84fa21f3377d708be",
"0x52e42734b947285cf0dcfbc12f8ff99576f6fe9d",
"0xe9970cb7ce95cdd825d76e70ea801430d20b91ee",
"0x8ab340d9bd5d9a505ef9af5270db2f0913ae71ab",
"0xcbff4b0c71c25c7c22480bd6c201564fe3c3e9ca",
"0x559b4072022c0cef3eb22b09dfb9cd1b71500441",
"0xb482983396350a5b0b41a8f6ec1b17a8aefa494b",
"0xbf0d86ddd7c97df96a76ffd91d05e56da1976a6c",
"0x8b8f4204a4f002cca7b57d67b9609e8c4bc7adff",
"0xb98e6f5b31455e59f25ecdf5bb3acccb56133e45",
"0x1e213da982b2680a3d5aa09c983988039258703d",
"0x6647e6057a6b9dd9242817ea6485eb2a5ea0d35e",
"0x52372858ae8c4f58569edb8f88f26f927c8a4904",
"0x68e8b269dd49fe4a49feea9e83339b807156f22c",
"0x7a32ead1801292d0d308befa9624f4c69420773f",
"0x70f31a72e38b9826abe5c0e7d737d9d8bbd87ba2",
"0x3bad9cf4fd548eaf35955952f7eb7ea7be5b1e1b",
"0x87fdf783304f7efc09c6434cfdede6fefa1cfe77",
"0x11cb365312667fe126100585a8707e874f31ce47",
"0xc600a6e331f3bab2f04c4a86e695b968923a613a",
"0x8bec5aa79ca8ecfa17e134ad72eeefffec7018de",
"0x120e71227e17c09711cbb158c31f529892e635a9",
"0x976c49f1b2478e2ffad54de80b603e5cc870eead",
"0xa11c449eec5dd164c082b96faad106c0a06d097c",
"0xe9f552dcb37a9b33a1031e06248d04cb49cc99e2",
"0x011940720188fb092233a25ffc1ac49db7b0cb1b",
"0x0c4f2610b6e986dc3e48e19e2c7d491af576c531",
"0x375f3c70437bc5ee0274230da8bab9e44b426071",
"0x31e21146fe0513e298ffd06db36bf6a8e8ae95d1",
"0xf0c7706d61c4c683172bccd8d0efd30f70614f9c",
"0xa34e55ae0e1e8e47cfc7b85bd8add6477f2b358d",
"0x5239ad0b45da671ca2cd200a8e911ac653d9b561",
"0xedf977c47f9bb2c76ea7a36fd6bb3261c7023465",
"0xc99b6e9ba2e3487c1c87c2b14434405d9623dbb2",
"0x813d3c9a97f0f4babd9d2934ec547733667c2c88",
"0x5b2cd5c4dcd2514dabf2909a5aae1a6c3f582ae4",
"0xdadd6fce67a341bda46db3ada1d7a909ca8f93cd",
"0xc9d111ed57213a0094b10ea298aaa81921b65548",
"0x65b96d0de850ea065843dade6979c041cbed3fc7"


); 


$arrlength=count($wallet_array);


return;
exit;



for($x=0;$x<$arrlength;$x++)
  {
	$Address = $wallet_array[$x];

	$getPvtKey = '';
	if(empty($getPvtKey)){
		$userWalletAddress = $Address;
//		$userWalletPass = 'onefamilyhappynewyear2020ONEFAMILYHAPPYNEWYEAR2020';
		$userWalletPass = 'market2020MARKET@)@)barrybarriesBARRYBARRIES@)@)MARKET2020market';


//		$userWalletPass = "+821076588676ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM";
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_PORT => "3000",
		 CURLOPT_URL => "http://127.0.0.1:3000/getpvtkey",
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
			echo "<br>";

		}
		//$err = curl_error($curl);
	}
	echo $getPvtKey; 
	
	
	
}
?>