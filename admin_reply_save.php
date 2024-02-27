<?php
session_start();
require_once './config/config.php';
require('includes/web3/vendor/autoload.php');

use Web3\Web3;

//error_reporting(E_ALL);


//If User has already logged in, redirect to dashboard page.
//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	
	$web3 = new Web3('http://195.201.168.34:8545/');
	$personal = $web3->personal;
	
	
	
	//Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    $data_to_store = filter_input_array(INPUT_POST);
	$message = $_POST['message_text'];//filter_input(INPUT_POST, 'email');
	
	
   $data_to_store['message_text'] = $_POST['message_text'];
   //die('hello');
	 $data_to_store['created_at'] = date('Y-m-d H:i:s');
	
	//$vCode = md5($_POST['email'].time());
	$data_to_store['message_text'] =$_POST['message_text'];
	
	//$data_to_store['created_at'] =$_POST['message'];
	//print_r($data_to_store); 
	
    $db = getDbInstance();
	
	
    $last_id = $db->insert('messages', $data_to_store);

    if($last_id)
    { 	
		$_SESSION['success'] = "Add Message successfully";
    	header('location: admin_reply.php');
    	exit();
    } else{
		
		$_SESSION['success'] = "error!";
    	header('location: admin_reply.php');
    	exit();
	}
	


}	



?>