<?php

namespace Marmoset;

require_once __DIR__ . '/curl.php';

use Illuminate\Database\Capsule\Manager as Capsule;

use Curl\Curl as curl;


class Marmoset {
    protected $params = array();
    protected $serviceid;
    protected $serverid;

    protected $name; // name der VM
    
    // Prov. Modul Options
    protected $CPU;
    protected $HDD;
    protected $RAM;
    protected $prefix; 
    
    // Infos von Marmoset nach Create
    protected $vncport;
    protected $wsport;
    protected $vncpassword;
    
    // Datenbank
    protected $table = "mod_marmoset";
    protected $table_ip_manger = "mod_ip_manager";
    protected $ip;
    protected $ipv6;
    protected $mac;
    protected $uuid;
    protected $status;
    protected $status_ok = 1;
    protected $status_suspended = 2;
    protected $status_deleted = 3;
    protected $status_undefined = 0;
    
    // Marmoset Connection
    protected $loginname = "admin";
    protected $loginpw = "secret" ;
    protected $apiurl = "http://localhost";
    protected $apiport = "5000";


    public function __construct( $params, $debug = false ) {

        if ( $debug === true ) {
            ini_set( 'display_errors', 1 );
            ini_set( 'display_startup_errors', 1 );
            error_reporting( E_ALL );
        }

        if ( is_array( $params ) ) {
            $this->params = $params;
        }

        $this->CPU		= $params["configoption1" ];
        $this->HDD		= $params["configoption2" ];
        $this->RAM		= $params["configoption3" ];
        $this->prefix	= $params["configoption4" ];

        $this->serviceid = $params["serviceid" ]; # Unique ID of the product/service in the WHMCS Database
        $this->serverid  = $params["serverid" ]; 
        
        // select auf mod_marmoset
		$user = Capsule::table($this->table)->where("serviceid", $this->serviceid)->first();
		if(isset($user->name)){
		        	$this->name     = $user->name;
		        	$this->ip 		= $user->ip;
		    		$this->ipv6     = $user->ipv6;
		    		$this->mac      = $user->mac;
		    		$this->uuid 	= $user->uuid;
		    		$this->status   = $user->status;
		}else{
		        	$this->name      = $this->get_vm_name();
		        	$this->ip 		= "0.0.0.0";
		    		$this->ipv6     = "0:0:0:0:0:0:0:0";
		    		$this->mac      = "00:00:00:00:00:00";
		    		$this->uuid 	= "00000000-0000-0000-0000-000000000000";
		    		$this->status   = $user->status_undefined;
		}
    }
    

    public function get_vm_name(){
		  return $this->prefix."".(string)$this->params["serviceid"];
    }
    
    private function curlRequest($url,$mode,$param = array()){
        	$c = new curl($url, $mode, $param);
    		$c->setBasicAuthentication($this->loginname, $this->loginpw);
    		$ret = $c->exec();
    		$info = $c->getInfo();
    		$c->close();
    		return array("ret" =>$ret,"info" => $info, "obj" => $c, "code" => $info["http_code"], "param" => $param);	
    }
    
    private function curlPost($url,$param = array()){
    	    return $this->curlRequest($url,"post",$param);
    }
    
    private function curlGet($url,$param = array()){
    	    return $this->curlRequest($url,"get",$param);
    }
    
    private function createVM($param = array()){
    		$url = $this->apiurl.":".$this->apiport."/v1/vm";
    		return $this->procReturn($this->curlPost($url,$param));
    }
    
    private function setPXE($param = array()){
    		$url = $this->apiurl.":".$this->apiport."/v1/pxe";
    		return $this->procReturn($this->curlPost($url,$param));
    }   
    
    private function procReturn($obj){
    	if(!$obj["ret"]){
    		logModuleCall('marmoset',"api return",$obj["param"],$obj["code"],'Error:' . curl_error($obj["obj"]) . '" - Code: ' . curl_errno($obj["obj"]));
    		return 0;
		}else{
			logModuleCall('marmoset',"api return",$obj["param"],$obj["code"]."<br>".$obj["ret"]);
			return json_decode($obj["ret"]);
		}
    }

    public function create(){
    		$d = Capsule::table($this->table)->where("serviceid", $this->serviceid)->first();
    		if($d->name){
    			$this->update();
    		}else{
    			$freeip = Capsule::table("mod_ip_manager")->where("serviceid", "0")->first();
    			$this->createVM([
    				"name"=>$this->name, 
    				"user"=>"testuser",
    				"ip_address"=>$freeip->ip,
    				"memory"=>$this->RAM."G",
    				"disk"=>$this->HDD."G"]);
    			$c = $this->setPXE(["ip_adress"=>$freeip->ip, "label"=>"rescue", "password"=>"SeCrEt"]);
    			//logModuleCall('marmoset',"CURL PXE",$this->params,$info,$ret);
	    		$pdo = Capsule::connection()->getPdo();
	    		$pdo->beginTransaction();
	    		try {
	        		// http://docs.whmcs.com/Provisioning_Module_Developer_Docs#Module_Parameters
	        		$statement = $pdo->prepare(
	        		 	'insert into '.$this->table.' (name, ip, serviceid, mac, ipv6, uuid, status) values (:name, :ip, :serviceid, :mac, :ipv6, :uuid, :status)'
	        		);
	        		$statement->execute(
	            		[
	            	    ':name' => 				$this->name,
	            	    ':serviceid' => 		$this->serviceid,
	            	    ':ip' => 				$freeip->ip,
	            	    ':ipv6' => 				$this->ipv6,
	            	    ':mac' => 				$this->mac,
	            	    ':uuid' => 				$this->uuid,
	            	    ':status' => 			$this->status_ok,
	        			]
	        		);
	     			
	        		$pdo->commit();
	        		$this->ip = $freeip->ip;
	    			Capsule::table($this->table_ip_manger)->where("id", $freeip->id)->update(["serviceid" => $this->serviceid]);
	    		}catch (Exception $e) {
	        		$pdo->rollBack();
	        		return $e;
	    		}
    		}
    		return 'success';
    }

    public function update(){
    	Capsule::table($this->table)->where('serviceid', $this->params["serviceid"])->
    		update(['ip' => $this->ip, 'ipv6' => $this->ipv6,'mac' => $this->mac,'uuid' => $this->uuid, ':status' => $this->status_ok]);
    	return 'success';
    }
    
    public function suspend($pdo){
    	Capsule::table($this->table)->where('serviceid', $this->params["serviceid"])->update(['status' => $this->status_suspended]);
    	return 'success';
    }
    	
    public function unsuspend($pdo){
    	Capsule::table($this->table)->where('serviceid', $this->params["serviceid"])->update(['status' => $this->status_ok]);
    	return 'success';
    }
    
    public function terminate($pdo){
    	Capsule::table($this->table)->where('serviceid', $this->params["serviceid"])->delete();
    	Capsule::table($this->table_ip_manger)->where("ip", $this->ip)->update("serviceid", "0");
    	return 'success';
    }
}

