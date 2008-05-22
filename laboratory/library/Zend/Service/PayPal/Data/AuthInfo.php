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
 * @version    $Id: AuthInfo.php 126 2007-06-21 20:23:05Z shahar $
 */ 

/**
 * PayPal container class for PayPal account authentication information
 * 
 * @todo    Add support for API certificate authentication
 * 
 * @category   Zend
 * @package    Zend_Service
 * @subpackage PayPal
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_PayPal_Data_AuthInfo
{
	/**
	 * PayPal API user name
	 *
	 * @var string
	 */
	protected $api_user = null;
	
	/**
	 * PayPal API password
	 *
	 * @var string
	 */
	protected $api_pass = null;
	
	/**
	 * PayPal API 128 bit signature
	 *
	 * @var string
	 */
	protected $signature = null;
	
	/**
	 * Create a new PayPal API authentication object
	 *
	 * @param string $user API User name
	 * @param string $pass API Password
	 * @param string $sig  API 128 bit Signature string
	 */
	public function __construct($user, $pass, $sig)
	{
		if (! $user) {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception('User must be set!');
		}
			
		if (! $pass) {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception('Password must be set!');
		}
			
		if (! ($sig && strlen($sig) == 56)) {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception('Signature must be a 128 bit PayPal API account signature');
		}
			
		$this->api_user = $user;
		$this->api_pass = $pass;
		$this->signature = $sig;
	}

	/**
	 * Get the PayPal API user name
	 *
	 * @return string
	 */
	public function getUser()
	{
		return $this->api_user;
	}
	
	/**
	 * Get the PayPal API password
	 *
	 * @return string
	 */
	public function getPassword()
	{
		return $this->api_pass;
	}
	
	/**
	 * Get the PayPal API signature
	 *
	 * @return string
	 */
	public function getSignature()
	{
		return $this->signature;
	}
}
