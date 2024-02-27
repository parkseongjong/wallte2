<?php 

require('helpers.php');


$wallertAddress = '';$db = getDbInstance();

// for language
/* if(empty($_SESSION['lang'])) {
	$_SESSION['lang'] = "ko";
}
$langFolderPath = file_get_contents("lang/".$_SESSION['lang']."/index.json");
$langArr = json_decode($langFolderPath,true); */
// for language

// for check wallertAddress is empty or not start 
if(isset($_SESSION['user_id'])) {
	
	$db->where("id", $_SESSION['user_id']);
	$row = $db->get('admin_accounts');
 

	if ($db->count > 0) {
		$wallertAddress = $row[0]['wallet_address'];
		$Login_userName = $row[0]['name'];
		$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=".$wallertAddress;
	}
}



	$result = $db->get("messages");
	$last = end($result);
	$last1 = $last['message_text'];
	
	$last2 = end($result);
	//print_r($last2);die('hello');
	$abc = $last2['status'];
	//print_r($abc);die('hello');
	if($abc=='Y'){
	// echo '<marquee>'.$last1.'</marquee>' ;
	}
  
  

 ?>

<?php
//if(empty($_SESSION['user_id']) || $_SESSION['user_id']!=1393){ 
?>
<!--<!doctype html>
<title>Site Maintenance</title>
<style>
  body { text-align: center; padding: 150px; }
  h1 { font-size: 50px; }
  body { font: 20px Helvetica, sans-serif; color: #333; }
  article { display: block; text-align: left; width: 650px; margin: 0 auto; }
  a { color: #dc8100; text-decoration: none; }
  a:hover { color: #333; text-decoration: none; }
</style>

<article>
    <h1>We&rsquo;ll be back soon!</h1>
    <div>
        <p>Sorry for the inconvenience but we&rsquo;re performing some maintenance at the moment. We&rsquo;ll be back online shortly!</p>
        <p>&mdash; The Team</p>
    </div>
</article>-->
<?php
//die;
//}
?> 
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <!--<meta name="viewport" content="width=device-width, initial-scale=1">-->
		<meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no" />

        <meta name="description" content="">
        <meta name="author" content="">
        <title><?php echo !empty($langArr['title']) ? $langArr['title'] : "CyberTron Coin | Wallet"; ?></title>
		<link rel="icon"  href="favicon.ico" />
        <link  rel="stylesheet" href="css/bootstrap.min.css"/>
        <link href="js/metisMenu/metisMenu.min.css" rel="stylesheet">
        <link href="css/sb-admin-2.css?v=<?php echo rand(1000,9999);?>" rel="stylesheet">
        <link href="fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
		<link  rel="stylesheet" href="css/common.css" type="text/css" />
		<!--<link  rel="stylesheet" href="css/font.css"/>-->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script src="js/jquery.min.js" type="text/javascript"></script> 
		<script async src="https://www.googletagmanager.com/gtag/js?id=G-3XFB95C8VL"></script>
		<script>
		  window.dataLayer = window.dataLayer || [];
		  function gtag(){dataLayer.push(arguments);}
		  gtag('js', new Date());
		
		  gtag('config', 'G-3XFB95C8VL');
		</script>
		<style>


/* lang */
#side-menu .lang_new {
	display: inline-block;
	float: right;
}
#side-menu li img.lang_check {
	height: 10px;
	width: auto;
}
#side-menu li.lang {
	position: relative;
	display: block;
}
#side-menu li.lang div.lang_a {
	padding: 10.25px 15px;
	overflow: hidden;
}
#side-menu li.lang div.lang_a span {
	margin-top: 2px;
}
#side-menu .lang_new select {
	background: transparent;
	filter: progid:DXImageTransform.Microsoft.gradient(startColorstr=#80FFFFFF,endColorstr=#80FFFFFF);
	border: none;
	padding: 0px;
	border-radius: 0;
	margin: 0 0 0 3px;
	-webkit-appearance: none; /* for chrome */
	-moz-appearance: none; /*for firefox*/
	appearance:none;
	color: #063bff;
	font-size: 1.12rem;
	font-weight: 500;
}
#side-menu .lang_new select::-ms-expand{
	display:none;/*for IE10,11*/
}
@media only screen and (max-width: 767px) {
	#side-menu .lang_new select {
		margin-right: 9.2vw;
	}
}
@media (min-width: 768px) {
	#side-menu .lang_new select {
		margin-right: 15px;
	}
	.sidebar-nav {
		border-right: 1px solid #F5F5F5;
	}
}
.sidebar-nav {
    background-color: #ffffff;
}



#side-menu li {
	position: relative;
	border-bottom: 1px solid #F5F5F5;
	font-size: 0.945rem;
	overflow: hidden;

}
#side-menu li img.menu_icon {
	width: 18px;
	height: auto;
	position: absolute;
	top: 50%;
	transform:translateY(-50%);
}
#side-menu li span {
	display: inline-block;
	margin-left: 30px;
	font-weight: 500;
	margin-right: 15px;
}
#side-menu li.admin_text {
	background-color: #f5f5f5;
	padding: 9px 15px;
	font-weight: 500;
}


/* arrrow */
#side-menu li span:after {
	content: "";
	background-image: url("images/icons/arrow_gray.png");
	background-size: 7.8px 14.24px;
	width: 7.8px;
	height: 14.24px;
	display: inline-block;
	position: absolute;
	top: 50%;
	right: 20px;
	transform:translateY(-50%);
}

/*
.barcode_top {
	text-align: center;
}
.barcode_top .center1 {
	width: 100%;
	padding: 0 90px;
	display: inline-block;
	border: 1px solid #000;
	position: absolute;
	top: 50%;
	left: 0;
	transform:translateY(-50%);
	font-size: 1.568rem;
	line-height: 1.568rem;
	overflow: hidden;

}*/
		</style>
    </head>
	<?php
	$back_url = '';
	$server_sf = $_SERVER['SCRIPT_FILENAME'];
	if (stristr($server_sf, '/index.php') == true || stristr($server_sf, '/index_test1.php') == true) {
	} else if (stristr($_SERVER['REQUEST_URI'], '/send_') == true) {
		if (stristr($server_sf, '/send_token.php') == true) {
			$back_url = 'token.php?token=ctc';
		} else if (stristr($server_sf, '/send_eth.php') == true) {
			$back_url = 'token.php?token=eth';
		} else if (stristr($server_sf, '/send_other.php') == true) {
			$back_url = 'token.php?token='.$_GET['token'];
		} else {
			$back_url = 'token.php?token=ctc';
		}
	} else if (stristr($_SERVER['REQUEST_URI'], '/change_transfer_') == true || stristr($_SERVER['REQUEST_URI'], '/change_pass.php') == true) {
		$back_url = 'profile.php';
	} else if (stristr($_SERVER['REQUEST_URI'], '/admin_user_approval') == true) {
		$back_url = 'admin_users.php';
	} else {
		$back_url = 'index.php';
	}

	$barcode_url = '';
	if (stristr($server_sf, '/index.php') == true || stristr($server_sf, '/index_test1.php') == true || stristr($server_sf, '/send_') == true || stristr($server_sf, '/token.php') == true || stristr($server_sf, '/exchange.php') == true) {
		$barcode_url = 'y';
	}

	$menu_show = '';
	if (stristr($server_sf, '/set_transferpw_frm') == true) {
		$menu_show = 'n';
	}
	?>
    <body>
		<?php if ( empty($menu_show) ) { ?>

			<div id="loading-o" class="none">
				<div class="loading active">
					<div><img src="images/icons/loading.gif" alt="loading" /></span></div>
					<!-- <div><i class="fa fa-cog"></i><span>Working...</span></div> -->
				</div>
			</div>
			
			<div id="wrapper">
				<?php if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] == true ) { ?>
					<nav class="navbar navbar-default navbar-static-top" role="navigation" >
						<div class="navbar-header barcode_top">
							<?php
							if ( empty($back_url)) { ?>
								<button type="button" class="navibar_menu" data-toggle="collapse" data-target=".navbar-collapse">
									<img src="images/icons/menu.png" alt="menu" />
								</button>
							<?php } else { ?>
								<div class="navibar_back">
									<a href="<?php echo $back_url; ?>" title="main page"><img src="images/icons/top_subject_back_btn.png" alt="back" /></a>
								</div>
							<?php } ?>
							<!--<div class="center1">application for conversion of CTC fees</div>-->
							<div class="barcode_open2<?php if ( $barcode_url != 'y') { echo ' barcode_none'; } ?>"><img src="images/icons/tmenu_qrcorde.png" alt="barcode" onclick="showBarcode()" /></div>
							

		
							<!--<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
								<span class="sr-only"><?php echo !empty($langArr['navigation']) ? $langArr['navigation'] : "Toggle navigation"; ?></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
							<img src="images/icons/tmenu_qrcorde.png" alt="barcode" class="barcode_open" onclick="showBarcode()" />-->

						</div>
						<div class="navbar-default sidebar" role="navigation" style="margin-top:0px;overflow:auto;">
							<div class="sidebar-nav navbar-collapse collapse" aria-expanded="false">
								<center>
								
								</center>
								<ul class="nav" id="side-menu">
									
									<li class="lang">
										<div class="lang_a">
											<img src="images/menu/lang.png" alt="language" class="menu_icon" /> <span><?php echo !empty($langArr['language_setting']) ? $langArr['language_setting'] : "language setting"; ?></span>
											<div class="lang_new">
												<img src="images/menu/lang_chk.png" alt="language" class="lang_check" />
												<select name="getlang" onChange="changeLanguage(this);">
													<option <?php echo ($_SESSION['lang']=='ko') ? 'selected' : ""; ?> value="ko">한국어</option>
													<option <?php echo ($_SESSION['lang']=='en') ? 'selected' : ""; ?> value="en">English</option>
												</select>
											</div>
										</div>
									</li>

									<li><a href="exchange.php"><img src="images/menu/charging.png" alt="<?php echo !empty($langArr['buy_ctc']) ? $langArr['buy_ctc'] : "Charging CTC"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['buy_ctc']) ? $langArr['buy_ctc'] : "Charging CTC"; ?></span></a></li>

									<li><a href="exchange_tp3.php"><img src="images/menu/charging.png" alt="<?php echo !empty($langArr['buy_tp3']) ? $langArr['buy_tp3'] : "Charging TP3"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['buy_tp3']) ? $langArr['buy_tp3'] : "Charging TP3"; ?></span></a></li>

									<li><a href="all_stores2.php"><img src="images/menu/stores.png" alt=" <?php echo !empty($langArr['customer_stores']) ? $langArr['customer_stores'] : "All Stores"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['customer_stores']) ? $langArr['customer_stores'] : "All Stores"; ?></span></a></li> 

									<li><a href="store_transactions_user.php"><img src="images/menu/store_transactions.png" alt="<?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transactions"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transactions"; ?></span></a></li>

									<li><a href="https://etherscan.io/address/<?php echo $wallertAddress; ?>" target="_blank"><img src="images/menu/eterscan.png" alt="<?php echo !empty($langArr['view_on_etherscan']) ? $langArr['view_on_etherscan'] : "View On Etherscan"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['view_on_etherscan']) ? $langArr['view_on_etherscan'] : "View On Etherscan"; ?></span></a></li>


									<li><a href="profile.php"><img src="images/menu/profile.png" alt="<?php echo !empty($langArr['profile']) ? $langArr['profile'] : "My Info"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['profile']) ? $langArr['profile'] : "My Info"; ?></span></a></li>	

									<li><a href="logout.php"><img src="images/menu/logout.png" alt="<?php echo !empty($langArr['logout']) ? $langArr['logout'] : "Logout"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['logout']) ? $langArr['logout'] : "Logout"; ?></span></a></li>
									

									
								<?php if ($_SESSION['admin_type'] == 'admin') { ?>
								
									<li class="admin_text"> <?php echo !empty($langArr['admin_tools']) ? $langArr['admin_tools'] : "Admin Tools"; ?></li>
								
									<li><a href="admin_users.php"><img src="images/menu/a_admin_users.png" alt="<?php echo !empty($langArr['registered_user']) ? $langArr['registered_user'] : "Registered User"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['registered_user']) ? $langArr['registered_user'] : "Registered User"; ?></span></a></li>  
																	
									<li><a href="store_transactions.php"><img src="images/menu/a_store_transactions.png" alt="<?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transaction"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transaction"; ?></span></a></li>   

									<li><a href="stores.php"><img src="images/menu/a_stores.png" alt="<?php echo !empty($langArr['admin_stores']) ? $langArr['admin_stores'] : "Admin Stores"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['admin_stores']) ? $langArr['admin_stores'] : "Admin Stores"; ?></span></a></li> 

									<li><a href="blocked_ip.php"><img src="images/menu/a_blocked_ip.png" alt="<?php echo !empty($langArr['blocked_ip']) ? $langArr['blocked_ip'] : "Blocked IPs"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['blocked_ip']) ? $langArr['blocked_ip'] : "Blocked IPs"; ?></span></a></li>  								

									<li><a href="settings.php"><img src="images/menu/a_settings.png" alt="<?php echo !empty($langArr['settings']) ? $langArr['settings'] : "Settings"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['settings']) ? $langArr['settings'] : "Settings"; ?></span></a></li> 			
									
									
									<li><a href="sendlog_list.php"><img src="images/menu/a_logs.png" alt="<?php echo !empty($langArr['sendlog_subject1']) ? $langArr['sendlog_subject1'] : "User transaction All Logs"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['sendlog_subject1']) ? $langArr['sendlog_subject1'] : "User transaction All Logs"; ?><span></a></li>
									
									<li><a href="address_balances_check.php"><img src="images/menu/a_balance_check.png" alt="<?php echo !empty($langArr['master_address_get_balance_chk1']) ? $langArr['master_address_get_balance_chk1'] : "Check Balance"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['master_address_get_balance_chk1']) ? $langArr['master_address_get_balance_chk1'] : "Check Balance"; ?></span></a></li>
	
									<li><a href="change_fee.php"><img src="images/menu/fee_change.png" alt="<?php echo !empty($langArr['change_fee_subject']) ? $langArr['change_fee_subject'] : "application for conversion of CTC fees"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['change_fee_subject']) ? $langArr['change_fee_subject'] : "application for conversion of CTC fees"; ?></span></a></li>	
																								
									<!--li><a href="admin_wallet_blank_users.php"><i class="fa fa-user fa-fw"></i> <?php echo !empty($langArr['registered_user']) ? $langArr['registered_user'] : "Blank Wallet Address Users"; ?></a></li-->

									<!--li><a href="https://gruhn.github.io/vue-qrcode-reader/demos/DecodeAll.html"><i class="fa fa-gear fa-fw"></i> t</a></li-->

									<!--<li><a href="show_message.php"><i class="fa fa-user fa-fw"></i><?php //echo !empty($langArr['messages']) ? $langArr['messages'] : "Messages"; ?></a></li> --->

								<?php }	?>	

									<!--<li><a href="send_token.php"><i class="fa fa-download fa-fw"></i> <?php echo !empty($langArr['send_ctc']) ? $langArr['send_ctc'] : "Send"; ?></a></li>

									<li><a href="receive_token.php"><i class="fa fa-download fa-fw"></i> <?php echo !empty($langArr['receive']) ? $langArr['receive'] : "Receive"; ?></a></li>
									
									<li><a href="coin_bank.php"><i class="fa fa-bank fa-fw"></i> <?php echo !empty($langArr['coin_bank']) ? $langArr['coin_bank'] : "Coin bank"; ?></a></li>-->

									<!--<li><a href="send_token.php"><i class="fa fa-share fa-fw"></i> Send Token</a></li>-->
									
									<!--<li><a href="users-trans.php"><i class="fa fa-user fa-fw"></i> Transactions</a></li> -->
									
									<!--<li><a href="kyc.php"><i class="fa fa-download fa-fw"></i> KYC</a></li>-->
									
									<!--<li><a href="coin_change.php"><i class="fa fa-exchange fa-fw"></i> Coin Change</a></li>-->

								</ul>
								<div class="clearfix"></div>
							</div>
						</div>
					</nav>
				<?php }
			} ?>