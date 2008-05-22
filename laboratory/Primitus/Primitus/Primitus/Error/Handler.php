<?php
/**
 * Primitus
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 *
 * @category   Primitus
 * @package    Application
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Primitus/Exception.php';

/**
 * This is the Primitus Error Handler, responsible for catching PHP E_WARNING, etc
 * and transforming them into exceptions. For debugging purposes, those error messages
 * which were not converted into exceptions (i.e. E_NOTICE), can be logged when
 * Primitus_DEBUG is enabled and displayed with the other debugging information.
 * 
 * @category   Primitus
 * @package    Primitus_Error
 * @subpackage Handler
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_Error_Handler {
    
    protected $_prettyNames = array(E_WARNING => "PHP Warning",
                                  E_ERROR => "PHP Error",
                                  E_NOTICE => "PHP Notice",
                                  E_USER_ERROR => "PHP User Error",
                                  E_USER_WARNING => "PHP User Warning",
                                  E_USER_NOTICE => "PHP User Notice");
                    
    protected $_unhandledErrors;
                  
    /**
     * Return an array containing all of the unhandled errors
     *
     * @return array The array of all of the unhandled errors
     */
    public function getUnhandledErrors() {
    	if(defined("Primitus_DEBUG") && Primitus_DEBUG) {
    		return $this->_unhandledErrors;
    	}
    	
    	return array();
    }
    
    /**
     * Callback function called internally by PHP in the event of any PHP error condition
     * which will process the error and throw an exception if it isn't ignored
     *
     * @param int $errNo The error code
     * @param string $errString The error message
     * @param string $errFile The file where the error occurred
     * @param int $errLine The line # of the error
     * @param array $errContext The context of the error (references to each variable in scope
     * @return boolean Returns false, telling PHP that it shouldn't handle the error itself
     */
    public function handleError($errNo, $errString, $errFile, $errLine, $errContext) {
        switch($errNo) {
        	case E_NOTICE:
        		
        		if(defined("Primitus_DEBUG") && Primitus_DEBUG) {
    				$this->_unhandledErrors = is_array($this->_unhandledErrors) ? 
    										  $this->_unhandledErrors : array();
				    
					$this->_unhandledErrors[] = array('errNo' => $errNo,
					                                  'errString' => $errString,
					                                  'errFile' => $errFile,
					                                  'errLine' => $errLine,
					                                  'errContext' => $errContext);
        		}
        		break;
            case E_WARNING:
            case E_ERROR:
            case E_USER_ERROR:
            case E_USER_WARNING:
            case E_USER_NOTICE:
                $exception = new Primitus_Exception("{$this->_prettyNames[$errNo]}: $errString", $errNo);
                $exception->setFile($errFile);
                $exception->setLine($errLine);
                throw $exception;
        }
        
        return false;
    }
    
    /**
     * Constructs the object and registers itself as the new error handler
     *
     */
    public function __construct() {
        set_error_handler(array($this, 'handleError'));
    }
}
?>