<?php namespace Tinyrocket\Magento\Objects;

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
      * Setter/Getter underscore transformation cache
      *
      * @var array
      */
     protected static $underscoreCache = array();

	/**
     * Object attributes
     *
     * @var array
     */
    protected $data = array();


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
      * If $key is empty will return all the data as an array
      * Otherwise it will return value of the attribute specified by $key
      *
      * If $index is specified it will assume that attribute data is an array
      * and retrieve corresponding member.
      *
      * @param string $key
      * @param string|int $index
      * @param mixed $default
      * @return mixed
      */
     public function getData($key = null, $index = null)
     {
        // Return everything
         if (is_null($key)) {
             return $this->data;
         }
 
         $default = null;
         if (isset($this->data[$key])) {
             if (is_null($index)) {
                 return $this->data[$key];
             }
 
             $value = $this->data[$key];
             if (is_array($value)) {
                 if (isset($value[$index])) {
                     return $value[$index];
                 }
                 return null;
             } elseif (is_string($value)) {
                 $arr = explode("\n", $value);
                 return (isset($arr[$index]) && (!empty($arr[$index]) || strlen($arr[$index]) > 0)) ? $arr[$index] : null;
             } elseif ($value instanceof Varien_Object) {
                 return $value->getData($index);
             }
             return $default;
         }
         return $default;
     }

     public function getId()
     {
        if ( isset($this->data) ) {
            return current($this->data);
        }
     }

     /**
      * Set/Get attribute wrapper
      *
      * @param   string $method
      * @param   array $args
      * @return  mixed
      */
     public function __call($method, $args)
     {
        if (substr($method, 0, 3) == 'get') {
            $key = $this->underscore(substr($method,3));
            $data = $this->getData($key, isset($args[0]) ? $args[0] : null);
            return $data;
         }
         // throw new \Exception("Invalid method ".get_class($this)."::".$method."(".print_r($args,1).")");
     }

     /**
	  * Converts field names for setters and geters
	  *
	  * @param string $name
	  * @return string
	  */
	 protected function underscore($name)
	 {
	     if (isset(self::$underscoreCache[$name])) {
	         return self::$underscoreCache[$name];
	     }
	     $result = strtolower(preg_replace('/(.)([A-Z])/', "$1_$2", $name));
	     self::$underscoreCache[$name] = $result;
	     return $result;
	 }
}
