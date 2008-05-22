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
 * @package    Zend_Validate
 * @subpackage Zend_Validate
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Zend/Validate/Abstract.php';

/**
 * Zend Validate AllEqual
 *
 * Compares each element of the given array, and returns true if they all are 
 * equal to each other.
 */
class Zend_Validate_AllEqual extends Zend_Validate_Abstract
{
    /**
     * Validation failure reason codes
     */
    const NOT_ARRAY = 'allEqualNotArray';
    const NOT_EQUAL = 'allEqualNot';
    const NOT_EQUAL_STRICT = 'allEqualNotStrictly';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_ARRAY => '\'%value%\' must be an array',
        self::NOT_EQUAL => '\'%value%\' is not equal to \'%standard%\'',
        self::NOT_EQUAL_STRICT => '\'%value%\' is not strictly equal to \'%standard%\'',
    );

    /**
     * Optional strict type checking
     */
    public $strict = false;


    /**
     * Constructor
     *
     * @param bool Optional flag for strict type checking. Default: false
     * @returns void
     * @throws none
     */
    public function __construct($strict = false)
    {
        $this->strict = $strict;
    }

    /**
     * Is Valid
     *
     * @see Zend_Validate_Interface::isValid()
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if (!is_array($value)) {
            $this->_error(self::NOT_ARRAY);
            return false;
        }

        $standard = reset($value);

        foreach ($value as $test) {
            if ($this->strict) {
                if ($test !== $this->standard) {
                    $this->_error(self::NOT_EQUAL_STRICT);
                    return false;
                }
            } else {
                if ($test != $this->standard) {
                    $this->_error(self::NOT_EQUAL);
                    return false;
                }
            }
        }

        return true;
    }
}
