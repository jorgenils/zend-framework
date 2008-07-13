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
 * @package   Zend_Validate
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 * @version   $Id: $
 */

/**
 * @see Zend_Validate_Abstract
 */
require_once 'Zend/Validate/Abstract.php';

/**
 * Validator for the maximum size of a file up to a max of 2GB
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_Upload extends Zend_Validate_Abstract
{
    const INI_SIZE       = 'fileUploadErrorIniSize';
    const FORM_SIZE      = 'fileUploadErrorFormSize';
    const PARTITIAL      = 'fileUploadErrorPartitial';
    const NO_FILE        = 'fileUploadErrorNoFile';
    const NO_TMP_DIR     = 'fileUploadErrorNoTmpDir';
    const CANT_WRITE     = 'fileUploadErrorCantWrite';
    const EXTENSION      = 'fileUploadErrorExtension';
    const ATTACK         = 'fileUploadErrorAttack';
    const FILE_NOT_FOUND = 'fileUploadErrorFileNotFound';
    const UNKNOWN        = 'fileUploadErrorUnknown';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::INI_SIZE       => "The file '%value%' exceeds the defined ini size",
        self::FORM_SIZE      => "The file '%value%' exceeds the defined form size",
        self::PARTITIAL      => "The file '%value%' was only uploaded partitial",
        self::NO_FILE        => "The file '%value%' was not uploaded",
        self::NO_TMP_DIR     => "No temporary directory was found for the file '%value%'",
        self::CANT_WRITE     => "The file '%value%' can't be written",
        self::EXTENSION      => "The extension returned an error while uploading the file '%value%'",
        self::ATTACK         => "The file '%value%' was illegal uploaded, possible attack",
        self::FILE_NOT_FOUND => "The file '%value%' was not found",
        self::UNKNOWN        => "Unknown error while uploading the file '%value%'"
    );

    /**
     * Internal array of files
     *
     * @var array
     */
    protected $_files = array();

    /**
     * Sets validator options
     *
     * The array $files must be given in syntax of Zend_File_Transfer to be checked
     * If no files are given the $_FILES array will be used automatically.
     * NOTE: This validator will only work with HTTP POST uploads!
     *
     * @param  array $files Array of files in syntax of Zend_File_Transfer
     * @return void
     */
    public function __construct($files = array())
    {
        $this->setFiles($files);
    }

    /**
     * Returns the array of set files
     *
     * @return array
     */
    public function getFiles()
    {
        return $this->_files;
    }

    /**
     * Sets the minimum filesize
     *
     * @param  array $files The files to check in syntax of Zend_File_Transfer
     * @return Zend_Validate_File_Upload Provides a fluent interface
     */
    public function setFiles($files = array())
    {
        if (count($files) === 0) {
            $this->_files = $_FILES;
        } else {
            $this->_files = $files;
        }

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the file was uploaded without errors
     *
     * @param  string $value Single file to check for upload errors, when giving null the $_FILES array
     *                       from initialization will be used
     * @return boolean
     */
    public function isValid($value)
    {
        if (array_key_exists($value, $this->_files)) {
            $files = $this->_files;
        } else {
            foreach ($this->_files as $file => $content) {
                if ($content['name'] === $value) {
                    $files[$file] = $this->_files[$file];
                }

                if ($content['tmp_name'] === $value) {
                    $files[$file] = $this->_files[$file];
                }
            }
        }

        if (empty($files) === true) {
            $this->_error(self::FILE_NOT_FOUND);
            return false;
        }

        foreach ($files as $file => $content) {
            $this->_value = $file;
            switch($content['error']) {
                case 0:
                    if (is_uploaded_file($content['tmp_name']) === false) {
                        $this->_error(self::ATTACK);
                    }
                    break;

                case 1:
                    $this->_error(self::INI_SIZE);
                    break;

                case 2:
                    $this->_error(self::FORM_SIZE);
                    break;

                case 3:
                    $this->_error(self::PARTITIAL);
                    break;

                case 4:
                    $this->_error(self::NO_FILE);
                    break;

                case 6:
                    $this->_error(self::NO_TMP_DIR);
                    break;

                case 7:
                    $this->_error(self::CANT_WRITE);
                    break;

                case 8:
                    $this->_error(self::EXTENSION);
                    break;

                default:
                    $this->_error(self::UNKNOWN);
                    break;
            }
        }

        if (count($this->_messages) > 0) {
            return false;
        } else {
            return true;
        }
    }
}
