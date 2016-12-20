<?php

namespace Curl;

class Curl{
    protected $data = array();
    protected $baseUrl;
    protected $curl;
    protected $useragent = "whmcs marmoset";
    

    public function __construct( $baseUrl, $mode, $data = array() ) {
		$this->curl = curl_init();
		if($mode == "post" || $mode == "POST"){
			$this->setOpt(CURLOPT_POST, 1);
		}
		$this->setUseragent($this->useragent);
		$this->setOpt(CURLOPT_RETURNTRANSFER, 1);
		$this->setUrl($baseUrl, $data);
    }
    
    private function buildUrl($url, $data = array()){
        return $url . (empty($data) ? '' : '?' . http_build_query($data));
    }
    
    public function setUrl($baseUrl, $data = array()){
    	$this->baseUrl = $baseUrl;
    	$this->setOpt(CURLOPT_URL, $this->buildUrl($baseUrl, $data));	
    }
    
    public function setTimeout($seconds){
        $this->setOpt(CURLOPT_TIMEOUT, $seconds);
    }
    
    public function setOpt($p,$d){
		curl_setopt($this->curl, $p, $d);
    }
    
    public function setUserAgent($useragend){
    	$this->setOpt(CURLOPT_USERAGENT, $useragent);	
    }
    
    public function setPostData($data){
    	$this->setOpt(CURLOPT_POSTFIELDSR, $data);
    }
    
    public function getInfo(){
    	return curl_getinfo($this->curl);
    }
    
    public function setBasicAuthentication($username, $password = ''){
        $this->setOpt( CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
    }
    
    public function setDigestAuthentication($username, $password = ''){
        $this->setOpt(CURLOPT_HTTPAUTH, CURLAUTH_DIGEST);
        $this->setOpt(CURLOPT_USERPWD, $username . ':' . $password);
    }
    
    public function exec(){
    	// false - if there is an error executing the request
		// true - if the request executed without error and CURLOPT_RETURNTRANSFER is set to false
		// The result - if the request executed without error and CURLOPT_RETURNTRANSFER is set to true
		return curl_exec($this->curl);
    }
    
    public function close(){
    	curl_close($this->curl);
    }
    
} 