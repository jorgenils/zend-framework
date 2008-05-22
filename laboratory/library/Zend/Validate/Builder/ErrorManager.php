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
 * @subpackage Builder
 * @author     Bryce Lohr
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Zend/Validate/Builder/ErrorManager/Interface.php';

/**
 * Zend Validate Builder Error Manager
 * 
 * Serves sort of as a bridge to get error messages from userland code into 
 * Validator classes. The error messages can be defined in a config file and 
 * read into this class. Using this class to raise error messages for invalid 
 * fields will ensure the correct error messages are associated with the correct 
 * fields. This makes it easy for the View code to display the validation errors 
 * to the user.
 *
 * Zend_Validate_Builder uses this class by default to manage error messages, 
 * but this class can easily be replaced with a custom implementation for 
 * specific needs, without having to extend or modify the Zend_Validate_Builder 
 * class.
 *
 * Due to the fact that there was apparently no forethought put into the design 
 * of the error message handling in the Zend_Validate_* classes, the 
 * implementation and interface of this class is unnecessary obtuse; 
 * particularly with the get/setTemplates and get/setErrors methods.
 */
class Zend_Validate_Builder_ErrorManager implements Zend_Validate_Builder_ErrorManager_Interface
{
    /**
     * Collection of error messages, by field, validator class, and reason
     *
     * @var array
     */
    protected $_messages;

    /**
     * The subset of the error messages that are currently raised; representing 
     * fields that valdiators have failed, and produced errors for.
     *
     * @var array
     */
    protected $_raised;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->_messages = array();
        $this->_raised   = array();
    }

    /**
     * Set Message Template
     *
     * Assigns a specific message template to a Validator class. The message is 
     * assigned by field name, validator class, and reason code, so all 
     * arguments are required. The failure reason argument isn't optional as it 
     * is in the validator classes, because the validator classes have access to 
     * internal protected info this class doesn't.
     * 
     * @param string Field Name
     * @param string Validator class name
     * @param string Validation failure reason code
     * @param string Message template
     * @returns void
     * @throws Zend_Validate_Exception
     */
    public function setTemplate($field, $valClass, $reason, $message)
    {
        if (empty($field) || empty($valClass) || empty($reason) || empty($message)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Valdiate_Exception('All parameters are required');
        }

        $this->_messages[$field][$valClass][$reason] = $message;
    }

    /**
     * Set Message Templates
     *
     * Allows all the error message templates to be assigned at once, via an 
     * associative array. The array must be a three-level-deep set of nested 
     * arrays. The first level keys should be field names, the second level keys  
     * should be validator class names, and the third level keys should be 
     * validation failure reason codes. The values should be the actual error 
     * message templates. In other words, the array keys should all correspond 
     * to the $field, $valClass, and $reason arguments to setTemplate(), and the 
     * value should correspond to the $message argument of the same. Note that 
     * here, the $reason argument is not optional.
     * 
     * This method is useful in conjunction with a Zend_Config object to assign 
     * all the error messages from a config file. Suppose you had the errors in 
     * a section called 'Errors' in 'file.ini':
     *   <?php
     *   $cfg = new Zend_Config_Ini('/path/to/file.ini');
     *   $errorMgr->setMessages($cfg->Errors->toArray());
     *   // Same as calling setTemplate() on each message in the file
     *   ?>
     *
     * @param array List of error message templates
     * @returns void
     * @throws none
     */
    public function setTemplates(array $errors)
    {
        foreach ($errors as $field => $classes) {
            foreach ($classes as $class => $reasons) {
                foreach ($reasons as $reason => $template) {
                    $this->setTemplate($field, $class, $reason, $template);
                }
            }
        }
    }

    /**
     * Get Message Templates
     *
     * Returns error message templates, as they were defined. This does *not* 
     * include raised error messages; that is, messages after they become 
     * errors. This is merely the output analog of the setTemplate[s]() methods.
     *
     * @param string Optional field name
     * @param string Optional validator class name
     * @param string Optional validation failure reason code
     * @returns array
     * @throws none
     */
    public function getTemplates($field = null, $valClass = null, $reason = null)
    {
        if (!empty($field) && !empty($valClass) && !empty($reason)) {
            return $this->_messages[$field][$valClass][$reason];

        } else if (!empty($field) && !empty($valClass)) {
            return $this->_messages[$field][$valClass];

        } else if (!empty($field)) {
            return $this->_messages[$field];

        } else {
            return $this->_messages;
        }
    }

    /**
     * Raise
     *
     * Raises an error message for the given field. The validator instance that 
     * failed the field is needed to retrieve the right error message.
     * 
     * If not using its default error messages, the validator is used to 
     * substitute the placeholders in the custom error message templates.  
     * Therefore, the given validator instance must have a getMessageVariables() 
     * method in that case.
     *
     * @see Zend_Validate_Builder_ErrorManager_Interface
     *
     * @param string Field to raise an error message for
     * @param Zend_Validate_Interface Validator instance that failed the field
     * @returns void
     * @throws none
     */
    public function raise($field, Zend_Validate_Interface $val)
    {
        // Get *all* the errors raised by the validator
        $messages = $val->getMessages();
        $valClass = get_class($val);

        // If there are no custom messages defined, use the default messages 
        // returned by the validator.
        if (empty($this->_messages) || 
            empty($this->_messages[$field]) ||
            empty($this->_messages[$field][$valClass])) {

            $this->_raised[$field][$valClass] = $messages;

        } else {
            $templates = array_intersect_key($this->_messages[$field][$valClass], $messages);

            foreach ($templates as $reason => $msg) {
                $this->_raised[$field][$valClass][$reason] = 
                    $this->_createMessage($msg, $val);
            }
        }
    }

    /**
     * Get Messages
     *
     * Returns a set of *raised* error messages as an associative array. The set 
     * of errors returned is determined by the arguments passed: giving a field 
     * name returns just the errors for that field; giving a field and validator 
     * class name returns just the errors for that field by that class; giving 
     * all three arguments returns just one specific error message. Omitting all 
     * the arguments returns the entire set of error messages.
     *
     * IMHO, "getErrors" would be a better name, but this is trying to be 
     * consistent with the existing validators.
     *
     * @see Zend_Validate_Builder_ErrorManager_Interface
     *
     * @param string Optional field name
     * @param string Optional validator class name
     * @param string Optional validation failure reason code
     * @returns array
     * @throws none
     */
    public function getMessages($field = null, $valClass = null, $reason = null)
    {
        if (!$this->hasMessages($field, $valClass, $reason)) {
            return array();
        }

        if (!empty($field) && !empty($valClass) && !empty($reason)) {
            return $this->_raised[$field][$valClass][$reason];

        } else if (!empty($field) && !empty($valClass)) {
            return $this->_raised[$field][$valClass];

        } else if (!empty($field)) {
            return $this->_raised[$field];

        } else {
            return $this->_raised;
        }
    }

    /**
     * Has Messages
     *
     * Checks to see whether or not any errors have been raised. Takes the same 
     * filtering arguments that getErrors() does. Returns true if any errors 
     * have been raised (based on the arguments), false otherwise.
     *
     * IMHO, "hasErrors" would be a better name, but this is trying to be 
     * consistent with the existing validators.
     *
     * @see Zend_Validate_Builder_ErrorManager_Interface
     *
     * @param string Optional field name
     * @param string Optional validator class name
     * @param string Optional validation failure reason code
     * @returns bool
     * @throws none
     */
    public function hasMessages($field = null, $valClass = null, $reason = null)
    {
        if (!empty($field) && !empty($valClass) && !empty($reason)) {
            return !empty($this->_raised[$field][$valClass][$reason]);

        } else if (!empty($field) && !empty($valClass)) {
            return !empty($this->_raised[$field][$valClass]);

        } else if (!empty($field)) {
            return !empty($this->_raised[$field]);

        } else {
            return !empty($this->_raised);
        }
    }

    /**
     * Clear
     *
     * Clears the "raised" state of a set of errors. Takes the same filtering 
     * arguments as getErrors(). Omitting all the arguments resets all the 
     * errors back to the "no errors raised" state. The filtering ability is 
     * mainly useful just to Zend_Validate_Builder, which must not reset the 
     * state of all the errors when dealing with nested Builder objects.
     *
     * @see Zend_Validate_Builder_ErrorManager_Interface
     *
     * @param string Optional field name
     * @param string Optional validator class name
     * @param string Optional validation failure reason code
     * @returns void
     * @throws none
     */
    public function clear($field = null, $valClass = null, $reason = null)
    {
        if (!$this->hasMessages($field, $valClass, $reason)) {
            return;
        }

        if (!empty($field) && !empty($valClass) && !empty($reason)) {
            unset($this->_raised[$field][$valClass][$reason]);

        } else if (!empty($field) && !empty($valClass)) {
            unset($this->_raised[$field][$valClass]);

        } else if (!empty($field)) {
            unset($this->_raised[$field]);

        } else {
            $this->_raised = array();
        }
    }

    /**
     * Create Message
     *
     * Substitutes actual values into an error message template, and returns the 
     * result. The message template should be in the exact same format of the 
     * message templates used in the standard Zend_Validate_* classes. This only 
     * does something if the given message contains any substitution variables.
     *
     * @param string Message template
     * @param Zend_Validate_Interface Validator instance that raised the error
     * @returns string Message template with parameters substituted
     * @throws Zend_Validate_Exception
     */
    protected function _createMessage($message, Zend_Validate_Interface $val)
    {
        // Sure, this could be more accurate, but it's fine for now
        if (false === strpos($message, '%')) {
            return $message;
        }

        // The getMessageVariables() method isn't part of the interface.
        if (!method_exists($val, 'getMessageVariables')) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('The given validator instance must implement a getMessageVariables() method');
        }

        // This uses data exposed via Zend_Validate_Abstract::__get()
        $message = str_replace('%value%', $val->value, $message);
        foreach ($val->getMessageVariables() as $property) {
            $message = str_replace("%$property%", $val->$property, $message);
        }

        return $message;
    }
}
