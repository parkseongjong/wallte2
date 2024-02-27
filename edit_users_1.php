<?php
session_start();
require_once './config/config.php';
require_once 'includes/auth_validate.php';


// Sanitize if you want
$user_id = filter_input(INPUT_GET, 'admin_user_id', FILTER_VALIDATE_INT);
$operation = filter_input(INPUT_GET, 'operation',FILTER_SANITIZE_STRING); 
($operation == 'edit') ? $edit = true : $edit = false;
 $db = getDbInstance();

 
  //Only super admin is allowed to access this page
if ($_SESSION['admin_type'] !== 'admin') {
    $_SESSION['failure'] = "You can't perform this action!";
        //Redirect to the listing page,
        header('location: index.php');
}
//Handle update request. As the form's action attribute is set to the same script, but 'POST' method, 
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
	
	if($_SESSION['admin_type']!='admin'){
		 $_SESSION['failure'] = "You can't perform this action!";
        //Redirect to the listing page,
        header('location: index.php');
	}
    //Get customer id form query string parameter.
    $user_id = filter_input(INPUT_GET, 'admin_user_id', FILTER_SANITIZE_STRING);

    //Get input data
    $data_to_update = filter_input_array(INPUT_POST);
    
   // $data_to_update['updated_at'] = date('Y-m-d H:i:s');
    $db = getDbInstance();
    $db->where('id',$user_id);
	$updateArr = [] ;
	$updateArr['name'] =  $data_to_update['fname'];
	$updateArr['lname'] =  $data_to_update['lname'];
	$updateArr['gender'] =  $data_to_update['gender'];
	$updateArr['dob'] =  $data_to_update['dob'];
	$updateArr['admin_type'] =  $data_to_update['admin_type'];
	$updateArr['location'] =  $data_to_update['location'];
    $stat = $db->update('admin_accounts', $updateArr);

    if($stat)
    {
        $_SESSION['success'] = "Users updated successfully!";
        //Redirect to the listing page,
        header('location: admin_users.php');
        //Important! Don't execute the rest put the exit/die. 
        exit();
    }
}


//If edit variable is set, we are performing the update operation.
if($edit)
{
    $db->where('id',$user_id);
    //Get data to pre-populate the form.
    $users = $db->getOne("admin_accounts");
}
?>


<?php
    include_once 'includes/header.php';
?>
   <!-- MetisMenu CSS -->
        <link href="dist/css/bootstrap-datepicker.css" rel="stylesheet">
		 <script src="dist/js/bootstrap-datepicker.js" type="text/javascript"></script> 
<div id="page-wrapper">
    <div class="row">
        <h2 class="page-header">Update Users</h2>
    </div>
    <!-- Flash messages -->
    <?php
        include('./includes/flash_messages.php')
    ?>

    <form class="" action="" method="post" enctype="multipart/form-data" id="contact_form">
        
       <fieldset>
			<div class="form-group">
				<label for="l_name">Name*</label>
				<input type="text" name="fname" value="<?php echo $edit ? $users['name'] : ''; ?>" placeholder="Name" class="form-control" required="required" id="name">
			</div> 

			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?></label>
				  <input type="text" name="lname" value="<?php echo $users['lname']; ?>" placeholder="<?php echo !empty($langArr['last_name']) ? $langArr['last_name'] : "Last Name"; ?>" class="form-control" required="required" id = "lname">
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['gender']) ? $langArr['gender'] : "Gender"; ?></label>
				  <select class="form-control" id="gender" name="gender">
				  <option  value=""><?php echo !empty($langArr['select']) ? $langArr['select'] : "Select"; ?></option>
				  <option <?php echo ($users['gender']=="male") ? "Selected" : ""; ?> value="male"><?php echo !empty($langArr['male']) ? $langArr['male'] : "Male"; ?></option>
				  <option <?php echo ($users['gender']=="female") ? "Selected" : ""; ?> value="female"><?php echo !empty($langArr['female']) ? $langArr['female'] : "Female"; ?></option>
				  <option <?php echo ($users['gender']=="other") ? "Selected" : ""; ?> value="other"><?php echo !empty($langArr['other']) ? $langArr['other'] : "Other"; ?></option>
				 </select>
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['dob']) ? $langArr['dob'] : "DOB"; ?></label>
				  <input type="text" name="dob" readonly value="<?php echo $users['dob']; ?>" placeholder="<?php echo !empty($langArr['dob']) ? $langArr['dob'] : "DOB"; ?>" class="form-control" required="required" id = "dob">
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['location']) ? $langArr['location'] : "Location"; ?></label>
				  <input type="text" name="location" value="<?php echo $users['location']; ?>" placeholder="<?php echo !empty($langArr['location']) ? $langArr['location'] : "Location"; ?>" class="form-control" required="required" id = "location"">
			</div> 
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['user_role']) ? $langArr['user_role'] : "User Role"; ?></label>
				  <select class="form-control" required id="role" name="admin_type">
				  <option  value=""><?php echo !empty($langArr['select']) ? $langArr['select'] : "Select"; ?></option>
				  <option <?php echo ($users['admin_type']=="user") ? "Selected" : ""; ?> value="user"><?php echo !empty($langArr['user']) ? $langArr['user'] : "User"; ?></option>
				  <option <?php echo ($users['admin_type']=="admin") ? "Selected" : ""; ?> value="admin"><?php echo !empty($langArr['admin']) ? $langArr['admin'] : "Admin"; ?></option>
				 
				 </select>
			</div>
			
			<div class="form-group text-center">
				<label></label>
				<button type="submit" class="btn btn-warning" >Save <span class="glyphicon glyphicon-send"></span></button>
			</div>            
		</fieldset>
    </form>
</div>


<script type="text/javascript">
$(document).ready(function(){

	$('#dob').datepicker({format: "yyyy/mm/dd"});
});

</script>

<?php include_once 'includes/footer.php'; ?>