<?php
session_start();
require_once './config/config.php';
//If User has already logged in, redirect to dashboard page.
//serve POST method, After successful insert, redirect to customers.php page.
if ($_SERVER['REQUEST_METHOD'] == 'POST') 
{
    //Mass Insert Data. Keep "name" attribute in html form same as column name in mysql table.
    $data_to_store = filter_input_array(INPUT_POST);
	$email = filter_input(INPUT_POST, 'email');
	$pass = filter_input(INPUT_POST, 'passwd');
	
	 //Get DB instance. function is defined in config.php
    $db = getDbInstance();

    $db->where("email", $email);
    $row = $db->get('admin_accounts');
     
    if ($db->count > 0) {
		$_SESSION['login_failure'] = "email id registered already!";
    	header('location: register.php');
    	exit();
	}
	
	//Insert timestamp$passwd=  md5($passwd);
    $data_to_store['created_at'] = date('Y-m-d H:i:s');
	$data_to_store['admin_type'] = 'user';
	$data_to_store['user_name'] = $_POST['email'];
	$data_to_store['passwd'] = md5($pass);
    $db = getDbInstance();
    $last_id = $db->insert('admin_accounts', $data_to_store);
    
    if($last_id)
    { 	
		$_SESSION['success'] = "Registered successfully!";
    	header('location: login.php');
    	exit();
    }  
}


include_once 'includes/header.php';
?>
<div id="page-" class="col-md-4 col-md-offset-4">
	<form class="form loginform" method="POST" action="register.php">
		<div class="login-panel panel panel-default">
			<div class="panel-heading">Registration</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="control-label">Name</label>
					<input type="text" name="name" class="form-control" required="required">
				</div>
				<div class="form-group">
					<label class="control-label">Email</label>
					<input type="email" name="email" class="form-control" required="required">
				</div>
				<div class="form-group">
					<label class="control-label">password</label>
					<input type="password" name="passwd" class="form-control" required="required">
				</div>
				
				<?php
				if(isset($_SESSION['login_failure'])){ ?>
				<div class="alert alert-danger alert-dismissable fade in">
					<a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
					<?php echo $_SESSION['login_failure']; unset($_SESSION['login_failure']);?>
				</div>
				<?php } ?>
				<button type="submit" class="btn btn-success loginField" >Submit</button>
			</div>
		</div>
	</form>
</div>
<?php include_once 'includes/footer.php'; ?>