<?php
// 테스트용. 잔액조회
//session_start();
//require_once './config/config.php';
//require_once './config/new_config.php';
//require_once './includes/auth_validate.php';
// https://cybertronchain.com/wallet2/get_balances.php

// $getbalance_read_type : empty / 'web3'
// $get_balances_type = array('ctc'=>'T', 'tp3'=>'T', 'usdt'=>'T', 'mc'=>'T', 'krw'=>'T');

//$walletAddress = '0xf4a587c23316691f8798cf08e3b541551ec1ffcb';

$n_balances = array('eth'=>0, 'ctc'=>0, 'tp3'=>0, 'usdt'=>0, 'mc'=>0, 'krw'=>0);

$getBalance = 0;
$balance_eth = 0; // ETH
$balance_ctc = 0; // CTC
$balance_tp3 = 0; // TP3
$balance_usdt = 0; // USDT
$balance_mc = 0; // MC
$balance_krw = 0; // KRW


require('vendor/autoload.php');
use EthereumRPC\EthereumRPC;
use ERC20\ERC20;

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

if ( empty($getbalance_read_type)) {

	$geth = new EthereumRPC($n_connect_ip, $n_connect_port);
	$erc20 = new ERC20($geth);

	try {
		$tk = 'eth';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$getBalance = $geth->eth()->getBalance($walletAddress);
			$balance_eth = $getBalance;
			//$balance_eth = $getBalance/1000000000000000000;
		}

		// CTC
		$tk = 'ctc';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$ethObj = $erc20->token($contractAddressArr[$tk]['contractAddress']);
			$balance_ctc = $ethObj->balanceOf($walletAddress,false);
			$scale = 18;
			$balance_ctc = bcdiv($balance_ctc, bcpow("10", strval($scale), 0), $scale);
		}
		
			
		// TP3
		$tk = 'tp3';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$tokenPay = $erc20->token($contractAddressArr[$tk]['contractAddress']);
			$balance_tp3 = $tokenPay->balanceOf($walletAddress,false);
			$scale = 18;
			$balance_tp3 = bcdiv($balance_tp3, bcpow("10", strval($scale), 0), $scale);
		}
		
		// USDT
		$tk = 'usdt';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$usdtObj = $erc20->token($contractAddressArr[$tk]['contractAddress']);
			$balance_usdt = $usdtObj->balanceOf($walletAddress,false);
			$scale = 6;
			$balance_usdt = bcdiv($balance_usdt, bcpow("10", strval($scale), 0), $scale);
		}

		// MC
		$tk = 'mc';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$mcObj = $erc20->token($contractAddressArr[$tk]['contractAddress']);
			$balance_mc = $mcObj->balanceOf($walletAddress,false);
			$scale = 6;
			$balance_mc = bcdiv($balance_mc, bcpow("10", strval($scale), 0), $scale);
		}

		// KRW
		$tk = 'krw';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$krwObj = $erc20->token($contractAddressArr[$tk]['contractAddress']);
			$balance_krw = $krwObj->balanceOf($walletAddress,false);
			$scale = 6;
			$balance_krw = bcdiv($balance_krw, bcpow("10", strval($scale), 0), $scale);
		}

	} catch(Exception $e) {
		new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
		error_reporting(0);
	}

} else {

	$functionName = "balanceOf";

	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
	$eth = $web3->eth;

	try {
		// ETH
		$tk = 'eth';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$decimal = $contractAddressArr[$tk]['decimal'];
			$eth->getBalance($walletAddress, function ($err, $balance) use (&$balance_eth, &$decimal) {
				if ( !empty($err) ) {
					throw new Exception($err->getMessage(), 1);
				}
				$balance_eth = $balance->toString();
				$balance_eth = $balance_eth/$decimal; // 1000000000000000000
			});
		}

		// CTC
		$tk = 'ctc';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$decimal = $contractAddressArr[$tk]['decimal'];
			$contract = new Contract($web3->provider, $contractAddressArr[$tk]['abi']);
			$contract->at($contractAddressArr[$tk]['contractAddress'])->call($functionName, $walletAddress,function($err, $result) use (&$balance_ctc, &$decimal){
				if ( !empty($err) ) {
					throw new Exception($err->getMessage(), 2);
				}
				if ( !empty( $result )) {
					$balance_ctc = reset($result)->toString();
					$balance_ctc = $balance_ctc/$decimal; // 1000000000000000000
				}
			});
		}

		// TP3
		$tk = 'tp3';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$decimal = $contractAddressArr[$tk]['decimal'];
			$contract = new Contract($web3->provider, $contractAddressArr[$tk]['abi']);
			$contract->at($contractAddressArr[$tk]['contractAddress'])->call($functionName, $walletAddress,function($err, $result) use (&$balance_tp3, &$decimal){
				if ( !empty($err) ) {
					throw new Exception($err->getMessage(), 3);
				}
				if ( !empty( $result )) {
					$balance_tp3 = reset($result)->toString();
					$balance_tp3 = $balance_tp3/$decimal; // 1000000000000000000
				}
			});
		}

		// USDT
		$tk = 'usdt';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$decimal = $contractAddressArr[$tk]['decimal'];
			$contract = new Contract($web3->provider, $contractAddressArr[$tk]['abi']);
			$contract->at($contractAddressArr[$tk]['contractAddress'])->call($functionName, $walletAddress,function($err, $result) use (&$balance_usdt, &$decimal){
				if ( !empty($err) ) {
					throw new Exception($err->getMessage(), 4);
				}
				if ( !empty( $result )) {
					$balance_usdt = reset($result)->toString();
					$balance_usdt = $balance_usdt/$decimal; // 1000000
				}
			});
		}

		// MC
		$tk = 'mc';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$decimal = $contractAddressArr[$tk]['decimal'];
			$contract = new Contract($web3->provider, $contractAddressArr[$tk]['abi']);
			$contract->at($contractAddressArr[$tk]['contractAddress'])->call($functionName, $walletAddress,function($err, $result) use (&$balance_mc, &$decimal){
				if ( !empty($err) ) {
					throw new Exception($err->getMessage(), 5);
				}
				if ( !empty( $result )) {
					$balance_mc = reset($result)->toString();
					$balance_mc = $balance_mc/$decimal; // 1000000
				}
			});
		}

		// KRW
		$tk = 'krw';
		if ( !empty($get_balances_type) && ( $get_balances_type == 'all' || ( !empty($get_balances_type[$tk]) && $get_balances_type[$tk] == 'T') ) ) {
			$decimal = $contractAddressArr[$tk]['decimal'];
			$contract = new Contract($web3->provider, $contractAddressArr[$tk]['abi']);
			$contract->at($contractAddressArr[$tk]['contractAddress'])->call($functionName, $walletAddress,function($err, $result) use (&$balance_krw, &$decimal){
				if ( !empty($err) ) {
					throw new Exception($err->getMessage(), 6);
				}
				if ( !empty( $result )) {
					$balance_krw = reset($result)->toString();
					$balance_krw = $balance_krw/$decimal; // 1000000
				}
			});
		}

	} catch(Exception $e) {
		new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : ' . $e->getCode() . ', File : ' . $e->getFile() . ' on line ' . $e->getLine());
		error_reporting(0);
	}

}

$n_balances = array(
	'eth'		=>	$balance_eth,
	'ctc'		=>	$balance_ctc,
	'tp3'		=>	$balance_tp3,
	'usdt'	=>	$balance_usdt,
	'mc'		=>	$balance_mc,
	'krw'		=>	$balance_krw
);
//foreach ($n_balances as $k=>$v) {
//	$n_balances[$k] = new_number_format($n_balances[$k], $n_decimal_point_array[$k]);
//}


?>	
