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

require_once 'Zend/Validate/Builder.php';
require_once 'Zend/Validate/Builder/FluentAdder.php';
require_once 'Zend/Validate/Builder/ValidatorFactory.php';

/**
 * Zend Validate Builder Fluent Facade
 *
 * Provides an easy-to-use fluent facade interface to Zend_Validate_Builder. By 
 * default, it uses the Zend_Validate_Builder_ValidatorFactory class to provide 
 * the auto-instantiation feature, but that can be easily replaced with a call 
 * to setFactory().
 */
class Zend_Validate_Builder_FluentFacade implements Zend_Validate_Interface
{
    /**
     * The Builder object we're facading
     *
     * @var Zend_Validate_Builder
     */
    protected $_builder;

    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    /**
     * Validator Factory
     *
     * @var Zend_Validate_Builder_ValidatorFactory_Interface
     */
    protected $_factory;


    /**
     * Constructor
     *
     * @param Zend_Validate_Builder
     * @param array Optional options
     */
    public function __construct(Zend_Validate_Builder $builder, array $options = array())
    {
        $this->_builder = $builder;
        $this->_factory = null;

        $this->setOptions($options);
    }

    /**
     * Get Builder
     *
     * @param void
     * @returns Zend_Validate_Builder The current Builder object
     * @throws none
     */
    public function getBuilder()
    {
        return $this->_builder;
    }

    /**
     * Set Builder
     *
     * @param Zend_Validate_Builder
     * @returns void
     * @throws none
     */
    public function setBuilder(Zend_Validate_Builder $builder)
    {
        $this->_builder = $builder;
    }

    /**
     * Get Factory
     *
     * Sort of a "Factory factory". Returns the validator factory assigned to 
     * this object, or creates a new default instance of one, if it doesn't 
     * already exist. This is purely for convenience when not using a custom 
     * validator factory class.
     *
     * @returns Zend_Validate_Builder_ValidatorFactory_Interface
     */
    public function getFactory()
    {
        if (empty($this->_factory)) {
            $this->_factory = new Zend_Validate_Builder_ValidatorFactory($this->_options);
        }
        return $this->_factory;
    }

    /**
     * Set Factory
     *
     * Assigns the object that will be used to generate validator instances.
     *
     * @param Zend_Validate_Builder_ValidatorFactory_Interface
     */
    public function setFactory(Zend_Validate_Builder_ValidatorFactory_Interface $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * Get Options
     *
     * @returns array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set Options
     *
     * @param array
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
    }

    /**
     * Glob
     *
     * Allows adding validators to all the fields that match the given pattern.  
     * This delegates the actual pattern matching to the underlying Builder 
     * object.
     *
     * This returns a special-purpose object that allows subsequent calls in the 
     * fluent interface to be associated with the fields specified here. This 
     * object is not intended to ever be instantiated in userland code.
     *
     * @param string Pattern to match fields
     * @returns Zend_Validate_Builder_FluentAdder
     * @throws none
     */
    public function glob($pattern)
    {
        return $this->_getAdder($pattern, Zend_Validate_Builder::PATTERN);
    }

    /**
     * Each
     *
     * Allows adding validators to each of the fields passed at once. This 
     * method can be called two ways: with an array of field names in the first 
     * parameter; or with multiple parameters, where each parameter is a field 
     * name.
     *
     * This returns a special-purpose object that allows subsequent calls in the 
     * fluent interface to be associated with the fields specified here. This 
     * object is not intended to ever be instantiated in userland code.
     *
     * @param array|string Field name(s)
     * @param string ...,n Each subsequent argument is a field name
     * @returns Zend_Validate_Builder_FluentAdder
     * @throws none
     */
    public function each()
    {
        $args = func_get_args();

        if (is_array($args[0])) {
            return $this->_getAdder($args[0]);
        } else {
            return $this->_getAdder($args);
        }
    }

    /**
     * Group
     *
     * Allows the given group of fields to passed as a single array to 
     * validators. This is called just like each(), but is equivalent to setting 
     * the PASSGROUP flag on the underlying Builder object.
     *
     * This returns a special-purpose object that allows subsequent calls in the 
     * fluent interface to be associated with the fields specified here. This 
     * object is not intended to ever be instantiated in userland code.
     *
     * @param array|string Field name(s)
     * @param string ...,n Each subsequent argument is a field name
     * @returns Zend_Validate_Builder_FluentAdder
     * @throws none
     */
    public function group()
    {
        $args = func_get_args();

        if (is_array($args[0])) {
            return $this->_getAdder($args[0], Zend_Validate_Builder::PASS_GROUP);
        } else {
            return $this->_getAdder($args, Zend_Validate_Builder::PASS_GROUP);
        }
    }

    /**
     * Get magic method
     *
     * Access to undefined properties is mapped to a call to 
     * Zend_Validate_Builder::add() for a single field.
     *
     * This returns a special-purpose object that allows subsequent calls in the 
     * fluent interface to be associated with the fields specified here. This 
     * object is not intended to ever be instantiated in userland code.
     *
     * @param string Field name
     * @returns Zend_Validate_Builder_FluentAdder
     * @throws none
     */
    public function __get($field)
    {
        return $this->_getAdder($field);
    }

    /**
     * Call magic method
     *
     * Access to undefined methods attempts to create a new validator based on 
     * the name of the method. Quick way to pass validators to other validators.
     *
     * @param string "Base name" of the validator
     * @param array Constructor arguments
     * @returns Zend_Validate_Interface Validator object
     * @throws none
     */
    public function __call($name, $args)
    {
        return $this->getFactory()->create($name, $args);
    }

    /**
     * Get Adder
     *
     * Returns an instance of the special-purpose class 
     * Zend_Validate_Builder_FluentAdder.
     *
     * @param string|array Field(s) the validators will attach to
     * @param int Field flags, as in the 3rd param to ZVB::add()
     * @returns Zend_Validate_Builder_FluentAdder
     * @throws none
     */
    protected function _getAdder($fieldSpec, $flags = 0)
    {
        return new Zend_Validate_Builder_FluentAdder($this->_builder,
                                                     $this->getFactory(),
                                                     $fieldSpec,
                                                     $flags);
    }

    // Interface Zend_Validate_Interface

    /**
     * Proxies to the underlying Builder class.
     *
     * @see Zend_Validate_Interface::isValid()
     */
    public function isValid($data)
    {
        return $this->_builder->isValid($data);
    }

    /**
     * Proxies to the underlying Builder class.
     *
     * @see Zend_Validate_Interface::getMessages()
     *
     * @return array
     */
    public function getMessages()
    {
        return $this->_builder->getMessages();
    }

    /**
     * Proxies to the underlying Builder class.
     *
     * @see Zend_Validate_Interface::getErrors()
     * @deprecated Since 1.5.0
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->_builder->getErrors();
    }
}
