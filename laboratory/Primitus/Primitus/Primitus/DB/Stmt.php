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
 * This is a wrapper class around the PDO which currently only implements the
 * singleton pattern. Eventually, it would be nice if this was replaced with a 
 * more robust data layer provided by Zend Framework itself, but for now this
 * will do.
 * 
 * @category   Primitus
 * @package    Primitus
 * @subpackage DB
 * @copyright  Copyright (c) 2006 John Coggeshall
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Primitus_DB_Stmt extends PDOStatement {
	
}

?>