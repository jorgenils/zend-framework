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
 * @package    Zend_Ldap
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../../TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Ldap_AllTests::main');
}

require_once 'Zend/Ldap/ConnectTest.php';
require_once 'Zend/Ldap/BindTest.php';
require_once 'Zend/Ldap/CanonTest.php';

/**
 * @category   Zend
 * @package    Zend_Ldap
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Ldap_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Ldap');

		if (defined('TESTS_ZEND_LDAP_ENABLED') && TESTS_ZEND_LDAP_ENABLED) {
	        $suite->addTestSuite('Zend_Ldap_ConnectTest');
   	    	$suite->addTestSuite('Zend_Ldap_BindTest');
    	    $suite->addTestSuite('Zend_Ldap_CanonTest');
		}

        return $suite;
    }
}

echo __LINE__ . "\n";
if (PHPUnit_MAIN_METHOD == 'Zend_Ldap_AllTests::main') {
    Zend_Ldap_AllTests::main();
}