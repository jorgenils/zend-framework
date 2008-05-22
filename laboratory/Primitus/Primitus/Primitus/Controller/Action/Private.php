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
require_once 'Primitus.php';


/**
 * @category   Primitus
 * @package    Primitus_Controller_Action
 * @subpackage Private
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_Controller_Action_Private extends Primitus_Controller_Action_Base {

	public function __construct() {
		$dispatcher = Primitus::registry('Dispatcher');
		
		if($dispatcher->getPrimaryController() == get_class($this)) {
			throw new Primitus_Controller_Exception("Cannot access private controller from public space");
		}
	}
}

?>