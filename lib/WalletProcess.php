<?php
// Page in use

class WalletProcess {

	protected $new_server_ip = '195.201.168.34';
	protected $new_server_port = 8545;
	protected $ethApiKey = 'ehtkey';

	// Token balance check
	// $token : eth, ctc, tp3, usdt, krw, mc
	// $contractArr : config/config.php : $contractAddressArr
	public function wi_get_balance($token, $walletAddress, $contractArr) {
		if($walletAddress=="s" || empty($walletAddress) ){
			return 0;
		}

		if ( $token == 'all' ) {
			
		} else {
			$get_balance = 0;
			
			$contractAddress = $contractArr[$token]['contractAddress'];
			$decimal = $contractArr[$token]['decimal'];
			$abi = $contractArr[$token]['abi'];

			require('/var/www/html/wallet2/includes/web3/vendor/autoload.php');
			$functionName = "balanceOf";
			$web3 = new Web3\Web3('http://'.$this->new_server_ip.':'.$this->new_server_port.'/');
			$eth = $web3->eth;
			try {
				switch ($token) {
					case 'eth':
						$eth->getBalance($walletAddress, function ($err, $balance) use (&$get_balance, &$decimal) {
							if ( !empty($err) ) {
								throw new Exception($err->getMessage(), 92);
							}
							$get_balance = $balance->toString();
							$get_balance = $get_balance/$decimal;
						});
						break;
					default:
							$contract = new Web3\Contract($web3->provider, $abi);
							$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$get_balance, &$decimal){
								if ( !empty($err) ) {
									throw new Exception($err->getMessage(), 93);
								}
								if ( !empty( $result )) {
									$get_balance = reset($result)->toString();
									$get_balance = $get_balance/$decimal;
								}
							});
						break;
				} // switch
			} catch(Exception $e) {
				$get_balance = -1;
				$this->wi_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
			}

		}

		return $get_balance;
	} //


	// Log
	protected function wi_fn_logSave($log)
	{
		$logPathDir = "/var/www/html/wallet2/_log";  //로그위치 지정

		$filePath = $logPathDir."/".date("Y")."/".date("n");
		$folderName1 = date("Y"); //폴더 1 년도 생성
		$folderName2 = date("n"); //폴더 2 월 생성

		if(!is_dir($logPathDir."/".$folderName1)){
			mkdir($logPathDir."/".$folderName1, 0777);
		}
		
		if(!is_dir($logPathDir."/".$folderName1."/".$folderName2)){
			mkdir(($logPathDir."/".$folderName1."/".$folderName2), 0777);
		}
		
		$log_file = fopen($logPathDir."/".$folderName1."/".$folderName2."/".date("Ymd").".txt", "a");
		fwrite($log_file, date("Y-m-d H:i:s ").$log."\r\n");
		fclose($log_file);
	}


}
	