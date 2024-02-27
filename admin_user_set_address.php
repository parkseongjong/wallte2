<?php
// Test Page
// New Register Users : not auto wallet_address / To manually assign an address
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;

//Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
  /*   header('HTTP/1.1 401 Unauthorized', true, 401);
    exit("401 Unauthorized"); */
	 header('Location:index.php');
}
require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

$db = getDbInstance();
//$db->where('wallet_address', '', '!=');
//$db->where('id', '10900', '>');
$db->where('id', '5885');
$resultData = $db->get('admin_accounts');


if ($_SERVER['REQUEST_METHOD'] == 'POST')  {
	/*
	if ( !empty($_POST['user_id']) && !empty($_POST['key1']) ) {
		$passcode = $_POST['user_id'].$n_wallet_pass_key;
		$userpvtkey = $_POST['key1'];

		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_PORT => "3000",
			CURLOPT_URL => "http://3.34.253.74:3000/generate_passcode",
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
	*/
}





include_once 'includes/header.php';
?>

<link href="css/admin.css" rel="stylesheet">


<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
            <h1 class="page-header"></h1>
        </div>
</div>
 <?php include('./includes/flash_messages.php') ?>
    
    <!--    Begin filter section-->
    <div class="well text-center filter-form">
        <form class="form form-inline" action="">
			
			<select name="user_id">
				<?php
				if ( !empty($resultData) ) {
					foreach($resultData as $row) {
						$name = get_user_real_name($row['auth_name'], $row['name'], $row['lname']);
						?><option value="<?php echo $row['email']; ?>"><?php echo $row['email'].' ( '.$name.' ) / '.$row['created_at']; ?></option><?php
					}
				}
				?>
			</select>
			
			<br />
            <label for="key1" >key</label>
            <input type="text" name="key1" id="key1" required />

			<br />
            <label for="wallet_address" >Wallet Address</label>
            <input type="text" name="wallet_address" id="wallet_address" required />
			
            <input type="submit" value="Go" class="btn btn-primary">

        </form>
    </div>

    <div class="tab-content">
		<div id="user_first" class="tab-pane fade in active">
			<div class="table-responsive">

			
			</div>


	    </div>
	</div>

</div>

<?php

include_once 'includes/footer.php'; ?>