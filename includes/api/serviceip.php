<?php

// This API Extension returns the IP Settings to an Service
// Needs the ip_manager addon

// Usage:
// localAPI("serviceip", array("serviceid" => $serviceid), $adminUser);

// Informations about extending WHMCS API:
// https://bobcares.com/blog/extend-whmcs-create-your-own-whmcs-api-functions/

if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

$table = "mod_ip_manager";

// Addon ip_manager muss installiert sein, prüfen das über die table
if(mysql_num_rows(mysql_query("SHOW TABLES LIKE " . $table )) > 0){

	function getParams($vars) {
	    $param = array(‘action’ => array(), ‘params’ => array());
	    if (isset($vars[‘cmd’])) {
	    	//Local API mode
	    	$param[‘action’] = $vars[‘cmd’];
	    	$param[‘params’] = (object) $vars[‘apivalues1’];
	    } else {
	    	//Post CURL mode
	    	$param[‘action’] = $vars[‘_POST’][‘action’];
	    	$param[‘params’] = (object) $vars[‘_POST’];
		}
		return (object) $param;
	}

	try {
	    	// Get the arguments
	    	$vars = get_defined_vars();
	    	$param = getParam($vars);
	
			$data = Capsule::table($table)->where("serviceid" , $param["params"]["serviceid"])->first();
			if(isset($data->name)){
	    		$apiresults = array("result" => "success", "interface" => $data);	
	    	}else{
	    		$apiresults = array("result" => "error", "message" => "data error");
	    	}
	} catch (Exception $e) {
	    	$apiresults = array("result" => "error", "message" => $e->getMessage());
	}
	
}