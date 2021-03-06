<?php

/**
 * Zend Framework
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
 * @category   Zend
 * @package    Zend_Session
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Session_AllTests::main');
}


/**
 * Test helper
 */
require_once dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'TestHelper.php';


/**
 * Zend_Session tests need to be output buffered because they depend on headers_sent() === false
 *
 * @see http://framework.zend.com/issues/browse/ZF-700
 */
ob_start();


/**
 * @category   Zend
 * @package    Zend_Session
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Session_AllTests
{
    /**
     * Runs this test suite
     *
     * @return void
     */
    public static function main()
    {
        /**
         * PHPUnit_TextUI_TestRunner
         */
        require_once 'PHPUnit/TextUI/TestRunner.php';

        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    /**
     * Creates and returns this test suite
     *
     * @return PHPUnit_Framework_TestSuite
     */
    public static function suite()
    {
        /**
         * PHPUnit_Framework_TestSuite
         */
        require_once 'PHPUnit/Framework/TestSuite.php';

        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Session');

        require_once 'SessionTest.php';

        $suite->addTestSuite('Zend_SessionTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Session_AllTests::main') {
    Zend_Session_AllTests::main();
}
