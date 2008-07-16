<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_View_Helper_AllTests::main');
}

require_once dirname(__FILE__) . '/../../../Zend/View/Helper/HtmlFlashTest.php';
require_once dirname(__FILE__) . '/../../../Zend/View/Helper/HtmlObjectTest.php';
require_once dirname(__FILE__) . '/../../../Zend/View/Helper/HtmlPageTest.php';
require_once dirname(__FILE__) . '/../../../Zend/View/Helper/HtmlQuicktimeTest.php';

/**
 * @category   Zend
 * @package    Zend_View
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_View_Helper_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_View_Helper');

        $suite->addTestSuite('Zend_View_Helper_HtmlFlashTest');
        $suite->addTestSuite('Zend_View_Helper_HtmlObjectTest');
        $suite->addTestSuite('Zend_View_Helper_HtmlPageTest');
        $suite->addTestSuite('Zend_View_Helper_HtmlQuicktimeTest');
        $suite->addTestSuite('Zend_View_Helper_PaginationControlTest');
        
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_View_Helper_AllTests::main') {
    Zend_View_Helper_AllTests::main();
}
