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

require_once 'Zend/Controller/Front.php';
require_once 'Primitus/View.php';

/**
 * The Primitus Front Controller
 * 
 * @category   Primitus
 * @package    Primitus_Controller
 * @subpackage Front
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_Controller_Front extends Zend_Controller_Front {

	static private $_instance;
	
	/**
	 * Dispatch the request to the dispatcher, catching any errors which bubble up
	 * to the surface and, in that event, display a nice template describing the error
	 * with debugging information. This method also controls the displaying of debug
	 * information in every request.
	 */
	public function dispatch() {
		try {
			Primitus::startTimer('request');
			parent::dispatch();
			$request_time = number_format(Primitus::stopTimer('request'), 2);
			
			if(defined("Primitus_DEBUG") && Primitus_DEBUG) {
				$engine = new Primitus_View_Engine();
				
				$debugInfo = Primitus::generateDebuggingData();
				
				$engine->assign("debug", $debugInfo);
				$engine->display("_main/debug.tpl");
			}

		} catch (Exception $e) {
			$originalError = $e;
			
			try {
				$engine = new Primitus_View_Engine();
				$engine->assign('error', $originalError);
				$engine->display("_main/error.tpl");				
			} catch(Exception $e) {
				$msg = "{$originalError->getMessage()} (Additionally, there was an error in the error handler: {$e->getMessage()})";
				print $e->getTraceAsString();
				$this->_printUglyMessage($msg);
				die();
			}
		}
	}
	
	/**
	 * In the event that an error occurs during the rendering of the error template,
	 * this simplisitic method is called to still display the error in a less-then
	 * pretty fashion.
	 *
	 * @param string $msg The error message to display
	 */
	private function _printUglyMessage($msg) {
		print "<HTML><HEAD><TITLE>Primitus Error</TITLE></HEAD><BODY>$msg</BODY></HTML>";
	}
	
	
	/**
	 * Return one and only one instance of the Zend_Controller_Front object
	 *
	 * @return Zend_Controller_Front
	 */
	static public function getInstance()
	{
        if (!self::$_instance instanceof self) {
           self::$_instance = new self();
        }

        return self::$_instance;
	}
}
?>
