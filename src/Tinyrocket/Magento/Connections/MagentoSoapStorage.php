<?php namespace Tinyrocket\Magento\Connections;

use Tinyrocket\Magento\Connections\ConnectionNotProvidedException;
use Tinyrocket\Magento\Connections\InvalidConnectionException;
use Illuminate\Support\Facades\Cache;

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
 * 	@package    MagentoApi_Connections_MagentoSoapStorage
 * 	@author     TinyRocket <michael@tinyrocket.co>
 * 	@copyright  2014 TinyRocket
 *
 */

class MagentoSoapStorage {

	/**
	 *	@var services
	 */
	protected $services;

	/**
	 *	@var primary
	 */
	protected $primary;

	/**
	 * Create an instance of available services and primary connection
	 *
	 * @return void
	 */
	public function __construct()
	{
		$this->primary = \Cache::has('magento_soap_primary') ? \Cache::get('magento_soap_primary') : 'default';
		$this->services = \Cache::has('magento_soap_services') ? \Cache::get('magento_soap_services') : array();
	}

	/**
	 *	Register Connection
	 *
	 *	@return Tinyrocket\Magento\Connections\MagentoSoapClient
	 */
	public function add($connection)
	{
		if ( !array_key_exists(key($connection), $this->services) ) {
			$this->services[key($connection)] = $connection[key($connection)];
			\Cache::forever('magento_soap_services', $connection);
		}
		return $connection;
	}

	/**
	 *	Remove Connection
	 *
	 *	@return Tinyrocket\Magento\Connections\MagentoSoapClient
	 */
	public function remove($connection)
	{
		if ( array_key_exists(key($connection), $this->services) ) {
			unset($this->services[key($connection)]);
			\Cache::forever('magento_soap_services', $this->services);
		}
		return $connection;
	}

	/**
	 *	Serve available connections
	 *
	 *	@return array
	 */
	public function services()
	{
		return $this->services;
	}

	/**
	 *	Serve primary connection
	 *
	 *	@return array
	 */
	public function primary($name = null)
	{
		if ( !is_null($name) ) {
			\Cache::forever('magento_soap_primary', $name);	
			$this->primary = \Cache::get('magento_soap_primary');	
		}
		return $this->primary;
	}
}