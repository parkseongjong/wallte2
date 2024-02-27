<?php
//die("Registration close for public user");
session_start();
require_once './config/config.php';

include_once 'includes/header.php';
?>

<div id="page-wrapper">
    <div class="row">
        <div class="col-lg-12">
            <h1 class="page-header">Add Message</h1>
        </div>
        <!-- /.col-lg-12 -->
    </div>
    <!-- /.row -->
    <div class="row">
	<form class="form messageform" method="POST" action="admin_reply_save.php">
	<?php include('./includes/flash_messages.php') ?>
        
        <div class="col-lg-6 col-md-6">
           
                <div class="panel-heading">
                    <div class="row">
                        <div class="col-xs-3">
                            &nbsp;
                        </div>
                        <div class="col-xs-9 text-right">
                           <textarea class="form-control" name="message_text" placeholder="message" class="form-control" required="required">

</textarea>
                        </div>
                    </div>
					<button type="submit" class="btn btn-success" value="save" >Save</button>  
                </div>
             
            
        </div>
       
	   
        <div class="col-lg-6 col-md-6">
            
        </div>
		</form>
    </div>
    <!-- /.row -->
    <div class="row">
        <div class="col-lg-8">


            <!-- /.panel -->
        </div>
        <!-- /.col-lg-8 -->
           </div>
    <!-- /.row -->
</div>
<!-- /#page-wrapper -->



	
		
<?php include_once 'includes/footer.php'; ?>










