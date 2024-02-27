<?php


$wallet_array = array(
"0x39246f5a499ccc229161e33845b051d6e9722997",
"0xcc74c198c0c20f3a2c26558e69bbd21cb365d25d",
"0x7e55e2bbdef92c7a3170dc8378c4e10cae90ed6a",
"0x54d894c5e31c74c54dd233d0daf6d1386b5bdfff"


); 

$arrlength=count($wallet_array);


for($x=0;$x<$arrlength;$x++)
  {
  echo $wallet_array[$x];
  echo "<br>";
  }
?>