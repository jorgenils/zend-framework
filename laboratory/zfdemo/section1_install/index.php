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
 * @version    $Id: index.php 124 2007-04-16 22:40:30Z gavin $
 *
 * Installation debug toolkit for http://framework.zend.com/wiki/display/ZFDEV/Tutorial
 * This is *not* a working demo, but only a set of tools and
 * diagnostics to help with installation issues.
 * Also, the code below is not intended to show best practices,
 * but only to help debug problems getting the real demo working.
 *
 * Change localhost to the correct name for your virtual web server setup:
 * http://localhost/zfdemo_debug/
 */

//$installDir = '/cygdrive/c/gavin/home/lighttpd/zf/zfdemo/section1_install/';
$installDir = 'section1_install'; // absolute real paths are also ok

error_reporting(E_ALL|E_STRICT); // verbose
require 'Zend/Registry.php';
require 'Zend/Date.php';
require 'Zend/Debug.php';

if (ZFDemo::bootstrap($installDir) === false) { // Run!
    ZFDemo_Log::show();
}

class ZFDemo
{
    public static $environment; // which application configuration variant? live, staging, etc.

    public static $registry; // set of <key,value> pairs for misc. information

    public static $view; // view used to collect and render final output

    public static function bootstrap($installDir)
    {
        if (self::testEnvironment() === false) {
            echo 'Environment fails to meet minimum requirements for this demo tutorial.';
            return;
        }

        // where to find this application's configuration (using Conventional Modular Layout)
        $ds = DIRECTORY_SEPARATOR; // too much typing ;)
        if ($installDir[0] === '/') {
            $tmp = $installDir;
        } else {
            $tmp = dirname(__FILE__) . $ds . '..' . $ds . '..' . $ds . 'zfdemo' . $ds . $installDir;
        }


        // STAGE 0: Initializations / Loading Configuration

        ZFDemo_Log::log("looking for application directory in: realpath($tmp" . $ds . ')');
        $appDir = realpath($tmp) . $ds;
        ZFDemo_Log::log('$appDir =' . $appDir);

        self::$registry = Zend_Registry::getInstance();
        self::$registry['appDir'] = $appDir;
        if (!is_readable($appDir)) {
            ZFDemo_Log::log("ERROR: Application directory is not readable (path problem).\n", true);
            return false;
        }

        // this application's configuration information
        $configDir = realpath($appDir . $ds . 'config' . $ds ) . $ds;
        self::$registry['configDir'] = $configDir;
        ZFDemo_Log::log('$configDir =' . $configDir);
        if (!is_readable($configDir)) {
            ZFDemo_Log::log("ERROR: Application configuration directory 'config' is not readable (path problem).\n", true);
            return false;
        }

        // persistent dynamic data, like log files or SQLite files
        $dataDir   = realpath($appDir . $ds . 'data'   . $ds ) . $ds;
        self::$registry['dataDir']   = $dataDir;
        ZFDemo_Log::log('$dataDir =' . $dataDir);
        if (!is_readable("$dataDir")) {
            ZFDemo_Log::log("ERROR: Application 'data' directory is not readable (path problem).\n", true);
            return false;
        }

        // temporary data, like PHP session state files
        $temporaryDir   = realpath($appDir . $ds . 'temporary'   . $ds ) . $ds;
        self::$registry['temporaryDir']   = $temporaryDir;
        ZFDemo_Log::log('$temporaryDir =' . $temporaryDir);
        if (!is_readable("$temporaryDir")) {
            ZFDemo_Log::log("ERROR: Application 'temporary' directory is not readable (path problem).\n", true);
            return false;
        }

        // add the application-specific source file path to PHP's include path for the Conventional Modular Layout
        set_include_path($appDir . PATH_SEPARATOR . get_include_path()); 
        ZFDemo_Log::log("PHP Include Path = \n    " . str_replace(':', "\n    ", ini_get('include_path')));

        self::$environment = 'sandbox'; // after this point, all defaults come from config files
        require 'Zend/Config/Ini.php';
        $config = new Zend_Config_Ini($configDir . 'config.ini', self::$environment, true);
        ZFDemo_Log::log("config.ini=" . print_r($config->asArray(), true));
        if (strpos($config->log, '/') !== 0) {
            $config->log = $dataDir . $config->log;
        }
        self::$registry['config'] = $config; // application configuration array

        date_default_timezone_set($config->timezone);

        $sessionConfig = new Zend_Config_Ini($configDir . 'Zend_Session.ini', self::$environment, true); 
        $sessionConfig->save_path = $temporaryDir . $sessionConfig->save_path;
        ZFDemo_Log::log("Zend_Session.ini=" . print_r($sessionConfig->asArray(), true));
        require 'Zend/Session.php'; 
        Zend_Session::setOptions($sessionConfig->asArray());  
        Zend_Session::start();

        /*
         * The zfdemo will not work unless the following code results creates a session file
         * in your save_path folder, * with file contents like:
         *    foo|a:2:{s:3:"bar";s:5:"apple";s:4:"time";s:19:"2007-02-20 21:30:36";}
         */
        $testSpace = new Zend_Session_Namespace('spaceFoo');
        $testSpace->keyBar = 'valueBar';
        $testSpace->time = time();
        $testSpace->date = date('Y-m-d H:i:s');
        // preemptively write session file now
        Zend_Session::writeClose();

        self::testPdo($config); // sanity check connection and zfdemo tables using PDO

        // Now test using ZF's MySQL PDO DB adapter:
        require 'Zend/Db.php';
        require 'Zend/Db/Adapter/Pdo/Mysql.php';
        // setup our DB adapter
        $db = new Zend_Db_Adapter_Pdo_Mysql($config->db->asArray());

        self::$registry['db'] = $db;
        self::testDb($db); // sanity check connection and zfdemo tables using Zend Db Adapter


        // STAGE 1: Prepare the front (primary) controller.

        require 'Zend/Controller/Front.php';
        $frontController = Zend_Controller_Front::getInstance(); // manages the overall workflow

        $baseUrl = substr($_SERVER['PHP_SELF'], 0, strpos($_SERVER['PHP_SELF'], '/index.php'));
        ZFDemo_Log::log("baseUrl=$baseUrl");
        //$frontController->setBaseUrl($baseUrl);

        $frontController->setControllerDirectory(array(
            'default' => $appDir . 'default' . $ds . 'controllers', // install a default module
            'forum'   => $appDir . 'forum' . $ds . 'controllers' // all forum related controllers
        ));
        
        // Initialize views
        require 'Zend/View.php';
        self::$view = new Zend_View();
        self::$view->sectionName = basename($installDir); // e.g. "section1_install"
        self::$view->setScriptPath($appDir . 'default' . $ds . 'views');
        self::$view->showLog = true;
        ZFDemo_Log::log("scriptPaths=\n    " . implode("\n    ", self::$view->getScriptPaths()));
        $frontController->setParam('view', self::$view);


        // STAGE 2: Find the right action and execute it.
        
        // Use routes to calculate controllers and actions to execute

        // Dispatch calculated actions of the selected controllers
        $frontController->returnResponse(true); // return the response (do not echo it to the browser)

        // Use UTF-8.  See "1. Content-type, Charset, DOCTYPE" in NOTES.txt 
        require_once 'Zend/Controller/Response/Http.php';
        $response = new Zend_Controller_Response_Http();
        $response->setHeader('Content-type', 'text/html; charset=utf-8', true);
        $response->setBody(self::$view->render('header.php'));

        try {

            require_once 'Zend/Controller/Request/Http.php';
            $request = new Zend_Controller_Request_Http();

            // show exceptions immediately, instead of adding to the response
            $frontController->throwExceptions(true); // without this no exceptions are thrown

            // similar to "running" the configured MVC "program"
            $frontController->dispatch($request, $response);

        } catch (Zend_Controller_Dispatcher_Exception $exception) {

            self::analyzeError($frontController->getDispatcher(), $exception, $request, $response);

            return false;
        }

        // STAGES 3 to 5 occur in an action controller: /zfdemo/forum/*/controllers/*Controller.php
        // STAGE 6 occurs in a view template: /zfdemo/forum/*/views/*.phtml


        /**
         * STAGE 7: Render results in response to request.
         */
        $response->renderExceptions(true); // show any excpetions in the visible output (i.e. debug mode)
        // OR: Handle exceptions thrown in the dispatch loop.
        // Examine the exception type, and then redirect to an error page.
        
        ksort($_SERVER);
        self::$view->SERVER = $_SERVER;
        self::$view->log = ZFDemo_Log::get();
        $response->appendBody(self::$view->render('footer.php'), 'footer');
        //Zend::debug($frontController->getRequest());exit // debug the request object
        //Zend_Debug::dump($response);exit;  // examine the inner details of the response object
        $response->sendResponse(); // send final results to browser, including headers
    }


    /**
     * Test DB first, using raw PHP PDO driver, before using Zend DB Adapter.
     */
    public static function testPdo($config)
    {
        $expected = array('posts'=>null, 'users'=>null, 'topics'=>null, 'attachments'=>null);
        $dbConfig = $config->db->asArray();
        try {
            $dbh = new PDO('mysql:host='.$dbConfig['host'].';dbname='.$dbConfig['dbname'], $dbConfig['username'],
                $dbConfig['password']);
            foreach ($dbh->query('SHOW TABLES') as $row) {
                unset($expected[$row[0]]);
            }
            $dbh = null;
        } catch (PDOException $e) {
            ZFDemo_Log::log("Unable to connect to your database! " . $e->getMessage(), true);
            return false;
        }
        if (count($expected)) {
            ZFDemo_Log::log('database missing tables: '. implode(' ', $expected), true);
            return false;
        }
        return true;
    }


    public static function testEnvironment()
    {
        if (version_compare('5.1.4', PHP_VERSION) > 0) {
            echo "Please upgrade to a newer version of PHP (5.1.4+) for this demo.\n";
            return false; 
        }

        require_once 'Zend/Version.php';
        if (Zend_Version::compareVersion('0.9.2beta') > 0) {
            echo "Please upgrade to a newer version of ZF for this demo.\n";
            return false; 
        }

        $extensions = get_loaded_extensions();
        $expected = array(
            'PDO' => null,
            'pdo_mysql' => null,
            'ctype' => null,
            'SPL' => null,
            'pcre' => null,
            'session' => null
        );
        sort($extensions);
        ZFDemo_Log::log("PHP extensions loaded = \n    " . implode("\n    ", $extensions));
        foreach(array_keys($expected) as $ext) {
            if (extension_loaded($ext)) {
                unset($expected[$ext]);
            }
        }
        foreach($expected as $ext) {
            ZFDemo_Log::log("The '$ext' extension is required, but not currently loaded by PHP.");
        }
        if (count($expected)) {
            return false; 
        }
        return true;
    }


    public static function analyzeError($dispatcher, $exception, $request, $response)
    {
        if (!$dispatcher->isDispatchable($request)) {
            $className = $dispatcher->getControllerClass($request);
            if (!$className) {                                                                    
                ZFDemo_Log::log("ERROR: Unable to find controller class '$className'.\n", true);
                return;
            }
            $fileSpec    = $dispatcher->classToFilename($className);
            $dispatchDir = $dispatcher->getDispatchDirectory();
            $realFilename= $dispatchDir . $ds . $fileSpec;
            ZFDemo_Log::log("ERROR: Unable to find controller file '$realFilename'.\n", true);
            return;
        }

        echo "<h1>Request</h1>";
        Zend_Debug::dump($request);

        echo "<h1>Response</h1>";
        Zend_Debug::dump($response);

        echo "<h1>Exception</h1>";
        Zend_Debug::dump($e);
    }


    /**
     * Operational test for the MySQL database instance:
     */
    private static function testDb(Zend_Db_Adapter_Pdo_Mysql $db)
    {
        // (re)load the DB instance with the ZF Demo tables and data (overwrites existing tables)
        $registry = Zend_Registry::getInstance();
        $filename = $registry['configDir'] . 'zfdemo.' . $registry['config']->db->type . '.sql';
        $statements = preg_split('/;\n/', file_get_contents($filename, false));
        foreach ($statements as $blocks) {
            $sql = '';
            foreach (explode("\n", $blocks) as $line) {
                if (empty($line) || !strncmp($line, '--', 2)) {
                    continue;
                }
                $sql .= $line . "\n";
            }
            $sql = trim($sql);
            if (!empty($sql)) {
                $db->query($sql);
            }
        }

        // test to see if tables were loaded
        $expected = array('posts'=>null, 'users'=>null, 'topics'=>null, 'attachments'=>null);
        try {
            foreach ($db->query('show tables') as $row) {
                unset($expected[array_pop($row)]);
            }
            if (count($expected)) {
                ZFDemo_Log::log('database missing tables: '. implode(' ', $expected));
                return false;
            }
            return true;
        } catch (Exception $exception) {
            echo <<<EOD
                <html><head><title>Missing PDO MySQL driver?</title></head><body>
                <pre>
                Please make sure the PDO MySQL extension has been installed and enabled.';
                Either install the PECL packages below, or compile PHP with:
                     <b>--enable=mysql --enable-pdo --with-pdo-mysql</b>

                <a href="http://www.php.net/manual/en/install.pecl.php">http://www.php.net/manual/en/install.pecl.php</a>
                <a href="http://pecl.php.net/package/PDO">http://pecl.php.net/package/PDO</a>
                <a href="http://pecl.php.net/package/mysql">http://pecl.php.net/package/mysql</a>

EOD;
            echo "Exception: <b>", $exception->getMessage(), "</b>: \n";
            echo substr(print_r($exception,true), 0, 1024);
            echo '</pre>';
            phpinfo(E_ALL);
            exit;
        }
    }
}


/**
 * Ultra simple logging utility specialized to:
 * - minimize configuration dependencies
 * - minimize code
 * - help users quickly diagnose any problems with their installation or environment
 * - automatically sync messages to a disk-based file (helpful if no output in browser)
 */
class ZFDemo_Log
{
    // very simple running log of "debug" messages to highlight inner workings of demo
    public static $log = ''; 


    /**
     * Accumulate log messages, but also append them to a running log file for easy viewing.
     */
    public static function log($msg, $before = null)
    {
        static $flushed = false;
        if ($before) {
            self::$log = "$msg\n" . self::$log;
        } else {
            self::$log .= "$msg\n";
        }

        $registry = Zend_Registry::getInstance();
        // performance is not an issue, so just sync to disk everytime
        if (isset($registry['config'])) {
            if ($flushed) {
                file_put_contents($registry['config']->log, "$msg\n", FILE_APPEND);
            } else {
                file_put_contents($registry['config']->log, self::$log);
            }
            $flushed = true;
        }
    }


    /*
     * Useful if you modify the demo, and need to quickly see the debug log in your browser.
     */
    public static function show()
    {
        echo "<html><head><title>ZF Demo Debug Log</title></head>\n<body>\n<pre>";
        echo htmlentities(self::$log, ENT_QUOTES, 'UTF-8');
        echo '</pre></body></html>';
    }


    public static function get()
    {
        return self::$log;
    }
}
?>
