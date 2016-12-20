<?php 

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

if (!defined("DS")) {
    define("DS", DIRECTORY_SEPARATOR);
}
if (!defined("WHMCS_MAIN_DIR")) {
    define("WHMCS_MAIN_DIR", substr(dirname(__FILE__), 0, strpos(dirname(__FILE__), "modules" . DS . "addons")));
}


if (!class_exists("Marmoset")) {
    require_once(WHMCS_MAIN_DIR . DS . "modules" . DS . "servers" . DS . "marmoset" . DS . "lib" . DS . "marmoset.php");
}

use Illuminate\Database\Capsule\Manager as Capsule;
use Marmoset\Marmoset as Marmoset;

$table = "mod_ip_manager";

$status_ok = 1;
$status_suspended = 2;
$status_deleted = 3;
$status_undefined = 0;

// Addon ip_manager muss installiert sein, prüfen das über die table
//if(mysql_num_rows(mysql_query("SHOW TABLES LIKE " . $table )) > 0){

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
		  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
		  `updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
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
		# for later use
	    if ($version < 1.1) { // Dummy! 
	        $query = "CREATE TABLE IF NOT EXISTS `mod_marmoset_value_name_alias` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `option_alias_id` int(10) unsigned NOT NULL ,`technical_name` varchar(255) NOT NULL,`alias` varchar(255) NOT NULL, PRIMARY KEY (`id`), UNIQUE (`option_alias_id`,`alias`)) ENGINE=InnoDB";
	        $result = mysql_query($query);
	    }
	}
	
	function table(){
	        	$list = Capsule::table("mod_marmoset")->get();
                echo "<h3>{$vars['_lang']['masterlist']}</h3>";
                echo "<div class='tablebg'>";
                echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
                echo "<thead><tr><th>ID</th><th>Service</th><th>name</th><th>UUID</th><th>MAC</th><th>Status</th></thead>";
                echo "<tbody>";
                foreach ($list as $server) {
                    echo "<tr>";
                    echo "<td>{$server->id}</td>";
                    echo "<td>{$server->serviceid}</td>";
                    echo "<td>{$server->name}</td>";
                    echo "<td>{$server->uuid}</td>";
                    echo "<td>{$server->mac}</td>";
                    if($server->status == $status_ok){echo "<td>OK</td>";}
                    else if($server->status == $status_suspended){echo "<td>suspended</td>";}
                    else if($server->status == $status_deleted){echo "<td>deleted</td>";}
                    else if($server->status == $status_undefined){echo "<td>undefined</td>";}
                    else {echo "<td>dafuq</td>";}
                    echo "</tr>";
                }
                echo "</tbody></table>";
                echo "</div>";
}
	

	
	function marmoset_output($vars){
		table();
	}
//}
?>