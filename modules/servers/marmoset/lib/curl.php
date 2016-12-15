<?php

namespace marmoset;

class curl{
    protected $data = array();
    protected $baseUrl;
    protected $curl;
    protected $useragent = "whmcs marmoset";
    

    public function __construct( $baseUrl, $mode, $data = array() ) {
		$this->curl = curl_init();
		if($mode == "post" || $mode == "POST"){
			$this->setOpt(CURLOPT_POST, 1);
		}
		$this->setUseragend($this->useragent);
		$this->setOpt(CURLOPT_RETURNTRANSFER, 1);
		$this->setUrl($baseUrl, $data);
    }
    
    private function buildURL($url, $data = array()){
        return $url . (empty($data) ? '' : '?' . http_build_query($data));
    }
    
    public function setUrl($baseUrl, $data = array()){
    	$this->baseUrl = $baseUrl;
    	$this->setOpt(CURLOPT_URL, buildUrl($baseUrl, $data));	
    }
    
    public function setTimeout($seconds){
        $this->setOpt(CURLOPT_TIMEOUT, $seconds);
    }
    
    public function setOpt($p,$d){
		curl_setopt($this->curl, $p, $d);
    }
	
	public function setURL($url, $data = array()){
        $this->url = $url;
        $this->url = $this->buildURL($url, $data);
        $this->setOpt(CURLOPT_URL, $this->url);
    }
    
    public function setUserAgent($useragend){
    	$this->setOpt(CURLOPT_USERAGENT, $useragent);	
    }
    
    public function setPostData($data){
    	$this->setOpt(CURLOPT_POSTFIELDSR, $data);
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
    	if(!curl_exec($curl)){
    		return 'Error:' . curl_error($curl) . '" - Code: ' . curl_errno($curl);
		}
    }
    
    public function close(){
    	curl_close($curl);
    }
    
} 