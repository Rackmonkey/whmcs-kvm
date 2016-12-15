<?php 

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

function marmoset_config(){
    $version = "1.0";
    //$m = new Marmoset();
    //$newestVersion = $m->checkForUpdate();
    $description = "marmoset Addon - Rescue, KVM...";
    if ($version < $newestVersion) {
        $description .= " Deine Version ist veraltet. Du hast {$version}, aktuell ist {$newestVersion}.";
    }
    $configarray = array(
        "name" => "Marmoset",
        "description" => $description,
        "version" => $version,
        "author" => "David Bauer",
        "language" => "german",
    );	
    return $configarray;
}

function marmoset_activate(){
	/* CREATE TABLE IF NOT EXISTS `mod_marmoset` (
	`id` int(11) NOT NULL,
	  `serviceid` int(11) NOT NULL,
	  `name` varchar(255) NOT NULL,
	  `uuid` varchar(36) NOT NULL,
	  `ip` varchar(15) NOT NULL,
	  `ipv6` varchar(39) NOT NULL,
	  `mac` varchar(17) NOT NULL,
	  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
	) ENGINE=InnoDB; */
	
	$successArray = array();
    $query = "CREATE TABLE IF NOT EXISTS `mod_marmoset` (
	`id` int(11) NOT NULL,
	  `serviceid` int(11) NOT NULL,
	  `name` varchar(255) NOT NULL,
	  `uuid` varchar(36) NOT NULL,
	  `ip` varchar(15) NOT NULL,
	  `ipv6` varchar(39) NOT NULL,
	  `mac` varchar(17) NOT NULL,
	  `status` int(1) NOT NULL,
	  `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	  `updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
	) ENGINE=InnoDB;";
    full_query($query);
    
    $query = "ALTER TABLE `mod_marmoset` ADD PRIMARY KEY (`id`);";
    full_query($query);
    
    $query = "ALTER TABLE `mod_marmoset` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;";
    full_query($query);
    
    $successArray[] = "Created table mod_marmoset";
    
    return array(
        "status" => "info",
        "description" => "Marmoset Addon installed: " . implode(", ", $successArray)
    );
}

function marmoset_deactivate(){
	$query = "DROP TABLE IF EXISTS `mod_marmoset`";
    full_query($query);
}

function marmoset_upgrade($vars){
	$version = $vars['version'];
	
	# Run SQL Updates for V1.0 to V1.1
    if ($version < 1.1) { // Dummy! 
        $query = "CREATE TABLE IF NOT EXISTS `mod_marmoset_value_name_alias` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `option_alias_id` int(10) unsigned NOT NULL ,`technical_name` varchar(255) NOT NULL,`alias` varchar(255) NOT NULL, PRIMARY KEY (`id`), UNIQUE (`option_alias_id`,`alias`)) ENGINE=InnoDB";
        $result = mysql_query($query);
    }
}

function marmoset_output($vars){
	echo "marmoset output";
	print_r($vars);
}

?>