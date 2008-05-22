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
 * @version    $Id: LogonController.php 121 2007-04-12 21:48:01Z gavin $
 *
 * /////////////////////////////
 * // ==> SECTION: auth <==
 */

require_once 'Zend/Controller/Action.php';

class LogonController extends ZFDemo_Controller_Action
{
    /**
     * Holds the authentication namespace from the session.
     * @var Zend_Session_Namespace
     */
    private $_authSpace;


    /**
     * Initialize this controller before transferring control to the action selected by the router.
     */
    public function init()
    {
        parent::init();

        // STAGE 3: Choose, create, and optionally update models using business logic.
        $this->authSpace = new Zend_Session_Namespace('auth');
        if ($origRequest = $this->getInvokeArg('origRequest')) {
            $this->view->origPathInfo = $origRequest->getPathInfo();
        }
    }


    /**
     * The default action is "indexAction", unless explcitly set to something else.
     */
    public function indexAction()
    {
        // STAGE 4: Apply business logic to create a presentation model for the view.
        $origRequest = $this->getInvokeArg('origRequest');
        $this->view->rerouteToReason = $this->getInvokeArg('rerouteToReason');
        $this->view->origRequestUri  = $origRequest->REQUEST_URI;

        // if no credentials
        if (empty($_REQUEST['username'])) { // should be _POST, but this makes demo easier to tweak
            // STAGE 5: Choose view template and submit presentation model to view template for rendering.
            // if an admin area was requested, and authentication has been enabled in config.ini
            if (isset($this->authSpace->authenticationId)) {
                ZFDemo_Log::log(_('already have authentication id, showing logout form'));
                $this->_forward('logoutDecision');  // show logout form
            } else {
                ZFDemo_Log::log(_('no authentication id, showing login form'));
                $this->renderToSegment('body'); // show login form
            }
            return;
        }

        // prepare to authenticate credentials received from a form
        require_once 'Zend/Auth/Result.php';
        require_once 'Zend/Auth/Adapter/Digest.php';
        $config = Zend_Registry::get('config');
        $username = trim($_REQUEST['username']); // ought to be _POST, but this simplifies experimentation
        $password = trim($_REQUEST['password']); // by the reader of the tutorial

        // filtering will be added in a later section

        /////////////////////////////
        // ==> SECTION: auth <==
        $result = false;

        try { // try to authenticate using the md5 "digest" adapter

            $filename = $config->authenticate->filename; // file containing username:realm:password digests
            if ($filename[0] !== DIRECTORY_SEPARATOR) {
                $filename = Zend_Registry::get('dataDir') . $filename; // prepend path, if filename not absolute
            }
            $adapter = new Zend_Auth_Adapter_Digest($filename, $config->authenticate->realm, $username, $password);
            $result = $adapter->authenticate(); // result of trying to authenticate credentials
            $this->view->resultCode = $result->getCode(); // allow view to see result status (reason)

        } catch (Exception $exception) {
            $this->view->exception = ZFDemo::filterException($exception); // record exception description
            $this->view->resultCode = false;
        }

        if ($result && $result->isValid()) {
            // if successful authentication, save the authentication identity ( http://framework.zend.com/wiki/x/fUw )
            $id = $result->getIdentity();
            Zend_Registry::set('authenticationId', $id); // publish the identity (really need Observer pattern)
            $this->authSpace->authenticationId = $id;
            $this->authSpace->date = time(); // save the timestamp when authenticated successfully
            $this->authSpace->attempts = 0;  // success, so forget the number of previous login failures
            // @TODO: filter this ...
            $this->_redirect($_REQUEST['origPathInfo']); // now return to wherever user came from
        } else {
            $this->authSpace->attempts++; // record the authentication failure
            if ($this->authSpace->attempts > $config->authenticate->maxAttempts) {
                // Overly simplistic account "lockout" lasts for at least 10 seconds,
                // but increases with repeated failures.
                $this->view->lockout = 5 *  $this->authSpace->attempts;
                // Lockout time will be "forgotten" later, and expired from session, allowing logins.
                $this->authSpace->setExpirationSeconds($this->view->lockout);
                $this->blockHacker(); // show a view indicating account lockout
                return;
            }
        }

        // STAGE 5: Choose view template and submit presentation model to view template for rendering.
        $this->renderToSegment('body');
    }


    /**
     * User is currently logged in, but requested to login again, so let user decide:
     * logout, switch identities, or abort
     */
    public function logoutDecisionAction()
    {
        // STAGE 5: Choose view template and submit presentation model to view template for rendering.
        ZFDemo_Log::log(_('currently authenticated, showing logout form'));
        $this->renderToSegment('body');
    }


    /**
     * Show a view indicating account lockout.  
     * Reader excercise: Increase intelligence and sophistication, such as
     * limiting the number of logins / attempts per IP or IP block.
     */
    public function blockHacker()
    {
        // STAGE 5: Choose view template and submit presentation model to view template for rendering.
        $this->renderToSegment('body', 'logonBlockHacker');
    }


    /**
     * logoff, and return to home page
     */
    public function logoffAction()
    {
        ZFDemo_Log::log('logoff');
        // STAGE 3: Choose, create, and optionally update models using business logic.
        Zend_Session::namespaceUnset('auth'); // remove the authentication id and information
        Zend_Registry::set('authenticationId', 0); // really need observer pattern
        // STAGE 5: Choose view (Home Page)
        $this->_redirect('/');
    }


    /**
     * logoff, and show logon page
     */
    public function logoutAction()
    {
        ZFDemo_Log::log('logout');
        // STAGE 3: Choose, create, and optionally update models using business logic.
        Zend_Session::namespaceUnset('auth'); // remove the authentication id and information
        Zend_Registry::set('authenticationId', 0); // really need observer pattern
        // STAGE 4: Apply business logic to create a presentation model for the view.
        // STAGE 5: Choose view template and submit presentation model to view template for rendering.
        $this->_forward('index'); // possibly valid topic, so try to show its posts
    }
}
