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

/**
 * Zend Filter Builder FluentAdder
 *
 * Special-purpose class that facilitates adding Filters through the fluent 
 * Facade.
 */
class Zend_Filter_Builder_FluentAdder
{
    /**
     * The Builder object being facaded
     *
     * @var Zend_Filter_Builder
     */
    protected $_builder;

    /**
     * Factory object to use to create filter instances
     *
     * @var Zend_Filter_Builder_FilterFactory_Interface
     */
    protected $_factory;

    /**
     * The field or fields we're adding Filters to
     *
     * @var string|array
     */
    protected $_fieldSpec;

    /**
     * Field flags to set on each added filter
     *
     * @var int
     */
    protected $_flags;

    /**
     * List of the filter indexes in the Builder that were added by this 
     * FluentAdder. Useful if we needed to go back and set flags for any of the 
     * added fields.
     *
     * @var array
     */
    protected $_rowIds;


    /**
     * Constructor
     *
     * @param Zend_Filter_Builder
     * @param Zend_Filter_Builder_FilterFactory_Interface Factory object 
     * that can create filters
     * @param string|array Field name(s)
     * @param int Field flags, defaults to none
     * @returns void
     * @throws Zend_Filter_Exception
     */
    public function __construct(Zend_Filter_Builder $builder,
                                Zend_Filter_Builder_FilterFactory_Interface $factory,
                                $fieldSpec,
                                $flags = 0)
    {
        if (empty($fieldSpec)) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('The field specification parameter is required');
        }

        $this->_builder   = $builder;
        $this->_factory   = $factory;
        $this->_fieldSpec = $fieldSpec;
        $this->_flags     = $flags;
        $this->_rowIds    = array();
    }

    /**
     * Call magic method
     *
     * Maps the method call to a filter name and adds that filter to the 
     * Builder object.
     *
     * @param string Base name of the filter
     * @param array Constructor arguments
     * @returns Zend_Filter_Builder_FluentAdder
     * @throws none
     */
    public function __call($name, $args)
    {
        $this->_rowIds[] = $this->_builder->add(
            $this->_factory->create($name, $args),
            $this->_fieldSpec,
            $this->_flags
        );

        return $this;
    }
}
