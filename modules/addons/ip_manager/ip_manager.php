<?php 

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function ip_manager_config(){
    $version = "1.0";
    //$m = new Marmoset();
    //$newestVersion = $m->checkForUpdate();
    $description = "IP Manager";
    if ($version < $newestVersion) {
        $description .= " Deine Version ist veraltet. Du hast {$version}, aktuell ist {$newestVersion}.";
    }
    $configarray = array(
        "name" => "IP Manager",
        "description" => $description,
        "version" => $version,
        "author" => "David Bauer",
        "language" => "german",
    );	
    return $configarray;
}

function ip_manager_activate(){
	
	$successArray = array();
    $query = "CREATE TABLE IF NOT EXISTS `mod_ip_manager` (
	`id` int(11) NOT NULL,
	`serviceid` int(11) NOT NULL,
	`ip` varchar(15) NOT NULL,
	`ipv6` varchar(39) NOT NULL,
	`created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
	) ENGINE=InnoDB;";
    full_query($query);
    
    $successArray[] = "Created table mod_ip_manager";
    
    return array(
        "status" => "info",
        "description" => "Marmoset Addon installed: " . implode(", ", $successArray)
    );
}

function ip_manager_deactivate(){
	$query = "DROP TABLE IF EXISTS `mod_ip_manager`";
    full_query($query);
}

function ip_manager_upgrade($vars){
	$version = $vars['version'];
	
	# Run SQL Updates for V1.0 to V1.1
    if ($version < 1.1) { // Dummy! 
        $query = "CREATE TABLE IF NOT EXISTS `mod_marmoset_value_name_alias` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `option_alias_id` int(10) unsigned NOT NULL ,`technical_name` varchar(255) NOT NULL,`alias` varchar(255) NOT NULL, PRIMARY KEY (`id`), UNIQUE (`option_alias_id`,`alias`)) ENGINE=InnoDB";
        $result = mysql_query($query);
    }
}

function ip_manager_output($vars){
	echo "marmoset_output";
	print_r($vars);
}

?>