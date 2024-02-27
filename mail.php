
<?php



$to = $_POST['to'];
$subject = $_POST['subject'];

$message = $_POST['message'];

// Always set content-type when sending HTML email
$headers = "MIME-Version: 1.0" . "\r\n";
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";

// More headers
$headers .= 'From: <'.$_POST["from"].'>' . "\r\n";;

if(mail($to,$subject,$message,$headers)){
	echo "send";
}
else {
	echo "not send";
}
?> 