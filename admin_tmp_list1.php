<?php
// Test Page
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

$db = getDbInstance();
//Get data from query string

$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');
$page = filter_input(INPUT_GET, 'page');
$pagelimit = filter_input(INPUT_GET, 'filter_limit');

$date1 = filter_input(INPUT_GET, 'date1');
$date2 = filter_input(INPUT_GET, 'date2');

if($pagelimit == "") {
	$pagelimit = 10;
}
if ($page == "") {
    $page = 1;
}
// If filter types are not selected we show latest added data first
if ($filter_col == "") {
    $filter_col = "id";
}
if ($order_by == "") {
    $order_by = "desc";
}
// select the columns
$select = array('id', 'name', 'lname','wallet_address','email' ,'phone','created_at','pan_no','bank_ac_no','ifsc_code','bank_name','register_with','email_verify','user_ip','pvt_key','id_auth');

// $db->where('email', '%' . $search_string . '%', 'like');

if ( !empty($date1) ) {
	$db->where('created_at', $date1.' 00:00:00', '>=');
}
if ( !empty($date2) ) {
	$db->where('created_at', $date2.' 23:59:59', '<=');
}

$db->where('admin_type',  'user');
if ($order_by) {
    $db->orderBy($filter_col, $order_by);
}
function dd($var='') {
	ob_start();
	var_export($var);
	$result = ob_get_clean();
	die($result);
}


$db->pageLimit = $pagelimit;
$resultData = $db->arraybuilder()->paginate("admin_accounts", $page, $select);
$total_pages = $db->totalPages;

// dd($resultData);


// get columns for order filter
foreach ($resultData as $value) {
    foreach ($value as $col_name => $col_value) {
        $filter_options[$col_name] = $col_name;
    }
    //execute only once
    break;
}


include_once 'includes/header.php';
?>

<style>
.well {
	background-color:inherit;
	text-align:left;
}

#page-wrapper {
	height:auto !important;
}
</style>

<link href="dist/css/bootstrap-datepicker.css" rel="stylesheet">
<script src="dist/js/bootstrap-datepicker.js" type="text/javascript"></script> 
<script>
$(document).ready(function(){
	$('#date1').datepicker({format: "yyyy-mm-dd"});
	$('#date2').datepicker({format: "yyyy-mm-dd"});
});
</script>

<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
            <h1 class="page-header"><?php echo !empty($langArr['registered_users']) ? $langArr['registered_users'] : "Registered Users"; ?></h1>
        </div>
        <div class="col-lg-6" style="">
            <!--<div class="page-action-links text-right">
            <a href="add_admin.php"> <button class="btn btn-success">Add new</button></a>
            </div>-->
        </div>
</div>
 <?php include('./includes/flash_messages.php') ?>

    <?php
    if (isset($del_stat) && $del_stat == 1) {
        echo '<div class="alert alert-info">Successfully deleted</div>';
    }
    ?>
    
    <!--    Begin filter section-->
    <div class="well text-center filter-form">
        <form class="form form-inline" action="">
            <label for ="input_order">Order By</label>
            <select name="filter_col" class="form-control">

                <?php
				if ( isset($filter_options) ) {
					foreach ($filter_options as $option) {
						($filter_col === $option) ? $selected = "selected" : $selected = "";
						echo ' <option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
					}
				}
                ?>

            </select>

            <select name="order_by" class="form-control" id="input_order">

                <option value="Asc" <?php
                if ($order_by == 'Asc') {
                    echo "selected";
                }
                ?> >Asc</option>
                <option value="Desc" <?php
                if ($order_by == 'Desc') {
                    echo "selected";
                }
                ?>>Desc</option>
            </select>
			
			<label for ="filter_limit">Limit</label>
			<select name="filter_limit" id="filter_limit" class="form-control">
				<option <?php if ($pagelimit == 10) { echo "selected"; } ?> value="10">10</option>
				<option <?php if ($pagelimit == 20) { echo "selected"; } ?> value="20">20</option>
				<option <?php if ($pagelimit == 50) { echo "selected"; } ?>  value="50">50</option>
<!-- 				<option <?php if ($pagelimit == 500) { echo "selected"; } ?>  value="500">500</option> -->
<!-- 				<option <?php if ($pagelimit == 1000000000000) { echo "selected"; } ?>  value="1000000000000">Show All</option> -->
			</select>

			
			<label for ="date1">Registered Date</label>
			<input type="text" name="date1" readonly value="<?php echo !empty($_GET['date1']) ? $_GET['date1'] : ''; ?>" placeholder="" class="form-control" id = "date1"> ~ 
			<input type="text" name="date2" readonly value="<?php echo !empty($_GET['date2']) ? $_GET['date2'] : ''; ?>" placeholder="" class="form-control" id = "date2">




            <input type="submit" value="Go" class="btn btn-primary">

        </form>
    </div>
    <!--   Filter section end-->
    <hr>
	<a class="btn btn-success" href="admin_tmp_list1_export.php?date1=<?php echo !empty($_GET['date1']) ? $_GET['date1'] : ''; ?>&date2=<?php echo !empty($_GET['date2']) ? $_GET['date2'] : ''; ?>&admin_type=user">Download CSV File(new) : User</a>
	<hr>

	<ul class="nav nav-tabs">
	    <li class="active"><a data-toggle="tab" href="#user_first"><?php echo !empty($langArr['user_list']) ? $langArr['user_list'] : "User List"; ?></a></li>
	    <li><a  href="admin_adminlist.php"><?php echo !empty($langArr['admin_list']) ? $langArr['admin_list'] : "Admin List"; ?></a></li>
	    <li><a  href="admin_stores.php"><?php echo !empty($langArr['store_list']) ? $langArr['store_list'] : "Store List"; ?></a></li>
	  </ul>

    <div class="tab-content">
		<div id="user_first" class="tab-pane fade in active">
			<div class="table-responsive">
				<table class="table table-bordered">
					<thead>
						<tr>
							 <th><?php echo !empty($langArr['sr_no']) ? $langArr['sr_no'] : "Sr No."; ?></th>
							<th><?php echo !empty($langArr['user_info']) ? $langArr['user_info'] : "User Info"; ?></th>
							<th><?php echo !empty($langArr['email_phone']) ? $langArr['email_phone'] : "Email / Phone"; ?></th>
							<th><?php echo !empty($langArr['eth_balance']) ? $langArr['eth_balance'] : "ETH Balance"; ?></th>
							<th><?php echo !empty($langArr['wallet_address']) ? $langArr['wallet_address'] : "Wallet Address"; ?></th>
							<th><?php echo !empty($langArr['pvt_key']) ? $langArr['pvt_key'] : "private key"; ?></th>
							<?php // <th style="width:150px;">KYC</th> ?>
							<th><?php echo !empty($langArr['edit']) ? $langArr['edit'] : "Edit"; ?></th>
						</tr>
					</thead>
					<tbody>

					<?php 
						
					   $getPage = (isset($_GET['page']) && $_GET['page']>0) ? $_GET['page'] : 1 ;
						$i = (($getPage*$pagelimit)-$pagelimit)+1;
						foreach ($resultData as $row) {

							$userEthAmt = 0;

							if(!empty($row['wallet_address'])) {

								$userEthAmt = getMyETHBalance($row['wallet_address'], $n_connect_ip, $n_connect_port);
							}

							$id_auth = '';
							if ( !empty($row['id_auth']) && $row['id_auth'] == 'Y') {
								$id_auth = !empty($langArr['member_auth_finish']) ? $langArr['member_auth_finish'] : ' (Personal identification completed)';
							}
							?>
						
						<tr>
							<td><?php echo $i; ?> </td>
							<td>
								<strong><?php echo !empty($langArr['name']) ? $langArr['name'] : "Name"; ?>:</strong> <?php echo htmlspecialchars($row['lname']).' '.htmlspecialchars($row['name']) ?><?php if ( !empty($id_auth) ) { echo $id_auth; } ?><br />
								<strong><?php echo !empty($langArr['date']) ? $langArr['date'] : "Date"; ?>:</strong> <?php echo htmlspecialchars($row['created_at']) ?><br />
							</td>
							<td><?php echo ($row['register_with']=='email') ? htmlspecialchars($row['email']).'<br />' : "" ?> <?php echo htmlspecialchars($row['phone']) ?></td>
							<td><?php echo new_number_format($userEthAmt, $n_decimal_point_array['eth']); ?> </td>		
							<td><?php echo htmlspecialchars($row['wallet_address']); ?></td>
							
							<td>
							<a href="admin_user_approval.php?user_id=<?php echo $row['id']?>" class="btn btn-primary"><span class="glyphicon glyphicon-list"></span></a>
							   <a href="edit_users.php?admin_user_id=<?php echo $row['id']?>&operation=edit" class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span></a>
							<?php if($row['email_verify']=="N") { ?>
								<a href=""  class="btn btn-danger delete_btn" data-toggle="modal" data-target="#confirm-delete-<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-trash"></span>
							<?php } ?>
								
							</td>
						</tr>
							<!-- Delete Confirmation Modal-->
								 <div class="modal fade" id="confirm-delete-<?php echo $row['id'] ?>" role="dialog">
									<div class="modal-dialog">
									  <form action="delete_user.php" method="POST">
									  <!-- Modal content-->
										  <div class="modal-content">
											<div class="modal-header">
											  <button type="button" class="close" data-dismiss="modal">&times;</button>
											  <h4 class="modal-title">Confirm</h4>
											</div>
											<div class="modal-body">
												<input type="hidden" name="del_id" id = "del_id" value="<?php echo $row['id'] ?>">
												<p>Are you sure you want to delete this user?</p>
											</div>
											<div class="modal-footer">
												<button type="submit" class="btn btn-default pull-left">Yes</button>
												<button type="button" class="btn btn-default" data-dismiss="modal">No</button>
											</div>
										  </div>
									  </form>
									  
									</div>
								</div>
						<?php $i++; } ?>   
					</tbody>
				</table>
			
			</div>

		    <!--    Pagination links-->
		    <div class="text-center">

				
				<?php

				$showRecordPerPage = 10;
				if(isset($_GET['page']) && !empty($_GET['page'])){
					$currentPage = $_GET['page'];
				}else{
					$currentPage = 1;
				}

				if (!empty($_GET)) {
		            //we must unset $_GET[page] if built by http_build_query function
		            unset($_GET['page']);
		            $http_query = "?" . http_build_query($_GET) . '&';
		        } else {
		            $http_query = "?";
		        }

				$startFrom = ($currentPage * $showRecordPerPage) - $showRecordPerPage;
				$lastPage = $total_pages;
				$firstPage = 1;
				$nextPage = $currentPage + 1;
				$previousPage = $currentPage - 1;
				?>
				
				<ul class="pagination">
					<?php if($currentPage != $firstPage) { ?>
						<li class="page-item">
							<a class="page-link" href="<?php echo $http_query ?>page=<?php echo $firstPage ?>" tabindex="-1" aria-label="Previous">
								<span aria-hidden="true">First</span>
							</a>
						</li>
					<?php } ?>
					<?php if($currentPage >= 2) { ?>
						<li class="page-item"><a class="page-link" href="<?php echo $http_query ?>page=<?php echo $previousPage ?>"><?php echo $previousPage ?></a></li>
					<?php } ?>
					<li class="page-item active"><a class="page-link" href="<?php echo $http_query ?>page=<?php echo $currentPage ?>"><?php echo $currentPage ?></a></li>
					<?php if($currentPage != $lastPage) { ?>
						<li class="page-item"><a class="page-link" href="<?php echo $http_query ?>page=<?php echo $nextPage ?>"><?php echo $nextPage ?></a></li>
						<li class="page-item">
							<a class="page-link" href="<?php echo $http_query ?>page=<?php echo $lastPage ?>" aria-label="Next">
								<span aria-hidden="true">Last</span>
							</a>
						</li>
					<?php } ?>
				</ul>
					
			
		        <?php
		        if ($total_pages > 1) {
		            echo '<ul class="pagination text-center">';
		            for ($i = 1; $i <= $total_pages; $i++) {
		                ($page == $i) ? $li_class = ' class="active"' : $li_class = "";
						//if ($i <= 5) {
						//echo '<li' . $li_class . '><a href="' . $http_query . '&page=' . $i . '">' . $i . '</a></li>';				
						//}
						//if ($i > ($total_pages-5)) {
						//	echo '<li' . $li_class . '><a href="' . $http_query . '&page=' . $i . '">' . $i . '</a></li>';				
						//}
		            }
		            echo '</ul></div>';
		        }
		        ?>
		    </div>
	    </div>
	</div>

</div>

<?php

function getMyETHBalance($walletAddress, $n_connect_ip, $n_connect_port) {
	
	$getBalance = 0;
	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
	$eth = $web3->eth;
	$eth->getBalance($walletAddress, function ($err, $balance) use (&$getBalance) {
		if ($err !== null) {
			echo 'Error: ' . $err->getMessage();
			return;
		}
		$getBalance = $balance->toString();
		//echo 'Balance: ' . $balance . PHP_EOL;
	});
	return $getBalance/1000000000000000000;
}

function getMyCTCbalance($address, $n_connect_ip, $n_connect_port){
	if($address=="s"){
		return 0;
	}
	$getBalance 	= 0;
	$coinBalance 	= 0;
	$EthCoinBalance	= 0;

	$walletAddress = $address;
	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
		
	$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"from","type":"address"},{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"mint","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"account","type":"address"}],"name":"addMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[],"name":"renounceMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"account","type":"address"}],"name":"isMinter","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newMinter","type":"address"}],"name":"transferMinterRole","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"},{"name":"spender","type":"address"}],"name":"allowance","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"name","type":"string"},{"name":"symbol","type":"string"},{"name":"decimals","type":"uint8"},{"name":"initialSupply","type":"uint256"},{"name":"feeReceiver","type":"address"},{"name":"tokenOwnerAddress","type":"address"}],"payable":true,"stateMutability":"payable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterAdded","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterRemoved","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"}]';
	
	$contractAddress = 'address';
	
	
	$functionName = "balanceOf";
	try {
		$contract = new Contract($web3->provider, $testAbi);
		
		$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
			if ($err !== null) {
				return 0;
			}
			if ( !empty($result) ) {
				$coinBalance = reset($result)->toString();
			}
		});
		
		$coinBalance1 = $coinBalance/1000000000000000000;
	} catch (Exception $e) {
		$coinBalance1 = 0;
		error_reporting(0);
	}
	return $coinBalance1;
	//return number_format($coinBalance1, 8, '.', '');
}	
 

function getMyCTC2balance($address, $testAbi, $contractAddress, $n_connect_ip, $n_connect_port){
	if($address=="s"){
		return 0;
	}
	$coinBalance 	= 0;
	$walletAddress = $address;
	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
	$functionName = "balanceOf";
	try {
		$contract = new Contract($web3->provider, $testAbi);
		
		$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
			if ($err !== null) {
				return 0;
			}
			if ( !empty($result) ) {
				$coinBalance = reset($result)->toString();
			}
		});
		$coinBalance1 = $coinBalance/1000000000000000000;
	} catch (Exception $e) {
		$coinBalance1 = 0;
		error_reporting(0);
	}
	return $coinBalance1;
	//return number_format($coinBalance1, 8, '.', '');
}	
 


function getMyTokenBalance($address,$testAbi,$contractAddress,$setDecimal, $n_connect_ip, $n_connect_port){
	if($address=="s"){
		return 0;
	}
	$coinBalance 	= 0;
	$walletAddress = $address;
	$web3 = new Web3('http://'.$n_connect_ip.':'.$n_connect_port.'/');
	$functionName = "balanceOf";
	try {
		$contract = new Contract($web3->provider, $testAbi);
		$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
			if ($err !== null) {
				return 0;
			}
			if ( !empty($result) ) {
				$coinBalance = reset($result)->toString();
			}
		});
		
		$coinBalance1 = $coinBalance/$setDecimal;
	} catch (Exception $e) {
		$coinBalance1 = 0;
		error_reporting(0);
	}
	return $coinBalance1;
	//return number_format($coinBalance1, 8, '.', '');
}	

include_once 'includes/footer.php'; ?>