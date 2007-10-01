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
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Cipher
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Exception.php 2794 2007-01-16 01:29:51Z bkarwin $
 * @author     John Coggeshall <john@zend.com>
 */

/**
 * Zend_InfoCard_Cipher_PKI_Interface
 */
require_once 'Zend/InfoCard/Cipher/PKI/Interface.php';

/**
 * Zend_InfoCard_Cipher_Exception
 */
require_once 'Zend/InfoCard/Cipher/Exception.php';

/**
 * An abstract class for public-key ciphers
 * 
 * @category   Zend
 * @package    Zend_InfoCard
 * @subpackage Zend_InfoCard_Cipher
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @author     John Coggeshall <john@zend.com>
 */
abstract class Zend_InfoCard_Cipher_PKI_Adapter_Abstract implements Zend_InfoCard_Cipher_PKI_Interface {
	
	/**
	 * OAEP Padding public key encryption
	 */
	const OAEP_PADDING = 1;
	
	/**
	 * No padding public key encryption
	 */
    const NO_PADDING = 2;

    /**
     * The type of padding to use
     *
     * @var integer one of the padding constants in this class
     */
    protected $_padding;
    
    /**
     * Set the padding of the public key encryption
     *
     * @throws Zend_InfoCard_Cipher_Exception
     * @param integer $padding One of the constnats in this class
     * @return Zend_InfoCard_PKI_Adapter_Abstract
     */
    public function setPadding($padding) {
    	switch($padding) {
    		case self::OAEP_PADDING:
    		case self::NO_PADDING:
    			$this->_padding = $padding;
    			break;
    		default:
    			throw new Zend_InfoCard_Cipher_Exception("Invalid Padding Type Provided");
    	}
    	
    	return $this;
    }
    
    /**
     * Retruns the public-key padding used
     *
     * @return integer One of the padding constants in this class
     */
    public function getPadding() {
    	return $this->_padding;
    }
}