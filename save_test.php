
<?php

session_start();
require_once './config/config.php';






	$data_to_store_error = [];
	$data_to_store_error['user_name'] = '1';
	$data_to_store_error['register_with'] = '2';
	$data_to_store_error['verify_code_sms'] = '3';
	$data_to_store_error['verify_code_write'] = '4';


	$db_error_insert = getDbInstance();
	$db_error_insert->insert('admin_accounts_error', $data_to_store_error);






?>


