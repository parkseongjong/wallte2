<?php
require_once './config/config.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$emailCode = rand(10000000,99999999);
	$_SESSION['emailcode'] = $emailCode; 
	$userId = $_SESSION['user_id'];

	$db = getDbInstance();
	$db->where ("id", $userId);
	$userData = $db->get('admin_accounts');
	if ($db->count >= 1) {
	$fname = $userData[0]['name'];	
	$email = $userData[0]['email'];	
	$date = date('Y');
		$mailHtml = '<table style="background:#f6f6f6; width:100%;    height: 100vh;">
    <tr>
        <td>
            <table align="center" width="600"  style=" background:#fff; ">
        <tbody>
    
          <tr>
          <td><h4 style="text-align: left;
padding-left: 16px; margin:0px;">Hi '.$fname.',</h4></td>
          </tr>
   
		  
		  <tr align="center">
            <td><p style="padding:0 3%; line-height:25px;    text-align: justify;">Below is your Authentication code </p></td>
          </tr>
		  
		   <tr>
				  <td align="center";><div style=" font-weight:bold;   padding: 12px 35px;
			color: #fff;
			border-radius:5px;
			text-align:center
			font-size: 14px;
			margin: 10px 0 20px;
			background: #ec552b;
			display: inline-block;
			text-decoration: none;">Authentication Code: '.$emailCode.'</div></td>
			</tr>
          
          <tr align="center">
            <td><p style="padding:0 3%; line-height:25px;    text-align: justify;
            margin:0px;">Thanks, <br/>Team Support</p></td>
          </tr>

          
    
    </tbody>
    </table>
    
  <table align="center" width="600"  style=" background:#f3f5f7; color:#b7bbc1 ">
          
    <tr>
    <td>
    <h4>©'.$date.' All right reserved</h4>
    </td>
    </tr>  
    
          
          
         
        </table>';
		 
		require 'sendgrid-php/vendor/autoload.php'; // If you're using Composer (recommended)

		$emailObj = new \SendGrid\Mail\Mail();
		$emailObj->setFrom("gulfcoingold@gmail.com", "Gulfcoin Gold");
		$emailObj->setSubject("Assero Money	Balance Coin verification code for send token");
		$emailObj->addTo($email);//$email_id;
		//$email->addContent("text/plain", "and easy to do anywhere, even with PHP");
		$emailObj->addContent("text/html", $mailHtml);


		$sendgrid = new \SendGrid('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');
		try {
			$response = $sendgrid->send($emailObj);
			
			  print $response->statusCode() . "\n";
			//print_r($response->headers());
		    print $response->body() . "\n";die;
		} catch (Exception $e) {
			
			echo 'Caught exception: '.  $e->getMessage(). "\n";die;
			/*=====================Mail========================*/

		}
	}
}
?>