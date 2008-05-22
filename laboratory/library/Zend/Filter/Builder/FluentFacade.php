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
 * @package    Zend_Filter
 * @subpackage Zend_Filter_Builder
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Zend/Filter/Builder.php';
require_once 'Zend/Filter/Builder/FluentAdder.php';
require_once 'Zend/Filter/Builder/FilterFactory.php';

/**
 * Zend Filter Builder Fluent Facade
 *
 * Provides an easy-to-use fluent facade interface to Zend_Filter_Builder.
 *
 * This facade searches multiple namespaces for Zend_Filter_Interface classes.  
 * If there's a name conflict across namespaces, the first one found will always 
 * be chosen. It's not possible to select any of the others through the facade 
 * at this time.
 */
class Zend_Filter_Builder_FluentFacade implements Zend_Filter_Interface
{
    /**
     * The Builder object we're facading
     *
     * @var Zend_Filter_Builder
     */
    protected $_builder;

    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    /**
     * Filter Factory
     *
     * @var Zend_Filter_Builder_FilterFactory_Interface
     */
    protected $_factory;


    /**
     * Constructor
     *
     * @param Zend_Filter_Builder
     * @param array Optional options
     */
    public function __construct(Zend_Filter_Builder $builder, array $options = array())
    {
        $this->_builder = $builder;
        $this->setOptions($options);
    }

    /**
     * Get Builder
     *
     * @param void
     * @returns Zend_Filter_Builder The current Builder object
     * @throws none
     */
    public function getBuilder()
    {
        return $this->_builder;
    }

    /**
     * Set Builder
     *
     * @param Zend_Filter_Builder
     * @returns void
     * @throws none
     */
    public function setBuilder(Zend_Filter_Builder $builder)
    {
        $this->_builder = $builder;
    }

    /**
     * Get Factory
     *
     * Sort of a "Factory factory". Returns the filter factory assigned to 
     * this class, or creates a new default instance of one, if it doesn't 
     * already exist. This is purely for convenience when not using a custom 
     * filter factory class.
     *
     * @returns Zend_Filter_Builder_FilterFactory_Interface
     */
    public function getFactory()
    {
        if (empty($this->_factory)) {
            $this->_factory = new Zend_Filter_Builder_FilterFactory($this->_options);
        }
        return $this->_factory;
    }

    /**
     * Set Factory
     *
     * Assigns the object that will be used to generate filter instances.
     *
     * @param Zend_Filter_Builder_FilterFactory_Interface
     */
    public function setFactory(Zend_Filter_Builder_FilterFactory_Interface $factory)
    {
        $this->_factory = $factory;
    }

    /**
     * Get Options
     *
     * @param void
     * @returns array
     * @throws none
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Set Options
     *
     * @param array
     * @returns void
     * @throws none
     */
    public function setOptions(array $options)
    {
        $this->_options = $options;
    }

    /**
     * Glob
     *
     * Allows adding filters to all the fields that match the given pattern.  
     * This delegates the actual pattern matching to the underlying Builder 
     * object.
     *
     * This returns a special-purpose object that allows subsequent calls in the 
     * fluent interface to be associated with the fields specified here. This 
     * object is not intended to ever be instantiated in userland code.
     *
     * @param string Pattern to match fields
     * @returns Zend_Filter_Builder_FluentAdder
     * @throws none
     */
    public function glob($pattern)
    {
        return $this->_getAdder($pattern, Zend_Filter_Builder::PATTERN);
    }

    /**
     * Each
     *
     * Allows adding filters to each in a group of fields. This method can be 
     * called two ways: with an array of field names in the first parameter; or 
     * with multiple parameters, where each parameter is a field name.
     *
     * This returns a special-purpose object that allows subsequent calls in the 
     * fluent interface to be associated with the fields specified here. This 
     * object is not intended to ever be instantiated in userland code.
     *
     * @param array|string Field name(s)
     * @param string ...,n Each subsequent argument is a field name
     * @returns Zend_Filter_Builder_FluentAdder
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
     * Get magic method
     *
     * Access to undefined properties is mapped to a call to 
     * Zend_Filter_Builder::add() for a single field.
     *
     * This returns a special-purpose object that allows subsequent calls in the 
     * fluent interface to be associated with the fields specified here. This 
     * object is not intended to ever be instantiated in userland code.
     *
     * @param string Field name
     * @returns Zend_Filter_Builder_FluentAdder
     * @throws none
     */
    public function __get($field)
    {
        return $this->_getAdder($field);
    }

    /**
     * Call magic method
     *
     * Access to undefined methods attempts to create a new filter based on the 
     * name of the method. Quick way to pass filters to other filters.
     *
     * @param string "Base name" of the filter
     * @param array Constructor arguments
     * @returns Zend_Filter_Interface Filter object
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
     * Zend_Filter_Builder_FluentAdder.
     *
     * @param string|array Field(s) the validators will attach to
     * @param int Field flags, as in the 3rd param to ZFB::add()
     * @returns Zend_Filter_Builder_FluentAdder
     * @throws none
     */
    protected function _getAdder($fieldSpec, $flags = 0)
    {
        return new Zend_Filter_Builder_FluentAdder($this->_builder,
                                                   $this->getFactory(),
                                                   $fieldSpec,
                                                   $flags);
    }

    // Interface Zend_Filter_Interface

    /**
     * Filter
     *
     * Just proxies to the Builder object
     *
     * @see Zend_Filter_Builder::filter()
     */
    public function filter($data)
    {
        return $this->_builder->filter($data);
    }
}
