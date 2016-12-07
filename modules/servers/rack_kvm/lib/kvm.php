<?php

namespace rackmonkey;

use Illuminate\Database\Capsule\Manager as Capsule;


class kvm {
    protected $params = array();
    protected $configOptions;
    protected $configOptionCore;
    protected $configOptionHDD;
    protected $configOptionRAM;
    protected $configOptionBackup;
    protected $serviceid;
    protected $serverid;
    protected $pid;
    protected $name; // name der VM
    protected $hostname;


    public function __construct( $params, $debug = false ) {

        if ( $debug === true ) {
            ini_set( 'display_errors', 1 );
            ini_set( 'display_startup_errors', 1 );
            error_reporting( E_ALL );
        }

        if ( is_array( $params ) ) {
            $this->params = $params;
        }

        $this->$configOptions		= $this->getParam( "configoptions" );
        $this->$configOptionCore	= $this->getParam( "configoption1" );
        $this->$configOptionHDD		= $this->getParam( "configoption3" );
        $this->$configOptionRAM		= $this->getParam( "configoption2" );
        $this->$configOptionBackup	= $this->getParam( "configoption4" );

        $this->serviceid = $this->getParam( "serviceid" ); # Unique ID of the product/service in the WHMCS Database
        $this->pid       = $this->getParam( "pid" ); # Product/Service ID
        $this->serverid  = $this->getParam( "serverid" ); 
        $this->hostname  = $this->get_vm_name();

    }

    public function get_vm_name(){
		return substr($this->params["clientsdetails"]["firstname"], 0, 3) + substr($this->params["clientsdetails"]["lastname"], 0, 3) + $this->params["serviceid"];
    }

    public function create($pdo){
    	$statement = $pdo->prepare(
            'insert into rack_kvm (core, name, status, serviceid, serverid, serverhostname, ram, hdd, backup) values (:core, :name, :status, :serviceid, :serverid, :serverhostname, :ram, :hdd, :backup)'
        );
     
        $statement->execute(
            [
                ':name' => 				$this->get_vm_name(),
                ':serviceid' => 		$this->params["serviceid"],
                ':status' => 			'active',
                ':core' => 				$this->configOptionCore,
                ':ram' => 				$this->configOptionRAM,
                ':hdd' => 				$this->configOptionHDD,
                ':backup' => 			$this->configOptionBackup,
                ':serverid' => 			$this->serverid,
                ':serverhostname' => 	$this->hostname,
            ]
        );
     
        $pdo->commit();
    }

    public function update($pdo){

    }
}

