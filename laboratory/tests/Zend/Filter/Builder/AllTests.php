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
 * @package    UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Filter_Builder_AllTests::main');
}

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/TestHelper.php';

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

// Require_once suites here
require_once 'Zend/Filter/Builder/FluentFacadeTest.php';
require_once 'Zend/Filter/Builder/FilterFactoryTest.php';
require_once 'Zend/Filter/Builder/IntegrationTest.php';


class Zend_Filter_Builder_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Filter_Builder');

        // Add suites here
        $suite->addTestSuite('Zend_Filter_Builder_FluentFacadeTest');
        $suite->addTestSuite('Zend_Filter_Builder_FilterFactoryTest');
        $suite->addTestSuite('Zend_Filter_Builder_IntegrationTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Filter_Builder_AllTests::main') {
    Zend_Filter_Builder_AllTests::main();
}
