<?php
// ini_set('display_errors', true);
// error_reporting(E_ALL);
// ini_set('log_errors', 1);
// ini_set('error_log', realpath(dirname(__FILE__) .  '/../cache/logs') . '/test_error.log');

ini_set('memory_limit', -1);
if (!defined('PHPUnit2_MAIN_METHOD')) {
    define('PHPUnit2_MAIN_METHOD', 'AllTests::main');
}

$PATH = realpath(dirname(__FILE__) . '/../');
set_include_path($PATH . PATH_SEPARATOR . $PATH . '/tests/' . PATH_SEPARATOR . get_include_path());
require_once 'PHPUnit2/Framework/TestSuite.php';
require_once 'PHPUnit2/TextUI/TestRunner.php';

class AllTests
{
    /**
     * Root directory of tests
     */
    public static $root;
    
    /**
     * Pattern against which to test files to see if they contain tests
     */
    public static $filePattern;

    /**
     * Pattern against which to test directories to see if they are for source
     * code control metadata
     */
    public static $sscsPattern = '/(CVS|\.svn)$/';

    /**
     * Associative array of test class => file
     */
    public static $list = array();
    
    public static $dirSeparator = '/';

    /**
     * Main method
     * 
     * @static
     * @access public
     * @return void
     */
    public static function main()
    {
        PHPUnit2_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Create test suite by recursively iterating through tests directory
     * 
     * @static
     * @access public
     * @return PHPUnit2_Framework_TestSuite
     */
    public static function suite()
    {
        $suite = new PHPUnit2_Framework_TestSuite('Zend_Framework');

        self::$root = realpath(dirname(__FILE__));
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        	self::$dirSeparator = '\\';
        }
        
        self::$filePattern = '|^' . addCSlashes(self::$root . self::$dirSeparator,'\\')  . '(.*?Test)\.php$|';
        self::createTestList(new RecursiveDirectoryIterator(self::$root));

        foreach (self::$list as $class => $file) {
            require_once $file;
            $suite->addTestSuite($class);
        }

        return $suite;
    }

    /**
     * Recursively iterate through a directory looking for test classes
     * 
     * @static
     * @access public
     * @param RecursiveDirectoryIterator $dir 
     * @return void
     */
    public static function createTestList(RecursiveDirectoryIterator $dir)
    {
        for ($dir->rewind(); $dir->valid(); $dir->next()) {
            if ($dir->isDot()) {
                continue;
            }

            $file = $dir->current()->getPathname();

            if ($dir->isDir()) {
                if (!preg_match(self::$sscsPattern, $file) 
                    && $dir->hasChildren()) 
                {
                    self::createTestList($dir->getChildren());
                }
            } elseif ($dir->isFile()) {
                if (preg_match(self::$filePattern, $file, $matches)) {
                    self::$list[str_replace(self::$dirSeparator, '_', $matches[1])] = $file;
                }
            }
        }
    }
}

/**
 * Run tests
 */
if (PHPUnit2_MAIN_METHOD == 'AllTests::main') {
    AllTests::main();
}
