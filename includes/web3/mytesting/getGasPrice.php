<?php

require('./config.php');

 use Web3\Web3;
 use Web3\Utils;
/* list($bnq, $bnr) = Utils::fromWei('1000000000000000000', 'ether'); 
  echo $bnq->toString(); // 1

die; */
 $eth = $web3->eth;
 
 $eth->gasPrice(function($err,$result){
/* 	print_r($err);
	echo "=========>"; */
	print_r($result->toString());
 });
 die;
 ?>