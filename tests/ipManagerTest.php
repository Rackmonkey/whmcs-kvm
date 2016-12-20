<?php

class WHMCSModuleTest extends PHPUnit_Framework_TestCase
{
    protected $moduleName = 'marmoset';

    public function testRequiredConfigOptionsFunctionExists()
    {
        $this->assertTrue(function_exists($this->moduleName . '_ConfigOptions'));
    }

    public function providerIP(){
        return array(
            array('192.168.0.1', true),
            array('88.198.244.252', true),
            array('88.198.244.257', false),
            array('0.198.244.252', false),
        );
    }
    
    public function providerCIDR(){
        return array(
            array('192.168.0.0/24', 256, "255.255.255.0", "192.168.0.1", true),
            array('88.198.244.252/30', 4, "255.255.255.252", "88.198.244.252", true),
            array('88.198.253.0/27', 32, "255.255.255.224", "88.198.253.0", true),
            array('178.63.176.32/27', 32, "255.255.255.224", "178.63.176.32", true),
            array('78.46.169.142/32', 1, "255.255.255.255", "78.46.169.142", true),
        );
    }
    
    /**
     * @dataProvider providerCIDR
     */
    
    public function testCIDR($cidr, $size, $mask, $ip, $return){
    	$this->assertEquals($size, getIpAnzahl(explode("/",$cidr)[1]));
    	$this->assertEquals($return, checkCIDR($cidr));
    	$this->assertEquals($mask, cidr2mask($cidr));
    	$this->assertEquals($cidr, $ip."/".(string)mask2cidr($mask));
    	
    }
    
        /**
     * @dataProvider providerIP
     */
    
    public function testIP($ip, $returnType){
    	
    }
}
