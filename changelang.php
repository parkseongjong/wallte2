<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	$getLang = $_POST['lang'];
	echo $_SESSION['lang'] = $getLang;
}

?>