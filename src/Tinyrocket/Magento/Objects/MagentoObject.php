<?php namespace Tinyrocket\Magento\Objects;

use Tinyrocket\Magento\Objects\MagentoObjectException;

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
 * 	@package    MagentoApi_Objects_MagentoObjectCollection
 * 	@author     TinyRocket <michael@tinyrocket.co>
 * 	@copyright  2014 TinyRocket
 *
 */
class MagentoObject {



	/**
     * Object attributes
     *
     * @var array
     */
    protected $data = array();

    /**
     * Construct
     *
     * Set data for the object, if applicable
     *
     * @return void
     */
	public function __construct()
	{
		$args = func_get_args();
        if (empty($args[0])) {
			$args[0] = array();
        }
        $this->data = (array)$args[0];
	} 

     /**
      * Retrieves data from the object
      * 
      * @see Varien_Object
      * @return mixed
      */
     public function getData($key = null)
     {
        // Return everything
        if (is_null($key)) {
            return $this->data;
        }

        // Return key
        if (isset($this->data[$key])) {
            return $this->data[$key];
        };

        throw new MagentoObjectException("Key [$key] not found for this item");
     }

     /**
      * Get Item Id
      *
      * This assumes that the first item in the array is the PK
      *
      * @return int
      */
     public function getId()
     {
        if ( isset($this->data) ) {
            return current($this->data);
        }
     }

     /**
      * Get Functions
      *
      * Generates a list of available functions for a given object
      * based on the keys in it's data collection.
      *
      * @return mixed
      */
     public function getFunctions()
     {
        $functions = array();
        if ( is_array($this->getData()) ) {
            foreach ( $this->getData() as $key => $value ) {
                $functions[] = $this->camelize($key);
            }
            echo '<pre>';
            print_r($functions);
            echo '</pre>';
            return;
        }
     }

     /**
      * Camelize
      *
      * @return string
      */
     public function camelize($key)
     {
        return 'get' . implode('', array_map('ucfirst', array_map('strtolower', explode('_', $key))));
     }

     /**
      * Set/Get attribute wrapper
      *
      * @see Varien_Object
      * @return  mixed
      */
     public function __call($method, $args)
     {
        if (substr($method, 0, 3) == 'get') {
            $key = $this->underscore(substr($method,3));
            $data = $this->getData($key, isset($args[0]) ? $args[0] : null);
            return $data;
         }
     }

     /**
	  * Converts field names for setters and geters
	  *
      * @see Varien_Object
	  * @return string
	  */
	 protected function underscore($name)
	 {
	     return strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
	 }
}
