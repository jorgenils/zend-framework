<?php
/**
 * ZFTestManager - Becuase testing is important
 *
 * This file relys strictly on PHPUnit to run tests that test the Zend Framework.  This
 * script is several components that meet one specific end.
 * 
 * @copyright  2006 Zend Technologies
 * @license    http://framework.zend.com/license/   
 * @version    $Id$
 * @link       http://framework.zend.com/wiki/display/ZFDEV/Zend+Framework+Testing+Standards
 * @since      File available since Release 0.2
 */

require_once 'PHPUnit/TextUI/TestRunner.php';
require_once 'PHPUnit/Util/Getopt.php';
require_once 'PHPUnit/Framework/TestSuite.php';
    
// set the correct error reporting level
if (ini_get('error_reporting') != 4095) {
    echo "\nSetting Error Reporting to E_ALL | E_STRICT\n";
    error_reporting(E_ALL | E_STRICT);
}

// load any external definitions
if (is_readable('TestConfiguration.ini')) {
    $GLOBALS['TESTCONFIG'] = parse_ini_file('TestConfiguration.ini', true);
} elseif (is_readable('TestConfiguration.ini.dist')) {
    $GLOBALS['TESTCONFIG'] = parse_ini_file('TestConfiguration.ini.dist', true);
}

/**
 * ZFTestManager
 * 
 * This is the master class for the test manager.  Technically, it has only one
 * method, getConfig(),  which is accessible to all test suites, test cases and tests.
 *
 */
class ZFTestManager
{
    /**
     * GetConfig - get the config directives for your module
     *
     * @param string $section
     * @return array
     */
    public static function getConfig($section)
    {
        if (isset($GLOBALS['TESTCONFIG'][$section])) {
            return $GLOBALS['TESTCONFIG'][$section];
        } else {
            return null;
        }
    }
}
    
/**
 * ZFTestManager_Command - this is the test manager core.
 *
 */
class ZFTestManager_Command
{
    const SKIP_SUITE = -1;
    
    private static $_cwd = null;
    
    private static $_configuration = array();
    
    private static $_arguments = array();
    
    private static $_test_suites = array();
    
    /**
     * @access public
     * @static
     */
    public static function main()
    {
        self::_initConfiguration();
        self::_initSuitesAndTests();
        self::_initArguments();
        
        $runner    = new PHPUnit_TextUI_TestRunner;

        $master_suite = new PHPUnit_Framework_TestSuite('Zend Framework');
        
        $loadable_suites = array();

        if (count(self::$_arguments['suite_filter']) > 0) {
            
            $first_pass = true;
            
            foreach (self::$_arguments['suite_filter'] as $suite_filter) {
                
                // if the suite filter is not valid class name (extranious char.) - continue
                if (!preg_match("/[a-z0-9\-_.]/i", $suite_filter))
                    continue;
                
                
                if ( ($suite_filter[0] != "@") && (strpos($suite_filter, "#") === false) ) {
                    
                    /**
                     * This block will parse all filters below the named suite (branch)
                     */

                    foreach (self::$_test_suites as $test_suite) {
                        if (strpos($test_suite, $suite_filter) !== false) {
                            
                            $suite_config = ZFTestManager::getConfig($test_suite);
                            
                            if (isset($suite_config['disabled']) && ($suite_config['disabled'] == true) )
                                continue;
                            
                            $loadable_suites[$test_suite] = null;
                        }
                    }
                    
                } elseif ($suite_filter[0] == "@") {
                    
                    /**
                     * This block will mark only
                     */
                    
                    $suite_filter_base = ltrim($suite_filter, "@");
                    
                    if (in_array($suite_filter_base, self::$_test_suites))
                        $loadable_suites[$suite_filter_base] = null;
                    else
                        continue;
                        
                    // remove branches deeper than this one if they exist
                    foreach (self::$_test_suites as $test_suite_id => $test_suite) {
                        if (strpos($test_suite, $suite_filter_base) === 0)
                            unset(self::$_test_suites[$test_suite_id]);
                    }
                    
                    continue;
                    
                } elseif (strpos($suite_filter, "#") !== false) {
                    
                    list($suite_filter_base, $suite_filter_testcase) = split("#", $suite_filter);
                    
                    if ($suite_filter_base != "" && $suite_filter_testcase != "")
                        $loadable_suites[$suite_filter_base] = $suite_filter_testcase;
                    
                }
                
            }
            
        } else {
            
            foreach (self::$_test_suites as $suite_name) {
                
                $suite_config = ZFTestManager::getConfig($suite_name);
                
                if (isset($suite_config['disabled']) && ($suite_config['disabled'] == true))
                    continue;

                $loadable_suites[$suite_name] = null;
            }

        }
        
        foreach ($loadable_suites as $suite_name => $sub_suite) {
            $runner->getLoader()->load($suite_name . "_AllTests", str_replace("_", DIRECTORY_SEPARATOR, $suite_name) . DIRECTORY_SEPARATOR . "AllTests.php");
            $classname = $suite_name . "_AllTests";
            $master_suite->addTestSuite(call_user_func(array($classname, "suite"), $sub_suite));
        }
        
        if ($master_suite->testCount() == 0) {
            self::_printVersionString();
            echo "\nNo tests found in the current working directory.\n\n";
            exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
        }
       
        try {
            self::_printVersionString(false);
            $runner_parameters = array_merge(self::$_configuration['runner_parameters'], self::$_arguments['runner_parameters']);
            $result = $runner->doRun($master_suite, $runner_parameters);
        } catch (Exception $e) {
            throw new RuntimeException(
              'Could not create and run test suite: ' . $e->getMessage()
            );
        }
        
        if ($result->wasSuccessful())
            exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
        else if($result->errorCount() > 0)
            exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
        else
            exit(PHPUnit_TextUI_TestRunner::FAILURE_EXIT);
    }
    
    public static function getCwd()
    {
        return self::$_cwd;
    }
    
    protected static function _initConfiguration()
    {
        self::$_cwd = getcwd();
        
        $config = ZFTestManager::getConfig('ZFTestManager');
        
        // set the library path
        if (isset($config['library_path'])) {
            $inc_path = $config['library_path'];
        
            if (isset($config['library_path2']))
                $inc_path .= PATH_SEPARATOR . $config['library_path2'];
        
            set_include_path(get_include_path() . PATH_SEPARATOR . $inc_path);
            
        } else {
            
            // load default which is one dir up in ./library/
            set_include_path(get_include_path() . PATH_SEPARATOR . dirname(self::$_cwd) . DIRECTORY_SEPARATOR . 'library');
            
        }

        self::$_configuration['runner_parameters'] = array();
        
        if (isset($config['report_directory']) && (file_exists($config['report_directory'])) && extension_loaded('xdebug'))
            self::$_configuration['runner_parameters']['reportDirectory'] = $config['report_directory'];
    }
    
    /**
     * @access protected
     * @static
     */
    protected static function _initArguments()
    {
        $arguments['suite_filter'] = array();
        $arguments['runner_parameters'] = array();
        
        $longOptions = array(
            'list-suites',
            'new-suite=',
            'suite-filter=',
            'test-filter=',
            'help',
            'verbose',
            'version'
        );
        
        try {
            $options = PHPUnit_Util_Getopt::getopt(
                $_SERVER['argv'],
                'd:',             // short options
                $longOptions      // long options
                );
        } catch (RuntimeException $e) {
            PHPUnit_TextUI_TestRunner::showError($e->getMessage());
        }
        
        foreach ($options[0] as $option) {
            
            switch ($option[0]) {
                
                case 'd':
                    
                    $ini = explode('=', $option[1]);

                    if (isset($ini[0])) {
                        if (isset($ini[1]))
                            ini_set($ini[0], $ini[1]);
                        else
                            ini_set($ini[0], TRUE);
                    }

                    break;

                case '--list-suites':
                    
                    self::_listSuites();
                    exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
                    break;

                case '--new-suite':
                    
                    self::_createNewSuite($option[1]);
                    exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
                    break;
                
                case '--suite-filter':
                    
                    $arguments['suite_filter'] = explode(",", $option[1]);
                    break;
                    
                case '--test-filter':
                    
                    $arguments['runner_parameters']['filter'] = "/" . $option[1] . "/i";
                    break;
                
                case '--help':
                    
                    self::_showHelp();
                    exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
                    break;

                case '--verbose':
                    
                    $arguments['runner_parameters']['verbose'] = true;
                    break;
                    
                case '--version':
                
                    self::_printVersionString();
                    exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
                    break;
                    
            }
            
        }
            
        self::$_arguments = $arguments;
        return true;
    }
    
    protected static function _printVersionString($internal_version = true)
    {
        print "Zend Framework - Unit Test Runner and Manager Script\n";
        print "built on ";
        
        if ($internal_version)
            PHPUnit_TextUI_TestRunner::printVersionString();
    }

    protected static function _initSuitesAndTests()
    {
        self::$_test_suites = self::_recurseSuitesAndTests(self::$_cwd);
    }
    
    protected static function _listSuites()
    {
        self::_printVersionString();
        
        foreach (self::$_test_suites as $test_suite)
            echo "  " . $test_suite . "\n";
        
        return;
    }
    
    protected static function _recurseSuitesAndTests($directory, $suite_parent = "")
    {
        $items = array();
        
        $dir_iterator = new DirectoryIterator($directory);
        
        foreach ($dir_iterator as $dir_object) {
            
            if ($dir_object->isDot())
                continue;
            
            if (($dir_object->isDir()) && is_readable($dir_object->getPathname() . DIRECTORY_SEPARATOR . "AllTests.php"))
                $items[] = ltrim($suite_parent . "_" . $dir_object->getFilename(), "_");
            
            if ($dir_object->isDir()) {
                
                if ( count($sub_suites = self::_recurseSuitesAndTests($directory . DIRECTORY_SEPARATOR . $dir_object->getFilename(), $suite_parent . "_" . $dir_object->getFilename()) ) > 0) {
                    foreach ($sub_suites as $sub_suite) {
                        $items[] = $sub_suite;
                    }
                }
                
            }

        }

        return $items;
    }
    
    protected static function _createNewSuite($suite_name)
    {
        self::_printVersionString();

        echo "Attempting to create a suite shell named: $suite_name\n\n";
        
        if (in_array($suite_name, self::$_test_suites)) {
            echo "WARNING: A suite by that name already exist!\n";
            exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
        }
        
        // alltests template -gzcompressed template stored at hex data
        $at = "78DA6D54DB4EDB40107DF7578C04122150FBDD2D2A114D5424"
            . "4054097DE065B55EAFED559C5D772FD014F1EF9D1D5FB85A91"
            . "6CEFCC9C3967E638DFBE774D9724D97C9EC01CCE05F7B23676"
            . "0F00F7529774D671B1E5B5C4233858DF5D6E96EC6671BD3CA0"
            . "980BC518BED3CA6FA4F30E03599288963BF7A6802DDA961240"
            . "FEF508EEE07E15DFAFB9C67A3B85D9A270DE72E193A72436ED"
            . "42D12A01CE738FB7C352395EB4B28433E0D6F2FDECF8EB6769"
            . "56FE09CA4AC784D195AADF67C78B1E4879BCE6B00ECACBD931"
            . "7C01DF2807F8F38D444023B63092CB733764EDA46F4C99026C"
            . "6272FF36223DAAB6052B7DB01A38C442C2065311A4A72954D6"
            . "ECFA0E7C27A144B2C2E3E809518E4801A74AF94AD780521EA4"
            . "F6CA6807C1E10894066125A720718E99400C51AE8411A53422"
            . "ECB0B22F893DE3766165B1F3A3B15B24BC55294CF9E3FD5750"
            . "A87DEDB9F57063103C1F238053EA97495A0BEC17BCC1DD08F3"
            . "202DF6F18D35A16EA819C32B26A7E835784100D07CF75657FA"
            . "06DFC0B06C1C22292BF650CA8A87D69F022F4B02AF02F6A7A8"
            . "8E73F4864E5F7779B10C590078811C51EDB2CE07531CC571B0"
            . "0B720ABBD42A923D1A9D3291192C8564064F612F14EEA487CA"
            . "583CAE15CA202E44CF11B7D74C3ED0FC6053E293BEDFC3B993"
            . "121AEFBB3CCBAA7169E93F249D0AB3CBE2F63214D9B57C9FDD"
            . "AF7E2C7F6751D0C9B4DF934DEFA0135CA52EB92DDD843CB8F4"
            . "F6E76DFC80D954C226D70EA9D9275F5915B4886BEB2D373BC4"
            . "BF03D6BBEF0C346A3FA69AA7E98B1B9A75E84DEDF3BCCF9D31"
            . "7671B558AF193B45A7AC2EAF96F1E9056BD8C373F29CFC073F"
            . "8777E2";
        
        // get the string representation of the template
        for ($i=0; $i<(strlen($at)); $i+=2)
            $data .= chr(hexdec($at[$i].$at[$i+1]));

        $alltests_template = gzuncompress($data);

        $alltests_template = str_replace("#SUITE_NAME#", $suite_name, $alltests_template);
        $alltests_template = str_replace("#SUITE NAME#", str_replace("_", " ", $suite_name), $alltests_template);
        
        $alltests_path = self::$_cwd . DIRECTORY_SEPARATOR . str_replace("_", DIRECTORY_SEPARATOR, $suite_name);
        
        if (!file_exists($alltests_path)) {
            echo "Creating directory " . str_replace("_", DIRECTORY_SEPARATOR, $suite_name) . " in suite.. \n";
            mkdir($alltests_path, 0755, true);
        }
        
        $alltests_file = $alltests_path . DIRECTORY_SEPARATOR . "AllTests.php";
        echo "Writing AllTests.php file..\n";
        file_put_contents($alltests_file, $alltests_template);
        
        echo "Complete.\n\n";
        exit(PHPUnit_TextUI_TestRunner::SUCCESS_EXIT);
    }
    
    protected static function _showHelp() 
    {
        self::_printVersionString();

        print "Usage:\n" .
              "  " . basename(__FILE__, ".php") . " [options] - if installed in a bin with path to php.\n" .
              "  php " . basename(__FILE__) . " [options] - from unit test directory.\n\n" .
              
              "  * With no options, " . basename(__FILE__, ".php") . " will run all found tests.\n" .
              "  * All tests must pass with error reporting set at E_ALL | E_STRICT.\n" .
              "  * All tests must pass COMPLETELY in ISOLATION before commiting.\n\n" .
              
              "Options:\n" . 
              "  --list-suites            Prints a hierarchical list of suites for testing.\n" .
              "  --new-suite <name>       Create a new suite at the <name> location.\n" .
              "  --suite-filter <filter>  Where filter is: \n" . 
              "                              Zend_Suite - run this suite and its sub-suites. \n" .
              "                              @Zend_Suite - run only this suite.               \n" .
              "                              Zend_Suite::MyTestCase - run only this TestCase in this suite. \n" .
              "  --test-filter <filter>   Where filter is a regex of the test cases you wish to run.\n" .
              "  --help                   Prints this usage information.\n" .
              "  --version                Prints the version and exits.\n\n" .
              "  -d key[=value]           Sets a php.ini value.\n\n";
    
        return;
    }

}

abstract class ZFTestManager_AllTests_Abstract
{
    public static $disabled = array();
    public static $requires_config = array();
    
    abstract public static function suite($sub_suite = null);
    
    protected static function _suite($alltests_class, $alltests_file, $sub_suite = null)
    {
        $suite_name = basename($alltests_class, "_AllTests");
        $readable_name = str_replace("_", " ", $suite_name);
        
        $suite = new PHPUnit_Framework_TestSuite($readable_name);

        $tests = self::_getTests(dirname($alltests_file));
        
        // trickery to get around the statics problem in php
        eval("\$disabled = " . $alltests_class . "::\$disabled;");
        eval("\$requires_config = " . $alltests_class . "::\$requires_config;");
        
        foreach ($tests as $test => $filename) {
            
            $test_file_path = dirname($alltests_file) . DIRECTORY_SEPARATOR . $filename;
            
            $test_class_name = $suite_name . "_" . $test;
            
            $test_class_config = ZFTestManager::getConfig($test_class_name);
            
            if ( !is_null($sub_suite) && (strpos($test, $sub_suite) === false) )
                continue;

            // if test is disabled by default, and not turned on, continue
            if (in_array($test_class_name, $disabled) && $test_class_config['enabled'] != true)
                continue;
            
            // if test is not disabled by default, but is disabled in config, continue
            if (!in_array($test_class_name, $disabled) && $test_class_config['disabled'] == true)
                continue;
            
            if (in_array($test_class_name, $requires_config) && $test_class_config === null) {
                
                echo "Class {$test_class_name} is enabled, but requires config options to be set in TestConfiguration.ini.\n";
                echo "See {$test_file_path} for more details.\n";
                exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
                
            }
            
            PHPUnit_Util_Fileloader::checkAndLoad($test_file_path);
            
            try {
                $suite->addTestSuite($test_class_name);
            } catch (InvalidArgumentException $e) {
                echo "File for {$test_class_name} was loaded, but a class named {$test_class_name} was not found.\n";
                exit(PHPUnit_TextUI_TestRunner::EXCEPTION_EXIT);
            }
            
            //$suite->addTestFile($test_file_path);
            
        }
        
        return $suite;
    }
    
    protected static function _getTests($directory_path)
    {
        $directory_iterator = new DirectoryIterator($directory_path);

        $tests = array();
        
        foreach ($directory_iterator as $directory) {
            
            if ($directory->isDot())
                continue;

            if (strripos($directory, "Test.php") !== false)
                $tests[basename($directory->getFilename(), ".php")] = $directory->getFilename();

        }
        
        return $tests;
    }
    
}

ZFTestManager_Command::main();

