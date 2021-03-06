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
 * @package    Zend_Environment
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: RegisterGlobals.php 124 2007-04-16 22:40:30Z gavin $
 */

/**
 * require the Zend_Environment_Security_Test_Core class
 */
require_once 'Zend/Environment/Security/Test/Core.php';


/**
 * Test Class for register_globals
 *
 * @package Zend_Environment
 */
class Zend_Environment_Security_Test_Core_RegisterGlobals extends Zend_Environment_Security_Test_Core
{

    /**
	 * This should be a <b>unique</b>, human-readable identifier for this test
	 *
	 * @var string
	 */
    protected $_name = "register_globals";


    protected $_recommended_value = FALSE;


    protected function _retrieveCurrentValue() {
        $this->_current_value = $this->getIniValue('register_globals');
    }


    /**
	 * register_globals has been removed since PHP 6.0
	 *
	 * @return boolean
	 */
    public function isTestable() {
        return version_compare(PHP_VERSION, '6', '<') ;
    }



    /**
	 * Checks to see if allow_url_fopen is enabled
	 *
	 */
    protected function _execTest() {
        if ($this->_current_value == $this->_recommended_value) {
            return self::RESULT_OK;
        }

        return self::RESULT_WARN;
    }


    /**
	 * Set the messages specific to this test
	 *
	 */
    protected function _setMessages() {
        parent::_setMessages();

        $this->setMessageForResult(self::RESULT_NOTRUN, 'en', 'You are running PHP 6 or later and register_globals has been removed');
        $this->setMessageForResult(self::RESULT_OK, 'en', 'register_globals is disabled, which is the recommended setting');
        $this->setMessageForResult(self::RESULT_WARN, 'en', 'register_globals is enabled.  This could be a serious security risk.  You should disable register_globals immediately');
    }


}