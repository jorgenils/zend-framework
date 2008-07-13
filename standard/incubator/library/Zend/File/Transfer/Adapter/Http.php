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
     * Constructor for Http File Transfers
     */
    public function __construct()
    {
        $this->_files = $this->_prepareFiles($_FILES);
        $this->addValidators("Upload", $this->_files);
    }

    /**
     * Sets a validator for the class, erasing all previous set
     *
     * @param  string|array $validator Validator to set
     * @param  string|array $options   Options to set for this validator
     * @param  string|array $files     Files to limit this validator to
     * @return Zend_File_Transfer_Adapter
     */
    public function setValidators($validator, $options = null, $files = null)
    {
        $this->_validators = null;
        foreach($this->_files as $file => $content) {
            $this->_files[$file]['validators'] = array();
        }
        $this->addValidators("Upload", $this->_files);
        $this->addValidators($validator, $options, $files);

        return $this;
    }

    /**
     * Send the file to the client (Download)
     *
     * @param string|array $options Options for the file(s) to send
     */
    public function send($options = null)
    {
        require_once 'Zend/File/Transfer/Exception.php';
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Receive the file from the client (Upload)
     *
     * @todo Check if file exists otherwise existing will be overwritten
     * @todo Add validations
     * @todo Add filters
     * @param string|array $files (Optional) Files to receive
     */
    public function receive($files = null)
    {
        if ($this->isValid() === false) {
            require_once 'Zend/File/Transfer/Exception.php';
            throw new Zend_File_Transfer_Exception('Validation failed');
        }

        $check = $this->_getFiles($files);
        foreach($check as $file => $content) {
            $directory = "";
            if (isset($content['destination']) === true) {
                $directory = $content['destination'] . DIRECTORY_SEPARATOR;
            }

            // Should never go here as it's tested by the upload validator
            if (move_uploaded_file($content['tmp_name'], ($directory . $content['name'])) === false) {
                require_once 'Zend/File/Transfer/Exception.php';
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
        require_once 'Zend/File/Transfer/Exception.php';
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Checks if the file was already received
     *
     * @param string|array $files (Optional) Files to check
     */
    public function isReceived($files = null)
    {
        $validate = new Zend_Validate_File_Upload();
        if ($validate->isValid($files) === false) {
            return false;
        }

        return true;
    }

    /**
     * Returns the actual progress of file up-/downloads
     *
     * @return string Returns the state
     */
    public function getProgress()
    {
        require_once 'Zend/File/Transfer/Exception.php';
        throw new Zend_File_Transfer_Exception('Method not implemented');
    }

    /**
     * Prepare the $_FILES array to match the internal syntax of one file per entry
     *
     * @param  array $files
     * @return array
     */
    private function _prepareFiles(array $files = array())
    {
        $result = array();
        foreach($files as $form => $content) {
            if (is_array($content['name'])) {
                foreach($content as $param => $file) {
                    foreach ($file as $number => $target) {
                        $result[$form . "__" . $number][$param] = $target;
                    }
                }
            } else {
                $result[$form] = $content;
            }
        }

        return $result;
    }
}
