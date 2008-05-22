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

    /* Authorization id used for authorization / access control in this module.
     * Anonymous users will have an id of zero (0).
     * @var integer
     */
    protected static $_authorizationId = 0;


    /**
     * Initialize the module by mapping any authentication id into the forum system's user table,
     * and performing access control on forum controllers and actions.
     * @return Zend_Config|null  Zend_Config should contain keys mapping to module/controller/action for a reroute
     */
    public static function moduleAuth(Zend_Config $config, Zend_Controller_Request_Abstract $request)
    {
        // access the session namespace 'auth' to see if user is authenticated
        $authSpace = new Zend_Session_Namespace('auth');
        if (!empty($authSpace->authenticationId)) {
            // try mapping the application's concept of authentication id to this module's authorization id
            self::authenticationId2authorizationId($authSpace->authenticationId);
        }

        /* Variant of "Authenticate #2" ( see "Authenticate Where?" http://framework.zend.com/wiki/x/fUw )
         * This point in the flow of execution also enables us to examine the module's
         * interpretation of the authentication id.
         * If anonymous use has been disabled in this module's settings in "modules.ini",
         * and the user was not recognized by this module's authenticationId2authorizationId(), then
         */
        if (empty($config->allowAnonymousUse) && empty(self::$_authorizationId)) {
            return $config->authenticate; // allow front controller plugin to redirect in preDispatch()
            // Reader excercise: redirect to something with a "prettier" explanation message
        }

        if (!$config->acl) { // ACL has been enabled in "modules.ini" for this module
            return;
        }

        /////////////////////////////
        // ==> SECTION: acl <==
        require_once 'Zend/Acl.php'; 
        require_once 'Zend/Acl/Role.php';     // Use the default ZF "role" class
        require_once 'Zend/Acl/Resource.php'; // Use the default ZF "resource" class

            /////////////////////////////
            // ==> SECTION: acl <==
            // Reader excercise: load the following from a config file
            $acl = new Zend_Acl(); 
            $acl->add(new Zend_Acl_Resource('posts')); // add() is really addResource()
            $acl->add(new Zend_Acl_Resource('topics')); // add resource 'topics' to our ACL
            $acl->add(new Zend_Acl_Resource('submissions')); // add resource 'topics' to our ACL

            $acl->addRole(new Zend_Acl_Role('anonymous')); // 'anonymous' role does not inherit access controls from any role
            $acl->allow('anonymous', 'posts',  'display'); // grant "display" privileges on posts
            $acl->allow('anonymous', 'topics', 'display'); // grant "display" privileges on topics
            // also works:
            // $acl->allow('anonymous', array('posts', 'topics'),  'display');

            // 'member' role inherits from 'anonymous' role
            $acl->addRole(new Zend_Acl_Role('member'), 'anonymous');
            // grant 'member' role ability to add/submit posts
            $acl->allow('member', 'submissions',  'post');

            // 'moderator' role inherits from 'member' role
            $acl->addRole(new Zend_Acl_Role('moderator'), 'member');
            // grant 'moderator' role ability to hide and delete posts
            $acl->allow('moderator', 'posts', array('visible', 'delete'));
            $acl->allow('moderator', null, 'display'); // moderators can display everything

            $acl->addRole(new Zend_Acl_Role('admin')); // inherit no constraints
            $acl->allow('admin'); // grant all privileges on all resources to 'admin' role

            $acl->add(new Zend_Acl_Resource('sorry')); // add resource for the "sorry" controller
            $acl->allow(null, 'sorry', 'display'); // grant display privilege for "sorry" messages to all roles

            $acl->allow(array('member', 'moderator', 'admin'), 'submissions',  null); // allow them to add posts

        /////////////////////////////
        // ==> SECTION: acl <==
        // NORMALIZE information needed for ACL check
        $controller = $request->getControllerName();
        $action = $request->getActionName();
        // map default privilege to correct privilege name
        if ($action === 'index') {
            $action = 'display';
        }
        // map default resource to correct resource name
        if ($controller === 'index') {
            $controller = 'posts';
        }
        // determine the role of the current user
        $role = self::$_user ? self::$_user->role : 'anonymous';
        // if the controller has been defined as a resource in the ACL, then check the ACL
        if ($acl->has($controller)) {
            $isAllowed = $acl->isAllowed($role, $controller, $action);
        } else {
            // allow access by admins for controllers not registered in the ACL
            $isAllowed = ($role === 'admin') ? true : false;
        }
        ZFDemo_Log::log("acl->isAllowed(role: $role, resource: $controller, permission: $action) = "
            . ($isAllowed ? 'true' : 'false') . "\n");
        if (!$isAllowed) { // if the request was not allowed, then
            // redirect to this module's "unauthorized" setting in "modules.ini"
            return $config->unauthorized; // front controller plugin will perform redirection in preDispatch()
        }
    }


    /////////////////////////////
    // ==> SECTION: auth <==
    /**
     * This method is responsible for mapping the ZFDemo's authentication ids to forum module authorization ids.
     * Each module can maintain its own set of ids used for authorization,
     * thus allowing integration of diverse modules.
     */
    public static function authenticationId2authorizationId($authenticationId)
    {
        self::loadModels();
        // getByUsername returns null if lookup fails
        if (self::$_user = ZFDemoModel_Users::getByUsername($authenticationId['username'])) {
            self::$_username = self::$_user->username;
            self::$_authorizationId = self::$_user->user_id;
        }
    }


    public static function getAuthorizationId()
    {
        return self::$_authorizationId;
    }


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
