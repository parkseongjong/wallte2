<?php

/**/
for($x=0;$x<300;$x++)
{

$rand_num = sprintf('%d',rand(700000,1200000));
echo $rand_num ;
echo "<br>" ;
}




return;
exit;

	$Variable = bcmul (500000, 1000000000000000000);
	echo $Variable;

	echo "<br>";
	$amountToSend = dec2hex($Variable);
	echo $amountToSend;



function dec2hex($number)
{
    $hexvalues = array('0','1','2','3','4','5','6','7',
               '8','9','A','B','C','D','E','F');
    $hexval = '';
     while($number != '0')
     {
        $hexval = $hexvalues[bcmod($number,'16')].$hexval;
        $number = bcdiv($number,'16',0);
    }
    return $hexval;
}
?>
