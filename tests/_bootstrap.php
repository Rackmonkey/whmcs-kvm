<?php

// This is the bootstrap for PHPUnit testing.

if (!defined('WHMCS')) {
    define('WHMCS', true);
}

// Include the WHMCS module.
require_once __DIR__ . '/../modules/servers/marmoset/marmoset.php';
require_once __DIR__ . '/../modules/addons/marmoset/marmoset.php';
require_once __DIR__ . '/../modules/addons/ip_manager/ip_manager.php';


function logModuleCall(
    $module,
    $action,
    $request,
    $response,
    $data = '',
    $variablesToMask = array()
) {
    // do nothing during tests
}
