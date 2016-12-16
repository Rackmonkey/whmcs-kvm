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
	`id` int(11) NOT NULL,
	`serviceid` int(11) DEFAULT 0,
	`subnetid` int(11) NOT NULL,
	`ip` varchar(15) DEFAULT '0.0.0.0',
	`ipv6` varchar(39) DEFAULT '0',
	`created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
	`updated` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' ON UPDATE CURRENT_TIMESTAMP
	) ENGINE=InnoDB;";
    $successArray[] = full_query($query);
    
    $successArray[] = "Created table mod_ip_manager";
    
    $query = "CREATE TABLE IF NOT EXISTS `mod_ip_manager_subnet` (
	`id` int(11) NOT NULL,
	`baseip` int(11),
	`mask` varchar(15),
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

function addSubnet($cidr,$gateway){
	if(checkCIDR($cidr)){
		return -2;
	}
	$netmask = cidr2mask($cidr);
	$ex = explode("/", $cidr);
	$baseip = $ex[0];
	$check = Capsule::table("mod_ip_manager_subnet")->where("baseip",$baseip)->orWhere("netmask",$netmask)->orWhere("gateway",$gateway)->first();
	if(isset($check->baseip)){
		// gibt schon ein eintrag??
		return -1;
	}
	$ipanzahl = getIpAnzahl($ex[1]);
	Capsule::table("mod_ip_manager_subnet")->insert(["baseip" => $baseip, "netmask" => $netmask, "gateway" => $gateway]);
	$ret = Capsule::table("mod_ip_manager_subnet")->where("baseip",$baseip)->where("netmask",$netmask)->where("gateway",$gateway)->first();
	$subnetid = $ret->id;
	$ip = $baseip;
	// Baseip ist +1 network ip, damit $ipanzahl--
	$ipanzahl--;
	for(;$ipanzahl>1;$ipanzahl--){
		// letzte IP ist broadcast
		if($ip == "255.255.255.255"){
			break;
		}
		Capsule::table("mod_ip_manager")->insert(["serviceid" => 0, "subnetid" => $subnetid, "ip" => $ip, "ipv6" => ""]);
		$ipl = ip2long($ip);
		$ipl++;
		$ip = long2ip($ipl);
	}
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

function ip_manager_output($vars){
    // Create Easy-Wi object, so we can see config options

    if (0) {
        echo "<div  style='margin:0;padding:10px;background-color:#FBEEEB;border:1px dashed #cc0000;font-weight: bold;color: #cc0000;font-size:14px;text-align: center;'>";
        echo $vars['_lang']['syncNoPointExternal'];
        echo "</div>";
    }
    // Help text and navigation buttons
    echo "<div  style='margin:10px;padding:10px;background-color:#D9EDF7;border:1px #BCE8F1;font-weight: bold;color: #3A87AD;font-size:14px;text-align: center;'>";
    if (isset($_GET["method"])) {
        if ($_GET["method"] == "List") {
            echo "Liste aller Subnetze";
        } else if ($_GET["method"] == "Add") {
            echo "Füge neue Subnetze hinzu";
        }else if ($_GET["method"] == "Remove") {
            echo "Lösche Subnetze - Es dürfen keine IPs mehr zugewiesen sein.";
        }
    } else {
        echo $vars['_lang']['intro'];
    }
    echo "</div>";
    echo "<div>";
    echo "<a href='{$vars['modulelink']}'><button type='button' class='btn'>Übersicht</button></a> ";
    echo "<a href='{$vars['modulelink']}&amp;method=List'><button type='button' class='btn'>List Subnets</button></a> ";
    echo "<a href='{$vars['modulelink']}&amp;method=Add'><button type='button' class='btn'>Add Subnet</button></a> ";
    echo "<a href='{$vars['modulelink']}&amp;method=Remove'><button type='button' class='btn'>Remove Subnet</button></a> ";
    echo "</div>";
    
}

?>