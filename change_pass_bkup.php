<?php
error_reporting("E_ALL");
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

	if(trim($_POST['new_pass'])!="" && ($_POST['new_pass'] == $_POST['conf_pass'])){


	$data_to_store['created_at'] = date('Y-m-d H:i:s');
	$db = getDbInstance();
	$db->where("id", $_SESSION['user_id']);
	$db->where("passwd", md5($_POST['old_pass']));
	$last_id = $db->update('admin_accounts', ['passwd'=>md5($_POST['new_pass'])]);
	    if($last_id)
	    {
    		$_SESSION['success'] = $langArr['password_changed_successfully'];
	    	header('location: change_pass.php');
    		exit();
	   }  
	}else{
		$_SESSION['failure'] = "Some error are occurred";
//		exit();
	}
}

//We are using same form for adding and editing. This is a create form so declare $edit = false.
$edit = false;

require_once 'includes/header.php'; 
?>
<style>
/* Style all input fields */
body {
	background-color: #ffcd07 !important;
}

input {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  box-sizing: border-box;
  margin-top: 6px;
  margin-bottom: 16px;
}

/* Style the submit button */
input[type=submit] {
  background-color: #4CAF50;
  color: white;
}

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
	border-left: 1px solid #fff;
	border-bottom: 1px solid #fff;
	background: rgba(255, 255, 255, 0.81);
	height:auto;
}

/* Style the container for inputs */
.container {
  background-color: #f1f1f1;
  padding: 20px;
}

/* The message box is shown when the user clicks on the password field */
#message {
  display:none;
  background: #f1f1f1;
  color: #000;
  position: relative;
  padding: 20px;
  margin-top: 10px;
}

#message p {
  padding: 10px 35px;
  font-size: 18px;
}

/* Add a green text color and a checkmark when the requirements are right */
.valid {
  color: green;
}

.valid:before {
  position: relative;
  left: -35px;
  content: "✔";
}

/* Add a red text color and an "x" when the requirements are wrong */
.invalid {
  color: red;
}

.invalid:before {
  position: relative;
  left: -35px;
  content: "✖";
}

.row.login-bg {
	margin:0;
	height:100%;
	background: #ffcd07;
}

.login-panel {
	background-color: #fff;
	background-position: center;
	background-size: cover;
	padding: 0.5em 2em;
	margin: 0em auto;
	border: rgba(23, 19, 19, 0.95) !important;
	box-shadow: 0px 0px 5px 4px rgba(121, 121, 121, 0.36) !important;
}

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

.panel-body {
	padding:0 !important;
	text-align:center;
	color:#000;	
}
#page- {
	padding:0;
}

.btn-success.loginField {
	width: 100%;
	background: #ffcd07;
	outline: none;
	color: #000;
	margin: 10px 0px;
	font-size: 18px;
	font-weight: 400;
	border: 1px solid #ffcd07;
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

.login-panel {
    margin-top: 30px;
	margin-bottom: 30px;
}

.tab-content {
	padding-top: 12px;
}

.nav.nav-tabs select {
	float: right;
	background: #ffcd07;
	border: none;
	text-align: center;
	padding: 11px;
	border-radius: 3px;
}

</style>
<div id="page-wrapper">
	<div class="row">
		 <div class="col-lg-12">
				<h2 class="page-header"><?php echo !empty($langArr['change_password']) ? $langArr['change_password'] : "Change Password"; ?></h2>
			</div>
			
	</div>
	<?php include('./includes/flash_messages.php') ?>
	<form class="form" action="" method="post"  id="customer_form" enctype="multipart/form-data">
		<fieldset>
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['old_password']) ? $langArr['old_password'] : "Old Password"; ?> *</label>
				  <input type="password" name="old_pass" title="<?php echo $langArr['this_field_is_required']; ?>"  class="form-control" required="required" id = "old_pass" >
			</div> 
			
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['new_password']) ? $langArr['new_password'] : "New Password"; ?> *</label>
				  <input type="password" name="new_pass" value="" pattern=".{8,}" title="Must contain at least 8 or more characters"  class="form-control" required="required" id = "new_pass" >
			</div> 
			<div id="message">
			  <h3><?php echo !empty($langArr['password_contain']) ? $langArr['password_contain'] : "Password must contain the following :"; ?></h3>
			 
			  <p id="length" class="invalid"><?php echo !empty($langArr['minimum']) ? $langArr['minimum'] : "Minimum"; ?> <b><?php echo !empty($langArr['8']) ? $langArr['8'] : "8"; ?> <?php echo !empty($langArr['characters']) ? $langArr['characters'] : "characters"; ?></b></p>
			</div>
			<div class="form-group">
				<label for="f_name"><?php echo !empty($langArr['confirm_password']) ? $langArr['confirm_password'] : "Confirm Password"; ?> *</label>
				  <input type="password" name="conf_pass" value=""  class="form-control" required="required" id = "conf_pass" >
			</div> 

			<div class="form-group text-center">
				<label></label>
				<button type="submit" class="btn btn-warning" ><?php echo !empty($langArr['submit']) ? $langArr['submit'] : "Submit"; ?> <span class="glyphicon glyphicon-send"></span></button>
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
	
	
	var myInput = document.getElementById("new_pass");
	var letter = document.getElementById("letter");
	var capital = document.getElementById("capital");
	var number = document.getElementById("number");
	var length = document.getElementById("length");

	// When the user clicks on the password field, show the message box
	myInput.onfocus = function() {
	  document.getElementById("message").style.display = "block";
	}

	// When the user clicks outside of the password field, hide the message box
	myInput.onblur = function() {
	  document.getElementById("message").style.display = "none";
	}

	// When the user starts to type something inside the password field
	myInput.onkeyup = function() {
	  // Validate lowercase letters
	 /*  var lowerCaseLetters = /[a-z]/g;
	  if(myInput.value.match(lowerCaseLetters)) {  
		letter.classList.remove("invalid");
		letter.classList.add("valid");
	  } else {
		letter.classList.remove("valid");
		letter.classList.add("invalid");
	  }
	  
	  // Validate capital letters
	  var upperCaseLetters = /[A-Z]/g;
	  if(myInput.value.match(upperCaseLetters)) {  
		capital.classList.remove("invalid");
		capital.classList.add("valid");
	  } else {
		capital.classList.remove("valid");
		capital.classList.add("invalid");
	  }

	  // Validate numbers
	  var numbers = /[0-9]/g;
	  if(myInput.value.match(numbers)) {  
		number.classList.remove("invalid");
		number.classList.add("valid");
	  } else {
		number.classList.remove("valid");
		number.classList.add("invalid");
	  } */
	  
	  // Validate length
	  if(myInput.value.length >= 8) {
		length.classList.remove("invalid");
		length.classList.add("valid");
	  } else {
		length.classList.remove("valid");
		length.classList.add("invalid");
	  }
	}
	
	$(".form").submit(function(){
		var getpasslength = $("#new_pass").val();
		if(getpasslength.length<8){
			 document.getElementById("message").style.display = "block";
			 return false;
		}
	})
});
</script>

<?php include_once 'includes/footer.php'; ?>
