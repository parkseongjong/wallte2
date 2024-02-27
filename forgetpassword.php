<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === TRUE) {
	
	header('Location:index.php');

}

//require_once(__DIR__ . '/messente_api/vendor/autoload.php');

//use \Messente\Omnichannel\Api\OmnimessageApi;
//use \Messente\Omnichannel\Configuration;
//use \Messente\Omnichannel\Model\Omnimessage;
//use \Messente\Omnichannel\Model\SMS; 


use Nurigo\Api\Message;
use Nurigo\Exceptions\CoolsmsException;

require_once "./sms/bootstrap.php";


//serve POST method, After successful insert, redirect to customers.php page.

// blocked IP Code, 20.10.20
$userip = new_getUserIpAddr();
if ( !empty($userip) ) {
	$blocked_ip_count = 0;
	$db = getDbInstance();
	$db->where("ip_name", $userip);
	$blocked_ip_count = $db->getValue('blocked_ips', 'count(*)');
	if ($blocked_ip_count > 0) { 
		header('location: login.php');
		exit();
	}
}

// 20.09.04
// only post
if ($_SERVER['REQUEST_METHOD'] == 'POST' ) {
	if ( !isset($_POST['submit']) && empty($_POST['wallet_change_agree']) ) { // login.php -> forgetpassword
		$_SESSION['login_failure'] = !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : 'The wrong approach.';
		header('location: login.php');
		exit();
	}
} else {
	$_SESSION['login_failure'] = !empty($langArr['wrong_approach']) ? $langArr['wrong_approach'] : 'The wrong approach.';
	header('location: login.php');
	exit();
}
$return_page = 'login.php'; // forgetpassword.php



if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit']) ) {

	$country = $_POST['country'];	
	$phone2 = $_POST['phone2'];	
	
    //Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    $data_to_store = filter_input_array(INPUT_POST);
    $data_to_store['created_at'] = date('Y-m-d H:i:s');
    
	if(empty($data_to_store['user_id']) && empty($data_to_store['phone'])) {
		
		$_SESSION['login_failure'] = $langArr['em_ph_req'];
		header('location: '.$return_page);
		exit();
	}



    //email , phone
	$user_id = !empty($data_to_store['user_id']) ? $data_to_store['user_id'] : str_replace(" ","",str_replace("-","",$data_to_store['phone']));

    $db = getDbInstance();
	$db->where("email",$user_id );
	$db->where("email_verify", 'Y' ); // 20.09.04
	$row = $db->get('admin_accounts', null,'id, email, name, email_verify, login_or_not');



    //admin_accounts에 없는 경우 휴면 회원쪽도 확인한다.
    if(!$row){
        $row = $db->where("email",$user_id )->get('admin_accounts_sleep', null,'id, email, name');
        if($row){
            $temp = $db->where('id',$row[0]['id'])->where('email_verify','Y')->get('admin_accounts', null,'email_verify, login_or_not');
            foreach ($temp[0] as $tempKey => $tempValue){
                $row[0][$tempKey] = $tempValue;
            }
            unset($temp, $tempKey, $tempValue);
        }
    }


	if (!empty($row)) {

		// 20.09.03
		if ( empty($row[0]['login_or_not']) || $row[0]['login_or_not'] == 'N' ) {
			$_SESSION['login_failure'] = !empty($langArr['forgetpass_failed_message1']) ? $langArr['forgetpass_failed_message1'] : "The password cannot be reset. Please contact customer service.";
			header('location: login.php');
			exit();
		}
		
		$checkVerify = $row[0]['email_verify'];
		
	/*	if ($checkVerify=="N"){

			$_SESSION['login_failure'] = "Verify your account first";
			header('location: '.$return_page);
			exit();
			
		}*/



		$password=rand(9999,99999);
		//$password = $row[0]['passwd_b'];
		$name = $row[0]['name'];
		$email = $row[0]['email'];
		$myVcode = rand(100000,999999);
		$generateVcode = generateVcode($myVcode);
		$vCode = !empty($data_to_store['user_id']) ? md5($email.time()) : $generateVcode ;

        var_dump($vCode);
		//$db = getDbInstance();
		//$db->where("email", $user_id);
	
		$last_id = $db->where('id',$row[0]['id'])->update('admin_accounts', ['vcode'=>$vCode]);

		$date = date('Y');
		$email = $user_id;

            if(!empty($data_to_store['user_id']))
		{

			$verifyLink = "http://".$_SERVER['HTTP_HOST']."/wallet2/resetpassword.php?vcode=".$vCode;
			
			$hi = $langArr['hi'];
			$emailtext = $langArr['email'];
			$click_below_link_to_reset_password = $langArr['click_below_link_to_reset_password'];
			$reset_link_text = $langArr['reset_link'];
			$thanks = $langArr['thanks'];
			$team_support = $langArr['team_support'];
			$all_right_reserved = $langArr['all_right_reserved'];
			$reset_password_link_for_cybertron_coin = $langArr['reset_password_link_for_cybertron_coin'];
			$mailHtml = '	<table style="background:#f6f6f6; width:100%;    height: 60vh;">
								<tr>
								<td>
								<table align="center" width="600"  style=" background:#fff; ">
								<tbody>
								<tr align="center" > 
								<td><img src="http://'.$_SERVER['HTTP_HOST'].'/wallet2/images/logo3.png" /></td>
								</tr>	
								
								<tr>
								<td><h4 style="text-align: left; padding-left: 16px; margin:0px;">'.$hi.' '.$name.',</h4></td>
								</tr>
								<tr align="center">
								<td><p style="padding:0 3%; line-height:25px; text-align: justify;">'.$emailtext.' : '.$email.'</p></td>
								</tr>
								
								<tr align="center">
								<td><p style="padding:0 3%; line-height:25px; text-align: justify;">'.$click_below_link_to_reset_password .'</p></td>
								</tr>
								
								<tr>
								<td align="center";><div style=" font-weight:bold; padding: 12px 35px; color: #fff; border-radius:5px; text-align:center; font-size: 14px; margin: 10px 0 20px; background: #ec552b; display: inline-block; text-decoration: none;">'.$reset_link_text.': <a href="'.$verifyLink.'">'.$verifyLink.'</a></div></td>
								</tr>
								
								<tr align="center">
								<td><p style="padding:0 3%; line-height:25px;    text-align: justify; margin:0px;">'.$thanks.', <br/>'.$team_support.'</p></td>
								</tr>
								</tbody>
								</table>
								
								<table align="center" width="600"  style=" background:#f3f5f7; color:#b7bbc1 ">
								<tr>
								<td>
								<h4>©'.$date.' '.$all_right_reserved.'</h4>
								</td>
								</tr>  
							</table>
			';

			
			require 'sendgrid-php/vendor/autoload.php'; // If you're using Composer (recommended)
			
			$email = new \SendGrid\Mail\Mail();
			$email->setFrom($n_email_from_address, "CyberTron Coin");
			$email->setSubject($reset_password_link_for_cybertron_coin);
			$email->addTo($user_id);//$email_id;
			
			$email->addContent("text/html", $mailHtml);
			
			$sendgrid = new \SendGrid('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');
			
			try {
				
				$response = $sendgrid->send($email);
			
			} catch (Exception $e) {
				
				new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : 1, File : ' . $e->getFile() . ' on line ' . $e->getLine());
				//echo 'Caught exception: '.  $e->getMessage(). "\n";
				$_SESSION['login_failure'] = $langArr['unable_verification_code_send_no'];
				header('location: '.$return_page);
				exit();
			
			}
			
			$_SESSION['success'] = $langArr['forgot_success_msg'];
			header('location: login.php');
			exit();
		} else {
			// send sms start

			$cybertchain_verification_code = $langArr['cybertchain_verification_code'];
            $_SESSION['success'] = $langArr['verification_code_send_no'];

            header('location: resetpass.php');


			try {
				$phone3 = preg_replace('/[\(\)\-\_\.~\s]/i', '', $phone2);

				$rest = new Message($n_api_key, $n_api_secret);

				$options = new stdClass();
				$options->to = $phone3; // 수신번호
				$options->from = $n_sms_from_tel; // 발신번호
				
				$options->country = $country;
				$options->type = 'SMS'; // Message type ( SMS, LMS, MMS, ATA )
				$options->text = $cybertchain_verification_code." : ".$vCode; // 문자내용



				$result = $rest->send($options);     

				if($result->success_count == '1')
				{
					//echo 'success';
					$_SESSION['success'] = $langArr['verification_code_send_no'];
                    //exit;
					header('location: resetpass.php');
				}
				else
				{
					//echo 'fail';
					new_fn_logSave( 'Message : Send Fail, Code : 2, File : ' .$_SERVER['SCRIPT_FILENAME']);
					$_SESSION['login_failure'] = $langArr['unable_verification_code_send_no'];
                    //exit;
					header('location: '.$return_page);
				}

			} catch(CoolsmsException $e) {

					//echo 'fail';
					new_fn_logSave( 'Message : ' . $e->getMessage() . ', Code : 2, File : ' . $e->getFile() . ' on line ' . $e->getLine());
					$_SESSION['login_failure'] = $langArr['unable_verification_code_send_no'];
                exit;
					header('location: '.$return_page);

				echo $e->getMessage(); // get error message
				echo $e->getCode(); // get error code
			}


			// send sms end
		}
		
		
		
	}
	       
	else{

		$_SESSION['login_failure'] = $langArr['invalid_email_id'];
    	header('location: '.$return_page);
    	exit();
	 
	} 
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
	
?>


</head>
<link rel="stylesheet" type="text/css" href="flag/build/css/intlTelInput.css">
<link rel="stylesheet" type="text/css" href="css/login.css">
<body>
<div id="forgetpassword" class="login_input">

	<?php
	if(isset($_SESSION['login_failure'])){ ?>
		<div class="alert alert-danger alert-dismissable fade in">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?>
		</div>
	<?php }
	if(isset($_SESSION['success'])){ ?>
		<div class="alert alert-success alert-dismissable fade in">
			<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
			<?php echo $_SESSION['success']; unset($_SESSION['success']);?>
		</div>
	<?php } ?>
	<div class="login_logo"><!--<img src="images/eth_logo.png" alt="logo" />--><img src="images/logo3.png" alt="logo" /></div>
	<ul class="login_nav_tabs">
		<li role="presentation" class="active" onclick="callPhoneClick();"><a href="#phonebox" aria-controls="sign-up" role="tab" data-toggle="tab"><?php echo !empty($langArr['phone']) ? $langArr['phone'] : "Phone"; ?></a></li>
		<li class="bar">|</li>
		<li role="presentation" onclick="callEmailClick()"><a href="#emailbox" aria-controls="login" role="tab" data-toggle="tab"><?php echo !empty($langArr['email']) ? $langArr['email'] : "Email"; ?></a></li>
		<li class="lang">
			 <select name="getlang" onChange="changeLanguage(this);">
				<option <?php echo ($_SESSION['lang']=='ko') ? 'selected' : ""; ?> value="ko">KOR</option>
				<option <?php echo ($_SESSION['lang']=='en') ? 'selected' : ""; ?> value="en">ENG</option>
			</select>
		</li>
	</ul>

	<form class="form loginform" action='' method='post'>
		<div class="tab-content">
			<div id="phonebox" class="form-group tab-pane fade in active">
				<input type="text" id="phone" name="phone" class="input" >
				<input type="hidden" id="phone2" name="phone2"  >
				<input type="hidden" id="country" name="country"  >
			</div>
			<div id="emailbox" class="form-group tab-pane fade">
				<input type="email" name="user_id" id="emailfield" class="input" placeholder="<?php echo !empty($langArr['login_input_email']) ? $langArr['login_input_email'] : 'Please enter your email address.'; ?>">
			</div>
		</div>

		
		<div class="form-group">
			<input class="btn" type='submit' name='submit' value='<?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?>'/>
		</div>

		<ul class="bottom">
			<li class="text1"><a href="login.php" title="login"><?php echo !empty($langArr['login']) ? $langArr['login'] : "Login"; ?></a></li>
			<!--<li class="text3"><?php echo $n_version; ?></li>-->
		</ul>
		
	
	</form>
</div>

</body>
</html>

<script type="text/javascript">

function callEmailClick(){
	$("#phone").val('');
}
function callPhoneClick(){
	$("#emailfield").val('');
}
$(function () {
	/*
   $("#customer_form").validate({
       rules: {
            f_name: {
                required: true,
                minlength: 3
            },
            l_name: {
                required: true,
                minlength: 3
            },   
        }
    });

	$('input').iCheck({
		checkboxClass: 'icheckbox_square-blue',
		radioClass: 'iradio_square-blue',
		increaseArea: '20%' // optional
	}); */
	$("#phone").intlTelInput({
	  initialCountry: "kr",
	  preferredCountries : ['cn','jp','us','kr'],
	  geoIpLookup: function(callback) {
		$.get('https://ipinfo.io/json?token=6ad007f53defcc', function() {}, "jsonp").always(function(resp) { // 6ad007f53defcc
		  var countryCode = (resp && resp.country) ? resp.country : "";
		  callback(countryCode);
		});
	  },
	  utilsScript: "flag/build/js/utils.js" // just for formatting/placeholders etc
	});
	$(".loginform").submit(function(){
		var countryData = $("#phone").intlTelInput("getSelectedCountryData");
		var getPhoneVal = $("#phone").val();
		var getFirstChar = getPhoneVal.charAt(0);

		$("#phone2").val(getPhoneVal);
		$("#country").val(countryData.dialCode);

		if(getPhoneVal!='') {
			if(countryData.dialCode==82 && getFirstChar==0){
				getPhoneVal = getPhoneVal.substr(1);
			}
			$("#phone").val("+"+countryData.dialCode+getPhoneVal);
		}
	});
	
});
</script>
<?php include_once 'includes/footer.php'; ?>
<script src="flag/build/js/utils.js"></script>
<script src="flag/build/js/intlTelInput.js"></script>