<?php

require_once 'Primitus/View/Exception.php';
require_once 'Primitus/View/Engine.php';
require_once 'Primitus.php';
/**
 * The Primitus View plugin is a procedural function which implements the logic of
 * the template-level {render} function. It is responsible for either calling the
 * requested controller's render() method to get the template, or simply processing
 * the template in the views/ directory if the controller didn't exist.
 * 
 * You can set template-level variables in the module being rendered by simply setting
 * them in the smarty {render} function. i.e.
 * 
 * {render module="blog" action="view" entry=$entry}
 * 
 * will expose the {$entry} template variable to the blog::view template in /blog/view.tpl
 * 
 * @category   Primitus
 * @package    Primitus
 * @subpackage View
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
function Primitus_View_Plugin_Render($params, &$smarty) {

	if(!isset($params['module'])) {
		throw new Primitus_View_Exception("No module name was provided to render");
	}
	
	$module = $params['module'];

	if(strtolower(substr($module, strlen($module)-10)) != "controller") {
		$module .= "Controller";
	}
	
	$action = (isset($params['action'])) ? $params['action'] : "indexAction";

	$dispatcher = Primitus::registry('Dispatcher');

	$controllerFile = $dispatcher->formatControllerFile($module);
	
	if(file_exists($controllerFile)) {
		// Load the Class
		Zend::loadClass($module, $dispatcher->getControllerDirectory());
		$controller = new $module();
		if($controller instanceof Primitus_Controller_Action_Base) {
			unset($params['module']);
			unset($params['action']);
			
			if(!empty($params)) {
				$view = Primitus_View::getInstance($module);
				foreach($params as $key => $value) {
					$view->assign($key, $value);
				}
			}

			return $controller->render($action);
		} else {
			throw new Primitus_View_Exception("Bad Request");
		}
	} else {
		$view = Primitus_View::getInstance($module);

		unset($params['module']);
		unset($params['action']);
		
		if(!empty($params)) {
			$view = Primitus_View::getInstance($module);
			foreach($params as $key => $value) {
				$view->assign($key, $value);
			}
		}

		return $view->render($action);
	}
	
}

?>