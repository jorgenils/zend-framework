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

require_once 'Zend.php';

require_once 'Primitus/View.php';
require_once 'Primitus/DB.php';
require_once 'Primitus/Error/Handler.php';
require_once 'Primitus/Controller/Front.php';
require_once 'Primitus/Controller/Dispatcher.php';
require_once 'Primitus/Controller/Front/Plugin.php';

/**
 * @category   Primitus
 * @package    Primitus
 * @subpackage Primitus
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
final class Primitus {
	
	const VAR_PREFIX = "__Primitus_var_";
	const CONTORLLER_DIRECTORY = "%CONTROLLER_DIR%";

	static private $_startTimes;
	
	/**
	 * Start a timer with a given name
	 *
	 * @param string $name The name of the timer
	 * @return float The time when the clock was started
	 */
	static public function startTimer($name) {
		self::$_startTimes = (is_array(self::$_startTimes)) ? self::$_startTimes : array();
		
		return self::$_startTimes[$name] = microtime(true);
	}
	/**
	 * Stops a timer with a given name
	 *
	 * @param string $name The name of the timer
	 * @return float The total time the timer ran
	 */
	static public function stopTimer($name) {
		$endTime = microtime(true);
		
		if(!isset(self::$_startTimes[$name])) {
			throw new Primitus_Exception("Timer '$name' was never started");
		}
		
		return $endTime - self::$_startTimes[$name];
	}
	/**
	 * Generates debugging data used when Primitus_DEBUG is enabled, this
	 * data is passed directly to _main/error.tpl
	 *
	 * @return array An array passed to _main/error.tpl
	 */
	static public function generateDebuggingData() {
		
		$retval = array();
		
		$dispatcher = self::registry('Dispatcher');
		$frontController = self::registry('FrontController');
		$router = self::registry('Router');
		$errorHandler = self::registry('ErrorHandler');

		$executedActionsList = $dispatcher->getExecutedActions();
		
		$actionList = array();
		
		foreach($executedActionsList as $action) {
			$actionsList[] = array('controller' => $action->getControllerName(),
			                       'action' => $action->getActionName());
		}
		
		$retval['firstAction'] = $dispatcher->getFirstAction();
		$retval['executedActions'] = $actionsList;
		$retval['unhandledErrors'] = $errorHandler->getUnhandledErrors();
		return $retval;
	}

	/**
	 * Initializes the request, called from index.php. Creates registry entries
	 * for the relevant objects and configures the front controller
	 *
	 * @return Primitus_Controller_Front The Front Controller for Primitus
	 */
	static public function initializeRequest() {
		
		$frontController = Primitus_Controller_Front::getInstance();
		$frontController->setDispatcher(new Primitus_Controller_Dispatcher());
		$frontController->setControllerDirectory(self::CONTORLLER_DIRECTORY);
		$frontController->registerPlugin(new Primitus_Controller_Front_Plugin());
		
		self::register("FrontController", $frontController);
		self::register("Dispatcher", $frontController->getDispatcher());
		self::register("Router", $frontController->getRouter());
		self::register("ErrorHandler", new Primitus_Error_Handler());
		
		return $frontController;	
	}
	
	/**
	 * Registers an object into the Primitus registry. This registry uses
	 * the Zend Framework Registry, it simply prepends a constant to the
	 * front of every name to protect its variables from a name-collision
	 * with another Zend Framework component
	 *
	 * @param string $name The name of the object in the registry
	 * @param string $value The instance of the object being stored
	 */
	static public function register($name, $value) {
		$name = (string)$name;
		return Zend::register(self::VAR_PREFIX.$name, $value);
	}

	/**
	 * Returns the stored instance of an object from the Primitus registry
	 *
	 * @param string $name The name of the object to retrieve
	 * @return object The stored object instance
	 */
	static public function registry($name) {
		$name = (string)$name;
		$obj = Zend::registry(self::VAR_PREFIX.$name);
		
		if(!$obj) {
			throw new Primitus_Exception("Requested Object '$name' from Registry not found");
		}
		
		return $obj;
	}
}

?>