<?php
if(isset($_SESSION['success']))
{

echo '<div class="alert alert-success alert-dismissable">
   		<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
    		<strong>Success! </strong>'. $_SESSION['success'].'
  	  </div>';
  unset($_SESSION['success']);
}

if(isset($_SESSION['failure']))
{
echo '<div class="alert alert-danger alert-dismissable">
   		<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
    		<strong>Oops! </strong>'. $_SESSION['failure'].'
  	  </div>';
  unset($_SESSION['failure']);
}

if(isset($_SESSION['info']))
{
echo '<div class="alert alert-info alert-dismissable">
   		<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
    		'. $_SESSION['info'].'
  	  </div>';
  unset($_SESSION['info']);
}


if(isset($_SESSION['failure_error']))
{
echo '<div class="alert alert-danger alert-dismissable">
   		<a href="#" class="close" data-dismiss="alert" aria-label="close">×</a>
    		<strong>Error! </strong>'. $_SESSION['failure_error'].'
  	  </div>';
  unset($_SESSION['failure_error']);
}


 ?>