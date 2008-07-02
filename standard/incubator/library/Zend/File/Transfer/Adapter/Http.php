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
 * @category  Zend
 * @package   Zend_File_Transfer
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: $
 */

require_once 'Zend/File/Transfer/Adapter/Abstract.php';

/**
 * File transfer adapter class for the HTTP protocol
 *
 * @category  Zend
 * @package   Zend_File_Transfer
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_File_Transfer_Adapter_Http extends Zend_File_Transfer_Adapter_Abstract
{
    /**
     * Send the file to the client (Download)
     *
     * @param string|array $options Options for the file(s) to send
     */
    public function send($options = null)
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Receive the file from the client (Upload)
     *
     * @param string|array $options Options for the file(s) to receive
     */
    public function receive($options = null)
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Checks if the file was already sent
     *
     * @param string|array $file Files to check
     */
    public function isSent($file = null)
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Checks if the file was already received
     *
     * @param string|array $file Files to check
     */
    public function isReceived($file = null)
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Checks if the files are valid
     *
     * @param string|array $file Files to check
     */
    public function isValid($file = null)
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Returns the actual progress of file up-/downloads
     *
     * @return string Returns the state
     */
    public function getProgress()
    {
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }
}
