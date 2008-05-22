<?php
/**
 * Zend Framework
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
 * @category   Zend
 * @package    Zend_Bitfield
 * @subpackage Adapter
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 *
 * @todo XXX 
 */

/**
 * Zend_Bitfield_Exception
 */
require_once 'Exception.php';

/**
 * @category  Zend
 * @package   Zend_Bitfield
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Bitfield {
  static protected $bitMethods = array('32bit', '64bit', 'GMP');
  static protected $bitClass;

  /**
   * Zend_Bitfield provides a easy interface to handle 
   * and compare bit values
   *
   * @param string $method
   * @param string $group
   * @throws Zend_Bitfield_Exception
   */
  static public function initBitfield($method = '32bit', $group = 'default') {
    if(!in_array($method, Zend_Bitfield::$bitMethods)) {
      Zend_Bitfield::throwException("Invalid Method ($method)");
    }
    $methodClass = 'Zend_Bitfield_Adapter_'.$method;;
    require_once('Adapter/'.$method.'.php');
    Zend_Bitfield::$bitClass[$group] = new $methodClass;    
  }

  /**
   * Compares bits using loaded adapter
   *
   * @param string $key
   * @param int $value
   * @param string $group
   * @return boolean
   */
  static public function checkBit($key, $value, $group = 'default') {
    return Zend_Bitfield::$bitClass[$group]->comparebit($key, $value);
  }

  /**
   * Creates a new bit key
   *
   * @param string $key
   * @param string $group
   * @return int
   */
  static public function createBit($key, $group = 'default') {
    return Zend_Bitfield::$bitClass[$group]->createBit($key);
  }

  /**
   * Returns and array of all the bits
   *
   * @param string $group
   * @returnarray
   */
  static public function getBits($group = 'default') {
    return Zend_Bitfield::$bitClass[$group]->getBits();
  }

  /**
   * Used to load an array of bits
   *
   * @param array $input
   * @param string $group
   * @return array
   */
  static public function loadBits($input, $group = 'default') {
    return Zend_Bitfield::$bitClass[$group]->loadBits($input);
  }

  /**
   * Handles exceptions
   *
   * @param string $msg
   */
  static public function throwException($msg) {
#    require_once('Zend/Bitfield/Exception');
#    throw new Zend_Bitfield_Exception($msg);
     echo $msg;
     exit();
  }
}