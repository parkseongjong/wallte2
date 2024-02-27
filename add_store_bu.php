<?php
session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');

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
    //Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    $data_to_store = filter_input_array(INPUT_POST);
    //Insert timestamp
    $data_to_store['created_at'] = date('Y-m-d H:i:s');
    $db = getDbInstance();
	$insertArr = [] ;
	$insertArr['store_name'] =  $data_to_store['store_name'];
	$insertArr['store_wallet_address'] =  $data_to_store['store_wallet_address'];
	$insertArr['store_description'] =  $data_to_store['store_description'];
	$last_id = $db->insert('stores', $insertArr);
    
    if($last_id)
    {
    	$_SESSION['success'] = "Store Addeded Successfully";
    	header('location: stores.php');
    	exit();
    }  
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
   <!-- MetisMenu CSS -->
        <link href="dist/css/bootstrap-datepicker.css" rel="stylesheet">
		 <script src="dist/js/bootstrap-datepicker.js" type="text/javascript"></script> 
<style>
.form-group .form-control {
	flex-basis: 90%;
	color: #000;
	padding: 12px 12px;
	border: none;
	box-sizing: border-box;
	outline: none;
	letter-spacing: 1px;
	font-size: 17px;
	font-weight: 700;
	border: 1px solid #bbb;
	background: #e1e1e1;
	height:auto;
}

.submit-button {
	width: 70%;
	background: rgb(51, 232, 255);
	outline: none;
	color: #000;
	margin: 10px 0px;
	font-size: 14px;
	font-weight: 400;
	border: 1px solid #33e8ff;
	padding: 11px 11px;
	letter-spacing: 1px;
	text-transform: uppercase;
	border-radius: 20px;
	cursor: pointer;
	transition: 0.5s all;
	-webkit-transition: 0.5s all;
	-o-transition: 0.5s all;
	-moz-transition: 0.5s all;
	-ms-transition: 0.5s all;
	
}
</style>
<div id="page-wrapper">
	<div class="row">
		     <div class="col-lg-12">
				<h2 class="page-header"><?php echo !empty($langArr['add_Store']) ? $langArr['add_Store'] : "Add Store"; ?></h2>
			</div>
			
	</div>
	<?php include('./includes/flash_messages.php') ?>
    <div class="col-md-3"></div>
    <div class="col-md-6">
	<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">

		<fieldset>
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['store_name']) ? $langArr['store_name'] : "Store Name"; ?></label>
				  <input type="text" name="store_name"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "store_name">
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['store_wallet_address']) ? $langArr['store_wallet_address'] : "Store Wallet Address"; ?></label>
				  <input type="text" name="store_wallet_address"  class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "store_wallet_address">
			</div> 

			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['store_description']) ? $langArr['store_description'] : "Store Description"; ?></label>
				<textarea class="form-control" title="<?php echo $langArr['this_field_is_required']; ?>" required="required" id = "store_description" name = "store_description"></textarea>
				
			</div> 
			 <br/>
			<div class="form-group text-center">
				<label></label>
				<button type="submit" class="btn btn-warning submit-button" ><?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?> <span class="glyphicon glyphicon-send"></span></button>
			</div>            
		</fieldset>
	</form>
</div></div>


<script type="text/javascript">
$(document).ready(function(){
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
	$('#dob').datepicker({format: "yyyy/mm/dd"});
	

});

</script>

<?php include_once 'includes/footer.php'; ?>