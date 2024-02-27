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
    $data_to_store['created_at'] = date('Y-m-d H:i:s');
    $db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$updateArr = [] ;
	$updateArr['name'] =  $data_to_store['fname'];
	$updateArr['lname'] =  $data_to_store['lname'];
	$updateArr['gender'] =  $data_to_store['gender'];
	$updateArr['dob'] =  $data_to_store['dob'];
	$updateArr['location'] =  $data_to_store['location'];
    $last_id = $db->update('admin_accounts', $updateArr);
    
    if($last_id)
    {
    	$_SESSION['success'] = $langArr['profile_updated_successfully'];
    	header('location: profile.php');
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
				<h2 class="page-header"><?php echo !empty($langArr['profile']) ? $langArr['profile'] : "Profile"; ?></h2>
			</div>
			
	</div>
	<?php include('./includes/flash_messages.php') ?>
	<ul class="nav nav-tabs">
		<li class="active"><a data-toggle="tab" href="#home"><?php echo !empty($langArr['profile']) ? $langArr['profile'] : "Profile"; ?></a></li>
		<li><a  href="change_pass.php"><?php echo !empty($langArr['change_password']) ? $langArr['change_password'] : "Change Password"; ?></a></li>
		
	</ul>
	<div class="tab-content" >
		<div class="col-md-3"></div>
		<div class="col-md-6 tab-pane fade in active" >
			<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
				<div style="margin-top:10px;">
					<div id="pvt_btn" class="btn btn-success"><?php echo $langArr['show_private_key'] ?></div>
					<div id="pvt_resp"></div>
				</div> 
				<fieldset>
					<div class="form-group">
						<label for="f_name"><?php echo !empty($langArr['first_name']) ? $langArr['first_name'] : "First Name"; ?></label>
						  <input type="text" name="fname" value="<?php echo $row[0]['name']; ?>" placeholder="<?php echo !empty($langArr['first_name']) ? $langArr['first_name'] : "First Name"; ?>" class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "fname">
					</div> 
					<div class="form-group">
						<label for="f_name"><?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?></label>
						  <input type="text" name="lname" value="<?php echo $row[0]['lname']; ?>" placeholder="<?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?>" class="form-control" required="required" title="<?php echo $langArr['this_field_is_required']; ?>" id = "lname">
					</div> 
					<div class="form-group">
						<label for="f_name"><?php echo !empty($langArr['gender']) ? $langArr['gender'] : "Gender"; ?></label>
						  <select class="form-control" id="gender" name="gender">
						  <option  value=""><?php echo !empty($langArr['select']) ? $langArr['select'] : "Select"; ?></option>
						  <option <?php echo ($row[0]['gender']=="male") ? "Selected" : ""; ?> value="male"><?php echo !empty($langArr['male']) ? $langArr['male'] : "Male"; ?></option>
						  <option <?php echo ($row[0]['gender']=="female") ? "Selected" : ""; ?> value="female"><?php echo !empty($langArr['female']) ? $langArr['female'] : "Female"; ?></option>
						  <option <?php echo ($row[0]['gender']=="other") ? "Selected" : ""; ?> value="other"><?php echo !empty($langArr['other']) ? $langArr['other'] : "Other"; ?></option>
						 </select>
					</div> 
					<div class="form-group">
						<label for="f_name"><?php echo !empty($langArr['dob']) ? $langArr['dob'] : "DOB"; ?></label>
						  <input type="text" name="dob" readonly value="<?php echo $row[0]['dob']; ?>" placeholder="<?php echo !empty($langArr['dob']) ? $langArr['dob'] : "DOB"; ?>" class="form-control" title="<?php echo $langArr['this_field_is_required']; ?>" required="required" id = "dob">
					</div> 
					<div class="form-group">
						<label for="f_name"><?php echo !empty($langArr['location']) ? $langArr['location'] : "Location"; ?></label>
						  <input type="text" name="location" value="<?php echo $row[0]['location']; ?>" placeholder="<?php echo !empty($langArr['location']) ? $langArr['location'] : "Location"; ?>" title="<?php echo $langArr['this_field_is_required']; ?>" class="form-control" required="required" id = "location">
					</div> 
					 <!-- <select class="form-control">
					  <option>KYC</option>
					  <option>PAN NO</option>
					  <option>BANK A/C NO</option>
					  <option>IFSC CODE</option>
					  <option>BANK NAME</option>
					 </select> -->
					 <br/>
					<div class="form-group text-center">
						<label></label>
						<button type="submit" class="btn btn-warning submit-button" ><?php echo !empty($langArr['update']) ? $langArr['update'] : "Update"; ?> <span class="glyphicon glyphicon-send"></span></button>
					</div>            
				</fieldset>
			</form>
		</div>
	</div>
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
	$('#dob').datepicker({format: "yyyy/mm/dd"});
	
	
	$("#pvt_btn").click(function(){
		$.ajax({
			beforeSend:function(){
				$("#pvt_resp").html('<img src="images/ajax-loader.gif" />');
			},
			url : 'showpvt.php',
			type : 'POST',
			//dataType : 'json',
			success : function(resp){
				$("#pvt_resp").html(resp);
			},
			error : function(resp){
				$("#pvt_resp").html(resp);
			}
		}) 
	 }); 
});

</script>

<?php include_once 'includes/footer.php'; ?>