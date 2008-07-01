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
class Zend_Validate_File_Size extends Zend_Validate_Abstract
{
    const TOO_BIG      = 'fileSizeTooBig';
    const TOO_SMALL    = 'fileSizeTooSmall';
    const NOT_READABLE = 'fileNotReadable';
    
    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::TOO_BIG      => "The file '%value%' is bigger than allowed",
        self::TOO_SMALL    => "The file '%value%' is smaller than allowed",
        self::NOT_READABLE => "The file '%value%' can not be read"
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'min' => '_min',
        'max' => '_max'
    );

    /**
     * Minimum filesize
     *
     * @var integer
     */
    protected $_min;

    /**
     * Maximum filesize
     *
     * If null, there is no maximum filesize
     *
     * @var integer|null
     */
    protected $_max;

    /**
     * Sets validator options
     *
     * Min limits the filesize, when used with max=null if is the maximum filesize
     * It also accepts an array with the keys 'min' and 'max'
     *
     * @param  integer|array $min Minimum filesize
     * @param  integer       $max Maximum filesize
     * @return void
     */
    public function __construct($min, $max = null)
    {
        if (is_array($min) === true) {
            if (isset($min['max']) === true) {
                $max = $min['max'];
            }

            if (isset($min['min']) === true) {
                $min = $min['min'];
            }

            if (isset($min[0]) === true) {
                if (count($min) === 2) {
                    $max = $min[1];
                    $min = $min[0];
                } else {
                    $max = $min[0];
                    $min = null;
                }
            }
        }

        if (empty($max) === true) {
            $max = $min;
            $min = null;
        }

        $this->setMin($min);
        $this->setMax($max);
    }

    /**
     * Returns the minimum filesize
     *
     * @param  boolean $unit Return the value with unit, when false the plan bytes will be returned
     * @return integer
     */
    public function getMin($unit = true)
    {
        $min = $this->_min;
        if ($unit === true) {
            $min = $this->_toByteString($min);
        }

        return $min;
    }

    /**
     * Sets the minimum filesize
     *
     * @param  integer $min            The minimum filesize
     * @throws Zend_Validate_Exception When min is greater than max
     * @return Zend_Validate_File_Size Provides a fluent interface
     */
    public function setMin($min)
    {
        $min = $this->_fromByteString($min);
        if (($this->_max !== null) and ($min > $this->_max)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The minimum must be less than or equal to the maximum filesize, but $min >"
                                            . " $this->_max");
        }

        $this->_min = max(0, (integer) $min);
        return $this;
    }

    /**
     * Returns the maximum filesize
     *
     * @param  boolean $unit Return the value with unit, when false the plan bytes will be returned
     * @return integer|null
     */
    public function getMax($unit = true)
    {
        $max = $this->_max;
        if ($unit === true) {
            $max = $this->_toByteString($max);
        }

        return $max;
    }

    /**
     * Sets the maximum filesize
     *
     * @param  integer|null $max       The maximum filesize
     * @throws Zend_Validate_Exception When max is smaller than min
     * @return Zend_Validate_StringLength Provides a fluent interface
     */
    public function setMax($max)
    {
        $max = $this->_fromByteString($max);
        if (null === $max) {
            $this->_max = null;
        } else if (($this->_min !== null) and ($max < $this->_min)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception("The maximum must be greater than or equal to the minimum length, but "
                                            . "$max < $this->_min");
        } else {
            $this->_max = (integer) $max;
        }

        return $this;
    }

    /**
     * Defined by Zend_Validate_Interface
     *
     * Returns true if and only if the filesize of $value is at least min and
     * not bigger than max (when max is not null).
     *
     * @param  string $value
     * @return boolean
     */
    public function isValid($value)
    {
        // Is file readable ?
        if (@is_readable($value) === false) {
            $this->_error(self::NOT_READABLE);
            return false;
        }

        // limited to 2GB files
        $size = @filesize($value);
        $this->_setValue($size);

        if ($size < $this->_min) {
            $this->_error(self::TOO_SMALL);
        }

        if (($this->_max !== null) and ($this->_max < $size)) {
            $this->_error(self::TOO_BIG);
        }

        if (count($this->_messages) > 0) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Returns the formatted size
     *
     * @param  integer $size
     * @return string
     */
    private function _toByteString($size) {
        $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        for ($i=0; $size > 1024 && $i < 9; $i++) {
            $size /= 1024;
        }
        return round($size, 2).$sizes[$i];
    }

    /**
     * Returns the unformatted size
     *
     * @param  string $size
     * @return integer
     */
    private function _fromByteString($size) {
        if (is_int($size) === true) {
            return $size;
        }

        $type  = trim(substr($size, -2));
        $value = substr($size, 0, -2);
        switch (strtoupper($type)) {
            case 'YB':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;

            case 'ZB':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;
            case 'EB':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024 * 1024);
                break;

            case 'PB':
                $value *= (1024 * 1024 * 1024 * 1024 * 1024);
                break;

            case 'TB':
                $value *= (1024 * 1024 * 1024 * 1024);
                break;

            case 'GB':
                $value *= (1024 * 1024 * 1024);
                break;

            case 'MB':
                $value *= (1024 * 1024);
                break;

            case 'KB':
                $value *= 1024;
                break;

            default:
                break;
        }

        return $value;
    }
}
