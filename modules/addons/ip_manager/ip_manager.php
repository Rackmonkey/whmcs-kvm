<?php 

if (!defined("WHMCS")) {
    die("This file cannot be accessed directly");
}

use Illuminate\Database\Capsule\Manager as Capsule;

function ip_manager_config(){
    $version = "1.0";
    $description = "IP Manager";
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
	`id` int(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
	`serviceid` int(11) DEFAULT 0,
	`subnetid` int(11) NOT NULL,
	`ip` varchar(15) DEFAULT '0',
	`ipv6` varchar(39) DEFAULT '0',
	`created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
	) ENGINE=InnoDB;";
    $successArray[] = full_query($query);
    
    $successArray[] = "Created table mod_ip_manager";
    
    $query = "CREATE TABLE IF NOT EXISTS `mod_ip_manager_subnet` (
	`id` int(11) NOT NUL PRIMARY KEY AUTO_INCREMENTL,
	`baseip` varchar(39),
	`mask` varchar(39),
	`gateway` varchar(39),
	`created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
	) ENGINE=InnoDB;";
    $successArray[] = full_query($query);
    
    $successArray[] = "Created table mod_ip_manager_subnet";
    
    return array(
        "status" => "info",
        "description" => "Marmoset Addon installed: " . implode(", ", $successArray)
    );
}

function ip_manager_deactivate(){
	$query = "DROP TABLE IF EXISTS `mod_ip_manager`";
    full_query($query);
    $query = "DROP TABLE IF EXISTS `mod_ip_manager_subnet`";
    full_query($query);
}

function ip_manager_upgrade($vars){
	$version = $vars['version'];
	
	# Run SQL Updates for V1.0 to V1.1
	# for later use
    if ($version < 1.1) { // Dummy! 
        $query = "CREATE TABLE IF NOT EXISTS `mod_marmoset_value_name_alias` (`id` int(10) unsigned NOT NULL AUTO_INCREMENT, `option_alias_id` int(10) unsigned NOT NULL ,`technical_name` varchar(255) NOT NULL,`alias` varchar(255) NOT NULL, PRIMARY KEY (`id`), UNIQUE (`option_alias_id`,`alias`)) ENGINE=InnoDB";
        $result = mysql_query($query);
    }
}


function mask2cidr($mask){
  $long = ip2long($mask);
  $base = ip2long('255.255.255.255');
  return 32-log(($long ^ $base)+1,2);

  /* xor-ing will give you the inverse mask,
      log base 2 of that +1 will return the number
      of bits that are off in the mask and subtracting
      from 32 gets you the cidr notation */
       
}

function cidr2mask($cidr) {
    $ta = substr($cidr, strpos($cidr, '/') + 1) * 1;
    $netmask = str_split(str_pad(str_pad('', $ta, '1'), 32, '0'), 8);
    foreach ($netmask as &$element) $element = bindec($element);
    return join('.', $netmask);
}

function getFreeIp(){
	return Capsule::table("mod_ip_manager")->where("serviceid", "0")->first();	
}

function getFreeIps(){
	return Capsule::table("mod_ip_manager")->where("serviceid", "0")->get();	
}

function setIPbyId($id,$serviceid){
	Capsule::table("mod_ip_manager")->where("id", $id)->update(["serviceid" => $serviceid]);
}

function setIPbyIP($ip,$serviceid){
	Capsule::table("mod_ip_manager")->where("ip", $ip)->update(["serviceid" => $serviceid]);
}

function getIpAnzahl($subnet){
	$ipanzahl = 1;
	for(;$subnet<32;$subnet++){
		$ipanzahl = $ipanzahl * 2;
	}
	return $ipanzahl;
}

function checkCIDR(){
	return true;
}

function addSubnet($cidr){
	if(!checkCIDR($cidr)){
		return 2;
	}
	$netmask = cidr2mask($cidr);
	$ex = explode("/", $cidr);
	$baseip = $ex[0];
	$gateway = long2ip(ip2long($baseip)+1);
	$check = Capsule::table("mod_ip_manager_subnet")->where("baseip",$baseip)->first();
	if(isset($check->baseip)){
		// gibt schon ein eintrag??
		return 3;
	}
	Capsule::table("mod_ip_manager_subnet")->insert(["baseip" => $baseip, "mask" => $netmask, "gateway" => $gateway]);
	$subnetid = Capsule::table("mod_ip_manager_subnet")->where("baseip",$baseip)->where("mask",$netmask)->where("gateway",$gateway)->first()->id;
	$ip = long2ip(ip2long($gateway)+1);
	// Baseip ist +1 network ip, damit $ipanzahl--
	// wir überspringen die gateway IP
	$ipanzahl = getIpAnzahl($ex[1]) - 3; // -gateway -networkip -broadcast
	for(;$ipanzahl>0;$ipanzahl--){
		// letzte IP ist broadcast
		if($ip == "255.255.255.255"){
			return 4;
		}
		Capsule::table("mod_ip_manager")->insert(["serviceid" => 0, "subnetid" => $subnetid, "ip" => $ip, "ipv6" => "0"]);
		$ip = long2ip(ip2long($ip)+1);
	}
	return 1;
}
	
// return 1 if successfull, 2 if subnet not empty, 3 other error
function removeSubnet($id){
	$ret = Capsule::table("mod_ip_manager")->where("subnetid",$id)->where("serviceid",0)->first();
	if(isset($ret->id)){
		return 2;
	}
	$ret = Capsule::table("mod_ip_manager_subnet")->where("id",$id)->first();
	if(!isset($ret->id)){
		return 3;
	}
	Capsule::table("mod_ip_manager_subnet")->where("id",$id)->remove();
	Capsule::table("mod_ip_manager")->where("subnetid",$id)->remove();
	return 1;
}

function subnetTable(){
	        	$list = Capsule::table("mod_ip_manager_subnet")->get();
                echo "<h3>{$vars['_lang']['masterlist']}</h3>";
                echo "<div class='tablebg'>";
                echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
                echo "<thead><tr><th>ID</th><th>CIDR</th><th>Mask</th><th>Gateway</th></thead>";
                echo "<tbody>";
                foreach ($list as $server) {
                    echo "<tr>";
                    echo "<td>{$server->id}</td>";
                    echo "<td>{$server->baseip}/".mask2cidr($server->mask)."</td>";
                    echo "<td>{$server->mask}</td>";
                    echo "<td>{$server->gateway}</td>";
                    echo "</tr>";
                }
                echo "</tbody></table>";
                echo "</div>";
}


function ipTable($ipv = 4, $free = false){
				if($ipv == 4)$ipversion = "ipv6"; 
				if($ipv == 6)$ipversion = "ip"; 
				if($free){
	        		$list = Capsule::table("mod_ip_manager")->join('mod_ip_manager_subnet', 'mod_ip_manager.subnetid', '=', 'mod_ip_manager_subnet.id')->select("mod_ip_manager.*","mod_ip_manager_subnet.baseip", "mod_ip_manager_subnet.mask")->where("serviceid", "0")->where($ipversion, "0")->get();
	        	}else{
	        		$list = Capsule::table("mod_ip_manager")->join('mod_ip_manager_subnet', 'mod_ip_manager.subnetid', '=', 'mod_ip_manager_subnet.id')->select("mod_ip_manager.*","mod_ip_manager_subnet.baseip", "mod_ip_manager_subnet.mask")->where("serviceid", ">", "0")->where($ipversion, "0")->get();
	        	}
                echo "<h3>{$vars['_lang']['masterlist']}</h3>";
                echo "<div class='tablebg'>";
                echo "<table class='datatable' border='0' width='100%' cellspacing='1' cellpadding=3>";
                echo "<thead><tr><th>ID</th>";
                if(!$free){
                	echo "<th>Kunde</th>";
                	echo "<th>Service ID</th>";
                }
                echo "<th>Subnet ID</th><th>";
                if($ipv == 4){
                    	echo "IPv4";
                }else{
                    	echo "IPv6";
                }
                echo "</th></thead><tbody>";
                foreach ($list as $server) {
                    echo "<tr><td>{$server->id}</td>";
                    if(!$free){
                    	$client = Capsule::table("tblhosting")->join('tblclients', 'tblhosting.userid', '=', 'tblclients.id')->select("tblclients.id","tblclients.firstname","tblclients.lastname", "tblclients.companyname", "tblhosting.packageid")->where("tblhosting.id", $server->serviceid)->first();
                    	echo "<td><a href='clientssummary.php?userid={$client->id}'>{$client->firstname} {$client->lastname} ({$client->companyname})</a></td>";
                    	$service = Capsule::table("tblproducts")->where("id", $client->packageid)->first();
                    	echo "<td><a href='clientsservices.php?id={$server->serviceid}'>{$service->name} ({$server->serviceid})</a></td>";
                	}
                    echo "<td>{$server->subnetid} - ({$server->baseip}/".mask2cidr($server->mask).")</td>";
                    if($ipv == 4){
                    	echo "<td>{$server->ip}</td>";
                    }else{
                    	echo "<td>{$server->ipv6}</td>";
                    }
                    echo "</tr>";
                }
                echo "</tbody></table>";
                echo "</div>";
}

function ip_manager_output($vars){
        echo "<h2>IPv4 Subnetze hinzufügen</h2>";
        echo '<form class="form-inline" action="'.$vars['modulelink'].'&amp;method=add" method="post">';
        echo '<div class="form-group">';
        echo '<label for="text">CIDR:</label>';
        echo '<input type="text" class="form-control" name="CIDR" placeholder="Enter CIDR">';
        echo '</div>';
        echo '<button type="submit" class="btn btn-default">Submit</button>';
        echo '</form>';
        if($_GET["method"] == "add"){
            echo addSubnet($_POST["CIDR"]);
        }
        
        echo "<h2>IPv4 Subnetze</h2>";
        subnetTable();
        echo "<h2>Belegte IPv4 Adressen</h2>";
        ipTable(4);
        echo "<h2>Freie IPv4 Adressen</h2>";
        ipTable(4, true);
        //ipTable(6);
}

?>