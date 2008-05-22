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
 * Zend Validate All-or-None
 *
 * Determines whether a field is valid based on whether either all the values at 
 * hand are empty, or none of them.
 *
 * The constructor argument is an associative array of user input data, keyed by 
 * field name. If the isValid() argument is empty, and *all* of the values from 
 * the constructor-passed array are empty, then the field is considered valid.  
 * If the isValid() argument is *not* empty, and *any* of the values from the 
 * constructor-passed array *are* empty, the field is considered invalid.
 *
 * In other words, if everything is empty, it's valid. If everything is *not* 
 * empty, it's valid. Otherwise, it's invalid. It's valid to have all or none of 
 * the values, but invalid to have only some of them.
 *
 * This is best used in conjunction with other validators that specifically test 
 * each individual field for correct values. This validator will help make sure 
 * you have the right set of fields to start with.
 */
class Zend_Validate_AllOrNone extends Zend_Validate_Abstract
{
    /**
     * This validator only has one validation failure reason code
     */
    const SOME = 'allOrNoneSome';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::SOME => 'All the values in the set must either be empty or they must all have values',
    );

    /**
     * The other fields involved in the comparison
     *
     * @var array
     */
    public $otherFields;


    /**
     * Constructor
     *
     * @param array Array of conditionally-required fields
     * @returns void
     * @throws none
     */
    public function __construct(array $otherFields)
    {
        $this->otherFields = $otherFields;
    }

    /**
     * Is Valid
     *
     * @see Zend_Validate_Interface
     *
     * @param mixed Current value to check
     * @returns bool
     * @throws Zend_Validate_Exception
     */
    public function isValid($value)
    {
        if (!is_array($this->otherFields) || !count($this->otherFields)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Valdiate_Exception('A list of other fields to compare with is required');
        }

        $this->_setValue($value);

        // If the current field is empty, and all the other fields are empty, 
        // it's valid
        if (empty($value) && 0 == count(array_filter($this->otherFields))) {
            return true;
        }
        // Otherwise, if *all* of the other fields are non-empty, it's valid
        if (!empty($value) && count($this->otherFields) == count(array_filter($this->otherFields))) {
            return true;
        }

        $this->_error(self::SOME);
        return false;
    }
}
