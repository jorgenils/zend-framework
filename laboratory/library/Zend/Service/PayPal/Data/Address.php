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
 * @version    $Id: Address.php 126 2007-06-21 20:23:05Z shahar $
 */ 

/**
 * PayPal container class for address information
 * 
 * @category   Zend
 * @package    Zend_Service
 * @subpackage PayPal
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_PayPal_Data_Address
{
	/**
	 * Street
	 *
	 * @var string
	 */
	protected $street;
	
	/**
	 * City
	 *
	 * @var string
	 */
	protected $city;

	/**
	 * State (2 character code)
	 *
	 * @var string
	 */
	protected $state = 'ZZ';

	/**
	 * Country (2 character code)
	 *
	 * @var string
	 */
	protected $countryCode;

	/**
	 * Zip code
	 *
	 * @var integer
	 */
	protected $zip;

	/**
	 * Create a new Address object
	 * 
	 * @todo  Validation
	 *
	 * @param string  $street
	 * @param string  $city
	 * @param string  $countryCode
	 * @param string  $state
	 * @param integer $zip
	 */
	public function __construct($street, $city, $countryCode, $state = null, $zip = null)
	{
		$this->street      = $street;
		$this->city        = $city;
		$this->countryCode = $countryCode;
		
		if ($state) $this->state = $state;
		if ($zip)   $this->zip   = $zip;
	}

	/**
	 * Get the value of street
	 *
	 * @return string
	 */
	public function getStreet()
	{
		return $this->street;
	}
	
	/**
	 * Get the value of city
	 * 
	 * @return string
	 */
	public function getCity()
	{
		return $this->city;
	}

	/**
	 * Get the value of state
	 * 
	 * @return string
	 */
	public function getState()
	{
		return $this->state;
	}

	/**
	 * Get the value of countryCode
	 * 
	 * @return string
	 */
	public function getCountryCode()
	{
		return $this->countryCode;
	}

	/**
	 * Get the value of zip
	 * 
	 * @return integer
	 */
	public function getZip()
	{
		return $this->zip;
	}
}
