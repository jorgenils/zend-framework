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
 * Zend Validate Array
 *
 * Validation decorator that runs the given validator over the array value 
 * passed to isValid(). Basically converts an existing validator into one that 
 * can handle arrays.
 *
 * In order to preseve the transparency of the decoration, this class proxies to 
 * the decorated validator all the methods related to getting errors. This way 
 * classes that use this decorator won't have to jump through unnecessary hoops 
 * to find the error messages for failed validation.
 */
class Zend_Validate_Array extends Zend_Validate_Abstract
{
    /**
     * This class only has one validation failure reason code
     */
    const NOT_ARRAY = 'arrayNot';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::NOT_ARRAY => '\'%value%\' is not an array',
    );

    /**
     * Validator we're decorating
     *
     * @var Zend_Validate_Interface
     */
    public $validator;


    /**
     * Constructor
     *
     * @param Zend_Validate_Interface Validator
     * @returns void
     * @throws none
     */
    public function __construct(Zend_Validate_Interface $validator)
    {
        $this->validator = $validator;
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

        foreach ($value as $elem) {
            if (!$this->validator->isValid($elem)) {
                // The validator's errors will available through this class's 
                // error-retreiving methods to preserve transparency
                return false;
            }
        }

        return true;
    }

    /**
     * Get Errors
     *
     * Proxies to the decorated validator when this doesn't have any raised 
     * errors of its own.
     *
     * @deprecated Since 1.5.0
     *
     * @returns array
     */
    public function getErrors()
    {
        if (empty($this->_errors)) {
            return $this->validator->getErrors();
        } else {
            return $this->_errors;
        }
    }

    /**
     * Get Messages
     *
     * Proxies to the decorated validator when this doesn't have any raised 
     * errors of its own.
     *
     * @returns array
     */
    public function getMessages()
    {
        if (empty($this->_messages)) {
            return $this->validator->getMessages();
        } else {
            return $this->_messages;
        }
    }

    /**
     * Get Message Variables
     *
     * Proxies to the decorated validator when this doesn't have any raised 
     * errors of its own.
     *
     * @returns array
     */
    public function getMessageVariables()
    {
        if (empty($this->_errors)) {
            return $this->validator->getMessageVariables();
        } else {
            return array_keys($this->_messageVariables);
        }
    }

    /**
     * Magic Get
     *
     * @param string $property
     * @return mixed
     * @throws Zend_Validate_Exception
     */
    public function __get($property)
    {
        if (empty($this->_errors)) {
            return $this->validator->__get($property);
        } else {
            return parent::__get($property);
        }
    }
}
