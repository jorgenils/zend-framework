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
 * @package    Zend_Log
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/** Zend_Log_Filter_Interface */
require_once 'Zend/Log/Filter/Interface.php';

/**
 * @category   Zend
 * @package    Zend_Log
 * @subpackage Filter
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */ 
class Zend_Log_Filter_Message implements Zend_Log_Filter_Interface
{
    /**
     * @var string
     */
    protected $_regexp;

    /**
     * Filter out any log messages not matching $regexp.
     *
     * @param  string  $regexp     Regular expression to test the log message
     * @throws InvalidArgumentException
     */
    public function __construct($regexp)
    {
        if (@preg_match($regexp, '') === false) {
            throw new InvalidArgumentException("Invalid regular expression '$regexp'");
        }
        $this->_regexp = $regexp;
    }

    /**
     * Returns TRUE to accept the message, FALSE to block it.
     *
     * @param  string   $message   message for the log
     * @param  integer  $priority  priority of message
     * @return boolean             accepted?
     */
    public function accept($message, $priority)
    {
        return preg_match($this->_regexp, $message) > 0;
    }

}
