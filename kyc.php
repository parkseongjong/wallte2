<?php
session_start();
require_once './config/config.php';
require_once './includes/auth_validate.php';

$db = getDbInstance();
$db->where("id", $_SESSION['user_id']);
$row = $db->get('admin_accounts');
 

//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    //Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    $data_to_store = filter_input_array(INPUT_POST);
    //Insert timestamp
  ;
    $db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$last_id = $db->update('admin_accounts', $data_to_store);
    
    if($last_id)
    {
    	$_SESSION['success'] = "KYC updated successfully!";
    	header('location: kyc.php');
    	exit();
    }  
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
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
	width: 30%;
	background: rgb(51, 232, 255);
	outline: none;
	color: #000;
	margin: 10px 0px;
	font-size: 18px;
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
				<h2 class="page-header">KYC</h2>
			</div>
			
	</div>
	<?php include('./includes/flash_messages.php') ?>
  
	<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
		<fieldset>
			
			<div class="form-group col-md-6">
				<label for="f_name">PAN NO *</label>
				  <input type="text" name="pan_no" value="<?php echo $row[0]['pan_no']; ?>" placeholder="Name" class="form-control" required="required" id = "pan_no">
			</div> 
			<div class="form-group col-md-6">
				<label for="f_name">BANK A/C NO *</label>
				  <input type="text" name="bank_ac_no" value="<?php echo $row[0]['bank_ac_no']; ?>" placeholder="Name" class="form-control" required="required" id = "bank_ac_no">
			</div> 
			<div class="form-group col-md-6">
				<label for="f_name">IFSC CODE *</label>
				  <input type="text" name="ifsc_code" value="<?php echo $row[0]['ifsc_code']; ?>" placeholder="Name" class="form-control" required="required" id = "ifsc_code">
			</div> 
			<div class="form-group col-md-6">
				<label for="f_name">BANK NAME *</label>
				  <input type="text" name="bank_name" value="<?php echo $row[0]['bank_name']; ?>" placeholder="Name" class="form-control" required="required" id = "bank_name">
			</div> 
			
			 <br/>
			<div class="form-group text-center">
				<label></label>
				<button type="submit" class="btn btn-warning submit-button" >Submit <span class="glyphicon glyphicon-send"></span></button>
			</div>            
		</fieldset>
	</form>
</div>


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
});
</script>

<?php include_once 'includes/footer.php'; ?>