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
 * @package    Zend_Form
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Zend_Form_DisplayGroup
 * 
 * @category   Zend
 * @package    Zend_Form
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */
class Zend_Form_DisplayGroup implements Iterator,Countable
{
    /**
     * Group attributes
     * @var array
     */
    protected $_attribs = array();

    /**
     * Display group decorators
     * @var array
     */
    protected $_decorators = array();

    /**
     * Description
     * @var string
     */
    protected $_description;

    /**
     * Element order
     * @var array
     */
    protected $_elementOrder = array();

    /**
     * Elements
     * @var array
     */
    protected $_elements = array();

    /**
     * Whether or not a new element has been added to the group
     * @var bool
     */
    protected $_groupUpdated = false;

    /**
     * Plugin loader for decorators
     * @var Zend_Loader_PluginLoader
     */
    protected $_loader;

    /**
     * Group name
     * @var string
     */
    protected $_name;

    /**
     * Group order
     * @var int
     */
    protected $_order;

    /**
     * @var Zend_Translate
     */
    protected $_translator;

    /**
     * @var Zend_View_Interface
     */
    protected $_view;

    /**
     * Constructor
     * 
     * @param  string $name 
     * @param  Zend_Loader_PluginLoader $loader 
     * @param  array|Zend_Config $options 
     * @return void
     */
    public function __construct($name, Zend_Loader_PluginLoader $loader, $options = null)
    {
        $this->setName($name);

        $this->setPluginLoader($loader);

        if (is_array($options)) {
            $this->setOptions($options);
        } elseif ($options instanceof Zend_Config) {
            $this->setConfig($options);
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormElements')
                 ->addDecorator('HtmlTag', array('tag' => 'dl'))
                 ->addDecorator('Fieldset')
                 ->addDecorator('DtDdWrapper');
        }
    }

    /**
     * Set options
     * 
     * @param  array $options 
     * @return Zend_Form_DisplayGroup
     */
    public function setOptions(array $options)
    {
        $forbidden = array(
            'Options', 'Config', 'PluginLoader', 'View',
            'Translator', 'Attrib'
        );
        foreach ($options as $key => $value) {
            $normalized = ucfirst($key);

            if (in_array($normalized, $forbidden)) {
                continue;
            }

            $method = 'set' . $normalized;
            if (method_exists($this, $method)) {
                $this->$method($value);
            } else {
                $this->setAttrib($key, $value);
            }
        }
        return $this;
    }

    /**
     * Set options from config object
     * 
     * @param  Zend_Config $config 
     * @return Zend_Form_DisplayGroup
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * Set group attribute
     * 
     * @param  string $key 
     * @param  mixed $value 
     * @return Zend_Form_DisplayGroup
     */
    public function setAttrib($key, $value)
    {
        $key = (string) $key;
        $this->_attribs[$key] = $value;
        return $this;
    }

    /**
     * Add multiple form attributes at once
     * 
     * @param  array $attribs 
     * @return Zend_Form_DisplayGroup
     */
    public function addAttribs(array $attribs)
    {
        foreach ($attribs as $key => $value) {
            $this->setAttrib($key, $value);
        }
        return $this;
    }

    /**
     * Set multiple form attributes at once
     *
     * Overwrites any previously set attributes.
     * 
     * @param  array $attribs 
     * @return Zend_Form_DisplayGroup
     */
    public function setAttribs(array $attribs)
    {
        $this->clearAttribs();
        return $this->addAttribs($attribs);
    }

    /**
     * Retrieve a single form attribute
     * 
     * @param  string $key 
     * @return mixed
     */
    public function getAttrib($key)
    {
        $key = (string) $key;
        if (!isset($this->_attribs[$key])) {
            return null;
        }

        return $this->_attribs[$key];
    }

    /**
     * Retrieve all form attributes/metadata
     * 
     * @return array
     */
    public function getAttribs()
    {
        return $this->_attribs;
    }

    /**
     * Remove attribute
     * 
     * @param  string $key 
     * @return bool
     */
    public function removeAttrib($key)
    {
        if (array_key_exists($key, $this->_attribs)) {
            unset($this->_attribs[$key]);
            return true;
        }

        return false;
    }

    /**
     * Clear all form attributes
     * 
     * @return Zend_Form
     */
    public function clearAttribs()
    {
        $this->_attribs = array();
        return $this;
    }

    /**
     * Filter a name to only allow valid variable characters
     * 
     * @param  string $value 
     * @return string
     */
    public function filterName($value)
    {
        return preg_replace('/[^a-zA-Z0-9_\x7f-\xff]/', '', (string) $value);
    }

    /**
     * Set group name
     * 
     * @param  string $name 
     * @return Zend_Form_DisplayGroup
     */
    public function setName($name)
    {
        $name = $this->filtername($name);
        if (('0' !== $name) && empty($name)) {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid name provided; must contain only valid variable characters and be non-empty');
        }

        $this->_name = $name;
        return $this;
    }

    /**
     * Retrieve group name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Set group legend
     * 
     * @param  string $legend 
     * @return Zend_Form_DisplayGroup
     */
    public function setLegend($legend)
    {
        return $this->setAttrib('legend', (string) $legend);
    }

    /**
     * Retrieve group legend
     * 
     * @return string
     */
    public function getLegend()
    {
        return $this->getAttrib('legend');
    }

    /**
     * Set description
     * 
     * @param  string $value 
     * @return Zend_Form_DisplayGroup
     */
    public function setDescription($value)
    {
        $this->_description = (string) $value;
        return $this;
    }

    /**
     * Get description
     * 
     * @return string
     */
    public function getDescription()
    {
        return $this->_description;
    }

    /**
     * Set group order
     * 
     * @param  int $order 
     * @return Zend_Form_Element
     */
    public function setOrder($order)
    {
        $this->_order = (int) $order;
        return $this;
    }

    /**
     * Retrieve group order
     * 
     * @return int
     */
    public function getOrder()
    {
        return $this->_order;
    }

    // Elements

    /**
     * Add element to stack
     * 
     * @param  Zend_Form_Element $element 
     * @return Zend_Form_DisplayGroup
     */
    public function addElement(Zend_Form_Element $element)
    {
        $this->_elements[$element->getName()] = $element;
        $this->_groupUpdated = true;
        return $this;
    }

    /**
     * Add multiple elements at once
     * 
     * @param  array $elements 
     * @return Zend_Form_DisplayGroup
     * @throws Zend_Form_Exception if any element is not a Zend_Form_Element
     */
    public function addElements(array $elements)
    {
        foreach ($elements as $element) {
            if (!$element instanceof Zend_Form_Element) {
                require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('elements passed via array to addElements() must be Zend_Form_Elements only');
            }
            $this->addElement($element);
        }
        return $this;
    }

    /**
     * Set multiple elements at once (overwrites)
     * 
     * @param  array $elements 
     * @return Zend_Form_DisplayGroup
     */
    public function setElements(array $elements)
    {
        $this->clearElements();
        return $this->addElements($elements);
    }

    /**
     * Retrieve element
     * 
     * @param  string $name 
     * @return Zend_Form_Element|null
     */
    public function getElement($name)
    {
        $name = (string) $name;
        if (isset($this->_elements[$name])) {
            return $this->_elements[$name];
        }

        return null;
    }

    /**
     * Retrieve elements
     * @return array
     */
    public function getElements()
    {
        return $this->_elements;
    }

    /**
     * Remove a single element
     * 
     * @param  string $name 
     * @return boolean
     */
    public function removeElement($name)
    {
        $name = (string) $name;
        if (array_key_exists($name, $this->_elements)) {
            unset($this->_elements[$name]);
            $this->_groupUpdated = true;
            return true;
        }

        return false;
    }

    /**
     * Remove all elements
     * 
     * @return Zend_Form_DisplayGroup
     */
    public function clearElements()
    {
        $this->_elements = array();
        $this->_groupUpdated = true;
        return $this;
    }

    // Plugin loader (for decorators)

    /**
     * Set plugin loader
     * 
     * @param  Zend_Loader_PluginLoader $loader 
     * @return Zend_Form_DisplayGroup
     */
    public function setPluginLoader(Zend_Loader_PluginLoader $loader)
    {
        $this->_loader = $loader;
        return $this;
    }

    /**
     * Retrieve plugin loader
     * 
     * @return Zend_Loader_PluginLoader
     */
    public function getPluginLoader()
    {
        return $this->_loader;
    }

    /**
     * Add a prefix path for the plugin loader
     * 
     * @param  string $prefix 
     * @param  string $path 
     * @return Zend_Form_DisplayGroup
     */
    public function addPrefixPath($prefix, $path)
    {
        $this->getPluginLoader()->addPrefixPath($prefix, $path);
        return $this;
    }

    /**
     * Add several prefix paths at once
     * 
     * @param  array $spec 
     * @return Zend_Form_DisplayGroup
     */
    public function addPrefixPaths(array $spec)
    {
        if (isset($spec['prefix']) && isset($spec['path'])) {
            return $this->addPrefixPath($spec['prefix'], $spec['path']);
        } 
        foreach ($spec as $prefix => $paths) {
            if (is_numeric($prefix) && is_array($paths)) {
                $prefix = null;
                if (isset($paths['prefix']) && isset($paths['path'])) {
                    $this->addPrefixPath($paths['prefix'], $paths['path']);
                }
            } elseif (!is_numeric($prefix)) {
                if (is_string($paths)) {
                    $this->addPrefixPath($prefix, $paths);
                } elseif (is_array($paths)) {
                    foreach ($paths as $path) {
                        $this->addPrefixPath($prefix, $path);
                    }
                }
            }
        }
        return $this;
    }

    // Decorators

    /**
     * Instantiate a decorator based on class name or class name fragment
     * 
     * @param  string $name 
     * @param  null|array $options 
     * @return Zend_Form_Decorator_Interface
     */
    protected function _getDecorator($name, $options)
    {
        $class = $this->getPluginLoader()->load($name);
        if (null === $options) {
            $decorator = new $class;
        } else {
            $r = new ReflectionClass($class);
            $decorator = $r->newInstance($options);
        }

        return $decorator;
    }

    /**
     * Add a decorator for rendering the group
     * 
     * @param  string|Zend_Form_Decorator_Interface $decorator 
     * @param  array|Zend_Config $options Options with which to initialize decorator
     * @return Zend_Form_DisplayGroup
     */
    public function addDecorator($decorator, $options = null)
    {
        if ($decorator instanceof Zend_Form_Decorator_Interface) {
            $name = get_class($decorator);
        } elseif (is_string($decorator)) {
            $decorator = $this->_getDecorator($decorator, $options);
            $name = get_class($decorator);
        } elseif (is_array($decorator)) {
            foreach ($decorator as $name => $spec) {
                break;
            }
            if (is_numeric($name)) {
                require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Invalid alias provided to addDecorator; must be alphanumeric string');
            }
            if (is_string($spec)) {
                $decorator = $this->_getDecorator($spec, $options);
            } elseif ($spec instanceof Zend_Form_Decorator_Interface) {
                $decorator = $spec;
            }
        } else {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid decorator provided to addDecorator; must be string or Zend_Form_Decorator_Interface');
        }

        $this->_decorators[$name] = $decorator;

        return $this;
    }

    /**
     * Add many decorators at once
     * 
     * @param  array $decorators 
     * @return Zend_Form_DisplayGroup
     */
    public function addDecorators(array $decorators)
    {
        foreach ($decorators as $decoratorInfo) {
            if (is_string($decoratorInfo)) {
                $this->addDecorator($decoratorInfo);
            } elseif ($decoratorInfo instanceof Zend_Form_Decorator_Interface) {
                $this->addDecorator($decoratorInfo);
            } elseif (is_array($decoratorInfo)) {
                $argc    = count($decoratorInfo);
                $options = array();
                if (isset($decoratorInfo['decorator'])) {
                    $decorator = $decoratorInfo['decorator'];
                    if (isset($decoratorInfo['options'])) {
                        $options = $decoratorInfo['options'];
                    }
                    $this->addDecorator($decorator, $options);
                } else {
                    switch (true) {
                        case (0 == $argc):
                            break;
                        case (1 <= $argc):
                            $decorator  = array_shift($decoratorInfo);
                        case (2 <= $argc):
                            $options = array_shift($decoratorInfo);
                        default:
                            $this->addDecorator($decorator, $options);
                            break;
                    }
                }
            } else {
                require_once 'Zend/Form/Exception.php';
                throw new Zend_Form_Exception('Invalid decorator passed to addDecorators()');
            }
        }

        return $this;
    }

    /**
     * Overwrite all decorators
     * 
     * @param  array $decorators 
     * @return Zend_Form_DisplayGroup
     */
    public function setDecorators(array $decorators)
    {
        $this->clearDecorators();
        return $this->addDecorators($decorators);
    }

    /**
     * Retrieve a registered decorator
     * 
     * @param  string $name 
     * @return false|Zend_Form_Decorator_Abstract
     */
    public function getDecorator($name)
    {
        if (!isset($this->_decorators[$name])) {
            $decorators = array_keys($this->_decorators);
            $len = strlen($name);
            foreach ($decorators as $decorator) {
                if ($len > strlen($decorator)) {
                    continue;
                }
                if (0 === substr_compare($decorator, $name, -$len, $len, true)) {
                    return $this->_decorators[$decorator];
                }
            }
            return false;
        }

        return $this->_decorators[$name];
    }

    /**
     * Retrieve all decorators
     * 
     * @return array
     */
    public function getDecorators()
    {
        return $this->_decorators;
    }

    /**
     * Remove a single decorator
     * 
     * @param  string $name 
     * @return bool
     */
    public function removeDecorator($name)
    {
        $decorator = $this->getDecorator($name);
        if ($decorator) {
            $name = get_class($decorator);
            unset($this->_decorators[$name]);
            return true;
        }

        return false;
    }

    /**
     * Clear all decorators
     * 
     * @return Zend_Form_DisplayGroup
     */
    public function clearDecorators()
    {
        $this->_decorators = array();
        return $this;
    }

    /**
     * Set view
     * 
     * @param  Zend_View_Interface $view 
     * @return Zend_Form_DisplayGroup
     */
    public function setView(Zend_View_Interface $view = null)
    {
        $this->_view = $view;
        return $this;
    }

    /**
     * Retrieve view
     * 
     * @return Zend_View_Interface
     */
    public function getView()
    {
        return $this->_view;
    }

    /**
     * Render display group
     * 
     * @return string
     */
    public function render(Zend_View_Interface $view = null)
    {
        if (null !== $view) {
            $this->setView($view);
        }
        $content = '';
        foreach ($this->getDecorators() as $decorator) {
            $decorator->setElement($this);
            $content = $decorator->render($content);
        }
        return $content;
    }

    /**
     * String representation of group
     * 
     * @return string
     */
    public function __toString()
    {
        try {
            $return = $this->render();
            return $return;
        } catch (Exception $e) {
            trigger_error($e->getMessage(), E_USER_WARNING);
            return '';
        }
    }

    /**
     * Set translator object
     * 
     * @param  Zend_Translate|Zend_Translate_Adapter|null $translator 
     * @return Zend_Form_DisplayGroup
     */
    public function setTranslator($translator = null)
    {
        if ((null === $translator) || ($translator instanceof Zend_Translate_Adapter)) {
            $this->_translator = $translator;
        } elseif ($translator instanceof Zend_Translate) {
            $this->_translator = $translator->getAdapter();
        } else {
            require_once 'Zend/Form/Exception.php';
            throw new Zend_Form_Exception('Invalid translator specified');
        }
        return $this;
    }

    /**
     * Retrieve translator object
     * 
     * @return Zend_Translate_Adapter|null
     */
    public function getTranslator()
    {
        if (null === $this->_translator) {
            require_once 'Zend/Form.php';
            return Zend_Form::getDefaultTranslator();
        }

        return $this->_translator;
    }

    // Interfaces: Iterator, Countable

    /**
     * Current element
     * 
     * @return Zend_Form_Element
     */
    public function current()
    {
        $this->_sort();
        current($this->_elementOrder);
        $key = key($this->_elementOrder);
        return $this->getElement($key);
    }

    /**
     * Current element
     * 
     * @return string
     */
    public function key()
    {
        $this->_sort();
        return key($this->_elementOrder);
    }

    /**
     * Move pointer to next element
     * 
     * @return void
     */
    public function next()
    {
        $this->_sort();
        next($this->_elementOrder);
    }

    /**
     * Move pointer to beginning of element loop
     * 
     * @return void
     */
    public function rewind()
    {
        $this->_sort();
        reset($this->_elementOrder);
    }

    /**
     * Determine if current element/subform/display group is valid
     * 
     * @return bool
     */
    public function valid()
    {
        $this->_sort();
        return (current($this->_elementOrder) !== false);
    }

    /**
     * Count of elements/subforms that are iterable
     * 
     * @return int
     */
    public function count()
    {
        return count($this->_elements);
    }

    /**
     * Sort items according to their order
     * 
     * @return void
     */
    protected function _sort()
    {
        if ($this->_groupUpdated || !is_array($this->_elementOrder)) {
            $elementOrder = array();
            foreach ($this->getElements() as $key => $element) {
                $elementOrder[$key] = $element->getOrder();
            }

            $items = array();
            $index = 0;
            foreach ($elementOrder as $key => $order) {
                if (null === $order) {
                    if (array_search($index, $elementOrder, true)) {
                        ++$index;
                    }
                    $items[$index] = $key;
                    ++$index;
                } else {
                    $items[$order] = $key;
                }
            }

            $items = array_flip($items);
            asort($items);
            $this->_elementOrder = $items;
            $this->_groupUpdated = false;
        }
    }
}
