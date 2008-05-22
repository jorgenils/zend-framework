<?php

/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Service
 * @subpackage PayPal
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Response.php 126 2007-06-21 20:23:05Z shahar $
 */ 

/**
 * PayPal Name-Value Pairs (NVP) API response object
 * 
 * @category   Zend
 * @package    Zend_Service
 * @subpackage PayPal
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_PayPal_Response
{
	protected $_data = array();
	
	/**
	 * Create a new response object from response string
	 *
	 * @param string $data
	 */
	public function __construct($response)
	{
		$response = (string) $response;
		
		// Parse response
		$data = array();
		parse_str($response, $data);
		
		if (! isset($data['ACK'])) {
			require_once 'Zend/Service/PayPal/Exception.php';
			throw new Zend_PayPal_PayPal_Exception('Provided data does not seem to be a valid PayPal NVP API response');
		}
			
		$this->_data = $data;
	}
	
	/**
	 * Check whether the request was successfull
	 *
	 * @return boolean 
	 */
	public function isSuccess()
	{
		return ($this->_data['ACK'] == 'Success');
	}
	
	/**
	 * Check whether the request failed
	 *
	 * @return boolean
	 */
	public function isFailure()
	{
		return ($this->_data['ACK'] == 'Failure');
	}
	
	/**
	 * Check whether the request produced warnings
	 * 
	 * Note: Successful requests might produce warnings as well
	 *
	 * @return boolean
	 */
	public function hasWarnings()
	{
		return ($this->_data['ACK'] == 'Warning' || 
		        $this->_data['ACK'] == 'SuccessWithWarning');
	}
	
	/**
	 * Get the value of a response parameter
	 *
	 * @param  string $name
	 * @return string|null Value or null if not set
	 */
	public function getValue($name)
	{
		if (isset($this->_data[$name])) {
			return $this->_data[$name];
		} else {
			return null;
		}
	}
	
	/**
	 * Magic wrapper around getValue()
	 *
	 * @param  string $name Key
	 * @return string
	 */
	public function __get($name)
	{
		return $this->getValue(strtoupper($name));
	}
}
