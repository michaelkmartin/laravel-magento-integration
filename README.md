Magento SOAP Integration for Laravel
===========================

A simple and somewhat intuitive package for managing and interacting with the Magento SOAP Api. Compatible with Laravel 4 and Magneto SOAP v1 & v2.

[![Build Status](https://travis-ci.org/TinyRocket/laravel-magento-integration.svg?branch=master)](https://travis-ci.org/TinyRocket/laravel-magento-integration)

### Installation

To install via composer, add the following to your requirements

    "require": {
		...
		"tinyrocket/magento": "1.0.*"
		...
	},
**Note:** You may need to change your **minimum-stability** to **dev**

### Configuration

Add the following to your Laravel Application configuration (app/config/app.php)

To your **providers** array


    'providers' => array(
        ...
		'Tinyrocket\Magento\MagentoServiceProvider',
		...
	),
	
and to your **aliases** array


	'aliases' => array(
	    ...
		'Magento' => 'Tinyrocket\Magento\Facades\Magento',
		...
	),
	
Publish the the package configuration file by running the following in CLI

    php artisan config:publish tinyrocket/magento
    
#### Setting up the SOAP connections

The quickest way to get started with Magento integration is to add your connection(s) to the newly published configuration file. The file can be found in

    app/config/packages/tinyrocket/magento/config.php
    
There is no limit to the amount of connections that you can save, but you should set a default configuration to handle fallbacks. Inside of the configuration file, set the following with your Magento SOAP information.

**Note:**  Use your store's base URL, not the URL for the SOAP endpoint. (e.g. http://magentostore.com rather than http://magentostore.com/api/soap?wsdl)

    'connections' => [
        ...
		'default'	=>	[
			'site_url'	=>	'http://magentohost',
			'user'		=>	'',
			'key'		=>	'',
			'version'   =>  'v2'
		],
		...
	]
	
The first parameter defines the name of the connection and should be unique. The API version is optional and will default to v2 for all connections, unless set otherwise.

##Usage

An exhaustive list of possible methods is available on the [Magento Website](http://www.magentocommerce.com/api/soap/)

There are two basic methods used to interact with the SOAP Api

For **SOAP v2**

    Magento::call()->someSoapRequest($requestParams)
    
    // Example
    Magento::call()->customerCustomerList()
    
For **SOAP v1**
    
    Magento::get('some.method', array($requestParams))
    
    //Example
    Magento::get('customer.list')
    
Alternatively, you can call methods directly for **SOAP v2** requests

    Magento::someSoapRequest($requestParams)
    
    // Example
     $customerParams = array(
		'email' 		=> 'customer@example.org', 
		'firstname' 	=> 'Dough', 
		'lastname' 		=> 'Deeks', 
		'password' 		=> 'password', 
		'website_id' 	=> 1, 
		'store_id' 		=> 1, 
		'group_id' 		=> 1
	);
	$customerId = Magento::customerCustomerCreate($customerParams)

	
###Working with the results

To make working with the SOAP API slightly more intuitive, some request results are returned as data objects and collections, inspired by **Varien_Object** and **Varien_Object_Collection** classes in Magento. These classes allow for the calling of information with some basic methods.

####Objects Collections
For SOAP responses that return a group of items, results are returned as object collections containing individual objects. These collections have four basic methods.

**getCollection()** - Returns all items as a group of objects

**getCount()** - Returns number of items in the collection

**getFirstItem()** - Returns the first response item

**getLastItem()** - Returns the last response item

    foreach ( Magento::salesOrderList()->getCollection() as $order ) {
        // Do stuff
    }
---
####Objects
For SOAP responses that return a single array item, or when iterating through a response collection, the MagnetoObject is used. This object comes with a couple methods that should be familiar to a Magento developer

**getData(optional $key)** - Either returns all of an objects values or a single value that matches the provided key.

**getId()** - Returns the primary key of a given response object

Like a **Varien_Object** you can also use a magic getter to pull information from an object. For example, you can use the following two methods to return the incrementId of an order object

    foreach ( Magento::salesOrderList()->getCollection() as $order ) {
        
        // with data
        echo $order->getData('increment_id')
        
        // with magic getter
        echo $order->getIncrementId()
    }

---    
####Single Results
For SOAP responses that return a single value or boolean, results are returned as a string/integer/boolean

    // will return an integer
    echo Magento::customerCustomerCreate($customerParams)
    
###Working multiple connections
You have the option to use connections to multiple Magento websites. This can be done by either adding a secondary connection to the package configuration or by creating a connection on the fly. Connections are stored, so once registered, you can continue to use that connection by referencing it's unique identifier or the newly created connection object.

To create a connection on the fly

    $connection = Magento::createConnection($name, $url, $user, $key, $version)
    
To use a stored connection

    // SOAP v2
    $orders = Magento::call($connection)->salesOrderList();
    $orders = Magento::call('unique_identifier')->salesOrderList();
    
    // SOAP v1
    $customers = Magento::get('customers.list', null, $connection)
    $customers = Magento::get('customers.list', null, 'unique_identifier')

To register a connection programmatically

    Magento::createAndRegisterConnection($name, $url, $user, $key, $version)
    
To see a list of available connections
    
    print_r( Magento::getAvailableConnections() )
    
**Setting a primary connection**
Inherently, unless explicitly passed to a SOAP call, the default connection found in the configuration file is used when making all calls. To change which connection is used by default, you can use

    Magento::setPrimaryConnection('unique_identifier')
    
    // Then use for subsequent calls
    Magento::salesOrderList()
    
To see the currently primary connection
    
    echo Magento::getPrimaryConnection()
    
To remove a connection from memory
    
    Magento::unregister('unique_identifier')
    
To create a temporary connection

    $connection = Magento::createAndForgetConnection($name, $url, $user, $key, $version)
    
    // Then reference it in the call
    $orders = Magento::call($connection)->salesOrderList()
    
###Helpers

**Getting Magic Getters for an object/collection**
Though used just to return data, you can use the following to get a list of available functions for a given object or collection
    
    // For a collection
    echo Magento::call()->customerCustomerList()->getFunctions();
    
    // For an object
    $customer->getFunctions();

This will return something like

    Array
    (
        [0] => getCustomerId
        [1] => getCreatedAt
        [2] => getUpdatedAt
        [3] => getStoreId
        [4] => getWebsiteId
        [5] => getCreatedIn
        [6] => getEmail
        [7] => getFirstname
        [8] => getLastname
        [9] => getGroupId
        [10] => getDob
        [11] => getPasswordHash
    )

**Test your SOAP connection**
Returns a boolean by default, but can return headers by flagging the second parameter as true.

    var_dump( Magento::testConnection('unique_identifier', $returnHeaders = false) )
    
**Get Magento Version**
Returns the build version of either the default connection or the one passed
    
    // Example return Community 1.9.0.0
    var_dump( Magento::getMagentoVersion(optional $connection) )
    
###XML Support
Currently, there is no support of XML request and responses. But, it's planned for future releases.

###WS-I Compliance Mode
Currently, there is no way enforce WS-I Compliance Mode.
    