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


//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	$getNewUserId = $_POST['user_id'];
	$db = getDbInstance();
	$db->where("id", $getNewUserId);
	$getNewRow = $db->getOne('admin_accounts');
	$checkWalletAddr = $getNewRow['wallet_address'];
	if(empty($checkWalletAddr)) {
		
		$getUserEmailId = $getNewRow['email'];
		$newAccount = '';
		$web3 = new Web3('http://195.201.168.34:8545/'); // 127.0.0.1
		
		// create walletAddress if not exists start
		$personal = $web3->personal;
		$personal->newAccount($getUserEmailId.'ZUMBAE54R2507c16VipAjaCyber34Tron66CoinImmuAM', function ($err, $account) use (&$newAccount) {
			if ($err !== null) {
				echo 'Error: ' . $err->getMessage();
				
			}
			else {
				$newAccount = $account;
			}
		});
		
		$db = getDbInstance();
		$db->where("id", $getNewUserId);
		$updateArr = [] ;
		$updateArr['wallet_address'] =  $newAccount;
		$last_id = $db->update('admin_accounts', $updateArr);
		header('Location:admin_wallet_blank_users.php');
		exit;
	}
	
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
$db->where('wallet_address', '');
$db->where('email_verify', 'Y');

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
            <h1 class="page-header">Blank Wallet Address Users</h1>
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
    
    <!--   Filter section end-->
    <hr>
	
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
                    $userCtcAmt = 0;
                    
                    $totalCtcAmt = 0;
                    
                    
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
				<form method="post" >
                   <input type="hidden" name="user_id" value="<?php echo $row['id']; ?>" />
				   <input type="Submit" class="btn btn-primary" name="submit" value="Generate Wallet" />
				</form>
                    
                </td>
            </tr>
			<?php }	?>
             
            <?php endforeach; ?>   
        </tbody>
    </table>
	
	</div>



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




?>	
	



<?php include_once 'includes/footer.php'; ?>