<?php
$host = "1.234.82.14";
$user = "hansdev";
$pw = "1234";
$dbName = "wallet";

$conn = new mysqli($host, $user, $pw, $dbName);

/* DB 연결 확인 */
//if($conn){ echo "Connection established"."<br>"; }
//else{ die( 'Could not connect: ' . mysqli_error($conn) ); }

return $conn;