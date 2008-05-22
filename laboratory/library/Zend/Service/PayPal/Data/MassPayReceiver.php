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
 * @version    $Id: MassPayReceiver.php 126 2007-06-21 20:23:05Z shahar $
 */ 

/**
 * PayPal container class for a Mass Payment receiver information
 * 
 * This object is used to represent each receiver in a MassPay API call - 
 * each MassPay call can handle up to 250 receivers.
 * 
 * @todo    Improve validation
 * 
 * @category   Zend
 * @package    Zend_Service
 * @subpackage PayPal
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_PayPal_Data_MassPayReceiver
{
	/**
	 * Reciever type constants
	 */
	const RT_EMAIL  = 'EmailAddress';
	const RT_USERID = 'UserID';
	
	/**
	 * Receiver identifier (email address or PayPal ID)
	 *
	 * @var string
	 */
	protected $receiverid   = null;
	
	/**
	 * Receiver ID type - one of the two type constants
	 *
	 * @var string
	 */
	protected $receivertype = null;
	
	/**
	 * Amount to pay (currency is defined in transaction)  
	 *
	 * @var float
	 */
	protected $amount       = null;
	
	/**
	 * Optional unique transaction ID
	 *
	 * @var string
	 */
	protected $uniqueid     = null;
	
	/**
	 * Optional customer-specific note
	 *
	 * @var string
	 */
	protected $customnote   = null;
	
	/**
	 * User ID validator object cache 
	 * 
	 * Since several Recipient objects are likely to be instantiated, we are
	 * caching the Zend_Validate object used to validate the recipient ID.
	 *
	 * @var Zend_Validate_EmailAddress
	 */
	protected static $emailValidator  = null;
	
	/**
	 * User ID validator object cache 
	 * 
	 * Since several Recipient objects are likely to be instantiated, we are
	 * caching the Zend_Validate object used to validate the recipient ID.
	 *
	 * @var Zend_Validate
	 */
	protected static $useridValidator = null;
	 
	/**
	 * Create a new receiver info object
	 *
	 * @param float  $amount    Amount to pay (> 0)
	 * @param string $rcpt      Unique receiver identifier
	 * @param string $rcpt_type One of the two type constants
	 */
	public function __construct($amount, $rcpt, $rcpt_type = self::RT_EMAIL)
	{
		$this->amount = (float) $amount;
		if (! $this->amount || $this->amount <= 0) {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception("MassPay amount must be more than 0");
		}

		if (! self::validateReceiverType($rcpt, $rcpt_type)) {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception("'$rcpt' is not a valid receiver ID of type '$rcpt_type'");
		}
		
		$this->receiverid   = $rcpt;
		$this->receivertype = $rcpt_type;		
	}
	
	/**
	 * Set the optional unique recipient ID
	 *
	 * @param  string $id
	 * @return Zend_Service_PayPal_Data_MassPayReceiver
	 */
	public function setUniqueId($id)
	{
		if (! $id === null && ! preg_match('/^\S{1,30}$/', $id))  {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception("Unique Recipient ID must be 1 - 30 non-space characters");			
		}
		
		$this->uniqueid = $id;
		
		return $this;
	}
	
	/**
	 * Get the optional recipient unique ID
	 *
	 * @return string
	 */
	public function getUniqueId()
	{
		return $this->uniqueid;
	}
	
	/**
	 * Set receiver specific custom note
	 *
	 * @param  string $note
	 * @return Zend_Service_PayPal_Data_MassPayReceiver
	 */
	public function setCustomNote($note)
	{
		if (strlen($note) > 4000) {
			require_once 'Zend/Service/PayPal/Data/Exception.php';
			throw new Zend_Service_PayPal_Data_Exception("Custom note must be less than 4000 bytes long");
		}
			
		$this->customnote = $note;
		
		return $this;
	}
	
	/**
	 * Return the customer-specific optional custom note
	 *
	 * @return string
	 */
	public function getCustomNote()
	{
		return $this->customnote;
	}
	
	/**
	 * Get the amount to pay (currency is determinded by the transaction)
	 *
	 * @return float
	 */
	public function getAmount()
	{
		return $this->amount;
	}
	
	/**
	 * Get the receiver ID (email address or PayPal ID)
	 *
	 * @return string
	 */
	public function getReceiverId()
	{
		return $this->receiverid;
	}
	
	/**
	 * Get the receiver type (must match transtaction-global type)
	 *
	 * @return string
	 */
	public function getReceiverType()
	{
		return $this->receivertype;
	}
	
	/**
	 * Validate that the receiver ID is well-formed according to it's type
	 *
	 * @param  string $value
	 * @param  string $type Either EMAIL or PAYPAL ID
	 * @return boolean
	 */
	static public function validateReceiverType($value, $type)
	{
		switch ($type) {
			case self::RT_EMAIL:
				if (! self::$emailValidator) {
					require_once 'Zend/Validate/EmailAddress.php';
					
					self::$emailValidator = new Zend_Validate_EmailAddress();
				}
				return self::$emailValidator->isValid($value);
				break;
				
			case self::RT_USERID:
				if (! self::$useridValidator) {
					require_once 'Zend/Validate.php';
					require_once 'Zend/Validate/StringLength.php';
					require_once 'Zend/Validate/Alnum.php';
					
					self::$useridValidator = new Zend_Validate();
					self::$useridValidator->addValidator(new Zend_Validate_StringLength(13))
					                      ->addValidator(new Zend_Validate_Alnum());
				}
				return self::$useridValidator->isValid($value);
				break;
				
			default:
				require_once 'Zend/Service/PayPal/Data/Exception.php';
				throw new Zend_Service_PayPal_Data_Exception("'$type' is not a valid Receiver ID type");
				break;
		}
	}
}
