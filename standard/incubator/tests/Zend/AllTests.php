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
 * @package    Zend
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


if (!defined('PHPUnit_MAIN_METHOD')) {
    require_once dirname(__FILE__) . '/../TestHelper.php';
    define('PHPUnit_MAIN_METHOD', 'Zend_AllTests::main');
}

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

require_once 'Zend/Controller/AllTests.php';
// require_once 'Zend/Crypt/AllTests.php';
require_once 'Zend/Db/AllTests.php';
require_once 'Zend/Dojo/AllTests.php';
require_once 'Zend/Dom/AllTests.php';
require_once 'Zend/Json/AllTests.php';
require_once 'Zend/Data/PaginatorTest.php';
require_once 'Zend/Service/AllTests.php';
require_once 'Zend/Test/AllTests.php';
require_once 'Zend/Text/AllTests.php';
require_once 'Zend/TimeSyncTest.php';

/**
 * @category   Zend
 * @package    Zend
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend');

        /*
         * Perform the tests for Zend_Controller component now.
         */
        $suite->addTest(Zend_Controller_AllTests::suite());
        
        /*
         * Perform the tests for Zend_Data_Paginator component now.
         */
        $suite->addTest(Zend_Data_Paginator_AllTests::suite());

        /*
         * Perform the tests for Zend_Crypt component now.
         *
         * Currenty there's a missing exception class; turning it off
         */
        // $suite->addTest(Zend_Crypt_AllTests::suite());

        /*
         * Perform the tests for Zend_Db component now.
         */
        $suite->addTest(Zend_Db_AllTests::suite());

        /*
         * Perform the tests for Zend_Dojo component now.
         */
        $suite->addTest(Zend_Dojo_AllTests::suite());

        /*
         * Perform the tests for Zend_Dom component now.
         */
        $suite->addTest(Zend_Dom_AllTests::suite());

        /*
         * Perform the tests for Zend_Json component now.
         */
        $suite->addTest(Zend_Json_AllTests::suite());

        /*
         * Perform the tests for Zend_Service component now.
         */
        $suite->addTest(Zend_Service_AllTests::suite());

        /*
         * Perform the tests for Zend_Test component now.
         */
        $suite->addTest(Zend_Test_AllTests::suite());

        /*
         * Perform the tests for Zend_Text component now.
         */
        $suite->addTest(Zend_Text_AllTests::suite());

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_AllTests::main') {
    Zend_AllTests::main();
}
