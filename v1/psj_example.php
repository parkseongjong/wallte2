<?php
include_once __DIR__ . '/Config/Bootstrap.php';
$exchange = new \App\Service\ExchangeService();
$userID = 3808;
$transactionId = 1;
$amount = 173200;
try {
    $exchange->setUserId($userID);
    $result = $exchange->exchangePoint($transactionId, $amount);
} catch (Exception $e) {
    debug($e);
}
