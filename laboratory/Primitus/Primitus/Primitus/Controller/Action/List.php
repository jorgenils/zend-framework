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


/**
 * This is a container object (list) which implements a number of SPL
 * interfaces to provide clean access. It specifically contains a list of
 * all the Actions which have taken place during this request. Useful for
 * introspection, or debugging. It is primary used in the Primitus Dispatcher
 * 
 * @category   Primitus
 * @package    Primitus_Controller
 * @subpackage List
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_Controller_Action_List implements Iterator, ArrayAccess {
	
	private $_actions;
	
	public function __construct() {
		$this->_actions = array();
	}
	
	public function offsetExists($offset) {
		return isset($this->_actions[$offset]);
	}
	
	public function offsetGet($offset) {
		return $this->_actions[$offset];
	}
	
	public function offsetSet($offset, $value) {
		throw new Primitus_Controller_Exception("You cannot modify a ActionList object externally");	
	}
	
	public function offsetUnset($offset) {
		throw new Primitus_Controller_Exception("You cannot modify a ActionList object externally");
	}
	
	public function addAction(Zend_Controller_Dispatcher_Token $action) {
		$this->_actions[] = $action;
	}
	
	public function current() {
		return current($this->_actions);
	}
	
	public function key() {
		return key($this->_actions);
	}
	
	public function next() {
		next($this->_actions);
	}
	
	public function rewind() {
		reset($this->_actions);
	}
	
	public function valid() {
		return (bool)current($this->_actions);
	}
}

?>