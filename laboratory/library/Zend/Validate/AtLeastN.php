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
 * Zend Validate At Least N
 *
 * This unusual validator allows you to decorate another validator and validate 
 * that an array of values (passed to isValid()) contains at least N valid 
 * values, according to the wrapped validator. This allows you to handle certain 
 * odd situations, like requiring the user checks at least 3 checkboxes, for 
 * example.
 */
class Zend_Validate_AtLeastN extends Zend_Validate_Abstract
{
    /**
     * Validation failure reason codes
     */
    const NOT_ARRAY = 'atLeastNNotArray';
    const TOO_FEW   = 'atLeastNTooFew';

    /**
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_ARRAY => '\'%value%\' is not an array',
        self::TOO_FEW   => 'At least %threshold% correct values must be given'
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

        $validCount   = 0;
        $invalidCount = 0;
        $invalidMax   = count($value) - $this->_threshold + 1;

        foreach ($this->value as $v) {

            if ($this->_validator->isValid($v)) {
                ++$validCount;
            } else {
                ++$invalidCount;
            }

            if ($validCount >= $this->_threshold) {
                return true;
            }
            if ($invalidCount >= $invalidMax) {
                $this->_error(self::TOO_FEW);
                return false;
            }
        }

        return true;
    }
}
