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
 * Zend Validate Equals
 *
 * Compares values to the one given to the constructor, and returns true if 
 * they're equal.
 */
class Zend_Validate_Equals extends Zend_Validate_Abstract
{
    /**
     * Validation failure reason codes
     */
    const NOT_EQUAL = 'equalsNot';
    const NOT_EQUAL_STRICT = 'equalsNotStrictly';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_EQUAL => '\'%value%\' is not equal to \'%standard%\'',
        self::NOT_EQUAL_STRICT => '\'%value%\' is not strictly equal to \'%standard%\'',
    );

    /**
     * Standard value to compare against
     */
    public $standard = null;

    /**
     * Optional strict type checking
     */
    public $strict = false;


    /**
     * Constructor
     *
     * @param mixed Optional standard value; can be anything
     * @param bool Optional flag for strict type checking. Default: false
     * @returns void
     * @throws none
     */
    public function __construct($standard = null, $strict = false)
    {
        $this->standard = $standard;
        $this->strict   = $strict;
    }

    /**
     * Is Valid
     *
     * @see Zend_Validate_Interface::isValid()
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if ($this->strict) {
            if ($value !== $this->standard) {
                $this->_error(self::NOT_EQUAL_STRICT);
                return false;
            }
        } else {
            if ($value != $this->standard) {
                $this->_error(self::NOT_EQUAL);
                return false;
            }
        }
        return true;
    }
}
