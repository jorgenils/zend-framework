<?php
/**
 * Zend Framework ZFDemo Tutorial
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
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 

/** Zend_Controller_Action */
require_once 'Zend/Controller/Action.php';


/**
 * Example of how to extend Zend Framework core library components.
 */
abstract class ZFDemo_Controller_Action extends Zend_Controller_Action
{
    /**
     * Initialize the controller
     */
    public function init()
    {
        parent::init(); // just good habit (nothing there currently)

        // make the view available to action controllers for holding presentation model
        $this->view = $this->getInvokeArg('view');
        $this->view->baseUrl = $this->_request->getBaseUrl();
        if (!empty($this->view->viewSuffix)) {
            // i.e. do not alter the view's filename suffix, if specified by the application
            $this->viewSuffix = $this->view->viewSuffix;
        }

        // now give the module a chance to initialize itself and the view used by the module's controllers
        $moduleName = $this->_request->getModuleName();
        $class = 'ZFModule_' . ucfirst($moduleName);
        if (!class_exists($class)) {
            $registry = Zend_Registry::getInstance();
            $moduleInit = $registry['appDir'] . $moduleName . DIRECTORY_SEPARATOR . $moduleName . '.php';
            if (is_readable($moduleInit)) {
                include_once $moduleInit;
            }
        }
        if (class_exists($class, false) && method_exists($class, 'moduleInit')) {
            call_user_func(array($class, 'moduleInit'), $this->view);
        }
    }


    /**
     * Initialize View object 
     *
     * Initializes {@link $view} if not otherwise a Zend_View_Interface.
     *
     * If {@link $view} is not otherwise set, instantiates a new Zend_View 
     * object, using the 'views' subdirectory at the same level as the 
     * controller directory for the current module as the base directory. 
     * It uses this to set the following:
     * - script path = views/scripts/
     * - helper path = views/helpers/
     * - filter path = views/filters/
     * 
     * @return Zend_View_Interface
     * @throws Zend_Controller_Exception if base view directory does not exist
     */
    public function initView(array $newViewOptions = array())
    {
        require_once 'Zend/View/Interface.php';
        if (!empty($this->view) && !($this->view instanceof Zend_View_Interface)) {
            throw new Zend_Controller_Exception("'" . (gettype($this->view) === 'object'
                ? get_class($this->view) : $this->view) . _("' does not implement Zend_View_Interface."));
        }

        $request = $this->getRequest();
        $module  = $request->getModuleName();
        $dirs    = $this->getFrontController()->getControllerDirectory();
        if (empty($module) || !isset($dirs[$module])) {
            $module = 'default';
        }
        $baseDir = dirname($dirs[$module]) . '/views';
        if (!file_exists($baseDir) || !is_dir($baseDir)) {
            throw new Zend_Controller_Exception(_('Missing base view directory ("%1$s"', $baseDir));
        }

        // if we don't have a view yet, create one
        if (empty($this->view)) {
            require_once 'Zend/View.php';
            $options = array_merge(array('basePath' => $baseDir), $newViewOptions);
            $this->view = new Zend_View($options);
        } else {
            // This is our chance to automatically *prepend* paths for the active module,
            // without having side-effects for other modules that might become active later
            // via an internal reroute (see bootstrap.php's reroute() and preDispatch()).
            // Since the current view object "inherited" from the view object setup in bootstrap.php,
            // this view object already has default paths pointing to the default module's 
            // script/helper/filter paths.  Thus, the views in ZFDemo modules can use default
            // scripts, helpers, and paths shared via the default module.
            $this->view->addScriptPath($baseDir . '/scripts')
                       ->addHelperPath($baseDir . '/helpers')
                       ->addFilterPath($baseDir . '/filters');
            // We could support $newViewOptions here, but code would not be DRY with respect
            // to Zend_View_Abstract::__construct(), and the view was already created elsewhere,
            // so there was already an opportunity to supply options.
        }

        return $this->view;
    }


    /**
     * Convenience method: render a view in <module directory>/views/scripts/<controller name><action name>.<viewSuffix>
     *
     * @param string $segmentName  OPTIONAL render view to named segment
     */
    public function renderToSegment($segmentName = null, $script = null)
    {
        $this->render($script ? $script : $this->getRequest()->getControllerName()
                . ucfirst($this->getRequest()->getActionName()), $segmentName, true);
    }
}
