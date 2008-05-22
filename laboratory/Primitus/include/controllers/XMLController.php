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

require_once 'Primitus/Controller/Action/Base.php';

/**
 * All user-defined controllers should extend from ApplicationController, you
 * can use this controller to define actions which will be available from every
 * controller in your application
 * 
 * @category   Primitus
 * @package    Primitus_Controller_Action
 * @subpackage XMLController
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class XMLController extends Primitus_Controller_Action_Base {

	const CONTENT_TYPE = "text/xml";
	
	public function getContentType(Zend_Controller_Dispatcher_Token $action) {
		return self::CONTENT_TYPE;
	}
}

?>