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
 * @version    $Id: PayPal.php 126 2007-06-21 20:23:05Z shahar $
 */ 

require_once 'Zend/Http/Client.php';
require_once 'Zend/Service/PayPal/Data/AuthInfo.php';
require_once 'Zend/Service/PayPal/Response.php';

/**
 * PayPal Name-Value Pairs (NVP) API implementation
 * 
 * @category   Zend
 * @package    Zend_Service
 * @subpackage PayPal
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_PayPal
{
	/**
	 * Paypal NVP service URL
	 *
	 */
	const SERVICE_URI = 'https://api-3t.sandbox.paypal.com/nvp';
	const SANDBOX_URI = 'https://api.sandbox.paypal.com/nvp';
	
	/**
	 * Authentication info for the PayPal API service
	 *
	 * @var Zend_Service_PayPal_Data_AuthInfo
	 */
	protected $authInfo = null;
	
	/**
	 * Zend_Http_Client to use for the service
	 *
	 * @var Zend_Http_Client
	 */
	protected $httpClient = null;
	
	/**
	 * Enter description here...
	 *
	 * @param Zend_Service_PayPal_Data_AuthInfo $auth_info
	 * @param Zend_Http_Client                  $httpClient
	 */
	public function __construct(Zend_Service_PayPal_Data_AuthInfo $authInfo, 
		$uri = self::SERVICE_URI, $httpClient = null)
	{
		$this->authInfo = $authInfo;
		
		if ($httpClient instanceof Zend_Http_Client) {
			$this->httpClient = $httpClient;
		} else {
			// Create and configure the default HTTP client
			$this->httpClient = new Zend_Http_Client();
			$this->httpClient->setConfig(array(
				'adapter'      => 'Zend_Http_Client_Adapter_Socket',
				'maxredirects' => 0,
				'timeout'      => 60,
				'ssltransport' => 'ssl'
			));
		}
		
		$this->httpClient->setUri($uri);
	}
	
	/**
	 * HTTP client preparation procedures - should be called before every API 
	 * call. 
	 * 
	 * Will clean up the HTTP client parameters, set the request method to POST
	 * and add the always-required authentication information
	 * 
	 * @param  string $method The API method we are about to use
	 * @return void
	 */
	protected function prepare($method)
	{
		// Reset parameters
		$this->httpClient->resetParameters();
		
		// Add authentication data
		$this->httpClient->setMethod('POST');
		$this->httpClient->setParameterPost(array(
			'USER'      => $this->authInfo->getUser(),
			'PWD'       => $this->authInfo->getPassword(),
			'SIGNATURE' => $this->authInfo->getSignature(),
			'VERSION'   => '2.3',
			'METHOD'    => $method
		));
	}
	
	/**
	 * Preform the request and return a response object
	 *
	 * @todo   Validate HTTP response status, etc - throw Exception if wrong
	 * 
	 * @return PayPal_Service_Nvp_Response
	 */
	protected function process()
	{
		$httpResponse = $this->httpClient->request();
		
		if (! $httpResponse->isSuccessful()) {
			require_once 'Zend/Service/PayPal/Exception.php';
			throw new Zend_Service_PayPal_Exception('HTTP response from PayPal was not OK: ' . 
				$httpResponse->getStatus());
		}
		
		return new Zend_Service_PayPal_Response($httpResponse->getBody());
	}
	
	/**
	 * Get the HTTP client to be used for this service
	 *
	 * @return Zend_Http_Client
	 */
	public function _getHttpClient()
	{
		return $this->httpClient;
	}
	
	/**
	 * Preform a DoDirectPayment call 
	 * 
	 * This call preforms a direct payment, directly charging a credit card 
	 * using PayPal's services. It does not require a valid PayPal user name,
	 * but does require the credit card details and billing address of the
	 * customer.
	 *
	 * @param  Zend_Service_PayPal_Data_CreditCard $creditCard
	 * @param  Zend_Service_PayPal_Data_Address    $address
	 * @param  float                               $amount
	 * @param  string                              $currency
	 * @param  string                              $remoteAddr
	 * @param  string                              $paymentAction
	 * @return Zend_Service_PayPal_Response
	 */
	public function doDirectPayment(Zend_Service_PayPal_Data_CreditCard $creditCard, 
		Zend_Service_PayPal_Data_Address $address, $amount, $currency = 'USD', 
		$remoteAddr = null, $paymentAction = 'Sale')
	{
		$this->prepare('DoDirectPayment');
		
		$paymentAction = ucfirst(strtolower((string) $paymentAction));
		if (! ($paymentAction == 'Sale' || $paymentAction == 'Authorization')) {
			require_once 'Zend/Service/PayPal/Exception.php';
			throw new Zend_Service_PayPal_Exception('Payment Action must be set to either "Sale" or "Authorization"');
		}
			
		$amount = (float) $amount;
		if (! $amount) {
			require_once 'Zend/Service/PayPal/Exception.php';
			throw new Zend_Service_PayPal_Exception('Amount must be a floating-point number bigger than zero');
		}
			
		if (! $remoteAddr)
			$remoteAddr = $_SERVER['REMOTE_ADDR'];

		$this->httpClient->setParameterPost(array(
			'PAYMENTACTION'  => $paymentAction,
			'CREDITCARDTYPE' => $creditCard->getType(),
			'ACCT'           => $creditCard->getAcct(),
			'FIRSTNAME'      => $creditCard->getFirstName(),
			'LASTNAME'       => $creditCard->getLastName(),
			'CVV2'           => $creditCard->getCvv2(),
			'EXPDATE'        => $creditCard->getExpDate(),
			'STREET'         => $address->getStreet(),
			'CITY'           => $address->getCity(),
			'STATE'          => $address->getState(),
			'COUNTRYCODE'    => $address->getCountryCode(),
			'ZIP'            => $address->getZip(),
			'IPADDRESS'      => $remoteAddr,
			'AMT'            => $amount,
			'CURRENCYCODE'   => $currency
		));
		
		return $this->process();
	}
	
	/**
	 * Preform a SetExpressCheckout PayPal API call, starting an Express 
	 * Checkout process. 
	 * 
	 * This call is expected to return a token which can be used to redirect
	 * the user to PayPal's transaction approval page
	 *
	 * @param  float  $amount
	 * @param  string $returnUrl
	 * @param  string $cancelUrl
	 * @param  array  $params    Additional parameters
	 * @return Zend_Service_PayPal_Response
	 */
	public function setExpressCheckout($amount, $returnUrl, $cancelUrl, $params = array())
	{
		$amount = (float) $amount;
		
		if (! Zend_Uri_Http::check($returnUrl)) {
			require_once 'Zend/Service/PayPal/Exception.php';
			throw new Zend_Service_PayPal_Exception('Return URL is not a valid URL');
		}
			
		if (! Zend_Uri_Http::check($cancelUrl)) {
			require_once 'Zend/Service/PayPal/Exception.php';
			throw new Zend_Service_PayPal_Exception('Cancel URL is not a valid URL');
		}
		
		if (! is_array($params)) {
			require_once 'Zend/Service/PayPal/Exception.php';
			throw new Zend_Service_PayPal_Exception('$params is expected to be an array, ' . gettype($params) . ' given');
		}
			
		$this->prepare('SetExpressCheckout');
		
		$this->httpClient->setParameterPost(array(
			'AMT'       => $amount,
			'RETURNURL' => $returnUrl,
			'CANCELURL' => $cancelUrl
		));
		
		foreach ($params as $k => $v) {
			$this->httpClient->setParameterPost(strtoupper($k), $v);
		}
		
		return $this->process();
	}
	
	/**
	 * Preform a GetExpressCheckoutDetails PayPal API call, requesting info
	 * about a started Express Checkout transaction
	 * 
	 * @param  string $token Transaction identifier token
	 * @return Zend_Service_PayPal_Response
	 */
	public function getExpressCheckoutDetails($token)
	{
		$this->prepare('GetExpressCheckoutDetails');
		$this->httpClient->setParameterPost('TOKEN', (string) $token);
		return $this->process();
	}
	
	/**
	 * Preform a DoExpressCheckoutPayment PayPal API call, finalizing a
	 * transaction.
	 *
	 * @param string $token
	 * @param string $payerId
	 * @param float  $amount
	 * @return Zend_Service_PayPal_Response
	 */
	public function doExpressCheckoutPayment($token, $payerId, $amount)
	{
		$this->prepare('DoExpressCheckoutPayment');
		 
		$this->httpClient->setParameterPost(array(
			'TOKEN'         => $token,
			'PAYERID'       => $payerId,
			'PAYMENTACTION' => 'Sale',
			'AMT'           => (float) $amount
		));
		
		return $this->process();
	}

	/**
	 * Preform a Mass Payment API call
	 * 
	 * @param  array  $receivers    Array of Zend_Service_PayPal_Data_MassPayRceiver objects
	 * @param  string $rcpttype     Receiver type
	 * @param  string $emailSubject Email subject for all receivers
	 * @param  string $currency     3 letter currency code, default is USD
	 * @return Zend_Service_PayPal_Response
	 */
	public function massPay(array $receivers, $rcpttype = Zend_Service_PayPal_Data_MassPayReceiver::RT_EMAIL, $emailSubject = '', $currency = 'USD')
	{
		// Make sure we have more than 0 and less than 256 receivers
		if (empty($receivers) || count($receivers) > 255) {
			require_once 'Zend/Service/PayPal/Exception.php';
			throw new Zend_Service_PayPal_Exception("Number of receivers must be between 1 and 255");
		}
			
		// Validate receiver type
		if ($rcpttype == Zend_Service_PayPal_Data_MassPayReceiver::RT_EMAIL) {
			$idfield = 'L_EMAIL';
		} elseif ($rcpttype == Zend_Service_PayPal_Data_MassPayReceiver::RT_USERID) {
			$idfield = 'L_RECEIVERID';
		} else {
			require_once 'Zend/Service/PayPal/Exception.php';
			throw new Zend_Service_PayPal_Exception("Receiver ID type '$rcpttype' is not valid");
		}

		// Validate currency code
		if (! self::validateCurrencyCode($currency)) {
			require_once 'Zend/Service/PayPal/Exception.php';
			throw new Zend_Service_PayPal_Exception("Currency code '$currency' is not valid");
		}

		// Prepare API call
		$this->prepare('MassPay');
		
		// Validate and optionally set email subject
		if ($emailSubject) {
			if (strlen($emailSubject) > 255) {
				require_once 'Zend/Service/PayPal/Exception.php';
				throw new Zend_Service_PayPal_Exception("Email Subject must be up to 255 single-byte characters");
			}

			$this->httpClient->setParameterPost('EMAILSUBJECT', $emailSubject);
		}
		
		// Set currency code and receiver type
		$this->httpClient->setParameterPost(array(
			'RECEIVERTYPE' => $rcpttype,
			'CURRENCYCODE' => $currency
		));
		
		$c = 0;
		foreach ($receivers as $rcpt) { /* @var $rcpt Zend_Service_PayPal_Data_MassPayReceiver */
			if (! $rcpt->getReceiverType() == $rcpttype) {
				require_once 'Zend/Service/PayPal/Exception.php';
				throw new Zend_Service_PayPal_Exception("All receiver ID types must be '$rcpttype', '{$rcpt->getReceiverId()}' is not"); 
			}

			// Set amount and receiver ID
			$this->httpClient->setParameterPost(array(
				"L_AMT$c"     => $rcpt->getAmount(),
				$idfield . $c => $rcpt->getReceiverId()
			));
			
			// Set optional fields
			if ($rcpt->getUniqueId()) 
				$this->httpClient->setParameterPost("L_UNIQUEID$c", $rcpt->getUniqueId());
				
			if ($rcpt->getCustomNote())
				$this->httpClient->setParameterPost("L_NOTE$c", $rcpt->getCustomNote());
			
			$c++;
		}		
		
		return $this->process();
	}
	
	public function getTransactionDetails($transactionId)
	{
		require_once 'Zend/Validate.php';
		
		if (! (Zend_Validate::is($transactionId, 'Alnum') &&
		       Zend_Validate::is($transactionId, 'StringLength', array(17)))) {
		    
			require_once 'Zend/Service/PayPal/Exception.php';
			throw new Zend_Service_PayPal_Exception("'$transactionId' is noa a valid PayPal transaction ID");   	
		}
			
			
		$this->prepare('GetTransactionDetails');
		$this->httpClient->setParameterPost('TRANSACTIONID', $transactionId);
		
		return $this->process();
	}
	
	/**
	 * Validate a 3-letter ISO currency code
	 * 
	 * @todo   This should be smarter than just making sure we have a 
	 *         3 capital letter string
	 * 
	 * @param  string $code
	 * @return boolean
	 */
	static public function validateCurrencyCode($code)
	{
		return preg_match('|^[A-Z]{3}$|', $code);
	}
}
