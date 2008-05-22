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
 * @version    $Id: AllTests.php 5928 2007-07-31 04:39:49Z stas $
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Validate_Builder_AllTests::main');
}

require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/TestHelper.php';

require_once 'PHPUnit/Framework/TestSuite.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

// Require_once suites here
require_once 'Zend/Validate/Builder/ErrorManagerTest.php';
require_once 'Zend/Validate/Builder/FluentFacadeTest.php';
require_once 'Zend/Validate/Builder/ValidatorFactoryTest.php';
require_once 'Zend/Validate/Builder/IntegrationTest.php';


/**
 * @category   Zend
 * @package    Zend_Validate_Builder
 * @subpackage UnitTests
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Validate_Builder_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Validate_Builder');

        // Add suites here
        $suite->addTestSuite('Zend_Validate_Builder_ErrorManagerTest');
        $suite->addTestSuite('Zend_Validate_Builder_FluentFacadeTest');
        $suite->addTestSuite('Zend_Validate_Builder_ValidatorFactoryTest');
        $suite->addTestSuite('Zend_Validate_Builder_IntegrationTest');

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Validate_Builder_AllTests::main') {
    Zend_Validate_Builder_AllTests::main();
}
