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
 * Validator for the file extension of a file
 *
 * @category  Zend
 * @package   Zend_Validate
 * @copyright Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_File_Extension extends Zend_Validate_Abstract
{
    const FALSE_EXTENSION = 'fileExtensionFalse';
    const NOT_FOUND       = 'fileExtensionNotFound';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::FALSE_EXTENSION => "The file '%value%' has a false extension",
        self::NOT_FOUND       => "The file '%value%' was not found"
    );

    /**
     * Internal list of extensions
     *
     * @var array
     */
    private $_extension = array();

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'extension' => '_extension'
    );

    /**
     * Sets validator options
     *
     * @param  string|array $extension
     * @return void
     */
    public function __construct($extension)
    {
        $this->setExtension($extension);
    }

    /**
     * Returns the set file extension
     *
     * @param  boolean $asArray Returns the values as array, when false an concated string is returned
     * @return string
     */
    public function getExtension($asArray = false)
    {
        $extension = $this->_extension;
        if ($asArray === false) {
            $extension = implode(',', $extension);
        }

        return $extension;
    }

    /**
     * Sets the file extensions
     *
     * @param  string|array $extension The extensions to validate
     * @return Zend_Validate_File_Extension Provides a fluent interface
     */
    public function setExtension($extension)
    {
        $this->_extension = array();
        $this->addExtension($extension);
        return $this;
    }

    /**
     * Adds the file extensions
     *
     * @param  string|array $extension The extensions to add for validation
     * @return Zend_Validate_File_Extension Provides a fluent interface
     */
    public function addExtension($extension)
    {
        if (is_string($extension) === true) {
            $extension = explode(',', $extension);
        }

        foreach($extension as $content) {
            $this->_extension[] = trim($content);
        }

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the fileextension of $value is included in the
     * set extension list
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value, $file = null)
    {
        // Is file readable ?
        if (@is_readable($value) === false) {
            $this->_error(self::NOT_FOUND);
            return false;
        }

        $info = @pathinfo($value);
        if (in_array($info['extension'], $this->_extension) === true) {
            return true;
        }

        return false;
    }
}
