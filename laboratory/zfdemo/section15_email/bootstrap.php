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
 * @version    $Id: bootstrap.php 123 2007-04-16 16:08:52Z gavin $
 *
 * http://framework.zend.com/wiki/display/ZFDEV/Tutorial
 *
 * View with 120 column-wide window for best results.
 *
 * Note: Each section of the tutorial introduces additional code.
 * The additional code is usually prefixed by:
/////////////////////////////
// ==> SECTION: mvc <==
 * This allows for easier identification of when (which tutorial section)
 * a particular block of code was introduced into the ongoing demo source tree.
 *
 * Those using visual "diff" code comparison tools, like vimdiff or Araxis Merge
 * will find the "monolithic" nature of this file more convenient that a plethora
 * of tiny files, and hopefully will forgive the crime of having more than one class
 * in this file ;), but if objections are strong enough, the classes will be split.
 */

require 'Zend/Registry.php';
require 'Zend/Debug.php';
require 'Zend/Controller/Plugin/Abstract.php';
require 'Zend/Date.php';


class ZFDemo
{
    public static $environment; // which application configuration variant? live, staging, etc.

    public static $registry; // set of <key,value> pairs for misc. information

    public static $view; // view used to collect and render final output

    public static function bootstrap($installDir, $appDir, $configEnv)
    {

        /*
         * Controller Workflow Summary
         *********************************
         * Zend_Controller_Front - manages the overall workflow.
         *
         * Zend_Controller_Request* - accessors for controllers and actions (and status .. e.g. already dispatched)
         *
         * Zend_Controller_Router - exactly once per request, calculates correct controllers and
         *                 actions based on environment in Zend_Controller_Request*
         *
         * Zend_Controller_Dispatcher - transfers flow of execution to controllers and actions in request object
         *                 (Zend_Controller_Request*). Process repeats until no more actions are scheduled.
         *
         * Zend_Controller_Action* - userland controller classes containing userland actions
         *
         * Zend_Controller_Response* - contains the output of the executed actions
         */

        // STAGE 1: Initializations / Loading Configuration
        $frontController = self::stage1($installDir, $appDir, $configEnv);
        if ($frontController === false) {
            ZFDemo_Log::log(_('STAGE 1 failed (Initializations / Loading Configuration)'));
            return false;
        }

        $response = self::stage2($frontController);  // STAGES 3 to 6 occur inside STAGE 2

        // STAGES 3 to 5 occur in an action controller: /zfdemo/forum/*/controllers/*Controller.php
        //   STAGE 3: Choose, create, and optionally update models using business logic.
        //   STAGE 4: Apply business logic to create a presentation model for the view.
        //   STAGE 5: Choose view template and submit presentation model to view template for rendering.

        // STAGE 6 occurs in a view template: /zfdemo/forum/*/views/*.phtml
        //   STAGE 6: Render results in response to request.

        if ($response === false) {
            ZFDemo_Log::log(_('STAGE 2-6 failed (Find the right action and execute it.)'));
            return false;
        }

        // STAGE 7: Render results in response to request.
        if (false === self::stage7($response)) {
            ZFDemo_Log::log(_('STAGE 7 failed (Render results in response to request.)'));
            return false;
        }

        return true;
    }


    /**
     * STAGE 1: Initializations / Loading Configuration
     */
    public static function stage1($installDir, $appDir, $configEnv)
    {
        /////////////////////////////
        // ==> SECTION: mvc <==
        $ds = DIRECTORY_SEPARATOR; // too much typing ;)

        ZFDemo_Log::log('$appDir =' . $appDir);

        self::$registry = Zend_Registry::getInstance(); // the "master" registry

        /////////////////////////////
        // ==> SECTION: l10n <==
        // determine user's locale from HTTP_ACCEPT_LANGUAGE
        self::$registry['userLocale'] = new Zend_Locale(Zend_Locale::BROWSER);

        /////////////////////////////
        // ==> SECTION: mvc <==
        self::$registry['appDir'] = $appDir;
        // add the application-specific source file path to PHP's include path for the Conventional Modular Layout
        set_include_path($appDir . PATH_SEPARATOR . get_include_path()); 
        ZFDemo_Log::log("PHP Include Path = \n    " . str_replace(':', "\n    ", ini_get('include_path')));

        // this application's configuration information
        $configDir    = $appDir . 'config' . $ds;
        self::$registry['configDir'] = $configDir;
        ZFDemo_Log::log('$configDir =' . $configDir);

        // persistent dynamic data, like log files or SQLite files
        $dataDir      = $appDir . 'data' . $ds;
        self::$registry['dataDir']   = $dataDir;
        ZFDemo_Log::log('$dataDir =' . $dataDir);

        /////////////////////////////
        // ==> SECTION: session <==
        // place for temporary files, like session data, cached data, compiled templates
        $temporaryDir = $appDir . 'temporary' . $ds;
        self::$registry['temporaryDir']   = $temporaryDir;
        ZFDemo_Log::log('$temporaryDir =' . $temporaryDir);


        /////////////////////////////
        // ==> SECTION: mvc <==
        self::$environment = $configEnv; // after this point, all defaults come from config files
        require 'Zend/Config/Ini.php';
        $config = new Zend_Config_Ini($configDir . 'config.ini', self::$environment, true);
        ZFDemo_Log::log("config.ini=" . print_r($config->toArray(), true));
        if (strpos($config->log, '/') !== 0) {
            $config->log = $dataDir . $config->log;
        }
        self::$registry['config'] = $config; // application configuration array

        date_default_timezone_set($config->timezone); // application default timezone

        // setup DB adapter for use by all models using "raw" SQL via PHP's PDO extension
        if ($config->db->modelSet === 'pdo') {
            $dsn = $config->db->dsn->toArray();
            foreach(array_diff_key($dsn, array('username', 'password')) as $key => $val) {
                $connect[] =  "$key=$val";
            }
            $db = new PDO( $config->db->type . ':' . implode(';', $connect), $dsn['username'], $dsn['password']);
            $db->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
        }
        /////////////////////////////
        // ==> SECTION: db <==
        else { // use ZF DB components
            require 'Zend/Db.php';
            require 'Zend/Db/Adapter/Pdo/Mysql.php';
            $db = new Zend_Db_Adapter_Pdo_Mysql($config->db->dsn->toArray());
            require 'Zend/Db/Table/Abstract.php';
            Zend_Db_Table_Abstract::setDefaultAdapter($db); // set the default for all future table objects
        }

        /////////////////////////////
        // ==> SECTION: mvc <==
        if (self::testDb($db) === false) {
            // sanity check of connection and zfdemo tables using Zend Db Adapter failed
            self::$registry['testDbFailed'] = true;
        }
        self::$registry['db'] = $db; // save for future reference within model classes

        require 'Zend/Controller/Front.php';
        $frontController = Zend_Controller_Front::getInstance(); // manages the overall workflow
        $frontController->setControllerDirectory(array(
            'default' => $appDir . 'default' . $ds . 'controllers', // install a default module
            'forum'   => $appDir . 'forum'   . $ds . 'controllers', // all forum related controllers
        ));

        // Initialize views
        require 'Zend/View.php';
        self::$view = new Zend_View($config->view->toArray());
        self::$view->strictVars(); // enables tracking/detection of typos and misspelled variables in views
        // do not show sensitive logs to requests from non-local networks
        if (self::isLocalRequest()) {
            self::$view->showLog = $config->view->showLog;
        }
        self::$view->sectionName = basename($installDir); // e.g. "section4_mvc"
        self::$view->viewSuffix = 'phtml'; // file name suffix for view script
        // default location shared by all modules
        self::$view->setScriptPath($appDir . 'default' . $ds . 'views' . $ds . 'scripts');
        ZFDemo_Log::log("scriptPaths=\n    " . implode("\n    ", self::$view->getScriptPaths()));

        self::$view->timezone = $config->timezone; // default timezone
        self::$view->now = $now = date('Y-m-d H:i:s');

        /////////////////////////////
        // ==> SECTION: l10n <==
        // $language = self::$registry['userLocale']->getBrowser(); // determine user's preferred language
        // date will have local server's time, but user's preferred format
        $now = Zend_Date::now(self::$registry['userLocale']);
        self::$registry['now'] = $now;
        self::$view->now = $now;

        /////////////////////////////
        // ==> SECTION: mvc <==
        // Cause controllers to use private views, but with inheritance (i.e. defaults in self::$view)
        $frontController->setParam('view', clone self::$view); // make private presentation model for controller
        $frontController->setParam('registry', self::$registry); // alternative to Zend_Registry::getInstance()

        //////////////////////////////
        // ==> SECTION: auth <==
        self::$registry['authenticationId'] = 0;

        //////////////////////////////
        // ==> SECTION: session <==
        // Enable tracking of all user sessions
        $sessionConfig = new Zend_Config_Ini($configDir . 'Zend_Session.ini', self::$environment, true); 
        $sessionConfig->save_path = $temporaryDir . $sessionConfig->save_path;
        ZFDemo_Log::log("Zend_Session.ini=" . print_r($sessionConfig->toArray(), true));
        require 'Zend/Session.php'; 
        Zend_Session::setOptions($sessionConfig->toArray());  
        Zend_Session::start();

        // configure the zfdemo session namespace container
        $zfSpace = new Zend_Session_Namespace('zfdemo');
        $time = time();
        if (!isset($zfSpace->startTime)) {
            Zend_Session::regenerateId(); // security feature
            $zfSpace->startDate = $now;
            $zfSpace->startTime = $time;
            /////////////////////////////
            // ==> SECTION: l10n  <==
            // same as date('Y-m-d H:i:s') except localized to the user's locale
            $zfSpace->startDate = $now->get(Zend_Date::ISO_8601, self::$registry['userLocale']);
            $zfSpace->startTime = $now->getTimestamp();
            $zfSpace->requests  = 1;
        } else {
            /////////////////////////////
            // ==> SECTION: l10n  <==
            $time = $now->getTimestamp();
            /////////////////////////////
            // ==> SECTION: session <==
            if (isset($zfSpace->lastVisit)) {
                $zfSpace->priorVisit = $zfSpace->lastVisit;
            } else {
                $zfSpace->priorVisit = $time;
            }
            $zfSpace->lastVisit = $time;

            // hours since last request
            self::$view->welcomeBackHours = floor(($zfSpace->lastVisit - $zfSpace->priorVisit) / 36);
            ZFDemo_Log::log("welcomeBackHours = " . self::$view->welcomeBackHours);
            $zfSpace->requests++; // side-effect: our session data-store modification time stays current with last visit

            //////////////////////////////
            // ==> SECTION: auth <==
            // purely for convenience, several parts of the application need to know if the user is authenticated:
            $authSpace = new Zend_Session_Namespace('auth');
            if (isset($authSpace->authenticationId)) {
                self::$registry['authenticationId'] = $authSpace->authenticationId;
            }
        }

        return $frontController;
    }


    /**
     * STAGE 2: Find the right action and execute it.
     */
    public static function stage2($frontController)
    {
        /////////////////////////////
        // ==> SECTION: auth <==
        $frontController->registerPlugin(new ZFDemo_ModuleInit()); // add plugin that initializes modules

        /////////////////////////////
        // ==> SECTION: mvc <==
        $frontController->returnResponse(true); // return the response (do not echo it to the browser)
        // show exceptions immediately, instead of adding to the response
        $frontController->throwExceptions(true); // without this no exceptions are thrown
        $config = self::$registry['config'];

        require 'lib/Controller/Action.php'; // ZFDemo's customized Zend_Controller_Action
        require_once 'Zend/Controller/Request/Http.php';
        $request = new Zend_Controller_Request_Http();
        require_once 'Zend/Controller/Response/Http.php';
        $response = new Zend_Controller_Response_Http();
        $response->append('body', ''); // initialize a body segment
        // safety shutoff, in case controllers are stuck in loop, each calling the other
        $maxDispatches = isset($config->maxDispatches) ? $config->maxDispatches : 5;
        // rerouteTo will designate either an error code (mapped to error controller), or module/controller/action
        $rerouteTo = $didRerouteTo = $rerouteToReason = null;
        if (isset(self::$registry['testDbFailed']) && substr($request->getRequestUri(), -12) !== '/index/reset') {
            // during STAGE 1, sanity check of the DB connection/tables failed, so reroute to informative page
            $rerouteTo = self::reroute($request, self::$registry['config']->testDbFailed, $frontController);
        }

        do {
            if ($rerouteTo) { // if a reroute was requested, process it now
                $didRerouteTo = self::reroute($request, $rerouteTo, $frontController, $rerouteToReason);
                $rerouteToReason = $rerouteTo = null;
            }

            /////////////////////////////
            // ==> SECTION: auth <==
            // Authenticate #1  ( see "Authenticate Where?" http://framework.zend.com/wiki/x/fUw )
            if ($config->adminRequiresAuthentication) {
                if (strpos($request->getPathInfo(), $config->authenticate->URI) !== false) {
                    // if an admin area was requested, and authentication has been enabled in config.ini
                    if (empty(self::$registry['authenticationId'])) { // if not already authenticated
                        $didRerouteTo = self::reroute($request, $config->authenticate, $frontController, 'Auth needed');
                    }
                }
            }

            /////////////////////////////
            // ==> SECTION: mvc <==
            try {
                // "Run" the configured MVC "program" - the calculated action of the selected controller
                $frontController->dispatch($request, $response);

            } catch (ZFDemo_Exception_Reroute $exception) {
                // action controller requested a reroute form of internal redirection
                $rerouteTo = $exception->getRouteTo();
                $suggestedHttpCode = $exception->getHttpCode();
                $rerouteToReason = 'Reroute: ' . $exception->getMessage() . '; ' . $exception->responseCodeAsText();

            } catch (Exception $exception) {
                // don't allow *any* exceptions to end this script, without handling them properly
                if (!$config->analyzeDispatchErrors) {
                    // we are not analyzing the errors, so 
                    self::doExit(404, $exception); // log event and exit
                }

                /////////////////////////////
                // ==> SECTION: except <==
                list($suggestedHttpCode, $rerouteToReason) = self::analyzeError($exception, $request, $response);

                // if we have not already tried dispatching this controller code
                if ($didRerouteTo['code'] !== $suggestedHttpCode) {
                    $rerouteTo = $suggestedHttpCode;
                    #$rerouteToReason = $exception->getMessage();
                } else {
                    // Already tried to dispatch the selected error controller, so now fail hard.
                    ZFDemo_Log::logDispatchFailure($didRerouteTo);
                    if (self::isLocalRequest() && $config->view->showLog) {
                        echo ZFDemo_Log::get($exception);
                    }
                    // Reader excercise: make output "pretty" when dispatching error controller fails
                    self::doExit($suggestedHttpCode, $exception,
                        ((is_int($didRerouteTo['code']) && $didRerouteTo['code'] > 400) ?
                        _('An additional error occurred while trying to use the error action controller.') : ''));
                }
            }
        } while ($rerouteTo && --$maxDispatches); // now loop to dispatch the alternative controller (if any)

        if ($maxDispatches === 0) {
            ZFDemo_Log::log(_('ERROR Too many reroutes. Controllers stuck in loop?') . $request->getRequestUri());
        }

        /////////////////////////////
        // ==> SECTION: except <==
        if (isset($suggestedHttpCode) && $suggestedHttpCode != 200) {
            // something unusual happened during dispatch, like a 404 or 500 error
            self::setHeaderStatus($suggestedHttpCode);
        }

        /////////////////////////////
        // ==> SECTION: mvc <==
        $baseUrl = $request->getBaseUrl(); // save for use in header/footer/site templates
        if (false === strpos($baseUrl, '/index.php')) {
            $baseUrl .= '/index.php';
        }
        self::$view->baseUrl = $baseUrl;

        if (ZFDemo::isLocalRequest()) {
            // Since this is only added for local network requests, this code
            // may remain in production applications to aid in ongoing development.
            // Add some helpful debugging information for inserting into HTML comments:
            self::$view->controller = $request->getControllerName();
            self::$view->module     = $request->getModuleName();
            self::$view->action     = $request->getActionName();
        }

        return $response;
    }


    /**
     * STAGE 7: Render results in response to request.
     */
    public static function stage7($response)
    {
        /////////////////////////////
        // ==> SECTION: mvc <==
        $response->renderExceptions(true); // show any excpetions in the visible output (i.e. debug mode)
        // OR: Handle exceptions thrown in the dispatch loop.
        // Examine the exception type, and then redirect to an error page.

        if (isset(self::$registry['ajax'])) {
            #$response->clearBody("any segments you don't want");
        } else {
            // Use UTF-8.  See "1. Content-type, Charset, DOCTYPE" in NOTES.txt 
            $response->setHeader('Content-type', 'text/html; charset=utf-8', true);
            ksort($_SERVER);
            self::$view->SERVER = $_SERVER;
            self::$view->log = ZFDemo_Log::get();

            /////////////////////////////
            // ==> SECTION: auth <==
            self::$view->authenticationId = self::$registry['authenticationId'];

            /////////////////////////////
            // ==> SECTION: mvc <==
            try {
                $doctype = self::$view->render('header.php');
                $title = $response->getBody('body');
                if (empty($title) || (stripos($response->getBody('body'), '<title>') === false)) {
                    // view did not include a title, so add a default title now
                    $doctype .= self::$view->render('title.php');
                }
                $response->prepend('doctype', $doctype);
                $response->appendBody(self::$view->render('footer.php'), 'footer');
            } catch (Exception $exception) {
                $msg = 'ERROR corrupt installation, can not find site template file(s) header.php or footer.php.';
                echo $msg;
                self::doExit(500, null, $msg); // reader excercise: smarter error handling for Zend_View exceptions
            }
        }

        //echo __FILE__,__LINE__;Zend_Debug::dump($response);exit; // debug only the response object
        $response->sendResponse(); // send final results to browser, including headers

        return true;
    }


    /////////////////////////////
    // ==> SECTION: except <==
    /**
     * reroute request to a different module, controller, action
     *
     * @param  Zend_Controller_Request_Http  $request    where we came from
     * @param  integer|array|Zend_Config     $rerouteTo  where we are rerouting to: HTTP code or module/controller
     * @param  Zend_Controller_Front*        $frontController 
     * @param  bool  $afterRouter  Is reroute occurring after or before router has completed?
     * @return  array   returns $config as array
     */
    public static function reroute(Zend_Controller_Request_Http $request, $rerouteTo,
                                   $frontController, $reason = null, $afterRouter = false)
    {
        // normalize the destination information, mapping error codes to configured controllers, if needed
        if (is_int($rerouteTo)) {
            $config = self::$registry['config'];
            if (empty($config->error->$rerouteTo)) {
                $code = '404';
            } else {
                $code = $rerouteTo;
            }
            $rerouteTo = $config->error->$code;
        }
        if ($rerouteTo instanceof Zend_Config) {
            $rerouteTo = $rerouteTo->toArray();
        }
        if (!isset($rerouteTo['code'])) {
            $rerouteTo['code'] = 200;
        }
        if (!is_array($rerouteTo)) {
            throw new ZFDemo_Exception(_('CRITICAL reroute() destination unusable (type "%1$s")', gettype($rerouteTo)), 500);
        }
        if ($reason === null && isset($rerouteTo['reason'])) {
            $reason = $rerouteTo['reason'];
        }
        ZFDemo_Log::logReroute($request, $rerouteTo, $reason); // log reroute "from" and "to"
        if (empty($rerouteTo['moduleName'])) {
            throw new ZFDemo_Exception(_('CRITICAL reroute() destination module missing'), 500);
        }
        if (empty($rerouteTo['controllerName'])) {
            throw new ZFDemo_Exception(_('CRITICAL reroute() destination controller missing'), 500);
        }
        if (empty($rerouteTo['actionName'])) {
            throw new ZFDemo_Exception(_('CRITICAL reroute() destination action missing'), 500);
        }

        $dispatcher = $frontController->getDispatcher();
        // make original request available to the controller via both ->_getParam() and ->getInvokeArg()
        $dispatcher->setParam('origRequest', clone $request); // accessible via
        $dispatcher->setParam('rerouteToReason', $reason);

        if ($afterRouter) {
            // rerouting after the router has completed requires a different technique:
            $request->setModuleName($rerouteTo['moduleName'])
                ->setControllerName($rerouteTo['controllerName'])
                ->setActionName($rerouteTo['actionName'])
                ->setParam('error', $reason) // e.g. 'Anonymous access not permitted.'
                ->setParam('origRequestUri', $request->REQUEST_URI)
                ->setDispatched(false);
        } else {
            // rerouting setup, when the router will be used (i.e. before routing):
            $request->setPathInfo(($rerouteTo['moduleName'] === 'default' ? '' :
                     '/' . $rerouteTo['moduleName'])
                   . '/' . $rerouteTo['controllerName']
                   . '/' . $rerouteTo['actionName']);
        }

        return $rerouteTo;
    }


    /////////////////////////////
    // ==> SECTION: mvc <==
    /*
     * If this server is on a local network, return true.
     *
     * Note: Only as reliable as your web server (i.e. not 100% foolproof).
     * Append '?nodebug' to the URI to force this application to consider the
     * request as "not local", which allows developers to see what public sees.
     * This application displays additional diagnostic information, when the
     * request is considered "local" instead of from the public Internet.
     *
     * http://en.wikipedia.org/wiki/Private_network
     * http://bugs.php.net/bug.php?id=18816 (i.e. must use string comparison)
     */
    public static function isLocalRequest()
    {
        static $local = null;
        if ($local !== null) {
            return $local;
        }
        $local = false;
        // For real-world applications, invert the logic below by requiring '?debug'
        // in order to enable the application to process the request as "local".
        if (isset($_REQUEST['nodebug'])) {
            return false; // allow developers to see what public sees
        }
        $addr = $_SERVER['REMOTE_ADDR'];
        if (strpos($addr, ',') !== false) {
            $ips = explode(',', $addr);
            $addr = trim($ips[0]); 
        }
        if (!strncmp($addr, '172.', 4)) {
            $octet2 = substr($addr, 4, 3);
            if ($octet2 > 15 && $octet2 < 32) {
                ZFDemo_Log::log(_('DEBUG Request is local.'));
                return $local = true;
            }
        }
        if (   strncmp($addr, '192.168.', 8)
            && strncmp($addr, '10.', 3)
            && strncmp($addr, '127.', 4)
            && strncmp($addr, '169.254.', 8)) {
            return $local = false;
        }
        ZFDemo_Log::log(_('DEBUG Request is local.'));
        return $local = true;
    }


    /////////////////////////////
    // ==> SECTION: except <==
    /**
     * Analyze the possible reasons why an exception occurred during routing, dispatching, or
     * otherwise trying to process the module's action controller.
     *
     * @param  Exception                     $exception  Exception to analyze.
     * @param  Zend_Controller_Request_Http  $request    Request object to process.
     * @param  Zend_Controller_Response_Http $response   Response object used for this request.
     * @return array(integer, string)    (Suggested HTTP response code, reason for reroute)
     */
    public static function analyzeError(Exception $exception, Zend_Controller_Request_Http $request,
                                        Zend_Controller_Response_Http $response)
    {
        if (self::isLocalRequest()) {
            self::$view->request   = Zend_Debug::dump($request, null, false);
            self::$view->response  = Zend_Debug::dump($response, null, false);
            self::$view->exception = self::filterException($exception);
            /**
             * Some alternatives to aid in debugging your custom ZF applications:
             * echo __FILE__,__LINE__;Zend_Debug::dump($request);exit;// debug the request object
             * echo __FILE__,__LINE__;Zend_Debug::dump($exception);exit;   // debug the exception object
             * ZFDemo_Log::log(Zend_Debug::dump(self::$view, null, true)); // alternatively, add debug info to log
             */
        }

        ZFDemo_Log::log(_('ERROR when processing')
            . '   Module: ' . $request->getModuleName()
            . ' / Controller: ' . $request->getControllerName()
            . ' / Action: ' . $request->getActionName(), true);
        $msg = 'ERROR ' . _($exception->getMessage());

        if ($exception instanceof Zend_Controller_Dispatcher_Exception) { 
            // Dispatcher object used by the Front Controller.
            $frontController = Zend_Controller_Front::getInstance();
            $dispatcher = $frontController->getDispatcher();
            if (!$dispatcher->isDispatchable($request)) {

                // Exception message: Invalid controller specified <controller name>
                $className = $dispatcher->getControllerClass($request);
                if (!$className) {                                                                    
                    $msg = _('ERROR Unable to find controller class "%1$s".', $className);

                } else {
                    $realFilename = self::getDispatchedFilename($request);
                    $msg = _('ERROR Unable to find controller file "%1$s".', $realFilename);
                }
                $code = 404;

            } else {
                // Exception message: Controller '<controller class name>' is not an instance of Zend_Controller_Action
                $code = 500;
            }

        } else if ($exception instanceof ZFDemo_Exception) {
            $code = $exception->getCode(); // general internal error
            if (!is_int($code) || $code < 200 || $code > 599) {
                $code = 500;
            }

        } else if ($exception instanceof Zend_Controller_Exception) {
            /*
             * Exception messages:
             *     No action specified and no default action has been defined in __call() for 
             *     <controller class name>() does not exist and was not trapped in __call()
             */
            $code = 404;
            $realFilename = self::getDispatchedFilename($request);
            #$msg = _('ERROR Unable to find controller file "%1$s".', $realFilename);
            $msg .= "  - $realFilename";

        } else if ($exception instanceof Zend_View_Exception) {
            /*
             * Exception messages:
             *      script 'indexindex.phtml' not found in path     
             */
            $code = 500;
            $msg .= _('View script paths =\n    ') . implode("\n    ", self::$view->getScriptPaths());

        } else { // unknown exception, but die ungracefully to remind me to add code for this exception ;)
            $code = 500;
            if (self::isLocalRequest()) {
                $msg = 'ALERT ' . _('Unknown Exception on line #%1$d! ', __LINE__) . $exception->getMessage();
                // ZFDemo_Log::show($exception);
                // self::doExit(500, $exception, $msg);
            }
        }

        ZFDemo_Log::log($msg, true); // prepend the exception analysis message to the log
        // ZFDemo_Log::show();exit; // skip error page and just show the log now

        if (self::isLocalRequest()) {
            try {
                self::$view->exceptionMessage = $msg;
                // insert $msg before body
                $response->insert('exception', self::$view->render('exception.php'), 'body', true);
                // now append dump *after* body segment
                $response->insert('dump', self::$view->render('dump.php'), 'body', false);
            } catch (Exception $exception) {
                echo $msg; // reader excercise: prettier error handling
                self::doExit(500, null, _('ERROR corrupt installation, can not find "dump.php".'));
            }
        }

        return array($code, $msg);
    }


    /**
     * returns the calculated filename of the dispatched controller
     */
    protected static function getDispatchedFilename($request)
    {
        $frontController = Zend_Controller_Front::getInstance();
        $dispatcher = $frontController->getDispatcher();
        $className = $dispatcher->getControllerClass($request);
        $fileSpec   = $dispatcher->classToFilename($className);
        $dispatchDir = $dispatcher->getDispatchDirectory();
        return $dispatchDir . DIRECTORY_SEPARATOR . $fileSpec;
    }


    /////////////////////////////
    // ==> SECTION: mvc <==
    /**
     * Operational test for the MySQL database instance:
     * @param  PDO|Zend_Db_Adapter_Pdo_Mysql  $db  Zend Db adapter instance
     * @return boolean   sanity check result
     */
    private static function testDb($db)
    {
        $expected = array('posts'=>null, 'users'=>null, 'topics'=>null, 'attachments'=>null);
        foreach ($db->query('show tables') as $row) {
            unset($expected[array_pop($row)]);
        }
        if (count($expected)) {
            ZFDemo_Log::log(_('database missing tables: '). implode(' ', $expected));
            return false;
        }
        return true;
    }


    /////////////////////////////
    // ==> SECTION: mvc <==
    /**
     * Send the $exception's code or $code as the formal HTTP status of the response.
     *
     * @param  integer    $code  suggested HTTP response code, if exception fails to provide one
     * @param  Exception  $exception   OPTIONAL HTTP status code
     * @return string   status code and description
     */
    private static function setHeaderStatus($code, Exception $exception = null)
    {
        if ($exception instanceof ZFDemo_Exception_Reroute) {
            $status = $exception->getStatus();
        } else {
            switch ($code) {
                case 404:
                    $status = '404 Not Found';
                    break;
                case 500:
                    $status = '500 Internal Server Error';
                    break;
                default:
                    require_once 'Zend/Http/Response.php';
                    $status = $code . ' ' . (Zend_Http_Response::responseCodeAsText($code));
            }
        }
        if ('cgi' !== substr(php_sapi_name(), 0, 3) && !empty($_SERVER['SERVER_PROTOCOL'])) {
            header($_SERVER['SERVER_PROTOCOL'].' '.$status, true);
        } else {
            header('Status: '.$status, true);
        }
        /////////////////////////////
        // ==> SECTION: email <==
        if (intval(substr($status, 0, 3)) >= 500) {
            // if the error $code is serious (e.g. 500-599 application errors),
            self::notifyAdmin($code, $status); // then send an email to the admin
        }

        return $status;
    }
    

    /////////////////////////////
    // ==> SECTION: mvc <==
    /**
     * Common exit point for "unusual" situations.
     * Send a raw status page, without using anything else that might break (e.g. any other files).
     *
     * @param  integer    $code         suggested HTTP response code, if exception fails to provide one
     * @param  Exception  $exception    exception thrown while processing dispatch()
     * @param  string     $msg          Log message related to the "unusual" situation
     * @param  bool       $doubleError  OPTIONAL  error occurred while processing error controller
     * @return void
     */
    private static function doExit($code, Exception $exception, $msg = null)
    {
        // set appropriate HTTP header indicating page does not exist
        $status = self::setHeaderStatus($code, $exception);
        if (self::isLocalRequest() && $msg === null) {
            $msg = $exception->getMessage(); // only append sensitive debug info. for local requests
        }
        $status .= ' ' . $msg;
        echo _('An error occured while processing your request:') . "\n$status\n\n";

        ZFDemo_Log::log(_('EXIT') . ": $status");
        exit;
    }


    /////////////////////////////
    // ==> SECTION: email <==
    /**
     * Notify the registered administrator of a problem via email.
     *
     * @param  integer  $code   HTTP status code sent with the response
     * @param  string   $msg    Log message related to the "unusual" situation
     * @return void
     */
    public static function notifyAdmin($code, $msg)
    {
        $registry = Zend_Registry::getInstance();
        $config = $registry['config'];
        if ($config->mail) {
            require 'lib/ZFDemo_Mail_Transport_File.php';
            $mail = new Zend_Mail(); 
            $transport = new ZFDemo_Mail_Transport_File(null, $registry['dataDir'] . $config->mail->filename);
            $mail->setBodyText("Code: $code\n$msg"); 
            $mail->setFrom($config->mail->fromEmail, $config->mail->fromName); 
            $mail->addTo($config->mail->toEmail, $config->mail->toName); 
            $mail->setSubject($config->mail->subject); 
            $mail->send($transport); // send the email using the special transport
        }
    }


    /////////////////////////////
    // ==> SECTION: except <==
    /**
     * Filter an exception object by truncating the lengthy trace output.
     */
    public static function filterException(Exception $exception)
    {
        return preg_replace('/(\[&quot;trace:private&quot;.{1,4096}).*/s',
            '\\1 => ' . _('rest not shown') . '</pre>', Zend_Debug::dump($exception, null, false));
    }
}


/////////////////////////////
// ==> SECTION: mvc <==
/**
 * Ultra simple logging utility specialized to:
 * - OVERWRITES log file for every request to simplify debugging and identifying
 *   logged messages of the most recent request
 * - minimize configuration dependencies
 * - minimize code
 * - help users quickly diagnose any problems with their installation or environment
 * - automatically sync messages to a disk-based file (helpful if no output in browser)
 *
 * ZF includes a powerful logging utility introduced in a later section.
 */
class ZFDemo_Log
{
    /////////////////////////////
    // ==> SECTION: log <==
    /* Logging component configured to log different things to two different locations.
     * @var Zend_log
     */
    private static $_logger = null;

    /* Queue of strings to log (queued until self::$_logger becomes useable)
     * @var array
     */
    private static $_messages;

    /* Array of priorities used to map strings to logger priorities below
     * @var array
     */
    static $priorities = array(
        'EMERG'    => 0, // Emergency: system is unusable
        'EMERGENCY'=> 0, // Emergency: system is unusable
        'ALERT'    => 1, // Alert: action must be taken immediately
        'CRIT'     => 2, // Critical: critical conditions
        'CRITICAL' => 2, // Critical: critical conditions
        'ERR'      => 3, // Error: error conditions
        'ERROR'    => 3, // Error: error conditions
        'WARN'     => 4, // Warning: warning conditions
        'WARNING'  => 4, // Warning: warning conditions
        'NOTICE'   => 5, // Notice: normal but significant condition
        'INFO'     => 6, // Informational: informational messages
        'DEBUG'    => 7  // Debug: debug messages
    );


    /////////////////////////////
    // ==> SECTION: mvc <==
    /* Very simple running log of "debug" messages, used to highlight inner workings of demo.
     * @var string
     */
    private static $_log = ''; 


    /**
     * Useful if you modify the demo, and need to quickly see the debug log in your browser.
     *
     * @param mixed $var  Append a dump of var to the log, before showing it.
     */
    public static function show($var = null)
    {
        if ($var) {
            self::log(Zend_Debug::dump($var, null, false));
        }
        echo "<html><head><title>" . _('ZF Demo Debug Log') . "</title></head>\n<body>\n<pre>";
        echo htmlentities(self::$_log, ENT_QUOTES, 'UTF-8');
        echo '</pre></body></html>';
    }


    /**
     * Retrieve the running log, possibly appending the contents of $var first.
     *
     * @param mixed $var  Append a dump of var to the log, before showing it.
     */
    public static function get($var = null)
    {
        if ($var) {
            self::log(Zend_Debug::dump($var, null, false));
        }
        return self::$_log;
    }


    /**
     * Accumulate log messages, but also append them to a running log file for easy viewing.
     */
    public static function log($msg, $before = null)
    {
        static $flushed = false;
        if ($before) {
            self::$_log = "$msg\n" . self::$_log;
        } else {
            self::$_log .= "$msg\n";
        }

        $registry = Zend_Registry::getInstance();
        // performance is not an issue for this demo, so just sync to disk everytime
        if (isset($registry['config'])) {
            if ($flushed) {
                file_put_contents($registry['config']->log, "$msg\n", FILE_APPEND);
            } else {
                file_put_contents($registry['config']->log, self::$_log);
                $flushed = true;

    /////////////////////////////
    // ==> SECTION: log <==
                require 'Zend/Log.php';
                require 'Zend/Log/Formatter/Simple.php';
                require 'Zend/Log/Writer/Stream.php';
                $filename = $registry['config']->log . '.error';
                $errorLogStream = @fopen($filename, 'wb', false);
                if ($errorLogStream === false) {
                    throw new ZFDemo_Exception("CRITICAL: Can not open log file '$filename' for writing", 500);
                }
                $filename = $registry['config']->log . '.verbose';
                $verboseLogStream = @fopen($filename, 'wb', false);
                if ($verboseLogStream === false) {
                    throw new ZFDemo_Exception("CRITICAL: Can not open log file '$filename' for writing", 500);
                }

                // create a custom formatter for our log writers, so that we can include the custom user %id% field
                $formatter = new Zend_Log_Formatter_Simple('%timestamp% %priorityName% (%priority%) %id%: %message%'
                    . PHP_EOL);

                // create a log writer for errors, using the previously opened stream
                $errorWriter = new Zend_Log_Writer_Stream($errorLogStream);
                $errorWriter->setFormatter($formatter);
                $filter = new Zend_Log_Filter_Priority(Zend_Log::ERR);
                $errorWriter->addFilter($filter); // log messages lower than priority ERR are ignored by this writer

                // create a log writer for all messages, using the previously opened stream
                $verboseWriter = new Zend_Log_Writer_Stream($verboseLogStream);
                $verboseWriter->setFormatter($formatter); // all log messages are logged by this writer

                // finally, create a logger and attach the two log writers to the logger
                self::$_logger = new Zend_Log($errorWriter);
                self::$_logger->addWriter($verboseWriter);
                self::$_logger->pause = false;
            }
        }

        self::$_messages[] = $msg;

        if (self::$_logger && !self::$_logger->pause) {
            while (count(self::$_messages)) {
                $msg = array_shift(self::$_messages);
                // Each log event has: timestamp, message, priority, and priorityName
                // Now we add an additional datum, the authentication id (if any) of the current user.
                if (isset($registry['authenticationId']) && isset($registry['authenticationId']['username'])) {
                    $id = $registry['authenticationId']['username'] . '.' . $registry['authenticationId']['realm'];
                } else {
                    $id = '-';
                }
                self::$_logger->setEventItem('id', $id);
                $priority = substr($msg, 0, $pos = strpos($msg, ' '));
                if (isset($priorities[$priority])) {
                    $priority = $priorities[$priority];
                    $msg = substr($msg, $pos+1);
                } else {
                    $priority = 7; // default to DEBUG priority
                }
                self::$_logger->log($msg, $priority);
            }
        }
    }


    /**
     * Pauses the Zend_Log logger above. Messages are not sent, but are added to the queue.
     */
    public static function pauseLogger()
    {
        self::$_logger->pause = true;
    }


    /**
     * Resumes the Zend_Log logger above. Queued messages sent.
     */
    public static function resumeLogger()
    {
        self::$_logger->pause = false;
    }


    /**
     * Empties the Zend_Log logger queue, discarding unsent messages.
     */
    public static function clearLogger()
    {
        self::$_messages = array();
    }


    /////////////////////////////
    // ==> SECTION: except <==
    /**
     * Log a message showing a reroute.
     * 
     * @param  Zend_Controller_Request_Http  $request
     * @param  Zend_Config|array  $rerouteTo   where to reroute the request to
     * @return void
     */
    public static function logReroute(Zend_Controller_Request_Http $request, $rerouteTo, $reason)
    {
        if ($rerouteTo instanceof Zend_Config) {
            $reroute = $rerouteTo->toArray();
        } else {
            $reroute = $rerouteTo;
        }
        self::log('Reroute from URI: ' .
            $request->getRequestUri() .
            ' (' .
            $request->getModuleName() . '/' .
            $request->getControllerName() . '/' .
            $request->getActionName() . '/' .
            ') to: ' .
            $reroute['moduleName'] . '/' .
            $reroute['controllerName'] . '/' .
            $reroute['actionName'] .
            " Reason: $reason"
        );
    }

    public static function logDispatchFailure($didRerouteTo)
    {
        self::log(_('ERROR Dispatching failed to complete for: \'')
            . $didRerouteTo['moduleName'] . '_'
            . $didRerouteTo['controllerName']
            . $didRerouteTo['actionName'] . "'.\n", true);
    }
}


class ZFDemo_Exception extends Exception
{}


/////////////////////////////
// ==> SECTION: except <==
class ZFDemo_Exception_Reroute extends ZFDemo_Exception
{
    /**
     * Integer status code: http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     * @var integer   HTTP status code
     *
     */
    protected $httpCode;

    /*
     * An array or Zend_Config with named keys for the module, controller, and action to reroute to.
     * The action may also contain any trailing portion of the PATHINFO.
     * @var array|Zend_Config
     */
    protected $routeTo;

    /**
     * Normalizes $code into an HTTP status code, and saves the request destination for the reroute.
     */
    public function __construct($message, $code, $routeTo = null)
    {
        parent::__construct($message, $code);
        if (!is_int($code) || $code < 200 || $code > 599) {
            $this->httpCode = 404;
        } else {
            $this->httpCode = $code;
        }
        if ($routeTo === null) {
            $this->routeTo = $this->httpCode;
        } else {
            $this->routeTo = $routeTo;
        }
    }

    /**
     * Maps HTTP status code to correct HTTP response text.
     */
    public function responseCodeAsText()
    {
        require_once 'Zend/Http/Response.php';
        return Zend_Http_Response::responseCodeAsText($this->code);
    }

    public function getHttpCode()
    {
        return $this->httpCode;
    }

    public function getRouteTo()
    {
        return $this->routeTo;
    }

    /**
     * return HTTP status code and text
     */
    public function getStatus()
    {
        return $this->httpCode . ' ' . $this->responseCodeAsText();
    }
}


/////////////////////////////
// ==> SECTION: auth <==
class ZFDemo_ModuleInit extends Zend_Controller_Plugin_Abstract
{
    /**
     * safety shutoff, in case controllers are stuck in loop, each calling the other
     * @var integer
     */
    private $maxDispatches;

    /**
     * memory of previous reroute (for detection of loop problems)
     * @var integer
     */
    private $didRerouteTo = null;


    public function __construct()
    {
        // safety shutoff, in case controllers are stuck in loop, each calling the other
        $this->maxDispatches = isset($config->maxDispatches) ? $config->maxDispatches : 5;
    }

/*
    // these are fun to watch, to see what the router does with a particular Request URI
    public function routeStartup(Zend_Controller_Request_Abstract $request)
    {
        //return;
        echo 'routeStartup: ';
        print_r($request);
        exit;
    }

    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        $this->view->baseUrl = $this->_request->getBaseUrl();
        //return;
        echo 'routeShutdown: ';
        print_r($request);
        exit;
    }
*/

    /**
     * Modules are semi-standalone, encapsulated "mini" applications.
     * Thus, there is a need for a global module initialization process,
     * for the dispatched module to provide shared initializations for
     * all controllers within this module.
     */
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $frontController = Zend_Controller_Front::getInstance();
        $moduleName = $request->getModuleName();
        $registry = Zend_Registry::getInstance();
        $moduleInit = $registry['appDir'] . $moduleName . DIRECTORY_SEPARATOR . $moduleName . '.php';
        $modulesIni = $registry['configDir'] . 'modules.ini';

        // If a module has an initialization / authorization component
        if (is_readable($moduleInit)) {
            include_once $moduleInit;

            if (!isset($registry['config']->cache)) {
                // if caching of module ini files is not enabled, just load it now
                $config = new Zend_Config_Ini($modulesIni, $moduleName);
            }

            /////////////////////////////
            // ==> SECTION: auth <==
            $rerouteTo = null;
            /* Allow modules.ini to disable anonymous access to individual modules.
             * Authenticate #2  ( see "Authenticate Where?" http://framework.zend.com/wiki/x/fUw )
             */
            if (empty($config->allowAnonymousUse)) {
                if (empty($registry['authenticationId'])) { // if not already authenticated
                    $rerouteTo = $config->authenticate;
                }
            }

            if ($rerouteTo === null) {
                // Access control could also be selectively applied to entire modules, instead of inside the module:
                $rerouteTo = call_user_func(array('ZFModule_' . ucfirst($moduleName), 'moduleAuth'), $config, $request);
            }

            if ($rerouteTo) {
                if (--$this->maxDispatches > 0) {
                    if ($rerouteTo == $this->didRerouteTo) {
                        $msg = _('ERROR Looping detected in preDispatch().') . $request->getRequestUri();
                        ZFDemo_Log::log($msg);
                        throw new ZFDemo_Exception_Reroute($msg, 500);
                    }
                    ZFDemo::reroute($request, $rerouteTo, $frontController, null, true);
                    $this->didRerouteTo = $rerouteTo;
                } else {
                    $msg = _('ERROR Too many reroutes in preDispatch(). Looping?') . $request->getRequestUri();
                    ZFDemo_Log::log($msg);
                    throw new ZFDemo_Exception_Reroute($msg, 500);
                }
            }
        }
        // dynamic configurations, DRY, O(1) with respect to number of modules
        // http://framework.zend.com/issues/browse/ZF-1125
        // $frontController->setControllerDirectory(array($moduleName => Zend_Registry::get('appDir')
        //     . $moduleName . $ds . 'controllers'));

        return;
    }
}
