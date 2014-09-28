<?php namespace Tinyrocket\Magento\Connections;

use Tinyrocket\Magento\Connections\ConnectionNotProvidedException;
use Tinyrocket\Magento\Connections\InvalidConnectionException;
use Tinyrocket\Magento\Connections\MagentoSoapClientException;
use Tinyrocket\Magento\Connections\MagentoSoapConfigurationException;
use Tinyrocket\Magento\Objects\MagentoObjectCollection;
use Tinyrocket\Magento\Objects\MagentoObject;

/**
 * 	Magento API | Connection Exceptions
 *
 *	The MIT License (MIT)
 *	
 *	Copyright (c) 2014 TinyRocket
 *	
 *	Permission is hereby granted, free of charge, to any person obtaining a copy
 *	of this software and associated documentation files (the "Software"), to deal
 *	in the Software without restriction, including without limitation the rights
 *	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *	copies of the Software, and to permit persons to whom the Software is
 *	furnished to do so, subject to the following conditions:
 *	
 *	The above copyright notice and this permission notice shall be included in
 *	all copies or substantial portions of the Software.
 *	
 *	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *	THE SOFTWARE.
 *
 * 	@category   MagentoApi
 * 	@package    MagentoApi_Connections_MagentoSoapClient
 * 	@author     TinyRocket <michael@tinyrocket.co>
 * 	@copyright  2014 TinyRocket
 *
 */
class MagentoSoapClient extends \SoapClient {

	/**
	 *	@var wsdl
	 */
	protected $wsdl;

		/**
	 *	@var connection
	 */
	protected $connection;

	/**
	 *	@var client
	 */
	protected $session;

	/**
	 *	@var results
	 */
	protected $results;

	/**
	 *	@var debugTrace
	 */
	protected $debugTrace;

	/**
	 *	Constructor
	 *
	 *	@return void
	 */
	public function __construct($connection = null, $options = array('trace' => 1))
	{
		if ( !is_null($connection) ) {
			try {
				$this->connection = $connection[key($connection)];
				$this->wsdl = $this->getConstructedUrl();

				parent::__construct($this->wsdl, $options);

				$this->session = $this->login($this->connection['user'], $this->connection['key']);			
			} catch (Exception $e) {
				throw new MagentoSoapClientException($e->getMessage());
			}
			
		}

	}

	/**
	 *	Capture and format SOAP calls for both
	 *	v1 and v2 of the Magento api. 
	 */
	 public function __call($method, $args) 
	 {
	 	if ( is_array($args) && count($args) == 1 ) {
	 		$args = current($args);
	 	}

	 	try {
	 		if ( $method == 'login' ) {
			    $this->results = $this->__soapCall($method, $args);
			} elseif ($method == 'call') {
				$this->results = $this->__soapCall($method, $args);
			} else {
				$this->results = $this->__soapCall($method, array($this->session, $args));
			}

			return $this->getResultsCollections();
		} catch ( \Exception $e) {
	 		throw new MagentoSoapClientException($e->getMessage());
	 	}
	 }

	 public function call($method, $params)
	 {
	 	try {
	 		$data = array_merge(array($this->session, $method), array($params));
	 		return parent::call($data);
	 	} catch (Exception $e) {
	 		throw new MagentoSoapClientException($e->getMessage());
	 	}
	 }

	 /**
	  *	Get Results Collection
	  *
	  * Determines whether to return a formatted collection for
	  * array returns, a single object for single array returns
	  * or simply the response, for single var/int returns
	  *
	  *	@return mixed
	  */
	 public function getResultsCollections()
	 {
	 	if ( is_array($this->results) && $this->isMulti() ) {

	 		return new MagentoObjectCollection($this->results);

	 	} elseif( is_array($this->results) && !$this->isMulti() ) {

	 		return new MagentoObject($this->results);

	 	} elseif( is_object($this->results) ) {

	 		return new MagentoObject($this->results);

	 	} else {

	 		return $this->results;
	 	}
	 }

	/**
	 *	Get Functions
	 *
	 *	Extension of the __getFunctions method core to SoapClient
	 *	
	 *	@return array
	 */
	public function getFunctions()
	{
		return $this->__getFunctions();
	}

	/**
	 *	Get Last Response
	 *
	 *	Extension of the __getLastResponse method core to SoapClient
	 *	
	 *	@return array
	 */
	public function getLastResponse()
	{
		return $this->__getLastResponse();
	}

	/**
	 *	Get Soap Version
	 *
	 *	Returns the selected version of the SOAP API	
	 *
	 *	@return string
	 */
	public function getSoapVersion()
	{
			
		$version = isset($this->connection['version']) ? $this->connection['version'] : '';

		if ( isset($this->connection['version']) && in_array(strtolower($this->connection['version']), $this->getAvailableVersions()) ) {

			// Return the requested version
			return strtolower($this->connection['version']);

		} elseif ( !isset($this->connection['version']) || !strlen($this->connection['version']) ) {

			// Fallback to v2 if no version is supplied
			return end($this->getAvailableVersions());
		}
		throw new MagentoSoapConfigurationException("The supplied version [$version] is invalid. Please check your configuration.");
	}

	/**
	 *	Get Applicable Soap Version
	 *
	 *	Returns an array of possible API versoins
	 *
	 *	@return string
	 *	@todo add support for XML responses
	 */
	public function getAvailableVersions()
	{
		return array('v1', 'v2');
	}

	/**
	 *	Is Multi
	 *
	 *	Check whether the returned result is a multi-level array
	 *
	 *	@return bool
	 */
	protected function isMulti()
	{
    	return isset($this->results[0]);
	}

	/**
	 *	Test SOAP Connection
	 *
	 *	Returns either a boolean reponse or response headers, depending
	 *	on whether $showHeaders is set to true.
	 *
	 *	@return mixed
	 */
	public function testConnection($connection, $showHeaders)
	{
		$testUrl = $this->getConstructedUrl($connection);
		try {
			file_get_contents($testUrl);
			if ( $showHeaders === true ) {
				return $http_response_header;
			}
			$responseCode = array_values($http_response_header)[0];
			return strpos($responseCode, '200 OK') === false ? false : true;

		// No Response returned
		} catch ( \Exception $e ) {
			if ( $showHeaders === true ) {
				return "No response headers. Test failed.";
			}

			return false;
		}
	}

	/**
	 *	Get Magento Version
	 *
	 *	Returns the Magento build version for either the default
	 *	connection or the connection passed through to the function
	 *
	 *	@return string
	 *	@example 
	 */
	public function getMagentoVersion()
	{
		$version = $this->getSoapVersion();
		switch ($version) {
			case 'v1':
				$response = $this->call('core_magento.info', array());
				break;
			
			case 'v2':
				$response = $this->__call('magentoInfo', array());
				break;
		}

		if ( isset($response) ) {
			return sprintf('%s %s', $response->getMagentoEdition(), $response->getMagentoVersion());
		}
	}

	/**
	 *	Construct URL
	 *
	 *	Used to created either a Soap V1 or V2 URL for the WSDL
	 *	based on the configuration for the primary connection.
	 *
	 *	@return string
	 *	@todo modify to work with rewrites
	 */
	private function getConstructedUrl($connection = null)
	{
		$url = is_null($connection) ? rtrim($this->connection['site_url'], '/') : rtrim($connection[key($connection)]['site_url'], '/');
		$version = is_null($connection) ? strtolower($this->connection['version']) : strtolower($connection[key($connection)]['version']);

		switch ($version) {
			case 'v1':
				return sprintf("%s/api/soap/?wsdl", $url);
				break;

			case 'v2':
				return sprintf("%s/api/v2_soap?wsdl", $url);
				break;
			
			default:
				return sprintf("%s/api/v2_soap?wsdl", $url);
				break;
		}
	}

	public function __destruct()
	{

	}
}