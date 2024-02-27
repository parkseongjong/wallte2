<?php
session_start();
require_once './config/config.php';
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
$search_string = filter_input(INPUT_GET, 'search_string');
$del_id = filter_input(INPUT_GET, 'del_id');

$filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');
$page = filter_input(INPUT_GET, 'page');
$pagelimit = filter_input(INPUT_GET, 'filter_limit');
if($pagelimit == "") {
	$pagelimit = 20;
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
$select = array('id', 'name', 'lname','wallet_address','email' ,'phone','created_at','pan_no','bank_ac_no','ifsc_code','bank_name','register_with','email_verify','user_ip');

// If user searches 
if ($search_string) {
    $db->where('email', '%' . $search_string . '%', 'like');
}


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

<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
            <h1 class="page-header">Registered Users</h1>
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
            <label for="input_search" >Search</label>
            <input type="text" placeholder="Email" class="form-control" id="input_search"  name="search_string" value="<?php echo $search_string; ?>">
            <label for ="input_order">Order By</label>
            <select name="filter_col" class="form-control">

                <?php
                foreach ($filter_options as $option) {
                    ($filter_col === $option) ? $selected = "selected" : $selected = "";
                    echo ' <option value="' . $option . '" ' . $selected . '>' . $option . '</option>';
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
			
			<label for ="input_order">Limit</label>
			<select name="filter_limit" class="form-control">
				<option <?php if ($pagelimit == 20) { echo "selected"; } ?> value="20">20</option>
				<option <?php if ($pagelimit == 100) { echo "selected"; } ?>  value="100">100</option>
				<option <?php if ($pagelimit == 500) { echo "selected"; } ?>  value="500">500</option>
				<option <?php if ($pagelimit == 1000000000000) { echo "selected"; } ?>  value="1000000000000">Show All</option>
			</select>
            <input type="submit" value="Go" class="btn btn-primary">

        </form>
    </div>
    <!--   Filter section end-->
    <hr>
	<a class="btn btn-success" href="admin_users_export.php">Download CSV File</a>
	<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th class="header">#</th>
                <th>Name</th>
				<th>Email</th>
				<th>Wallet Address</th>
				<th>CTC Balance</th>
				<th>Phone</th>
				<th>IP</th>
				<th style="width:150px;">KYC</th>
				<th>Dated</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>

            <?php 
            
            $totalCtcAmt = 0; 
           
            foreach ($resultData as $row) : ?>
                
				<?php if($row['email']!='ajay@mailinator.com'){
                    $userCtcAmt = getMyCTCbalance($row['wallet_address']);
                    
                    $totalCtcAmt = $totalCtcAmt+$userCtcAmt;
                    
                    
				?>
					
			
            <tr>
                <td><?php echo $row['id'] ?></td>
                <td><?php echo htmlspecialchars($row['lname']).' '.htmlspecialchars($row['name']) ?></td>
				<td><?php echo ($row['register_with']=='email') ? htmlspecialchars($row['email']) : "" ?></td>
				<td><?php echo htmlspecialchars($row['wallet_address']) ?></td>
				<td><?php echo $userCtcAmt; ?> </td>
              
				<td><?php echo htmlspecialchars($row['phone']) ?></td>
				<td><?php echo htmlspecialchars($row['user_ip']) ?></td>
				<td style="min-width: 150px !important;">
				
				<strong>PAN NO : </strong><?php echo htmlspecialchars($row['pan_no']) ?><br/>
				<strong>BANK A/C NO : </strong><?php echo htmlspecialchars($row['bank_ac_no']) ?><br/>
				<strong>IFSC CODE : </strong><?php echo htmlspecialchars($row['ifsc_code']) ?><br/>
				<strong>BANK NAME : </strong> <?php echo htmlspecialchars($row['bank_name']) ?><br/>
				
				
				</td>
				<td><?php echo htmlspecialchars($row['created_at']) ?></td>
                

                <td>
                   <a href="edit_users.php?admin_user_id=<?php echo $row['id']?>&operation=edit" class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span></a>
				<?php if($row['email_verify']=="N") { ?>
                    <a href=""  class="btn btn-danger delete_btn" data-toggle="modal" data-target="#confirm-delete-<?php echo $row['id'] ?>" style="margin-right: 8px;"><span class="glyphicon glyphicon-trash"></span>
				<?php } ?>
                    
                </td>
            </tr>
			<?php }	?>
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
            <?php endforeach; ?>   
        </tbody>
    </table>
	
	</div>
	<div class="col-md-6"><h1>Total CTC Balance</h1></div>
	<div class="col-md-6"><h1><?php echo number_format($totalCtcAmt,8); ?></h1></div>


    <!--    Pagination links-->
    <div class="text-center">

        <?php
        if (!empty($_GET)) {
            //we must unset $_GET[page] if built by http_build_query function
            unset($_GET['page']);
            $http_query = "?" . http_build_query($_GET);
        } else {
            $http_query = "?";
        }
        if ($total_pages > 1) {
            echo '<ul class="pagination text-center">';
            for ($i = 1; $i <= $total_pages; $i++) {
                ($page == $i) ? $li_class = ' class="active"' : $li_class = "";
                echo '<li' . $li_class . '><a href="' . $http_query . '&page=' . $i . '">' . $i . '</a></li>';
            }
            echo '</ul></div>';
        }
        ?>
    </div>
</div>

<?php




function getMyCTCbalance($address){
	if($address=="s"){
		return 0;
	}
	$getBalance 	= 0;
	$coinBalance 	= 0;
	$EthCoinBalance	= 0;

	$walletAddress = $address;

	$web3 = new Web3('http://195.201.168.34:8545/'); // 127.0.0.1

		
	$testAbi = '[{"constant":true,"inputs":[],"name":"name","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"value","type":"uint256"}],"name":"approve","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"totalSupply","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"from","type":"address"},{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transferFrom","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[],"name":"decimals","outputs":[{"name":"","type":"uint8"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"addedValue","type":"uint256"}],"name":"increaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"mint","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"}],"name":"balanceOf","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":true,"inputs":[],"name":"symbol","outputs":[{"name":"","type":"string"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"account","type":"address"}],"name":"addMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[],"name":"renounceMinter","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"spender","type":"address"},{"name":"subtractedValue","type":"uint256"}],"name":"decreaseAllowance","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":false,"inputs":[{"name":"to","type":"address"},{"name":"value","type":"uint256"}],"name":"transfer","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"account","type":"address"}],"name":"isMinter","outputs":[{"name":"","type":"bool"}],"payable":false,"stateMutability":"view","type":"function"},{"constant":false,"inputs":[{"name":"newMinter","type":"address"}],"name":"transferMinterRole","outputs":[],"payable":false,"stateMutability":"nonpayable","type":"function"},{"constant":true,"inputs":[{"name":"owner","type":"address"},{"name":"spender","type":"address"}],"name":"allowance","outputs":[{"name":"","type":"uint256"}],"payable":false,"stateMutability":"view","type":"function"},{"inputs":[{"name":"name","type":"string"},{"name":"symbol","type":"string"},{"name":"decimals","type":"uint8"},{"name":"initialSupply","type":"uint256"},{"name":"feeReceiver","type":"address"},{"name":"tokenOwnerAddress","type":"address"}],"payable":true,"stateMutability":"payable","type":"constructor"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterAdded","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"account","type":"address"}],"name":"MinterRemoved","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"from","type":"address"},{"indexed":true,"name":"to","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Transfer","type":"event"},{"anonymous":false,"inputs":[{"indexed":true,"name":"owner","type":"address"},{"indexed":true,"name":"spender","type":"address"},{"indexed":false,"name":"value","type":"uint256"}],"name":"Approval","type":"event"}]';
	
	$contractAddress = 'address';
	
	$functionName = "balanceOf";
	$contract = new Contract($web3->provider, $testAbi);
	
	$contract->at($contractAddress)->call($functionName, $walletAddress,function($err, $result) use (&$coinBalance){
		$coinBalance = reset($result)->toString();
	});
	
	$coinBalance1 = $coinBalance/1000000000000000000;
	return number_format($coinBalance1, 8, '.', '');
}	
 



?>	
	



<?php include_once 'includes/footer.php'; ?>