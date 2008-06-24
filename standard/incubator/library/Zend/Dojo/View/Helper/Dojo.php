<?php
/** Zend_Registry */
require_once 'Zend/Registry.php';

/**
 * Zend_Dojo_View_Helper_Dojo: Dojo View Helper
 *
 * Allows specifying stylesheets, path to dojo, module paths, and onLoad 
 * events. 
 * 
 * @package    Zend_Dojo
 * @subpackage View
 * @copyright  Copyright (C) 2008 - Present, Zend Technologies, Inc.
 * @license    New BSD {@link http://framework.zend.com/license/new-bsd}
 * @version    $Id: $
 */
class Zend_Dojo_View_Helper_Dojo 
{ 
    /**
     * @var Zend_View_Interface
     */
    public $view; 

    /**
     * @var Zend_Dojo_View_Helper_Dojo_Container
     */
    protected $_container;

    /**
     * Initialize helper
     *
     * Retrieve container from registry or create new container and store in 
     * registry.
     * 
     * @return void
     */
    public function __construct()
    {
        $registry = Zend_Registry::getInstance();
        if (!isset($registry[__CLASS__])) {
            require_once 'Zend/Dojo/View/Helper/Dojo/Container.php';
            $container = new Zend_Dojo_View_Helper_Dojo_Container();
            $registry[__CLASS__] = $container;
        }
        $this->_container = $registry[__CLASS__];
    }

    /**
     * Set view object
     * 
     * @param  Zend_Dojo_View_Interface $view 
     * @return void
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        $this->_container->setView($view);
    }

    /**
     * Return dojo container
     * 
     * @return Zend_Dojo_View_Helper_Dojo_Container
     */
    public function dojo()
    {
        return $this->_container;
    }

    /**
     * Proxy to container methods
     * 
     * @param  string $method 
     * @param  array $args 
     * @return mixed
     * @throws Zend_Dojo_View_Exception For invalid method calls
     */
    public function __call($method, $args)
    {
        if (!method_exists($this->_container, $method)) {
            require_once 'Zend/Dojo/View/Exception.php';
            throw new Zend_Dojo_View_Exception(sprintf('Invalid method "%s" called on dojo view helper', $method));
        }

        return call_user_func_array(array($this->_container, $method), $args);
    }
}
