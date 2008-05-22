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

require_once 'Primitus/DB/DO.php';
require_once 'Primitus/DB/Stmt.php';

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
class Primitus_DB {
	
	private static $_instance;

    const DB_USERNAME = "%DB_USERNAME%";
    const DB_PASSWORD = "%DB_PASSWORD%";
    const DB_DSN      = "%DB_DSN%";

    /**
     * Returns the instance of the PDO database connection
     *
     * @return PDO the instance of the PDO database connection
     */
    final static public function getInstance() {
        if(!self::$_instance instanceof PDO) {
        	
        	self::$_instance = new Primitus_DB_DO(self::DB_DSN, self::DB_USERNAME, self::DB_PASSWORD);

            self::$_instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$_instance->setAttribute(PDO::ATTR_STATEMENT_CLASS, 
            							   array('Primitus_DB_Stmt', array()));
        }

        return self::$_instance;
    }
}    