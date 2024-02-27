<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';


require('includes/web3/vendor/autoload.php');
use Web3\Web3;
use Web3\Contract;



//Only super admin is allowed to access this page
/* if ($_SESSION['admin_type'] !== 'admin') {
    // show permission denied message
    header('HTTP/1.1 401 Unauthorized', true, 401);
    //exit("401 Unauthorized");
} */
$db = getDbInstance();
//Get data from query string
//$search_string = filter_input(INPUT_GET, 'search_string');
//$del_id = filter_input(INPUT_GET, 'del_id');

/* $filter_col = filter_input(INPUT_GET, 'filter_col');
$order_by = filter_input(INPUT_GET, 'order_by');
$page = filter_input(INPUT_GET, 'page');
$pagelimit = filter_input(INPUT_GET, 'filter_limit');
 if($pagelimit == "") {
	$pagelimit = 20;
}
if ($page == "") {
    $page = 1;
}  */
// If filter types are not selected we show latest added data first
/* if ($filter_col == "") {
    $filter_col = "id";
}
if ($order_by == "") {
    $order_by = "desc";
}  */
// select the columns
$select = array('id', 'message_text','status','created_at');

// If user searches 
 /* if ($search_string) {
    $db->where('email', '%' . $search_string . '%', 'like');
}


if ($order_by) {
    $db->orderBy($filter_col, $order_by);
} */



//$db->pageLimit = $pagelimit;
$result = $db->get("messages");
$last=end($result);
 $last1=$last['message_text'];
//print_r($last1);die('hello');
//print_r($last);die('hello');


/* session_start();
$message = $_GET['message_text'];
$sql = "SELECT id FROM messages WHERE username='$message' limit 1";
$result = mysql_query($sql);
$value = mysql_fetch_object($result);
$_SESSION['myid'] = $value->id; */

// get columns for order filter
foreach ($result as $value) {
    foreach ($value as $col_name => $col_value) {
        $filter_options[$col_name] = $col_name;
    }
    //execute only once
    break;
}


include_once 'includes/header.php';
?>
<?php
/* $last2=end($result);
//print_r($last2);die('hello');
$abc=$last2['status'];
//print_r($abc);die('hello');
if($abc=='Y'){
	 echo '<marquee>'.$last1.'</marquee>' ;
} */

?>
  
            <!-- Navigation -->
<div id="page-wrapper">
<div class="row">
     <div class="col-lg-6">
            <h1 style="display: inline-block;" class="page-header">Show Message</h1>
        </div>
        <div class="col-lg-6" style="">
            <!--<div class="page-action-links text-right">
            <a href="add_admin.php"> <button class="btn btn-success">Add new</button></a>
            </div>-->
        </div>
</div>
 <?php include('./includes/flash_messages.php') ?>

    <?php
    /* if (isset($del_stat) && $del_stat == 1) {
        echo '<div class="alert alert-info">Successfully deleted</div>';
    } */
    ?>
    
    <!--    Begin filter section-->
   
    <!--   Filter section end-->
    
	
	<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <tr>
              
                <th>Id</th>
				<th>Message</th>
				<th>Created</th>
				<th>Status</th>
				<th>Actions</th>
				
            </tr>
        </thead>
        <tbody>

            <?php $totalGcgAmt = 0; foreach ($result as $row) : ?>
                
				<?php/*  if($row['email']!='ajay@mailinator.com'){
					$userGcgAmt = getMyGCGbalance($row['wallet_address']);
					$totalGcgAmt = $totalGcgAmt+$userGcgAmt; */
				?>
					
			
            <tr>
                <td><?php echo $row['id'] ?></td>
                <td><?php echo htmlspecialchars($row['message_text']) ?></td>
				<td><?php echo htmlspecialchars($row['created_at']) ?></td>
				<td><?php echo htmlspecialchars($row['status']) ?></td>
				<td> <a href="edit_message.php?message_id=<?php echo $row['id']?>&operation=edit" class="btn btn-primary"><span class="glyphicon glyphicon-edit"></span></a></td>
		    </tr>
			<?php //}	?>
               
                    
            <?php endforeach; ?>   
        </tbody>
    </table>
	
	</div>
	
	



<?php include_once 'includes/footer.php'; ?>