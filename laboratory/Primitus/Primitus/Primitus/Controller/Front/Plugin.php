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

require_once 'Zend/Controller/Plugin/Abstract.php';
require_once 'Primitus/Controller/Renderer.php';
require_once 'Zend/Controller/Dispatcher/Token.php';
require_once 'Primitus/Controller/Exception.php';
require_once 'Zend/Filter/Input.php';
require_once 'Zend/Filter.php';

/**
 * The Primitus Front Controller Plugin for Zend Framework is responsible for
 * two primary tasks:
 * 
 * PreDispatchLoop: Check to make sure we were actually given a token to dispatch,
 * this can be removed once the dispatch plugin stuff is better implemented
 * 
 * PostDispatchLoop: call the Primitus Renderer, which triggers the rendering of each
 * individual controller's view
 * 
 * @category   Primitus
 * @package    Primitus_Controller_Action
 * @subpackage Private
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_Controller_Front_Plugin extends Zend_Controller_Plugin_Abstract {

	/**
	 * Pre-Dispatch loop hook
	 *
	 * @param Zend_Controller_Dispatcher_Token $action The first action
	 * @return Zend_Controller_Dispatcher_Token $action The new first action
	 */
	public function dispatchLoopStartup($action) {
		
		if(!$action instanceof Zend_Controller_Dispatcher_Token) {
			throw new Primitus_Controller_Exception("Dispatcher plugin received a non-token action");
		}
		
		return $action;
	}

	/**
	 * Renders the views
	 */
	public function dispatchLoopShutdown() {
	    Primitus_Controller_Renderer::render();
	}
}

?>