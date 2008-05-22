<?php
/**
 * Zend Framework ZFDemo Tutorial
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
 * @copyright  Copyright (c) 2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Users.php 121 2007-04-12 21:48:01Z gavin $
 */

// STAGE 3: Choose, create, and optionally update models using business logic.

class ZFDemoModel_Users
{
    /**
     * Array of user models, keyed by id, cached for duration of request, lazy loaded.
     * @var mixed (depends on whether or not Zend_Db is used)
     */
    private static $_users = array();


    /**
     * Retrieve row in the users table having $username.
     * @return Object  properties are indexed by column name with values from the corresponding row in table
     */
    public static function getByUsername($username)
    {
        static $stmt = null;

        $db = Zend_Registry::get('db');
        $q = 'SELECT * FROM users WHERE username=\'' . mysql_escape_string($username) . "'";
        $stmt = $db->query($q);
        $row = $stmt->fetchObject();
        $stmt->closeCursor(); // utter paranoia (if UNIQUE constraint violated for username)
        return $row;
    }


    /**
     * Retrieve row in the users table having $userId.
     * @return Object  properties are indexed by column name with values from the corresponding row in table
     */
    public static function getById($userId)
    {
        static $stmt = null;

        if (!isset(self::$_users[$userId])) {
            $db = Zend_Registry::get('db');
            if ($stmt === null) {
                $q = 'SELECT * FROM users WHERE user_id = ?';
                $stmt = $db->prepare($q);
                if ($stmt === false) {
                    throw new ZFDemo_Exception("Preparing query '$q' failed.", 500);
                }
            }
            $stmt->execute(array(intval($userId)));
            self::$_users[$userId] = $stmt->fetchObject();
            $stmt->closeCursor();
        }
        return self::$_users[$userId];
    }
}
