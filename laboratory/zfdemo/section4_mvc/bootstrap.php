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
        // ==> SECTION: mvc <==
        // Cause controllers to use private views, but with inheritance (i.e. defaults in self::$view)
        $frontController->setParam('view', clone self::$view); // make private presentation model for controller
        $frontController->setParam('registry', self::$registry); // alternative to Zend_Registry::getInstance()

        return $frontController;
    }


    /**
     * STAGE 2: Find the right action and execute it.
     */
    public static function stage2($frontController)
    {
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

            /////////////////////////////
            // ==> SECTION: mvc <==
            try {
                // "Run" the configured MVC "program" - the calculated action of the selected controller
                $frontController->dispatch($request, $response);

            } catch (Exception $exception) {
                // don't allow *any* exceptions to end this script, without handling them properly
                if (!$config->analyzeDispatchErrors) {
                    // we are not analyzing the errors, so 
                    self::doExit(404, $exception); // log event and exit
                }
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
        if ('cgi' !== substr(php_sapi_name(), 0, 3) && !empty($_SERVER['SERVER_PROTOCOL'])) {
            header($_SERVER['SERVER_PROTOCOL'].' '.$status, true);
        } else {
            header('Status: '.$status, true);
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
            }
        }
    }
}


class ZFDemo_Exception extends Exception
{}
