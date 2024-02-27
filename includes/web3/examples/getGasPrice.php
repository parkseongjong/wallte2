<?php

require('./exampleBase.php');

 use Web3\Web3;
 
 $eth = $web3->eth;
 
 $eth->gasPrice(function($err,$result){
	print_r($err);
	echo "=========>";
	print_r($result->toString());
 });
 die;
 ?>