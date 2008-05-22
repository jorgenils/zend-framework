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
 * @version    $Id: Users.php 115 2007-04-10 17:11:36Z gavin $
 */

require_once 'Zend/Db/Table/Abstract.php';

// STAGE 3: Choose, create, and optionally update models using business logic.

class ZFDemoModel_Users extends Zend_Db_Table_Abstract
{
    /* exact name of users DB table (case-sensitive)
     * @var string
     */
    protected $_name = 'users';

    /* primary key name
     * @var array
     */
    protected $_primary = array('user_id');

    /* List of dependent table names having foreign key references to $_primary key above.
     * @var array
     */
    protected $_dependentTables = array('ZFDemoModel_Topics', 'ZFDemoModel_Posts', 'ZFDemoModel_Attachments');

    /* Array of users by id
     * @var array
     */
    private static $_users = null;

    /* Out user model table
     * @var Zend_Db_Table_Abstract
     */
    private static $_modelTable = null;


    /**
     * Return the singleton instance of the "users" table model used by this application.
     */
    public static function getInstance()
    {
        if (self::$_modelTable === null) {
            self::$_modelTable = new self();
        }

        return self::$_modelTable;
    }


    /**
     * Fetch a user row object from the "users" table uniquely identified by $username.
     */
    public static function getByUsername($username)
    {
        $usersTable = self::getInstance();
        $where = $usersTable->getAdapter()->quoteInto('username = ?', $username);

        return $usersTable->fetchRow($where);
    }


    /**
     * Fetch a user row object from the "users" table uniquely identified by $userId.
     */
    public static function getById($userId)
    {
        if (!isset(self::$_users[$userId])) {
            $usersTable = self::getInstance();
            $rowset = $usersTable->find($userId);
            self::$_users[$userId] = $rowset->current();
        }

        return self::$_users[$userId];
    }


    /**
     * When a user posts, this method is used to increase their total post count by 1.
     * This method also demonstrates the use of Zend_Db_Expr to avoid quoting a SQL
     * expression that should not be placed inside DB quoting characters.
     */
    public static function incrementPostCount($userId)
    {
        $db = self::getInstance()->getAdapter();
        $where = $db->quoteInto('user_id = ?', $userId);
        $rowsAffected = $db->update('users', array('post_count' => new Zend_Db_Expr('post_count + 1')), $where);

        return $rowsAffected;
    }
}
