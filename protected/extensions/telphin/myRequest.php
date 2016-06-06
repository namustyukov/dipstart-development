<?php
// Класс для запросов через CURL
class myRequest {

	/**
	 * HTTP methods
	 * @var string 
	 */
	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_DELETE = 'DELETE';
	const METHOD_PUT = 'PUT';
	
	/**
	 * Encoder for the body of the request
	 * @var string
	 */
	const ENCODER_JSON = 'json';
	const ENCODER_DEFAULT = 'dflt';
	
	/**
	 * Array containing the value from the cookie
	 * @var array
	 */
	protected $_cookies = array();
	
	/**
	 * Headers of the request
	 * @var array
	 */
	protected $_headers = array();
	
	/**
	 * The method used to send this request
	 * @var string
	 */
	protected $_method = null;

	/**
	 * Internal array with parameters
	 * @var array
	 */
	protected $_parameters = array();
	
	/**
	 * Body of the request
	 * @var string
	 */
	protected $_body = null;
	
	/**
	 * Files to be uploaded
	 * @var array
	 */
	protected $_files = array();
	
	/**
	 * Fetches the value of a header that was sent in the request
	 * 
	 * @param string $name the name of the header
	 * 
	 * @return mixed the value of the header
	 */
	public function getHeader($name){
		$name = (string)$name;
		if(isset($this->_headers[$name])) {
			return $this->_headers[$name];
		}
		return null;
	}
	
	/**
	 * Fetches all the headers that were sent in the request
	 * 
	 * @return array array containing all the headers from the request
	 */
	public function getHeaders(){
		return $this->_headers;
	}
	
	/**
	 * Fetches the method used when sending the request
	 * @example: GET, POST, etc...
	 * 
	 * @return string the method used by the request 
	 */
	public function getMethod(){
		return $this->_method;
	}
	
	/**
	 * Fetches the parameters of the request
	 * 
	 * @param array $names the names of the parameters to fetch
	 * 
	 * @return array the parameters of the request
	 */
	public function getParameters(array $names = array()){
		
		$params = array();
		if(!empty($names)) {
			foreach($names as $name) {
				if(isset($this->_parameters[$name])) {
					$params[$name] = $this->_parameters[$name];
				}
			}
		} else {
			$params = $this->_parameters;
		}
		return $params;
	}

	/**
	 * Parameters of the request
	 * 
	 * @param array $params array containing the parameters
	 * 
	 * @return boolean true  
	 */
	public function setParameters($params){
		foreach($params as $name => $value) {
			$this->_parameters[$name] = $value;
		}	
		return true;
	}
	
	/**
	 * Sets the parameters that are send in the body of the request
	 * @param array $params parms to be sent in the body of the request
	 */
	public function setBody($body, $encoder = null) {
		
		/* Init encoder */
		/* Encode body */
		/* Save the body internally */
		$bodyString = null;
		if(is_array($body)) {
			switch($encoder) {
				case self::ENCODER_JSON:
					$bodyString = json_encode($body);
					break;
				default:
					foreach($body as $name => $value) {
  						$bodyString .= $name.'='.$value.'&';
  					}
					$bodyString = trim($bodyString, '&');
					break;
			}
		} else {
			$bodyString = (string)$body;
		}
		$this->_body = $bodyString;
	}
	
	/**
	 * Add fields to send in the request
	 * @param array $params fiels to be sent in the body of the request
	 */
	public function setFiles(array $files) {
		$this->_files = $files;
		
	}
	/**
	 * Sets the headers for the request 
	 * 
	 * @param string $name the name of the header
	 * @param mixed $value the value of the header
	 * 
	 * @return boolean TRUE
	 */
	public function setHeaders($headers){
		return $this->_headers = $headers;
	}
	
	/**
	 * Set the method of the request
	 * @param string $method the HTTP method
	 */
	public function setMethod($method){
		$this->_method = (string)$method;
	}
	
  /**
     * Makes the query to be used when requesting 
     * @return string
     */
    protected function _buildQuery(){
    	$parameters = $this->getParameters();
    	
    	$query = null;
		foreach($parameters as $name => $value) {
			$query .= '&'.$name.'='.$value;
		}
		$query = trim($query, '&');
		$query = '?'.$query;
		return $query;
    }
   		
	/**
	 * Redirects to the give url
	 * @param string $url the URL where to redirect
	 */
	public function redirect($url){
		
		$method = $this->getMethod();
		if($method == self::METHOD_POST) {
			/* No way to redirect to the third party, unless we use cURL and echo the output using a redirection status
			 * But is this a good approach? */
			$response = $this->_sendPost($url, $encoder);
			return $response;
		} elseif($method == self::METHOD_GET) {
			$msg = $this->_buildQuery();
			$msg = trim($msg, '?');
			
			$pos = strpos($msg, '?');
			if($pos !== 0 || $pos === false) {
				$url .= '?'.$msg;
			}
			
			header('Location:'.$url);
		}
		return true;
	}	
	
	/**
	 * Send a request to an external URL
	 * 
	 * @return string the response from the server 
	 */
	public function sendRequest($url){
		
		$method = $this->getMethod();
		switch($method) {
			case self::METHOD_GET:
				$ret = $this->_sendGet($url);
				break;
			case self::METHOD_POST:
				$ret = $this->_sendPost($url);
				break;
			case self::METHOD_PUT:
				$ret = $this->_sendPut($url);
				break;
			case self::METHOD_DELETE:
				$ret = $this->_sendDelete($url);
				break;
			default:
				$ret = null;
				break;
		}
		return $ret;
	}
	/**
	 * Sends a POST request
	 * @param string $url the URL where to send the request
	 * @param string $encoder the encoder for the body if the request
	 * @seeTS\Request\aRequest::_sendPost()
	 */
	protected function _sendPost($url, $encoder = null){
		
		$result = $this->_runPostRequest($url);
		return $result;
	}
	
	/**
	 * Sends a GET request
	 * @param string $url the URL where to send the request
	 * @seeTS\Request\aRequest::_sendPost()
	 */
	protected function _sendGet($url){
		
		$result = $this->_runGetRequest($url);
		return $result;
	}
	
	
	/**
	 * Sends a GET request
	 * @param string $url the URL where to send the request
	 * @seeTS\Request\aRequest::_sendPost()
	 */
	protected function _sendPut($url){
		
		$result = $this->_runPutRequest($url);
		return $result;
	}
	
	
	/**
	 * Sends a GET request
	 * @param string $url the URL where to send the request
	 * @seeTS\Request\aRequest::_sendPost()
	 */
	protected function _sendDelete($url){
		
		$result = $this->_runDeleteRequest($url);
		return $result;
	}	
	
    /**
     * Initialize the cURL tool 
     * @param string $url the URL where to send the request
     * @return 
     */
    private function _initRequest($url){
    	
    	$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		
	
		
		
		/* We must send as string when using the application content type */
		$postData = $this->_body;
		
		$files = $this->_files;
		if(!empty($files)) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
			$files['request'] = $postData;
			curl_setopt($ch, CURLOPT_POSTFIELDS, $files);
		} else{
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
		}
		
		$headers = $this->getHeaders();

		$_headers = array();
		foreach($headers as $name => $value) {
			$_headers[] = $name.':'.$value;
		}

		curl_setopt($ch, CURLOPT_HTTPHEADER, $_headers);
		return $ch;
    }
    
     /**
     * Run a POST request using cURL
     * @param string $url the URL where to send the request
     * @returnTS\Request\Response 
     */
    private function _runGetRequest($url) {
    	
    	$url .= $this->_buildQuery();
    	
    	$ch = $this->_initRequest($url);
    	
    	curl_setopt($ch, CURLOPT_HTTPGET, true);
    	$response = $this->_getResponse($ch);
    	return $response;
    }
    
    /**
     * Run a POST request using cURL
     * @param string $encoder the encoder for the body if the request 
     * @returnTS\Request\Response 
     */
  	private function _runPostRequest($url, $encoder = null) {
  		
  		$url .= $this->_buildQuery();
  		
		$ch = $this->_initRequest($url, self::METHOD_POST);
		if(empty($this->_files)) {
			curl_setopt($ch, CURLOPT_POST, true);
		}
		$response = $this->_getResponse($ch);
    	return $response;	
    }
    
    
   /**
     * Run a POST request using cURL
     * @param string $encoder the encoder for the body if the request 
     * @returnTS\Request\Response 
     */
  	private function _runPutRequest($url, $encoder = null) {
  		
  		$url .= $this->_buildQuery();
		$ch = $this->_initRequest($url);
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		$response = $this->_getResponse($ch);
    	return $response;	
    }
    
    
   /**
     * Run a POST request using cURL
     * @param string $encoder the encoder for the body if the request 
     * @returnTS\Request\Response 
     */
  	private function _runDeleteRequest($url, $encoder = null) {
  		
  		$url .= $this->_buildQuery();
		$ch = $this->_initRequest($url);
		
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
		curl_setopt($ch, CURLINFO_HEADER_OUT, true);
		$response = $this->_getResponse($ch);
    	return $response;	
    }
    
    /**
     * Fetches a response instance
     * @param string $ch cURL resource
     * @returnTS\Request\Response
     * @throws Exception
     */
	private function _getResponse($ch) {
		$respData = curl_exec($ch);
	    $respHeaderSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	    $respStatus = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
	    $errorNum = curl_errno($ch);
	    $error = curl_error($ch);
	    curl_close($ch);
	    if ($errorNum != CURLE_OK) {
	    	throw new \Exception('HTTP Error: (' . $respStatus . ') ' . $error);
	    }
	   
	    // Parse out the raw response into usable bits
	    $rawHeaders = substr($respData, 0, $respHeaderSize);
	    $responseBody = substr($respData, $respHeaderSize);
	    $responseHeaderLines = explode("\r\n", $rawHeaders);
	    $responseHeaders = array();
	    $header = null;
	    foreach ($responseHeaderLines as $headerLine) {
	    	if ($headerLine && strpos($headerLine, ':') !== false) {
	        	list($header, $value) = explode(': ', $headerLine, 2);
	        	if (isset($responseHeaders[$header])) {
	          		$responseHeaders[$header] .= "\n" . $value;
	        	} else {
	          		$responseHeaders[$header] = $value;
	        	}
	      	}
	    }
		$response = new myResponse($respStatus, $responseHeaders, $responseBody);
		return $response;
	}
}