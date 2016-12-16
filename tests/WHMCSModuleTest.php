<?php
/**
 * WHMCS Sample Provisioning Module Test
 *
 * Sample PHPUnit test that asserts the fundamental requirements of a WHMCS
 * module, ensuring that the required ConfigOptions function is defined, and
 * that all defined functions return the appropriate data type.
 *
 * This is by no means intended to be a complete test, and does not exercise any
 * of the actual functionality of the functions within the module. We strongly
 * recommend you implement further tests as appropriate for your module use
 * case.
 *
 * @copyright Copyright (c) WHMCS Limited 2015
 * @license http://www.whmcs.com/license/ WHMCS Eula
 */
class WHMCSModuleTest extends PHPUnit_Framework_TestCase
{
    /** @var string $moduleName */
    protected $moduleName = 'marmoset';

    /**
     * Asserts the required config options function is defined.
     */
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
            array('192.168.0.1/24', 256, "255.255.255.0", "192.168.0.1", true),
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
