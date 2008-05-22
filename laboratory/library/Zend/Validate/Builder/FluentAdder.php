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
require_once 'Zend/Validate/Builder/ValidatorFactory/Interface.php';

/**
 * Zend Validate Builder FluentAdder
 *
 * Special-purpose class that facilitates adding Validators through the fluent 
 * Facade.
 */
class Zend_Validate_Builder_FluentAdder
{
    /**
     * The Builder object being facaded
     *
     * @var Zend_Validate_Builder
     */
    protected $_builder;

    /**
     * Factory object to use to create validator instances
     *
     * @var Zend_Validate_Builder_ValidatorFactory_Interface
     */
    protected $_factory;

    /**
     * The field or fields we're adding Validators to
     *
     * @var string|array
     */
    protected $_fieldSpec;

    /**
     * Field flags to set on each added validator
     *
     * @var int
     */
    protected $_flags;

    /**
     * List of the validator indexes in the Builder that were added by this 
     * FluentAdder.
     *
     * @var array
     */
    protected $_rowIds;


    /**
     * Constructor
     *
     * @param Zend_Validate_Builder
     * @param Zend_Validate_Builder_ValidatorFactory_Interface Factory object 
     * that can create validators
     * @param string|array Field name(s)
     * @param int Field flags, defaults to none
     * @returns void
     * @throws Zend_Validate_Exception
     */
    public function __construct(Zend_Validate_Builder $builder, 
                                Zend_Validate_Builder_ValidatorFactory_Interface $factory, 
                                $fieldSpec,
                                $flags = 0)
    {
        if (empty($fieldSpec)) {
            require_once 'Zend/Validate/Exception.php';
            throw new Zend_Validate_Exception('The field specification parameter is required');
        }

        $this->_builder   = $builder;
        $this->_factory   = $factory;
        $this->_fieldSpec = $fieldSpec;
        $this->_flags     = $flags;
        $this->_rowIds    = array();
    }

    /**
     * Optional
     *
     * This allows the OPTIONAL flag to be set through the fluent Facade. The 
     * presence of this method means that no validator with a base name of 
     * "Optional" could ever by set through the Facade. Hopefully this isn't 
     * likely to happen...
     *
     * NOTE: Currently, this does not work when the field whose flag is to be 
     * toggled is actually a pattern. It only works with plain field names and 
     * arrays of field names.
     *
     * @param bool Optional flag value. Default: true
     * @returns Zend_Validate_Builder_FluentAdder
     * @throws Zend_Validate_Exception
     */
    public function optional($value = true)
    {
        foreach ($this->_rowIds as $id) {
            $value && $this->_builder->addFlags($id, Zend_Validate_Builder::OPTIONAL);
        }

        return $this;
    }

    /**
     * Call magic method
     *
     * Maps the method call to a validator name and adds that validator to the 
     * Builder object.
     *
     * @param string Base name of the validator
     * @param array Constructor arguments
     * @returns Zend_Validate_Builder_FluentAdder
     * @throws none
     */
    public function __call($name, $args)
    {
        // Append the returned id to our list
        $this->_rowIds[] = $this->_builder->add(
            $this->_factory->create($name, $args),
            $this->_fieldSpec,
            $this->_flags
        );

        return $this;
    }
}
