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
 * Zend Validate At Most N
 *
 * This unusual validator allows you to decorate another validator and validate 
 * that an array of values (passed to isValid()) contains at most N valid 
 * values, according to the wrapped validator. This allows you to effectively 
 * limit how many values you want to accept, out of a larger set.
 */
class Zend_Validate_AtMostN extends Zend_Validate_Abstract
{
    /**
     * Validation failure reason codes
     */
    const NOT_ARRAY = 'atMostNNotArray';
    const TOO_MANY  = 'atMostNTooMany';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_ARRAY => '\'%value%\' is not an array',
        self::TOO_MANY  => 'At most %threshold% correct values must be given'
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'threshold' => '_threshold',
    );

    /**
     * Validator to decorate
     *
     * @var Zend_Validate_Interface
     */
    protected $_validator;

    /**
     * Threshold value
     *
     * @var int
     */
    protected $_threshold;


    /**
     * Constructor
     *
     * @param int Threshold
     * @param Zend_Validate_Interface Validator
     * @returns void
     * @throws Zend_Validate_Exception
     */
    public function __construct($threshold, Zend_Validate_Interface $validator)
    {
        if ($threshold < 1) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('The threshold must be 1 or greater');
        }

        $this->_threshold = $threshold;
        $this->_validator = $validator;
    }

    /**
     * Is Valid
     *
     * @see Zend_Validate_Interface
     */
    public function isValid($value)
    {
        $this->_setValue($value);

        if (!is_array($value)) {
            $this->_error(self::NOT_ARRAY);
            return false;
        }

        $validCount = 0;

        foreach ($this->value as $v) {

            if ($this->_validator->isValid($v)) {
                $validCount++;
            }

            if ($validCount > $this->_threshold) {
                $this->_error(self::TOO_MANY);
                return false;
            }
        }

        return true;
    }
}
