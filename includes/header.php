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


    //사용하나?
	if ($db->count > 0) {
		$wallertAddress = $row[0]['wallet_address'];
		$Login_userName = $row[0]['name'];
		$barCodeUrl = "https://chart.googleapis.com/chart?chs=225x225&chld=L|1&cht=qr&chl=".$wallertAddress;
	}
}
else{
    $row = false;
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

/*
    wallet2/common 과 동일한 구문 입니다... 레거시에서
    에셋 경로가 절대 경로가 아니라서... 불편 함이 있습니다.....
    wallet2/common.php에서 상수 선언이 없으면, 새로 선언 해줍니다.
*/
if(!defined('WALLET_URL') && !defined('WALLET_PATH')){
    require_once $_SERVER['DOCUMENT_ROOT'].'/wallet2/common.php';
}
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
        <link  rel="stylesheet" href="<?php echo WALLET_URL ?>/css/bootstrap.min.css"/>
        <link href="<?php echo WALLET_URL ?>/js/metisMenu/metisMenu.min.css" rel="stylesheet">
        <link href="<?php echo WALLET_URL ?>/css/sb-admin-2.css?v=<?php echo rand(1000,9999);?>" rel="stylesheet">
        <link href="<?php echo WALLET_URL ?>/fonts/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
		<link  rel="stylesheet" href="<?php echo WALLET_URL ?>/css/common.css?ver=<?php echo rand(1000,9999);?>" type="text/css" />
		<link href="css/admin.css?v=20220618" rel="stylesheet">
		<!--<link  rel="stylesheet" href="css/font.css"/>-->
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <script src="<?php echo WALLET_URL ?>/js/jquery.min.js" type="text/javascript"></script>
        <script src="<?php echo WALLET_URL ?>/js/common.js?v20211006" type="text/javascript"></script>
		<script async src="https://www.googletagmanager.com/gtag/js?id=G-3XFB95C8VL"></script>
		<script>
            WALLET_URL = '<?php echo WALLET_URL ?>';
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', 'G-3XFB95C8VL');
		</script>
		<style>

.sidebar .sidebar-nav {
	margin-bottom: 70px;
}
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
#side-menu li i.menu_icon {
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
	background-image: url("<?php echo WALLET_URL ?>/images/icons/arrow_gray.png");
	background-size: 7.8px 14.24px;
	width: 7.8px;
	height: 14.24px;
	display: inline-block;
	position: absolute;
	top: 50%;
	right: 20px;
	transform:translateY(-50%);
}
		</style>
    </head>
	<?php
	$back_url = '';
	$server_sf = $_SERVER['SCRIPT_FILENAME'];
	if (stristr($server_sf, '/index.php') == true || stristr($server_sf, '/index_test1.php') == true) {

	}
	else if (stristr($_SERVER['REQUEST_URI'], '/send_') == true) {
		if (stristr($server_sf, '/send_token.php') == true) {
			$back_url = WALLET_URL.'/'.'token.php?token=ctc';
		}
        else if (stristr($server_sf, '/send_eth.php') == true) {
			$back_url = WALLET_URL.'/'.'token.php?token=eth';
		}
        else if (stristr($server_sf, '/send_other.php') == true) {
			$back_url = WALLET_URL.'/'.'token.php?token='.$_GET['token'];
		}
        else if (stristr($server_sf, '/send_etoken.php') == true) {
			$back_url = WALLET_URL.'/'.'etoken.php?token='.$_GET['token'];
		}
        else {
			$back_url = WALLET_URL.'/'.'token.php?token=ctc';
		}
	}
	else if (stristr($_SERVER['REQUEST_URI'], '/change_transfer_') == true || stristr($_SERVER['REQUEST_URI'], '/change_pass.php') == true) {
		$back_url = WALLET_URL.'/'.'profile.php';
	}
	else if (stristr($_SERVER['REQUEST_URI'], '/admin_user_approval') == true) {
		$back_url = WALLET_URL.'/'.'admin_users.php';
	}
	else {
		$back_url = WALLET_URL.'/'.'index.php';
	}

	$back_url=$back_url;

	$barcode_url = '';
	if (stristr($server_sf, '/index.php') == true || stristr($server_sf, '/index_test1.php') == true || stristr($server_sf, '/send_') == true || stristr($server_sf, '/token.php') == true || stristr($server_sf, '/exchange.php') == true) {
		$barcode_url = 'y';
	}

	$menu_show = '';
	if (stristr($server_sf, '/set_transferpw_frm') == true) {
		$menu_show = 'n';
	}

	//권한이 적은 관리자 리스트 입니다.
	$lowLevelAdmin = array(
	        '12228',
	);

    //본인 인증이 되면 본인 인증 전화번호 삽입, 아니면 일반 핸드폰 삽입
    $buildUserInfo = array();
    if($row){
        if ($row[0]['id_auth'] == 'Y'){
            $buildUserInfo['phone'] = $row[0]['auth_phone'];
            $buildUserInfo['name'] = $row[0]['auth_name'];
            $buildUserInfo['auth'] = 'Y';
        }
        else{
            $buildUserInfo['phone'] = $row[0]['n_phone'];
            $buildUserInfo['name'] = $row[0]['lname'].$row[0]['name'];
            $buildUserInfo['auth'] = 'N';
        }
        $buildUserInfo['loginId'] = $row[0]['email'];
        $tempPathQueryString = '&userid='.urlencode($buildUserInfo['phone']).'&name='.urlencode($buildUserInfo['name']).'&auth='.urlencode($buildUserInfo['auth']).'&loginId='.urlencode($buildUserInfo['loginId']);
    }

	?>
    <body>
            <!-- 공통 header에 들어갈 구문 (include) START 2021.05.28 by.OJT -->
                <script>
                    //lang global variable
                    let walletLang = '<?php echo $_SESSION['lang']; ?>';
                    //emailHtml global variable
                    let emailHtml = <?php echo ($emailHtml !== false)?'true':'false'; ?>;
                </script>
                <?php if($emailHtml !== false): ?>
                    <script src="<?php echo WALLET_URL;?>/js/emailCollection.js"></script>
                    <?php echo $emailHtml; ?>
                <?php else: ?>
                    <?php unset($emailHtml); ?>
                <?php endif; ?>
            <!-- 공통 header에 들어갈 구문 (include) END -->
		<?php if ( empty($menu_show) ) { ?>
			<div id="loading-o" class="none">
				<div class="loading active">
					<div><img src="<?php echo WALLET_URL ?>/images/icons/loading.gif" alt="loading" /></span></div>
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
									<img src="<?php echo WALLET_URL ?>/images/icons/menu.png" alt="menu" />
									<!--<a href="<?php /*echo $back_url; */?>" title="main page"><img src="<?php /*echo WALLET_URL */?>/images/icons/top_subject_back_btn.png" alt="back" /></a>-->
								</button>
							<?php } else { ?>
								<div class="navibar_back">
									<a href="<?php echo $back_url; ?>" title="main page"><img src="<?php echo WALLET_URL ?>/images/icons/top_subject_back_btn.png" alt="back" /></a>
								</div>
							<?php } ?>


							<a href="exchange_bsc_ctctm.php"><img class="eth_to_bsc img-fluid" src="<?php echo WALLET_URL ?>/images/eth_to_bsc.jpg" alt="eth_to_bsc"/></a>


							<div class="barcode_open2<?php if ( $barcode_url != 'y') { echo ' barcode_none'; } ?>"><img src="<?php echo WALLET_URL ?>/images/icons/tmenu_qrcorde.png" alt="barcode" onclick="showBarcode()" /></div>



							<!--<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
								<span class="sr-only"><?php echo !empty($langArr['navigation']) ? $langArr['navigation'] : "Toggle navigation"; ?></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
								<span class="icon-bar"></span>
							</button>
							<img src="<?php echo WALLET_URL ?>/images/icons/tmenu_qrcorde.png" alt="barcode" class="barcode_open" onclick="showBarcode()" />-->

						</div>
						<script>
						    $(".navibar_menu").click(function (){

						    });
                        </script>
						<div class="navbar-default sidebar" role="navigation" style="margin-top:0px;overflow:auto;">
							<div class="sidebar-nav navbar-collapse collapse" aria-expanded="false">
								<ul class="nav" id="side-menu">

                                <li class="lang">
                                    <div class="lang_a">
                                        <img src="<?php echo WALLET_URL ?>/images/menu/lang.png" alt="language" class="menu_icon" /> <span><?php echo !empty($langArr['language_setting']) ? $langArr['language_setting'] : "Language"; ?></span>
                                        <div class="lang_new">
                                            <img src="<?php echo WALLET_URL ?>/images/menu/lang_chk.png" alt="language" class="lang_check" />
                                            <select name="getlang" onChange="changeLanguage(this);">
                                                <option <?php echo ($_SESSION['lang']=='ko') ? 'selected' : ""; ?> value="ko">한국어</option>
                                                <option <?php echo ($_SESSION['lang']=='en') ? 'selected' : ""; ?> value="en">English</option>
                                            </select>
                                        </div>
                                    </div>
                                </li>
                                    <li>
                                        <a href="https://www.bitsomon.com/front2/investment/index2?flag=1<?php echo $tempPathQueryString ?>">
                                        <!--<a href="#" onclick="bsCommonAlert('점검중 입니다.!')">-->
                                            <img src="<?php echo WALLET_URL ?>/images/menu/fee_change.png" alt="..." class="menu_icon" />
                                            <span>
                                                내지갑
                                            </span>
                                        </a>
                                    </li>
                                    <li style="display: none">
                                        <a href="https://www.bitsomon.com/front2/investment/index2?flag=2<?php echo $tempPathQueryString ?>">
                                            <img src="<?php echo WALLET_URL ?>/images/menu/fee_change.png" alt="..." class="menu_icon" />
                                            <span>
                                                트레이딩출금
                                            </span>
                                        </a>
                                    </li>
                                    <li style="display: none">
                                        <a href="https://www.bitsomon.com/front2/investment/index2?flag=3<?php echo $tempPathQueryString ?>">
                                            <img src="<?php echo WALLET_URL ?>/images/menu/fee_change.png" alt="..." class="menu_icon" />
                                            <span>
                                                스테이킹
                                            </span>
                                        </a>
                                    </li>
                                    <li style="display: none">
                                        <a href="https://www.bitsomon.com/front2/investment/index2?flag=4<?php echo $tempPathQueryString ?>">
                                            <img src="<?php echo WALLET_URL ?>/images/menu/fee_change.png" alt="..." class="menu_icon" />
                                            <span>
                                                쿠폰
                                            </span>
                                        </a>
                                    </li>


									<li style="display: none"><a href="<?php echo WALLET_URL ?>/all_stores2.php"><img src="<?php echo WALLET_URL ?>/images/menu/stores.png" alt=" <?php echo !empty($langArr['customer_stores']) ? $langArr['customer_stores'] : "All Stores"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['customer_stores']) ? $langArr['customer_stores'] : "All Stores"; ?></span></a></li>

									<li style="display: none"><a href="<?php echo WALLET_URL ?>/store_transactions_user.php"><img src="<?php echo WALLET_URL ?>/images/menu/store_transactions.png" alt="<?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transactions"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transactions"; ?></span></a></li>

									<li><a href="https://etherscan.io/address/<?php echo $wallertAddress; ?>"><img src="<?php echo WALLET_URL ?>/images/menu/eterscan.png" alt="<?php echo !empty($langArr['view_on_etherscan']) ? $langArr['view_on_etherscan'] : "View on Etherscan"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['view_on_etherscan']) ? $langArr['view_on_etherscan'] : "View On Etherscan"; ?></span></a></li>

									<li><a href="<?php echo WALLET_URL ?>/change_fee.php"><img src="<?php echo WALLET_URL ?>/images/menu/fee_change.png" alt="<?php echo !empty($langArr['change_fee_subject']) ? $langArr['change_fee_subject'] : "CTC Fees Conversion"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['change_fee_subject']) ? $langArr['change_fee_subject'] : "CTC Fees Conversion"; ?></span></a></li>

									<li><a href="<?php echo WALLET_URL ?>/profile.php"><img src="<?php echo WALLET_URL ?>/images/menu/profile.png" alt="<?php echo !empty($langArr['profile']) ? $langArr['profile'] : "My Info"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['profile']) ? $langArr['profile'] : "My Info"; ?></span></a></li>

									<li><a href="#" onClick="chatChannel();" target="_blank"><i class="fa fa-comments fa-2x menu_icon"></i><span><?php echo !empty($langArr['customer_inquiries1']) ? $langArr['customer_inquiries1'] : "1:1 Inquiries"; ?><span></a></li>

									<li><a href="<?php echo WALLET_URL ?>/logout.php"><img src="<?php echo WALLET_URL ?>/images/menu/logout.png" alt="<?php echo !empty($langArr['logout']) ? $langArr['logout'] : "Logout"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['logout']) ? $langArr['logout'] : "Logout"; ?></span></a></li>

									<?php if ( !empty($_SESSION['user_id']) && $_SESSION['user_id'] < 10900 ) { ?>
										<li><a href="<?php echo WALLET_URL ?>/change_address.php"><img src="<?php echo WALLET_URL ?>/images/menu/fee_change.png" alt=" <?php echo !empty($langArr['change_address_message2']) ? $langArr['change_address_message2'] : "Get my wallet address"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['change_address_message2']) ? $langArr['change_address_message2'] : "Get my wallet address"; ?></span></a></li>
									<?php } ?>

								<?php if ($_SESSION['admin_type'] == 'admin') { ?>

									<li class="admin_text"> <?php echo !empty($langArr['admin_tools']) ? $langArr['admin_tools'] : "Admin Tools"; ?></li>
									<li><a href="<?php echo WALLET_URL ?>/admin_users.php"><img src="<?php echo WALLET_URL ?>/images/menu/a_admin_users.png" alt="<?php echo !empty($langArr['registered_user']) ? $langArr['registered_user'] : "Registered User"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['registered_user']) ? $langArr['registered_user'] : "Registered User"; ?></span></a></li>
									<li><a href="<?php echo WALLET_URL ?>/store_transactions.php"><img src="<?php echo WALLET_URL ?>/images/menu/a_store_transactions.png" alt="<?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transaction"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['store_transactions']) ? $langArr['store_transactions'] : "Store Transaction"; ?></span></a></li>
									<li><a href="<?php echo WALLET_URL ?>/stores.php"><img src="<?php echo WALLET_URL ?>/images/menu/a_stores.png" alt="<?php echo !empty($langArr['admin_stores']) ? $langArr['admin_stores'] : "Admin Stores"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['admin_stores']) ? $langArr['admin_stores'] : "Admin Stores"; ?></span></a></li>
									<li><a href="<?php echo WALLET_URL ?>/blocked_ip.php"><img src="<?php echo WALLET_URL ?>/images/menu/a_blocked_ip.png" alt="<?php echo !empty($langArr['blocked_ip']) ? $langArr['blocked_ip'] : "Blocked IPs"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['blocked_ip']) ? $langArr['blocked_ip'] : "Blocked IPs"; ?></span></a></li>
                                    <?php if(!in_array($_SESSION['user_id'],$lowLevelAdmin)): ?>
                                        <li>
                                            <a href="<?php echo WALLET_URL ?>/blocked_admin_ip.php">
                                                <img src="<?php echo WALLET_URL ?>/images/menu/a_blocked_ip.png" alt="<?php echo !empty($langArr['blocked_admin_ip']) ? $langArr['blocked_admin_ip'] : "allow admin IPs"; ?>" class="menu_icon" />
                                                <span><?php echo !empty($langArr['blocked_admin_ip']) ? $langArr['blocked_admin_ip'] : "allow admin IPs"; ?></span>
                                            </a>
                                        </li>
									<li><a href="<?php echo WALLET_URL ?>/settings.php"><img src="<?php echo WALLET_URL ?>/images/menu/a_settings.png" alt="<?php echo !empty($langArr['settings']) ? $langArr['settings'] : "Settings"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['settings']) ? $langArr['settings'] : "Settings"; ?></span></a></li>
									<li><a href="<?php echo WALLET_URL ?>/swap_list.php"><img src="<?php echo WALLET_URL ?>/images/menu/a_settings.png" alt="<?php echo !empty($langArr['swap_list']) ? $langArr['swap_list'] : "Swap List"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['swap_list']) ? $langArr['swap_list'] : "Swap List"; ?></span></a></li>
                                  <?php endif; ?>

									<li><a href="<?php echo WALLET_URL ?>/sendlog_list.php"><img src="<?php echo WALLET_URL ?>/images/menu/a_logs.png" alt="<?php echo !empty($langArr['sendlog_subject1']) ? $langArr['sendlog_subject1'] : "User transaction All Logs"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['sendlog_subject1']) ? $langArr['sendlog_subject1'] : "User transaction All Logs"; ?><span></a></li>

									<li><a href="<?php echo WALLET_URL ?>/control.php/admin/user/log"><i class="fa fa-plus fa-2x menu_icon"></i><span>유저로그</span></a></li>

									<li><a href="<?php echo WALLET_URL ?>/admin_etoken_logs.php"><img src="<?php echo WALLET_URL ?>/images/menu/a_logs.png" alt="eMoney List" class="menu_icon" /> <span>E-Pay List<span></a></li>

									<li><a href="<?php echo WALLET_URL ?>/address_balances_check.php"><img src="<?php echo WALLET_URL ?>/images/menu/a_balance_check.png" alt="<?php echo !empty($langArr['master_address_get_balance_chk1']) ? $langArr['master_address_get_balance_chk1'] : "Check Balance"; ?>" class="menu_icon" /> <span><?php echo !empty($langArr['master_address_get_balance_chk1']) ? $langArr['master_address_get_balance_chk1'] : "Check Balance"; ?></span></a></li>


									<li><a href="<?php echo WALLET_URL ?>/admin_coupon_config.php"><img src="<?php echo WALLET_URL ?>/images/menu/coupon.png" alt="Coupon List" class="menu_icon" /> <span><?php echo !empty($langArr['coupon_adm_title1']) ? $langArr['coupon_adm_title1'] : "Coupon List"; ?><span></a></li>
									<li><a href="<?php echo WALLET_URL ?>/admin_coupon_list.php"><img src="<?php echo WALLET_URL ?>/images/menu/a_logs.png" alt="Coupon Logs" class="menu_icon" /> <span><?php echo !empty($langArr['coupon_adm_title2']) ? $langArr['coupon_adm_title2'] : "Coupon Logs"; ?><span></a></li>

									<li><a href="<?php echo WALLET_URL ?>/admin_addpoint_form.php"><i class="fa fa-plus fa-2x menu_icon"></i><span><?php echo !empty($langArr['addpoint_subject']) ? $langArr['addpoint_subject'] : "Bee Point&E-Pay Accumulation"; ?></span></a></li>
									<li><a href="<?php echo WALLET_URL ?>/control.php/admin/withdrawal"><i class="fa fa-plus fa-2x menu_icon"></i><span>탈퇴 회원 관리</span></a></li>

									<!--li><a href="<?php echo WALLET_URL ?>/admin_wallet_blank_users.php"><i class="fa fa-user fa-fw"></i> <?php echo !empty($langArr['registered_user']) ? $langArr['registered_user'] : "Blank Wallet Address Users"; ?></a></li-->
									<!--li><a href="https://gruhn.github.io/vue-qrcode-reader/demos/DecodeAll.html"><i class="fa fa-gear fa-fw"></i> t</a></li-->
									<!--<li><a href="show_message.php"><i class="fa fa-user fa-fw"></i><?php //echo !empty($langArr['messages']) ? $langArr['messages'] : "Messages"; ?></a></li> --->

								<?php }	?>
									<!--<li><a href="<?php echo WALLET_URL ?>/send_token.php"><i class="fa fa-download fa-fw"></i> <?php echo !empty($langArr['send_ctc']) ? $langArr['send_ctc'] : "Send"; ?></a></li>

									<li><a href="<?php echo WALLET_URL ?>/receive_token.php"><i class="fa fa-download fa-fw"></i> <?php echo !empty($langArr['receive']) ? $langArr['receive'] : "Receive"; ?></a></li>

									<li><a href="<?php echo WALLET_URL ?>/coin_bank.php"><i class="fa fa-bank fa-fw"></i> <?php echo !empty($langArr['coin_bank']) ? $langArr['coin_bank'] : "Coin bank"; ?></a></li>-->

									<!--<li><a href="<?php echo WALLET_URL ?>/send_token.php"><i class="fa fa-share fa-fw"></i> Send Token</a></li>-->

									<!--<li><a href="<?php echo WALLET_URL ?>/users-trans.php"><i class="fa fa-user fa-fw"></i> Transactions</a></li> -->

									<!--<li><a href="<?php echo WALLET_URL ?>/kyc.php"><i class="fa fa-download fa-fw"></i> KYC</a></li>-->

									<!--<li><a href="<?php echo WALLET_URL ?>/coin_change.php"><i class="fa fa-exchange fa-fw"></i> Coin Change</a></li>-->
								</ul>
								<div class="clearfix"></div>
							</div>
						</div>
					</nav>
				<?php }
			} ?>