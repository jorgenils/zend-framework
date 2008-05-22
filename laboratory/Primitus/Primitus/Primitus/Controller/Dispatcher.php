<?php
/**
 * Primitus
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 *
 * @category   Primitus
 * @package    Application
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

require_once 'Zend/Controller/Dispatcher.php';
require_once 'Primitus/Controller/Dispatcher/Exception.php';
require_once 'Primitus/Controller/Action/List.php';
require_once 'Primitus.php';


/**
 * The Primitus Dispatcher
 * 
 * @category   Primitus
 * @package    Primitus_Controller
 * @subpackage Dispatcher
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_Controller_Dispatcher extends Zend_Controller_Dispatcher {

	private $_firstAction;
	private $_actionList;
	
	private $_renderController;
	private $_renderAction;

	/**
	 * Constructor for the Primitus Dispatcher
	 */
	public function __construct() {
		$this->_actionList = new Primitus_Controller_Action_List();
		$this->_renderAction = "render";
		$this->_renderController = "Primitus_Controller_Action_Render";
		$this->_firstAction = null;
	}

	/**
	 * A wrapper for Zend::loadClass, which loads a given controller from
	 * the controller directory
	 *
	 * @param string $controllerName The qualified controller class name
	 */
	static public function loadController($controllerName) {
		return Zend::loadClass($controllerName, $this->_directory);
	}
	
	/**
	 * Returns a list of the controllers that have been executed in this request
	 *
	 * @return Primitus_Controller_List The current list of executed controllers
	 */
	public function getExecutedActions() {
		return $this->_actionList;	
	}
	
	/**
	 * Returns a Zend_Controller_Dispatcher_Token representing the first action executed
	 * during this request
	 *
	 * @return Zend_Controller_Dispatcher_Token The first action executed in this request
	 */
	public function getFirstAction() {
		if($this->_firstAction instanceof Zend_Controller_Dispatcher_Token) {
			return $this->_firstAction;
		}
		throw new Primitus_Controller_Dispatcher_Exception("There currently is no first action");
	}
	
	/**
	 * Returns the directory where developer-defined controllers will be looked for
	 *
	 * @return string The path to the controller directory
	 */
	public function getControllerDirectory() {
		return $this->_directory;
	}
	
	/**
	 * Returns the filename the dispatcher expects to find if a controller is available
	 *
	 * @param string $controllerName The fully-qualified controller class name
	 * @return string The complete path and filename to the controller class
	 */
	public function formatControllerFile($controllerName) {
		return "{$this->_directory}/{$controllerName}.php";
	}
	
	
	/**
	 * The Primitus Dispatcher 
	 *
	 * @param Zend_Controller_Dispatcher_Token $action The action to dispatch
	 * @param boolean $performDispatch Should the dispatch be performed, or just test
	 * @return mixed If testing, true/false to if the operation would succeed, if not the next token or null
	 */
	protected function _dispatch(Zend_Controller_Dispatcher_Token $action, $performDispatch = false) {
		if(is_null($this->_directory)) {
			throw new Primitus_Controller_Dispatcher_Exception("Controller directory never set. Use setControllerDirectory() first.");
		}
		
		$controllerName = ($controllerName = $action->getControllerName()) ? $controllerName : 'index';
		$controllerName = $this->formatControllerName($controllerName);
		$controllerFile = $this->formatControllerFile($controllerName);
		
		$actionName = $this->formatActionname(($actionName = $action->getActionName()) ? $actionName : 'index');
		
		// Does the Controller exist?
	
		if(file_exists($controllerFile)) {
			
			// Load the Class
			Zend::loadClass($controllerName, $this->_directory);
			$controller = new $controllerName();
			
			if($controller instanceof Zend_Controller_Action) {
				
				if(method_exists($controller, $actionName)) {
					
					if(!$performDispatch) {
						return true;
					}
					
					return $this->_runController($controller, $action);				
				} 
				
			} else {
				throw new Primitus_Controller_Dispatcher_Exception("Bad Request");
			}
		}
		
		/**
		 * If the Class file doesn't exist, we check to see if a corresponding
		 * template for the controller exists. If it does, we create a dummy controller
		 * to handle the request of displaying the template. 
		 */
		$view = Primitus_View::getInstance($controllerName);
		$templateFile = $view->getTemplatePathByAction($actionName);
		
		if(!$performDispatch) {
			return file_exists($templateFile);
		}
		
		$this->_firstAction = is_null($this->_firstAction) ? $action : $this->_firstAction;	
	}
	
	/**
	 * Runs the current action against the proper controller, recording meta data about the
	 * act for future use.
	 *
	 * @param Zend_Controller_Action $controller The controller being executed
	 * @param Zend_Controller_Dispatcher_Token $action The action within the controller
	 * @return Zend_Controller_Dispatcher_Token The next token in the execution chain
	 */
	protected function _runController(Zend_Controller_Action $controller, Zend_Controller_Dispatcher_Token $action) {
		
		$currentAction = strtolower($action->getActionName());
		$currentController = strtolower($action->getControllerName());
		
		$this->_firstAction = is_null($this->_firstAction) ? $action : $this->_firstAction;	

		$nextAction = $controller->run($this, $action);

		$this->_actionList->addAction($action);
		
		return $nextAction;
	}
	
	/**
	 * Sets the Controller and Action used to trigger the request-renderer
	 *
	 * @param string $controller The unqualified controller name to execute
	 * @param string $action The unqualified action name to execute
	 */
	public function setRenderer($controller, $action) {

		$this->_renderAction = strtolower($action);
		$this->_renderController = strtolower($controller);	
	}
}

?>