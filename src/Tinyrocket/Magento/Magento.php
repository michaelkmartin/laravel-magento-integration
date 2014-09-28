<?php namespace Tinyrocket\Magento;

use Tinyrocket\Magento\Connections\InvalidConnectionException;
use Tinyrocket\Magento\Connections\MagentoSoapStorage;
use Tinyrocket\Magento\Connections\MagentoSoapClient;
use Tinyrocket\Magento\Connections\MagentoSoapClientException;
use Tinyrocket\Magento\Objects\MagentoObjectCollection;
use Tinyrocket\Magento\Objects\MagentoObject;

use Illuminate\Config\Repository;


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
 * 	@package    MagentoApi_Magento
 * 	@author     TinyRocket <michael@tinyrocket.co>
 * 	@copyright  2014 TinyRocket
 *
 */

class Magento {

	/**
	 *	@var connections
	 */
	private $connections;

	/**
	 *	@var client
	 */
	protected $client;

	/**
	 *	@var forgets
	 */
	protected $forgets;

	/**
	 *	Construct Magento Instance
	 *
	 *	@return void
	 */
	public function __construct(Repository $config)
	{
		$this->connections = $config->get('magento::connections');
		if ( is_array($this->connections) ) {
			$this->batchRegister($this->connections);
		}
	}

	/**
	 *	__call Magic Method
	 *
	 *	Allows for SOAP v2 request to be ran directly against
	 *	the class rather than using the 'call' method
	 *
	 *	@return void
	 */
	public function __call($method, $args)
	{
		if ( in_array($method, get_class_methods($this)) ) {
			call_user_func($method, $args);
		} else {
			$clientExecutable = $this->call();
			return call_user_func_array(array($clientExecutable, $method), $args);
		}
	}

	/**
	 *	Call SOAP V2 Method
	 */
	public function call($connection = null)
	{
		if ( !is_array($connection) or is_null($connection) ) {
			$connection = !is_null($connection) ? $this->getConnection($connection) : $this->getPrimaryConnection();			
		}
		return new MagentoSoapClient($connection);
	}

	/**
	 *	Get Soap V1 Method
	 */
	public function get($method, $params = array(), $connection = null)
	{
		$connection = !is_null($connection) ? $this->getConnection($connection) : $this->getPrimaryConnection();
		$soap = new MagentoSoapClient($connection);
		if ( (isset($connection[key($connection)]['version']) && (strtolower($connection[key($connection)]['version']) == 'v1') ) ) {
			return $soap->call($method, $params);
		} else {
			return $soap->__call($method, $params);
		}
	}

	/**
	 *	Use SOAP Method
	 */
	public function connection($connection = null)
	{
		if ( !is_null($connection) ) {
			return new MagentoSoapClient($this->getConnection($connection));
		}
		throw new MagentoSoapClientException("This [connection] paramenter cannot be left blank");
	}

	/**
	 *	Create Connection
	 *
	 *	Allows a user to create a connection on the fly
	 *
	 *	@return array
	 */
	public function createConnection($name, $url, $user, $key, $version = null)
	{
		if ( !in_array($name, $this->getConnections()) ) {
			return array($name => array(
				'site_url'	=>	$url,
				'user'		=>	$user,
				'key'		=>	$key,
				'version'	=>	$version,
			));
		}
		throw new MagentoSoapClientException("Connection [$name] already exists. Please choose a different identifier");
	}

	/**
	 *	Create And Register Connection
	 *
	 *	Allows a user to create and register a connection on the fly
	 *
	 *	@return array
	 */
	public function createAndRegisterConnection($name, $url, $user, $key, $version = null)
	{
		return $this->register($this->createConnection($name, $url, $user, $key, $version), false);
	}

	/**
	 *	Create a temporary connection
	 *
	 *	Allows a user to create a temporary connection on the fly
	 *
	 *	@return array
	 */
	public function createAndForgetConnection($name, $url, $user, $key, $version = null)
	{
		if ( $this->forgets[] = $name ) {
			return $this->register($this->createConnection($name, $url, $user, $key, $version), false);
		}
	}

	/**
	 *	Get Connections
	 *
	 *	@return array
	 */
	public function getConnections()
	{
		return $this->connections;
	}

	/**
     *	Get Services
     */
	public function getAvailableConnections()
	{
		return \MagentoSoapStorage::services();
	}

	/**
	 *	Get Primary Connection
	 *
	 *	@return Tinyrocket\Magento\Connections\MagentoSoapClient
	 */
	public function getPrimaryConnection()
	{
		return $this->getConnection(\MagentoSoapStorage::primary());
	}

	/**
	 *	Test SOAP Connection
	 *
	 *	Returns either a boolean reponse or response headers, depending
	 *	on whether $showHeaders is set to true.
	 *
	 *	@return mixed
	 */
	public function testConnection($connection = null, $showHeaders = false)
	{
		if ( !is_null($connection) ) {
			$connection = is_array($connection) ? $connection : $this->getConnection($connection, false);
			return \MagentoSoapClient::testConnection($connection, $showHeaders);
		}
		throw new InvalidConnectionException("No connection provided to test. Please provide a connection object or identifier");
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
	public function getMagentoVersion($connection = null)
	{
		$connection = is_array($connection) ? $connection : $this->getConnection($connection);
		$temporaryClient = new MagentoSoapClient($connection);
		return $temporaryClient->getMagentoVersion();
	}

	/**
	 *	Get Primary Connection
	 *
	 *	@return Tinyrocket\Magento\Connections\MagentoSoapClient
	 */
	public function setPrimaryConnection($name)
	{
		return $this->getConnection(\MagentoSoapStorage::primary($name));
	}

	/**
	 *	Get Connection
	 *
	 *	@return connection array
	 */
	public function getConnection($identifier, $useDefault = true)
	{
		if ( is_array($identifier) ) {
			$identifier = key($identifier);
		}

		// Fallback if applicable
		if ( $useDefault ) {
			$identifier = array_key_exists($identifier, $this->connections) ? $identifier : 'default';
		}

		// Return Connection
		if ( isset($this->connections[$identifier]) ) {
			return array($identifier => $this->connections[$identifier]);
		}
		throw new InvalidConnectionException("Connection [$identifier] not found. No default configuration found.");
	}

	/**
	 *	Register Connection
	 *
	 *	@return Tinyrocket\Magento\Connections\MagentoSoapClient
	 */
	public function register($connection, $return = true, $forget = false)
	{
		try {
			$connection = is_array($connection) ? $connection : $this->getConnection($connection);
			\MagentoSoapStorage::add($connection);

			if ( true === $forget ) {
				$this->forgets[] = key($connection);
			}

			return $return ? $this->call($connection) : null;
		} catch (Exception $e) {
			throw new MagentoSoapClientException($e->getMessage());
		}
	}

	/**
	 *	Register Connection 
	 *
	 *	@return Tinyrocket\Magento\Connections\MagentoSoapClient
	 */
	public function batchRegister($connections)
	{
		try {
			foreach ( $connections as $connection => $data ) {
				\MagentoSoapStorage::add(array($connection => $data));
			}
		} catch (Exception $e) {
			throw new MagentoSoapClientException($e->getMessage());
		}
	}

	/**
	 *	Get Functions
	 *
	 *	Extension of the __getFunctions method core to SoapClient
	 *	
	 *	@return array
	 */
	public function getFunctions($connection = null)
	{
		return $this->call()->getFunctions();
	}

	/**
	 *	Unregister Connection
	 *
	 *	@return void
	 */
	public function unregister($connection)
	{
		$connection = is_array($connection) ? $connection : $this->getConnection($connection);
		\MagentoSoapStorage::remove($this->getConnection($connection));
	}


	/**
	 *	Has Default Connection
	 *
	 *	@return bool
	 */
	public function hasDefaultConnection()
	{
		return array_key_exists('default', $this->getConnections());
	}

	/**
	 *	Deconstructor
	 *
	 *	@return void
	 */
	public function __destruct() {
  		if ( !is_null($this->forgets) && is_array($this->forgets) ) {
  			foreach ( $this->forgets as $removeKey ) {
  				$this->unregister($removeKey);
  			}

  			$this->forgets = null;
  		}
  	}
}