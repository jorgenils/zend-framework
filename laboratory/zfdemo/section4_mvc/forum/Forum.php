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
 * @version    $Id: Forum.php 121 2007-04-12 21:48:01Z gavin $
 *
 * The organization of properties and methods are distorted intentionally in order
 * to reduce confusion over section additions to the ongoing tutorial. This reduces
 * the "scrambling" of code blocks as new sections add new code to this file.
 */


/////////////////////////////
// ==> SECTION: mvc <==
class ZFModule_Forum
{
    /* current user of this request
     * @var ZFDemoModel_Users
     */
    protected static $_user = null;

    /* forum module's name for the current user
     * @var string
     */
    protected static $_username = 'anonymous';

    public static function getRole()
    {
        return self::$_user ? self::$_user->role : null;
    }


    /**
     * Based on tutorial section, choose which set of model classes to load.
     * One set uses only PDO, while the other set uses Zend_Db_Table*.
     */
    protected static function loadModels()
    {
        static $loaded = false;
        if (!$loaded) {
            $loaded = true;
            $config = Zend_Registry::get('config'); // application-wide configuration ini
            $ds = DIRECTORY_SEPARATOR;
            // Use raw SQL with PDO, or use the ZF Row/Table gateway components for model classes
            $prefix = 'forum' . $ds . 'models' . $ds . $config->db->modelSet . $ds;
            require_once $prefix . 'Users.php';
            require_once $prefix . 'Posts.php';
            require_once $prefix . 'Topics.php';
            require_once $prefix . 'Attachments.php';
        }
    }


    /////////////////////////////
    // ==> SECTION: mvc <==
    /**
     * prepare module for use by module's controllers
     */
    public static function moduleInit($view)
    {
        $view->username = self::$_username;
        self::loadModels(); // make sure forum model classes are ready to use
    }
}
