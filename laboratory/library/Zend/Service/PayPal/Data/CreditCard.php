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
 * @version    $Id: CreditCard.php 126 2007-06-21 20:23:05Z shahar $
 */ 

/**
 * PayPal container class for credit card information
 * 
 * @todo    Improve validation
 * 
 * @category   Zend
 * @package    Zend_Service
 * @subpackage PayPal
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_PayPal_Data_CreditCard
{
	/**
	 * Card type constants
	 */
	const CC_VISA       = 'Visa';
	const CC_MASTERCARD = 'MasterCard';
	const CC_AMEX       = 'Amex';
	const CC_DISCOVER   = 'Discover';
	const CC_SWITCH     = 'Switch';
	const CC_SOLO       = 'Solo';
	
	/**
	 * Card type 
	 *
	 * @var string
	 */
	protected $type;
	
	/**
	 * Account (credit card) number
	 *
	 * @var string
	 */
	protected $acct;

	/**
	 * Card holder's first name
	 *
	 * @var string
	 */
	protected $firstName;

	/**
	 * Card holder's last name
	 *
	 * @var string
	 */
	protected $lastName;

	/**
	 * Expiry month (1 - 12)
	 *
	 * @var integer
	 */
	protected $expMonth;

	/**
	 * Expiry tear (4 digits)
	 *
	 * @var integer
	 */
	protected $expYear;

	/**
	 * Card validation number (3 - 4 digits)
	 *
	 * @var string
	 */
	protected $Cvv2 = null;

	/**
	 * Start date for Switch / Solo cards
	 *
	 * @var string
	 */
	protected $startDate = null;

	/**
	 * Issue number for Switch / Solo cards
	 *
	 * @var string
	 */
	protected $issueNumber = null;

	/**
	 * Create a new Credit Card object
	 *
	 * @param string  $type        Card type
	 * @param string  $acct        Account (card number)
	 * @param string  $firstName   Card holder first name
	 * @param string  $lastName    Card holder last name
	 * @param integer $expMonth    Expiry Month
	 * @param integer $expYear     Expiry Year
	 * @param string  $cvv2        CVV2
	 * @param string  $startDate   Start date for Switch / Solo cards
	 * @param string  $issueNumber Issue number for Switch / Solo cards
	 */
	public function __construct($type, $acct, $firstName, $lastName, $expMonth, $expYear, $cvv2 = null, $startDate = null, $issueNumber = null)
	{
		if (! defined(__CLASS__ . '::CC_' . strtoupper($type))) {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception('Unkown card type: "' . $type . '"');
		}
			
		$this->type = constant(__CLASS__ . '::CC_' . strtoupper($type));
		
		/** @todo validate card number */
		$this->acct = (string) $acct;
		
		if (strlen($firstName) < 1 || strlen($firstName) > 25) {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception('First name must be set and no longer than 25 characters');
		}
			
		$this->firstName = (string) $firstName;
		
		if (strlen($lastName) < 1 || strlen($lastName) > 25) {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception('Last name must be set and no longer than 25 characters');
		}
			
		$this->lastName = (string) $lastName;
		
		if ($expMonth < 1 || $expMonth > 12) {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception('Expiry month must be a valid month number (1 - 12)');
		}
			
		$this->expMonth = (int) $expMonth;
			
		if (strlen($expYear) != 4) {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception('Expiry year must be a 4 digit number');
		}
			
		$this->expYear = (int) $expYear;
		
		// If a CVV2 number was set, validate it
		/** @todo Check validation for other cards */
		if ($cvv2) {
			if ($this->type == self::CC_VISA || $this->type == self::CC_MASTERCARD || $this->type == self::CC_DISCOVER) {
				if (strlen($cvv2) != 3) {
					require_once 'Zend/Service/PayPal/Data/Exception.php';
					throw new Zend_Service_PayPal_Data_Exception('CVV2 is expected to be exactly 3 digits long');
				}
			    	
			} elseif ($this->type == self::CC_AMEX) {
				if (strlen($cvv2) != 4) {
					require_once 'Zend/Service/PayPal/Data/Exception.php';
					throw new Zend_Service_PayPal_Data_Exception('CVV2 is expected to be exactly 4 digits long');
				}
			}
			
			$this->cvv2 = $cvv2;
		}
		
		// Validate start date and issue number for Switch / Solo cards
		if ($this->type == self::CC_SWITCH || $this->type == self::CC_SOLO) {
			if (strlen($startDate) < 5 || strlen($startDate) > 6) {
				require_once 'Zend/Service/PayPal/Data/Exception.php';
				throw new Zend_Service_PayPal_Data_Exception('$startDate "' . $startDate . '" does not seem to be a valid startDate value');
			}
				
			
			$sdYear  = substr($startDate, -4);
			$sdMonth = substr($startDate, 0, strlen($startDate - 4));
			if ($sdMonth < 1 || $sdMonth > 12 || strlen($sdYear) != 4) {
				require_once 'Zend/Service/PayPal/Data/Exception.php';
				throw new Zend_Service_PayPal_Data_Exception('$startDate "' . $startDate . '" does not seem to be a valid startDate value');
			}
			 
			$this->startDate = sprintf("%06d", $startDate);
			
			if (! $issueNumber || strlen($issueNumber) > 2) {
				require_once 'Zend/Service/PayPal/Data/Exception.php';
				throw new Zend_Service_PayPal_Data_Exception('$issueNumber "' . $issueNumber . '" does not seem to be a valid issueNumber value');
			}
				
			$this->issueNumber = sprintf("%02d", $issueNumber);
		}
	}

	/**
	 * Get the card type
	 *
	 * @return string
	 */
	public function getType()
	{
		return $this->type;
	}
	
	/**
	 * Get the value of acct
	 * 
	 * @return string
	 */
	public function getAcct()
	{
		return $this->acct;
	}

	/**
	 * Get the value of firstName
	 * 
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * Get the value of lastName
	 * 
	 * @return string
	 */
	public function getLastName()
	{
		return $this->lastName;
	}

	/**
	 * Get the value of expMonth
	 * 
	 * @return integer
	 */
	public function getExpMonth()
	{
		return $this->expMonth;
	}

	/**
	 * Get the value of expYear
	 * 
	 * @return integer
	 */
	public function getExpYear()
	{
		return $this->expYear;
	}

	/**
	 * Get the expiry date of this card as a 6-digit string (MMYYYY)
	 *
	 * @return string
	 */
	public function getExpDate()
	{
		return sprintf("%02d%04d", $this->expMonth, $this->expYear);
	}
	
	/**
	 * Get the value of Cvv2
	 * 
	 * @return string
	 */
	public function getCvv2()
	{
		return $this->cvv2;
	}

	/**
	 * Get the value of startDate
	 * 
	 * @return string
	 */
	public function getStartDate()
	{
		return $this->startDate;
	}

	/**
	 * Get the value of issueNumber
	 * 
	 * @return string
	 */
	public function getIssueNumber()
	{
		return $this->issueNumber;
	}
}
