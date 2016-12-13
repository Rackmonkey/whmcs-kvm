<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use marmoset\marmoset as Marmoset;

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function marmoset_MetaData()
{
    return array(
        'DisplayName' => 'Marmoset',
        'APIVersion' => '1.1', // Use API Version 1.1
        'RequiresServer' => false, // Set true if module requires a server to work
        'DefaultNonSSLPort' => '80', // Default Non-SSL Connection Port
        'DefaultSSLPort' => '443', // Default SSL Connection Port
        'ServiceSingleSignOnLabel' => 'Login to Panel as User',
        'AdminSingleSignOnLabel' => 'Login to Panel as Admin',
    );
}

function marmoset_ConfigOptions()
{
    return array(
        'CPU' => array(
            'Type' => 'text',
            'Size' => '10',
            'Default' => '1',
            'Description' => 'Kerne',
        ),
        'RAM' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => '1024',
            'Description' => 'Enter MB',
        ),
        'HDD' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => '25',
            'Description' => 'Enter GB',
        ),
        'Name-Prefix' => array(
            'Type' => 'text',
            'Size' => '25',
            'Default' => 'marmo',
            'Description' => 'Prefix für VM Namen',
        ),
    );
}

function marmoset_CreateAccount(array $params)
{
    $pdo = Capsule::connection()->getPdo();
    $rkvm = new Marmoset($params);
    $pdo->beginTransaction();
    try {
        // http://docs.whmcs.com/Provisioning_Module_Developer_Docs#Module_Parameters
        $rkvm->create($pdo);
    } catch (Exception $e) {
        logModuleCall(
            'marmoset',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        $pdo->rollBack();
        return $e->getMessage();
    }
    return 'success';
}

function marmoset_SuspendAccount(array $params)
{
    try {
        Capsule::table('marmoset')->where('serviceid', $params["serviceid"])->update(['status' => "inactive"]);
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'suspend',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        return $e->getMessage();
    }

    return 'success';
}

function marmoset_UnsuspendAccount(array $params)
{
    try {
        Capsule::table('marmoset')->where('serviceid', $params["serviceid"])->update(['status' => "active"]);
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'unsuspend',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function marmoset_TerminateAccount(array $params)
{
    try {
        Capsule::table('marmoset')->where('serviceid', $params["serviceid"])->update(['status' => "terminate"]);
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'terminate',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function marmoset_ChangePassword(array $params)
{
    try {

    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'change password',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function marmoset_ChangePackage(array $params)
{
    $pdo = Capsule::connection()->getPdo();
    $rkvm = new Marmoset($params);
    $pdo->beginTransaction();
    try {
        $rkvm->update($pdo);
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'change package',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );
        $pdo->rollBack();
        return $e->getMessage();
    }

    return 'success';
}

function marmoset_TestConnection(array $params)
{
    try {
        // ping?!

        $success = true;
        $errorMsg = '';
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'marmoset',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        $success = false;
        $errorMsg = $e->getMessage();
    }

    return array(
        'success' => $success,
        'error' => $errorMsg,
    );
}

function marmoset_AdminCustomButtonArray()
{
    return array(
        "start" => "start",
        "stop" => "stop",
        "reboot" => "reboot",
        "shutdown" => "shutdown",
        "rescue" => "rescue",
        "noVNC" => "novnc",
    );
}

function marmoset_ClientAreaCustomButtonArray()
{
    return array(
        "start" => "start",
        "stop" => "stop",
        "reboot" => "reboot",
        "shutdown" => "shutdown",
        "rescue" => "rescue",
        "noVNC" => "novnc",
    );
}

function marmoset_start(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'vm start',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function marmoset_stop(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'vm stop',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return $e->getMessage();
    }

    return 'success';
}

function marmoset_AdminServicesTabFields(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
        $response = array();

        // Return an array based on the function's response.
        return array(
            'Number of Apples' => (int) $response['numApples'],
            'Number of Oranges' => (int) $response['numOranges'],
            'Last Access Date' => date("Y-m-d H:i:s", $response['lastLoginTimestamp']),
            'Something Editable' => '<input type="hidden" name="marmoset_original_uniquefieldname" '
                . 'value="' . htmlspecialchars($response['textvalue']) . '" />'
                . '<input type="text" name="marmoset_uniquefieldname"'
                . 'value="' . htmlspecialchars($response['textvalue']) . '" />',
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'marmoset',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, simply return no additional fields to display.
    }

    return array();
}

function marmoset_AdminServicesTabFieldsSave(array $params)
{
    // Fetch form submission variables.
    $originalFieldValue = isset($_REQUEST['marmoset_original_uniquefieldname'])
        ? $_REQUEST['marmoset_original_uniquefieldname']
        : '';

    $newFieldValue = isset($_REQUEST['marmoset_uniquefieldname'])
        ? $_REQUEST['marmoset_uniquefieldname']
        : '';

    // Look for a change in value to avoid making unnecessary service calls.
    if ($originalFieldValue != $newFieldValue) {
        try {
            // Call the service's function, using the values provided by WHMCS
            // in `$params`.
        } catch (Exception $e) {
            // Record the error in WHMCS's module log.
            logModuleCall(
                'marmoset',
                __FUNCTION__,
                $params,
                $e->getMessage(),
                $e->getTraceAsString()
            );

            // Otherwise, error conditions are not supported in this operation.
        }
    }
}

function marmoset_ServiceSingleSignOn(array $params)
{
    try {
        // Call the service's single sign-on token retrieval function, using the
        // values provided by WHMCS in `$params`.
        $response = array();

        return array(
            'success' => true,
            'redirectTo' => $response['redirectUrl'],
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'marmoset',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}

function marmoset_AdminSingleSignOn(array $params)
{
    try {
        // Call the service's single sign-on admin token retrieval function,
        // using the values provided by WHMCS in `$params`.
        $response = array();

        return array(
            'success' => true,
            'redirectTo' => $response['redirectUrl'],
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'marmoset',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        return array(
            'success' => false,
            'errorMsg' => $e->getMessage(),
        );
    }
}

/**
 * Client area output logic handling.
 *
 * This function is used to define module specific client area output. It should
 * return an array consisting of a template file and optional additional
 * template variables to make available to that template.
 *
 * The template file you return can be one of two types:
 *
 * * tabOverviewModuleOutputTemplate - The output of the template provided here
 *   will be displayed as part of the default product/service client area
 *   product overview page.
 *
 * * tabOverviewReplacementTemplate - Alternatively using this option allows you
 *   to entirely take control of the product/service overview page within the
 *   client area.
 *
 * Whichever option you choose, extra template variables are defined in the same
 * way. This demonstrates the use of the full replacement.
 *
 * Please Note: Using tabOverviewReplacementTemplate means you should display
 * the standard information such as pricing and billing details in your custom
 * template or they will not be visible to the end user.
 *
 * @param array $params common module parameters
 *
 * @see http://docs.whmcs.com/Provisioning_Module_SDK_Parameters
 *
 * @return array
 */
function marmoset_ClientArea(array $params)
{
    // Determine the requested action and set service call parameters based on
    // the action.
    $requestedAction = isset($_REQUEST['customAction']) ? $_REQUEST['customAction'] : '';

    if ($requestedAction == 'manage') {
        $serviceAction = 'get_usage';
        $templateFile = 'templates/manage.tpl';
    } else {
        $serviceAction = 'get_stats';
        $templateFile = 'templates/overview.tpl';
    }

    try {
        // Call the service's function based on the request action, using the
        // values provided by WHMCS in `$params`.
        $response = array();

        $extraVariable1 = 'abc';
        $extraVariable2 = '123';

        return array(
            'tabOverviewReplacementTemplate' => $templateFile,
            'templateVariables' => array(
                'extraVariable1' => $extraVariable1,
                'extraVariable2' => $extraVariable2,
            ),
        );
    } catch (Exception $e) {
        // Record the error in WHMCS's module log.
        logModuleCall(
            'marmoset',
            __FUNCTION__,
            $params,
            $e->getMessage(),
            $e->getTraceAsString()
        );

        // In an error condition, display an error page.
        return array(
            'tabOverviewReplacementTemplate' => 'error.tpl',
            'templateVariables' => array(
                'usefulErrorHelper' => $e->getMessage(),
            ),
        );
    }
}