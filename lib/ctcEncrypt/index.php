<?php

if($_SERVER['REMOTE_ADDR'] != '127.0.0.1' && $_SERVER['REMOTE_ADDR'] != '112.171.120.140'){
    exit();
}
//include_once('../../common.php');
error_reporting(E_ALL);
ini_set("display_errors", 1);
include_once('./Rsa.php');
use wallet\encrypt\Rsa as walletRsa;


//include_once('../../head.sub.php');
$test = new walletRsa;
var_dump($test->encrypt($_GET['in']));
var_dump($test->decrypt($_GET['out']));
var_dump($test->decrypt('cviznk8bE6unb8LFQFM3zITcxiWmgMl+nIuH3PJgcR5tRnaOHtnTwvNAPwOBEHkMWXh7RhVZ99l7M39Ae9n451O6Cs+rui/I3iDcfEzpVrYi5QzTXYoeIBNUVYgGyprVGwlMmwnAjWQ6rAf+UJAlIqb7+RR9H6jz2Tr5rFTuvTmRJ7fM1QDtjIchmYqnhrj4Q5RTQ1ckvabcVJlzC4sdcTo83t26WAOnLKOMM+/8qK5Zc0tk21BPlGYJt0tyQ43gY3u6cUY/D/dxPCEi1aY0U8SUkkcHadrN2Xq6R8SYTYRLTN5G31fzvzZdsPhfwWSrcBjpCorJdH1r4U++RPPb5g='));

//include_once('../../tail.sub.php');
?>