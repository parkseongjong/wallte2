<?php
// Page in use
session_start();
$getLang = '';
if(isset($_SESSION['lang']) && !empty($_SESSION['lang'] ) ) {
	$getLang= $_SESSION['lang'];
}
session_destroy();
session_start();
$_SESSION['lang']=$getLang;
if(isset($_COOKIE['phone']) && isset($_COOKIE['password'])){
    setcookie('phone', '', time() - 3600);
    unset($_COOKIE['phone']);
    setcookie('password', '', time() - 3600);
    unset($_COOKIE['password']);
}
header('Location:index.php');

 ?>