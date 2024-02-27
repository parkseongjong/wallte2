<?php

switch ($_SESSION['lang']) {
    case "en":
        define("LANG", include_once __DIR__ . '/../Language/en.php');
        break;
    default :
        define("LANG", include_once __DIR__ . '/../Language/ko.php');
}