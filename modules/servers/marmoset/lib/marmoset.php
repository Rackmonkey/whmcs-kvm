<?php

namespace Marmoset;

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
    protected $loginname;
    protected $loginpw;
    protected $apiurl;


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

    public function create(){
    		$d = Capsule::table($this->table)->where("serviceid", $this->serviceid)->first();
    		if($d->name){
    			$this->update();
    		}else{
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
	            	    ':ip' => 				$this->ip,
	            	    ':ipv6' => 				$this->ipv6,
	            	    ':mac' => 				$this->mac,
	            	    ':uuid' => 				$this->uuid,
	            	    ':status' => 			$this->status_ok,
	        			]
	        		);
	     			
	        		$pdo->commit();
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
    	return 'success';
    }
}

