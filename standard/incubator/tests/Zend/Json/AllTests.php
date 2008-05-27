<?php
if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Json_AllTests::main');
}

require_once dirname(__FILE__) . '/../../TestHelper.php';

require_once 'Zend/Json/ServerTest.php';
require_once 'Zend/Json/Server/ErrorTest.php';
require_once 'Zend/Json/Server/RequestTest.php';
require_once 'Zend/Json/Server/ResponseTest.php';
require_once 'Zend/Json/Server/SmdTest.php';
require_once 'Zend/Json/Server/Smd/ServiceTest.php';

class Zend_Json_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Zend Framework - Zend_Json');

        $suite->addTestSuite('Zend_Json_ServerTest');
        $suite->addTestSuite('Zend_Json_Server_ErrorTest');
        $suite->addTestSuite('Zend_Json_Server_RequestTest');
        $suite->addTestSuite('Zend_Json_Server_ResponseTest');
        $suite->addTestSuite('Zend_Json_Server_SmdTest');
        $suite->addTestSuite('Zend_Json_Server_Smd_ServiceTest');
       
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Json_AllTests::main') {
    Zend_Json_AllTests::main();
}
