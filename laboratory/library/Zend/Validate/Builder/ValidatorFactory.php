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

require_once 'Zend/Loader.php';
require_once 'Zend/Validate/Builder/ValidatorFactory/Interface.php';

/**
 * Zend Validate Builder Validator Factory
 *
 * Instantiates Validator objects, given only the "base name" of the validator 
 * class. A set of "namespace" class prefixes to search in can be specified in 
 * the options. This class always includes 'Zend_Validate_' at the end of the 
 * namespace list. The list of namespaces is searched through, and the first 
 * valid class that matches the given base name is instantiated. So, if there 
 * are multiple potential matches, only the first one is used, and there's no 
 * way to select any of the others without altering the order of the namespaces.
 *
 * This can be used as a quick way to create validator objects without having to 
 * manually include the class file and instantiate the object. The 
 * Zend_Validate_Builder_FluentFacade (and _FluentAdder) classes also use this 
 * class by default to provide their auto-instantiation functionality.
 *
 */
class Zend_Validate_Builder_ValidatorFactory implements Zend_Validate_Builder_ValidatorFactory_Interface
{
    /**
     * Options
     *
     * @var array
     */
    protected $_options = array();


    /**
     * Constructor
     *
     * @param array Optional array of options
     */
    public function __construct(array $options = array())
    {
        $this->setOptions($options);
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
        if (empty($this->_options['namespaces'])) {
            $this->_options['namespaces'] = array('Zend_Validate_');
        }

        // If the 'namespaces' option exists, process it
        if (array_key_exists('namespaces', $options) &&
            is_array($options['namespaces'])) {

            // Prepend all the given namespaces to the existing set, in the same 
            // order specified by the user
            foreach (array_reverse($options['namespaces']) as $ns) {
                array_unshift($this->_options['namespaces'], $ns);
            }
        }
    }

    /**
     * Create
     *
     * Factory method that produces an instance of Zend_Validate_Interface. It 
     * takes the base name of the validator class, and the list of namespaces in 
     * the options and returns an instance of the first matching class found.
     *
     * @param string Validator class base name
     * @param array Optional constructor arguments for the validator
     * @returns Zend_Validate_Interface
     * @throws none
     */
    public function create($name, $args)
    {
        $class = $this->namespaceLoad($name, $this->_options['namespaces']);

        $valClass  = new ReflectionClass($class);
        $validator = $valClass->newInstanceArgs($args);

        return $validator;
    }

    /**
     * Namespace Load
     *
     * Operates a lot like Zend_Loader::loadClass(), but instead of taking a 
     * full class name and a list of directories, it takes a class base name and 
     * a list of namespaces. It includes the first matching class, and returns 
     * the full name of the found class. If no such class is found, an exception 
     * is thrown.
     *
     * @param string Class base name
     * @param array Optional list of namespace class prefixes
     * @param string|array Optional list of directories to search in
     * @returns string Full name of the first matching class
     * @throws Zend_Exception
     */
    public function namespaceLoad($basename, array $namespaces = null, $dirs = null)
    {
        $basename = ucfirst($basename);

        if (null == $namespaces) {
           Zend_Loader::loadClass($basename, $dirs); 
           return $basename;
        }

        foreach ($namespaces as $ns) {
            try {
                $fullClass = rtrim($ns, '_').'_'.$basename;
                Zend_Loader::loadClass($fullClass, $dirs);

                // If the class was successfully loaded, return the name
                return $fullClass;

            } catch (Zend_Exception $e) {
                continue;
            }
        }

        throw new Zend_Exception("No class with base name '$basename' was found within any of the given namespaces, within the include path. Namespaces searched: ".implode(', ', (array)$namespaces));
    }
}
