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
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @version    $Id$
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 */

/** Zend_Controller_Router_Exception */
require_once 'Zend/Controller/Router/Exception.php';

/** Zend_Controller_Router_Route_Interface */
require_once 'Zend/Controller/Router/Route/Interface.php';

/**
 * Module Route
 *
 * Default route for module functionality
 *
 * @package    Zend_Controller
 * @subpackage Router
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://www.zend.com/license/framework/1_0.txt Zend Framework License version 1.0
 * @see        http://manuals.rubyonrails.com/read/chapter/65
 */
class Zend_Controller_Router_Route_Module implements Zend_Controller_Router_Route_Interface
{
    /**
     * @const string URI delimiter
     */
    const URI_DELIMITER   = '/';
    
    /**
     * Default values for module, controller, and action
     * @var array
     */
    protected $_defaults = array('module'=> 'default');

    /**
     * Front controller instance
     * @var Zend_Controller_Front
     */
    protected $_frontController;

    /**
     * Get front controller instance
     * 
     * @return Zend_Controller_Front
     */
    public function getFrontController()
    {
        if (null == $this->_frontController) {
            require_once 'Zend/Controller/Front.php';
            $this->_frontController = Zend_Controller_Front::getInstance();
        }

        return $this->_frontController;
    }

    /**
     * Prepares the route for mapping by splitting (exploding) it 
     * to a corresponding atomic parts. These parts are assigned 
     * a position which is later used for matching and preparing values.  
     *
     * @param array Defaults for map variables with keys as variable names
     */
    public function __construct(array $defaults = array())
    {
        $dispatcher = $this->getFrontController()->getDispatcher();

        if (isset($defaults['module'])) {
            unset($defaults['module']);
        }

        if (!isset($defaults['controller'])) {
            $defaults['controller'] = $dispatcher->getDefaultControllerName();
        }

        if (!isset($defaults['action'])) {
            $defaults['action'] = $dispatcher->getDefaultAction();
        }

        $this->_defaults = array_merge($this->_defaults, $defaults);
    }

    /**
     * Is a module valid?
     * 
     * @param string $module 
     * @return boolean
     */
    public function isValidModule($module)
    {
        require_once 'Zend/Controller/Front.php';
        $controllerDirs = $this->getFrontController()->getDispatcher()->getControllerDirectory();
        return isset($controllerDirs[$module]);
    }

    /**
     * Matches a user submitted path with parts defined by a map. Assigns and 
     * returns an array of variables on a successful match.  
     *
     * @param string Path used to match against this routing map 
     * @return array|false An array of assigned values or a false on a mismatch
     */
    public function match($path)
    {
        $pathStaticCount = 0;

        $module     = $this->_defaults['module'];
        $controller = $this->_defaults['controller'];
        $action     = $this->_defaults['action'];
        $params     = array();
        
        $path = trim($path, self::URI_DELIMITER);

        if ($path != '') {
            $path = explode(self::URI_DELIMITER, $path);
        
            // Module
            if (count($path) && $this->isValidModule($path[0])) {
                $module = array_shift($path);
            }

            // Controller
            if (count($path) && !empty($path[0])) {
                $controller = array_shift($path);
            }

            // Action
            if (count($path) && !empty($path[0])) {
                $action = array_shift($path);
            }

            // Path info
            $numSegs = count($path);
            if ($numSegs) {
                for ($i = 0; $i < $numSegs; $i = $i + 2) {
                    $key = urldecode($path[$i]);
                    $val = isset($path[$i + 1]) ? urldecode($path[$i + 1]) : null;
                    $params[$key] = $val;
                }
            }
        }
        
        $this->_values = array(
            'module'     => $module,
            'controller' => $controller,
            'action'     => $action
        );
        $this->_values = array_merge($params, $this->_values);

        return $this->_values;
    }

    /**
     * Assembles user submitted parameters forming a URL path defined by this route 
     *
     * @param array An array of variable and value pairs used as parameters 
     * @return string Route path with user submitted parameters
     */
    public function assemble($data = array())
    {
        $url = array();

        if (isset($data['module']) && $this->isValidModule($data['module'])) {
            $url[] = $data['module'];
            unset($data['module']);
        }

        if (isset($data['controller'])) {
            $url[] = $data['controller'];
            unset($data['controller']);

            if (isset($data['action'])) {
                $url[] = $data['action'];
                unset($data['action']);

                foreach ($data as $key => $value) {
                    $url[] = $key;
                    $url[] = $value;
                }
            }
        }

        return implode(self::URI_DELIMITER, $url);
    }
}
