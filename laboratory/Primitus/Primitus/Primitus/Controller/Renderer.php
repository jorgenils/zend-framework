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

require_once 'Primitus/View/Engine.php';
require_once 'Primitus.php';


/**
 * The Primitus Renderer, this object is called via a Front Controller plugin
 * after the dispatch loop is finished, and is responsible for two tasks
 * 
 * 1) Determine the first action that occurred, and sets the main view template
 *    variables properly to display the base template from _main/index.tpl
 * 
 * 2) Captures a noRoute() action and re-directs the request to displaying the
 *    _main/noroute.tpl template
 * 
 * @category   Primitus
 * @package    Primitus_Controller
 * @subpackage Renderer
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_Controller_Renderer {
	
	const MAIN_TEMPLATE = "_main/index.tpl";
	
	/**
	 * Render the primary template, setting the variables for the first action and
	 * redirects the user to the noroute template if necessary
	 */
	static public function render() {

		$engine = new Primitus_View_Engine();
		$dispatcher = Primitus::registry('Dispatcher');
		$action = $dispatcher->getFirstAction();
		
		$moduleName = $dispatcher->formatControllerName($action->getControllerName());
		$actionName = $dispatcher->formatActionName($action->getActionName());
		
		$controller = new $moduleName();
		
		$content_type = $controller->getContentType($action);
		
		header("Content-type: $content_type");
		
		if(($moduleName == "IndexController") &&
		   ($actionName == "norouteAction")) {

			$view = Primitus_View::getInstance($moduleName);
			print $view->render($action->getActionName());
					
	    } else {
			
	    	switch(true) {
	    		case ($controller instanceof ApplicationController):
					$engine->assign("__Primitus_Primary_Module_Name", $moduleName);
					$engine->assign("__Primitus_Primary_Module_Action", $actionName);
					
					$engine->display(self::MAIN_TEMPLATE);
					break;
	    		
	    		default:
	    			$view = Primitus_View::getInstance($moduleName);
	    			print $view->render($action->getActionName());
	    			break;
	    	}

	    }
	}
}