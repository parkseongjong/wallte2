<?php
// Page in use
session_start();
require_once './config/config.php';
require_once './config/new_config.php';
include_once (BASE_PATH.'/lib/WalletLogger.php');
use WalletLogger\Logger as walletLogger;

if ($_SESSION['admin_type'] !== 'admin') {
	$_SESSION['failure'] = "You can't perform this action!";
	header('location: index.php');
	exit();
}

require_once BASE_PATH.'/lib/WalletInfos.php';
$wi_wallet_infos = new WalletInfos();

$walletLoggerLoader = new walletLogger();
$walletLogger = $walletLoggerLoader->init();
$walletLoggerUtil = $walletLoggerLoader->initUtil();
unset($walletLoggerLoader);

$walletLogger->info('관리자 모드 > 잔액 목록 > 조회',['admin_id'=>$walletLoggerUtil->getAdminSession(),'user_id'=>0,'url'=>$walletLoggerUtil->getUrl(),'action'=>'S']);

// Master
$master_eth_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'eth', $n_master_wallet_address, $contractAddressArr), $n_decimal_point_array['eth']);
$master_balance_tp3 = new_number_format($wi_wallet_infos->wi_get_balance('', 'tp3', $n_master_wallet_address, $contractAddressArr), $n_decimal_point_array['tp3']);
$master_balance_ctc = new_number_format($wi_wallet_infos->wi_get_balance('', 'ctc', $n_master_wallet_address, $contractAddressArr), $n_decimal_point_array['ctc']);

// TP3 airdrop
$tp3_a_eth_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'eth', $n_master_wallet_address_tpa, $contractAddressArr), $n_decimal_point_array['eth']);
$tp3_a_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'tp3', $n_master_wallet_address_tpa, $contractAddressArr), $n_decimal_point_array['tp3']);

// TP3 exchange (IN)
$tp3_ex_eth_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'eth', $n_master_wallet_address_exc_tp3, $contractAddressArr), $n_decimal_point_array['eth']);
$tp3_ex_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'tp3', $n_master_wallet_address_exc_tp3, $contractAddressArr), $n_decimal_point_array['tp3']);



// TP3 exchange (OUT)
$tp3_ex_out_eth_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'eth', $n_master_wallet_address_exc_out_tp3, $contractAddressArr), $n_decimal_point_array['eth']);
$tp3_ex_out_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'tp3', $n_master_wallet_address_exc_out_tp3, $contractAddressArr), $n_decimal_point_array['tp3']);

$usdt_ex_out_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'usdt', $n_master_wallet_address_exc_out_tp3, $contractAddressArr), $n_decimal_point_array['usdt']);


// CTC airdrop
$ctc_a_eth_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'eth', $n_master_wallet_address_cta, $contractAddressArr), $n_decimal_point_array['eth']);
$ctc_a_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'ctc', $n_master_wallet_address_cta, $contractAddressArr), $n_decimal_point_array['ctc']);

// CTC exchange (IN)
$ctc_ex_eth_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'eth', $n_master_wallet_address_exc, $contractAddressArr), $n_decimal_point_array['eth']);
$ctc_ex_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'ctc', $n_master_wallet_address_exc, $contractAddressArr), $n_decimal_point_array['ctc']);


// CTC exchange (OUT)
$ctc_ex_out_eth_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'eth', $n_master_wallet_address_exc_out, $contractAddressArr), $n_decimal_point_array['eth']);
$ctc_ex_out_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'ctc', $n_master_wallet_address_exc_out, $contractAddressArr), $n_decimal_point_array['ctc']);


// CTC fee receiving address
$fee_balance = new_number_format($wi_wallet_infos->wi_get_balance('', 'eth', $n_master_wallet_address_fee, $contractAddressArr), $n_decimal_point_array['eth']);
$fee_balance_ctc = new_number_format($wi_wallet_infos->wi_get_balance('', 'ctc', $n_master_wallet_address_fee, $contractAddressArr), $n_decimal_point_array['ctc']);


// Token -> eToken : Token receive address
//$etoken_balance_ctc = new_number_format($wi_wallet_infos->wi_get_balance('', 'ctc', $n_master_ectc_wallet_address, $contractAddressArr), $n_decimal_point_array['ctc']);
//$etoken_balance_tp3 = new_number_format($wi_wallet_infos->wi_get_balance('', 'tp3', $n_master_etoken_receive_address['tp3'], $contractAddressArr), $n_decimal_point_array['tp3']);
$etoken_balance_mc = new_number_format($wi_wallet_infos->wi_get_balance('', 'mc', $n_master_etoken_receive_address['mc'], $contractAddressArr), $n_decimal_point_array['mc']);
//$etoken_balance_krw = new_number_format($wi_wallet_infos->wi_get_balance('', 'krw', $n_master_etoken_receive_address['krw'], $contractAddressArr), $n_decimal_point_array['krw']);

$db = getDbInstance();
$kiosk_row = $db->get('kiosk_config');
if ( !empty($kiosk_row) ) {
	foreach($kiosk_row as $k1=>$v1) {
		$kiosk_balance[$v1['name']]['wallet_address'] = $v1['wallet_address'];
		$kiosk_balance[$v1['name']]['eth'] = new_number_format($wi_wallet_infos->wi_get_balance('', 'eth', $v1['wallet_address'], $contractAddressArr), $n_decimal_point_array['eth']);
		$kiosk_balance[$v1['name']]['ctc'] = new_number_format($wi_wallet_infos->wi_get_balance('', 'ctc', $v1['wallet_address'], $contractAddressArr), $n_decimal_point_array['ctc']);
		$kiosk_balance[$v1['name']]['tp3'] = new_number_format($wi_wallet_infos->wi_get_balance('', 'tp3', $v1['wallet_address'], $contractAddressArr), $n_decimal_point_array['tp3']);
	}
} // if

include_once 'includes/header.php';
?>
<link href="css/admin.css" rel="stylesheet">
<link rel="stylesheet" href="css/lists.css" />

<div id="page-wrapper">
	<div class="row">
		 <div class="col-lg-6">
				 <h1 class="page-header"><?php echo !empty($langArr['master_address_get_balance_chk1']) ? $langArr['master_address_get_balance_chk1'] : "Check Balance"; ?></h1>
			</div>
			<div class="col-lg-6" style="">
			</div>
	</div>
	 <?php include('./includes/flash_messages.php') ?>


	<table class="table table-bordered admin_table_new">
		<colgroup>
			<col width="15%" />
			<col width="8%" />
			<col width="32%" />
			<col width="13%" />
			<col width="9%" />
			<col width="9%" />
			<col width="9%" />
			<col width="5%" />
		</colgroup>
		<thead>
			<tr>
				<th></th>
				<th>Name</th>
				<th>Wallet Address</th>
				<th>Ether</th>
				<th>CTC</th>
				<th>TP3</th>
				<th>MC</th>
				<th>KRW</th>
				<th>USDT</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>Master</td>
				<td>CybertronChain</td>
				<td><a href="https://etherscan.io/address/<?php echo $n_master_wallet_address; ?>" target="_blank" title="etherscan"><?php echo $n_master_wallet_address; ?></a></td>
				<td><?php echo $master_eth_balance; ?></td>
				<td><?php echo $master_balance_ctc; ?></td>
				<td><?php echo $master_balance_tp3; ?></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>CTC airdrop</td>
				<td>CybertronChain2</td>
				<td><a href="https://etherscan.io/address/<?php echo $n_master_wallet_address_cta; ?>" target="_blank" title="etherscan"><?php echo $n_master_wallet_address_cta; ?></a></td>
				<td><?php echo $ctc_a_eth_balance; ?></td>
				<td><?php echo $ctc_a_balance; ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>TP3 airdrop</td>
				<td>CybertronChain1</td>
				<td><a href="https://etherscan.io/address/<?php echo $n_master_wallet_address_tpa; ?>" target="_blank" title="etherscan"><?php echo $n_master_wallet_address_tpa; ?></a></td>
				<td><?php echo $tp3_a_eth_balance; ?></td>
				<td></td>
				<td><?php echo $tp3_a_balance; ?></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>CTC 수수료</td>
				<td>CybertronChain5</td>
				<td><a href="https://etherscan.io/address/<?php echo $n_master_wallet_address_fee; ?>" target="_blank" title="etherscan"><?php echo $n_master_wallet_address_fee; ?></a></td>
				<td><?php echo $fee_balance; ?></td>
				<td><?php echo $fee_balance_ctc; ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>

			
			<tr>
				<td>CTC exchange (ETH Receiving)</td>
				<td>CybertronChain3</td>
				<td><a href="https://etherscan.io/address/<?php echo $n_master_wallet_address_exc; ?>" target="_blank" title="etherscan"><?php echo $n_master_wallet_address_exc; ?></a></td>
				<td><?php echo $ctc_ex_eth_balance; ?></td>
				<td><?php echo $ctc_ex_balance; ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>TP3 exchange (ETH Receiving)</td>
				<td>CybertronChain4</td>
				<td><a href="https://etherscan.io/address/<?php echo $n_master_wallet_address_exc_tp3; ?>" target="_blank" title="etherscan"><?php echo $n_master_wallet_address_exc_tp3; ?></a></td>
				<td><?php echo $tp3_ex_eth_balance; ?></td>
				<td></td>
				<td><?php echo $tp3_ex_balance; ?></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>Send CTC : <br />Ether로 CTC 충전<br />eCTC로 CTC 충전</td>
				<td>CybertronChain6</td>
				<td><a href="https://etherscan.io/address/<?php echo $n_master_wallet_address_exc_out; ?>" target="_blank" title="etherscan"><?php echo $n_master_wallet_address_exc_out; ?></a></td>
				<td><?php echo $ctc_ex_out_eth_balance; ?></td>
				<td><?php echo $ctc_ex_out_balance; ?></td>
				<td></td>
				<td></td>
				<td></td>
				<td></td>
			</tr>
			<tr>
				<td>Send TP3&MC : <br />Ether로 TP3 충전<br />E-TP3 -&gt; TP3<br />E-MC -&gt; MC<br />E-USDT -&gt; USDT<br />E-ETH -&gt; ETH</td>
				<td>CybertronChain7</td>
				<td><a href="https://etherscan.io/address/<?php echo $n_master_wallet_address_exc_out_tp3; ?>" target="_blank" title="etherscan"><?php echo $n_master_wallet_address_exc_out_tp3; ?></a></td>
				<td><?php echo $tp3_ex_out_eth_balance; ?></td>
				<td></td>
				<td><?php echo $tp3_ex_out_balance; ?></td>
				<td><?php echo $etoken_balance_mc; ?></td>
				<td></td>
				<td><?php echo $usdt_ex_out_balance; ?></td>
			</tr><!--
			<tr>
				<td>eToken 충전시 Token 받는 주소</td>
				<td><a href="https://etherscan.io/address/<?php echo $n_master_ectc_wallet_address; ?>" target="_blank" title="etherscan"><?php echo $n_master_ectc_wallet_address; ?></a></td>
				<td></td>
				<td><?php echo $etoken_balance_ctc; ?></td>
				<td><?php echo $etoken_balance_tp3; ?></td>
				<td><?php echo $etoken_balance_mc; ?></td>
				<td><?php echo $etoken_balance_krw; ?></td>
			</tr>-->
			<?php
			if ( !empty($kiosk_balance) ) {
				foreach($kiosk_balance as $k1=>$v1) {
				?>
				<tr>
					<td colspan="2"><?php echo $k1; ?></td>
					<td><a href="https://etherscan.io/address/<?php echo $v1['wallet_address']; ?>" target="_blank" title="etherscan"><?php echo $v1['wallet_address']; ?></a></td>
					<td><?php echo $v1['eth']; ?></td>
					<td><?php echo $v1['ctc']; ?></td>
					<td><?php echo $v1['tp3']; ?></td>
					<td></td>
					<td></td>
					<td></td>
				</tr>
			<?php } } ?>
		</tbody>
	</table>

	<div>
		<h3>Contract</h3>

		<table class="table table-bordered admin_table_new">
			<thead>
				<tr>
					<th>Coin</th>
					<th>Contract Address</th>
					<th>Transaction List (Wallet Member Check)</th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($contractAddressArr as $key=>$cr) {
					if ( $key == 'eth' ) continue;
					?>
					<tr>
						<td class="align_center"><?php echo strtoupper($key); ?></td>
						<td><a href="https://etherscan.io/address/<?php echo $cr['contractAddress']; ?>" target="_blank" title="etherscan"><?php echo $cr['contractAddress']; ?></a></td>
						<td class="align_center"><a href="admin_wallet_tx_list.php?g_coin=<?php echo $key; ?>" title="coin transaction list">List</a></td>
					</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>

	<div>
		<h3>E-PAY <?php echo !empty($langArr['master_address_get_balance_chk1']) ? $langArr['master_address_get_balance_chk1'] : "Check Balance"; ?></h3>

		<?php
		$db = getDbInstance();
		$db->where('id', '45',  '>');
		$db->where('id', '10830', '!='); // vixber1
		$db->where('id', '10898', '!='); // vixber2
		$info_epay = $db->getOne('admin_accounts', 'sum(etoken_ectc) as ectc, sum(etoken_etp3) as etp3, sum(etoken_emc) as emc, sum(etoken_ekrw) as ekrw, sum(etoken_eusdt) as eusdt, sum(etoken_eeth) as eeth');
		?>
		<ul>
			<li>E-CTC : <?php echo new_number_format($info_epay['ectc'], $n_decimal_point_array2['ectc']); ?></li>
			<li>E-TP3 : <?php echo new_number_format($info_epay['etp3'], $n_decimal_point_array2['etp3']); ?></li>
			<li>E-MC : <?php echo new_number_format($info_epay['emc'], $n_decimal_point_array2['emc']); ?></li>
			<li>E-KRW : <?php echo new_number_format($info_epay['ekrw'], $n_decimal_point_array2['ekrw']); ?></li>
			<li>E-USDT : <?php echo new_number_format($info_epay['eusdt'], $n_decimal_point_array2['eusdt']); ?></li>
			<li>E-ETH : <?php echo new_number_format($info_epay['eeth'], $n_decimal_point_array2['eeth']); ?></li>
	</div>

</div>

<?php
include_once 'includes/footer.php'; ?>