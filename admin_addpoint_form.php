<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
require_once 'includes/auth_validate.php';

include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

if ($_SESSION['admin_type'] !== 'admin') {
	 header('Location:index.php');
}

// 모바일 접근 불가
if ( stristr($_SERVER['HTTP_USER_AGENT'], 'android-web-view') == TRUE || stristr($_SERVER['HTTP_USER_AGENT'], 'ios-web-view') == TRUE  || stristr($_SERVER['HTTP_USER_AGENT'], 'android') == TRUE  || stristr($_SERVER['HTTP_USER_AGENT'], 'iPhone') == TRUE ) {
	 header('Location:index.php');
}

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$walletLogger->info('관리자 모드 > 비포인트 & E-Pay 적립 > form 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);

include_once 'includes/header.php';
?>

<link href="css/admin.css?ver=1.1" rel="stylesheet">
<link rel="stylesheet" href="css/lists.css" />

<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
		<h1 class="page-header"><?php echo !empty($langArr['addpoint_subject']) ? $langArr['addpoint_subject'] : "Bee Point&E-Pay Accumulation"; ?></h1>
	</div>
</div>
 <?php include('./includes/flash_messages.php') ?>

<div class="admin_addpoint">
	 <form method="post" name="excelFrm" action="admin_addpoint_pro.php" enctype="multipart/form-data" >
		<input type="file" name="userfile" required />
		<input type="submit" value="Upload" class="btn btn-success" />
	 </form>
	
	<p class="subject"><?php echo !empty($langArr['addpoint_text1']) ? $langArr['addpoint_text1'] : "Cautions when uploading"; ?></p>
	<ul class="warning">
		<li><?php echo !empty($langArr['addpoint_text3']) ? $langArr['addpoint_text3'] : "Uploadable file extension"; ?> : <span class="wr_text">csv</span></li>
		<li>Wallet Address : <?php echo !empty($langArr['addpoint_text4']) ? $langArr['addpoint_text4'] : "Barrybarries store address and old address cannot recognized"; ?></li>
		<li>Type : bee(<?php echo !empty($langArr['addpoint_text6']) ? $langArr['addpoint_text6'] : "Bee point"; ?>), E-CTC, E-TP3, E-MC, E-KRW</li>
		<li>Amount : <?php echo !empty($langArr['addpoint_text5']) ? $langArr['addpoint_text5'] : "Comma(,),-amount can be entered"; ?></li>
	</ul>


	<table class="table table-bordered admin_table_new">
		 <caption class="subject"><?php echo !empty($langArr['addpoint_text2']) ? $langArr['addpoint_text2'] : "Excel upload format"; ?></caption>
		<thead>
			<tr>
				<th>Wallet Address</th>
				<th>Type</th>
				<th>Amount</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>0x123...</td>
				<td>bee</td>
				<td>1000</td>
			</tr>
			<tr>
				<td>0x123...</td>
				<td>bee</td>
				<td>-1000</td>
			</tr>
			<tr>
				<td>0x123...</td>
				<td>E-CTC</td>
				<td>-500</td>
			</tr>
			<tr>
				<td>0x123...</td>
				<td>E-TP3</td>
				<td>500</td>
			</tr>
			<tr>
				<td>0x123...</td>
				<td>E-MC</td>
				<td>1,500</td>
			</tr>
			<tr>
				<td>0x123...</td>
				<td>E-KRW</td>
				<td>500</td>
			</tr>
		</tbody>
	</table>
</div>

<?php
include_once 'includes/footer.php'; ?>