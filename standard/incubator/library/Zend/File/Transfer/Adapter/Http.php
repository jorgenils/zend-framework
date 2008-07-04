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
require_once 'Zend/File/Transfer/Exception.php';

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
     * Constructor for Http File Transfers
     */
    public function __construct()
    {
        $this->_files = $_FILES;
    }

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
     * @todo Check if file exists otherwise existing will be overwritten
     * @todo Add validations
     * @todo Add filters
     * @param string|array $options Options for the file(s) to receive
     */
    public function receive($options = null)
    {
        $this->isValid();
        foreach($this->_files as $file => $content) {
            $directory = "";
            if (isset($content['destination']) === true) {
                $directory = $content['destination'] . DIRECTORY_SEPARATOR;
            }
            
            if (move_uploaded_file($content['tmp_name'], ($directory . $content['name'])) === false) {
                throw new Zend_File_Transfer_Exception("'$file' was illegal uploaded... possible attack", 100);
            }
        }
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
        if ($file !== null) {
            throw new Zend_File_Transfer_Exception('Method not implemented');
        }

        foreach ($this->_files as $file => $content) {
            if ($content['error'] > 0) {
                switch ($content['error']) {
                    case 1:
                        throw new Zend_File_Transfer_Exception("'$file' exceeds the servers size definition", 1);
                        break;

                    case 2:
                        throw new Zend_File_Transfer_Exception("'$file' exceeds the HTML form size definition", 2);
                        break;

                    case 3:
                        throw new Zend_File_Transfer_Exception("'$file' was only uploaded partially", 3);
                        break;

                    case 4:
                        throw new Zend_File_Transfer_Exception("'$file' was not uploaded", 4);
                        break;

                    case 6:
                        throw new Zend_File_Transfer_Exception("'$file' could not be stored... missing temporary folder", 6);
                        break;

                    case 7:
                        throw new Zend_File_Transfer_Exception("'$file' could not be stored... error writing to disk", 7);
                        break;

                    case 8:
                        throw new Zend_File_Transfer_Exception("'$file' upload stopped by extension", 8);
                        break;

                    default:
                        throw new Zend_File_Transfer_Exception("'$file' unknown upload error", $content['error']);
                        break;
                }
            }

            if (is_uploaded_file($content['tmp_name']) === false) {
                throw new Zend_File_Transfer_Exception("'$file' was illegal uploaded... possible attack", 100);
            }
        }
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
