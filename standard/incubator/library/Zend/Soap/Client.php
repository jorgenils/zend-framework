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
 * @package    Zend_Soap
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 

/** Zend_Soap_Client_Exception */
require_once 'Zend/Soap/Client/Exception.php';

/**
 * Zend_Soap_Client 
 * 
 * @category   Zend
 * @package    Zend_Soap
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Soap_Client implements Zend_Server_Interface
{
    /**
     * Constructor
     * 
     * @param string $wsdl 
     * @param array $options 
     * @return void
     */
    public function __construct($wsdl = null, array $options = null)
    {
    }

    /**
     * Set wsdl
     *
     * @param string $wsdl
     * @return Zend_Soap_Server
     */
    public function setWsdl($wsdl)
    {
    }

    /**
     * Perform a SOAP call
     *
     * @return string
     */
    public function __get()
    {
        return $this->_wsdl;
    }

    /**
     * Return a server definition array
     *
     * @return array
     */
    public function getFunctions()
    {
    }
}
