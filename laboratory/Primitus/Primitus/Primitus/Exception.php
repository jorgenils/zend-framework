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


/**
 * This is the base Primitus exception. It implements a number of getter and setters
 * beyond the normal PHP Exception class
 * 
 * @category   Primitus
 * @package    Primitus
 * @subpackage Exception
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_Exception extends Exception {
	
    private $_context;
    
    /**
     * Set the context array of the Exception
     *
     * @param array $ctx The context array to set in the Exception
     */
    public function setContext($ctx) {
        $this->_context = $ctx;
    }
    
    /**
     * Returns the context array of the Exception
     *
     * @return array The context array of the Exception
     */
    public function getContext() {
        return $this->_context;
    }
    
    /**
     * Sets the file in which the exception occurred
     *
     * @param string $file The filename where the exception occurred
     */
    public function setFile($file) {
        $this->file = $file;
    }
    
    /**
     * Sets the line in the file which the exception occurred
     *
     * @param string $line The line where the exception occurred
     */
    public function setLine($line) {
        $this->line = $line;
    }
	
}
?>