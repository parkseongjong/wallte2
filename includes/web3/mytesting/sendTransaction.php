<?php

require('./exampleBase.php');

$personal = $web3->personal;
$personal->unlockAccount('0xa1b5e28cc33f4cae772ec6a7aaf170ebbf6fa178', 'admin@gmail.com', function ($err, $unlocked) {
	if ($err !== null) {
		echo 'Error: ' . $err->getMessage();
		return;
	}
	if ($unlocked) {
        echo 'New account is unlocked!' . PHP_EOL;
	} else {
	    echo 'New account isn\'t unlocked' . PHP_EOL;
	}
});


$eth = $web3->eth;




echo 'Eth Send Transaction' . PHP_EOL;
$eth->accounts(function ($err, $accounts) use ($eth) {
    if ($err !== null) {
        echo 'Error: ' . $err->getMessage();
        return;
    }
    /* $fromAccount = $accounts[0];
    $toAccount = $accounts[1]; */
	$fromAccount = '0xa1b5e28cc33f4cae772ec6a7aaf170ebbf6fa178';
    $toAccount = '0x10e9fab66554cb046503b6dbcd73e1381e0aad45';

    // get balance
    $eth->getBalance($fromAccount, function ($err, $balance) use($fromAccount) {
        if ($err !== null) {
            echo 'Error: ' . $err->getMessage();
            return;
        }
        echo $fromAccount . ' Balance: ' . $balance . PHP_EOL;
    });
    $eth->getBalance($toAccount, function ($err, $balance) use($toAccount) {
        if ($err !== null) {
            echo 'Error: ' . $err->getMessage();
            return;
        }
        echo $toAccount . ' Balance: ' . $balance . PHP_EOL;
    });

	
	
	
    // send transaction
    $eth->sendTransaction([
        'from' => $fromAccount,
        'to' => $toAccount,
        'value' => '0x9184e72a'
    ], function ($err, $transaction) use ($eth, $fromAccount, $toAccount) {
        if ($err !== null) {
            echo 'Error: ' . $err->getMessage();
            return;
        }
        echo 'Tx hash: ' . $transaction . PHP_EOL;

        // get balance
        $eth->getBalance($fromAccount, function ($err, $balance) use($fromAccount) {
            if ($err !== null) {
                echo 'Error: ' . $err->getMessage();
                return;
            }
            echo $fromAccount . ' Balance: ' . $balance . PHP_EOL;
        });
        $eth->getBalance($toAccount, function ($err, $balance) use($toAccount) {
            if ($err !== null) {
                echo 'Error: ' . $err->getMessage();
                return;
            }
            echo $toAccount . ' Balance: ' . $balance . PHP_EOL;
        });
    });
});