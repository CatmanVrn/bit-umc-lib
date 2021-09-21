<?php
if (is_file(realpath('../../../vendor/autoload.php')))
{
    require_once(realpath('../../../vendor/autoload.php'));
}

use AlexNzr\BitUmcIntegration\RequestController;
use AlexNzr\BitUmcIntegration\Utils;

$data = file_get_contents('php://input');
print_r(RequestController::sendRequest($data));