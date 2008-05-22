<?php
/* Zend Framework 1.0 - Basic MVC/Database example application */

require_once 'Zend/Loader.php';
Zend_Loader::registerAutoload();

class GridTest extends PHPUnit_Framework_TestCase
{

    public function setUp()
    {
        $this->_front = Zend_Controller_Front::getInstance();
        $this->_front->returnResponse(true);

        $dotdot = dirname(dirname(__FILE__));
        $this->_front->setControllerDirectory($dotdot . '/controllers');

        $this->_request = new Zend_Controller_Request_Http();
        $this->_front->setRequest($this->_request);

        $db = Zend_Db::factory('Mock');
        Zend_Registry::set('defaultDb', $db);
    }

    protected function _xquery($query, $htmlString)
    {
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->loadHTML($htmlString);
        $xpath = new DOMXPath($doc);
        $results = $xpath->query($query);
        $this->assertFalse($results === false, "Xquery '$query' failed");
        return $results;
    }

    public function assertXqueryContains($query, $needle, $htmlString)
    {
        $results = $this->_xquery($query, $htmlString);
        $this->assertNotEquals(0, $results->length,
            "Found no match for xquery '$query'");
        $haystack = $results->item(0)->nodeValue;
        $this->assertType('string', $haystack);
        $this->assertContains($needle, $haystack,
            "Found no match for '$needle' contained in '$haystack'");
    }

    public function assertXquery($query, $htmlString)
    {
        $results = $this->_xquery($query, $htmlString);
        $this->assertNotEquals(0, $results->length,
            "Found no match for xquery '$query'");
    }

    public function testIndexControllerIndexAction()
    {
        $this->_request->setControllerName('grid');
        $this->_request->setActionName('show');
        $response = $this->_front->dispatch();
        $this->assertFalse($response->isException());
        $body = $response->getBody();
        $this->assertXqueryContains('//head/title', 'Zend Framework', $body);
        $this->assertXqueryContains('//body/h1', 'Zend Framework', $body);
        $this->assertXqueryContains('//body/h2', 'Tables', $body);
        $this->assertXquery('//body/ul', $body);
    }

    public function testGridControllerShowAction()
    {
        $this->_request->setControllerName('grid');
        $this->_request->setActionName('show');
        $response = $this->_front->dispatch();
        $this->assertFalse($response->isException());
    }

    public static function main()
    {
        require_once "PHPUnit/TextUI/TestRunner.php";

        $suite  = new PHPUnit_Framework_TestSuite("GridTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }
}

class Zend_Db_Adapter_Mock extends Zend_Db_Adapter_Abstract
{
    public function listTables() { return array('dummy'); }
    public function describeTable($tableName, $schemaName=null) {
        return array(
            'column' => array(
                'SCHEMA_NAME'      => $schemaName,
                'TABLE_NAME'       => $tableName,
                'COLUMN_NAME'      => 'column',
                'COLUMN_POSITION'  => 1,
                'DATA_TYPE'        => 'VARCHAR',
                'DEFAULT'          => null,
                'NULLABLE'         => true,
                'LENGTH'           => 10,
                'SCALE'            => null,
                'PRECISION'        => null,
                'UNSIGNED'         => null,
                'PRIMARY'          => true,
                'PRIMARY_POSITION' => 1
            )
        );
    }
    public function prepare($sql) { return new Zend_Db_Statement_Mock($sql); }
    public function lastInsertId($tableName=null, $primaryKey='id') { }
    public function setFetchMode($mode) { return; }
    public function limit($sql, $count, $offset = 0) { }
    public function supportsParameters($type) { return true; }
    public function closeConnection() { $this->_connection = null; }
    protected function _checkRequiredOptions(array $config) { return true; }
    protected function _connect() { $this->_connection = $this; }
    protected function _beginTransaction() { return true; }
    protected function _commit() { return true; }
    protected function _rollBack() { return true; }
}

class Zend_Db_Statement_Mock extends Zend_Db_Statement
{
    public function closeCursor() { return true; }
    public function columnCount() { return 1; }
    public function errorCode() { return 'error'; }
    public function errorInfo() { return array('error', 'error', 'error'); }
    public function fetch($style=null, $cursor=null, $offset=null) {
        switch ($style) {
        case Zend_Db::FETCH_NUM:
            return array('column'=>'data');
        case Zend_Db::FETCH_OBJ:
            return (object) array('column'=>'data');
        case Zend_Db::FETCH_BOTH:
            return array('column'=>'data', 0=>'data');
        case Zend_Db::FETCH_ASSOC:
        default:
            return array('column'=>'data');
        }
    }
    public function nextRowset() { return true; }
    public function rowCount() { return 1; }
}

if (!defined("PHPUnit_MAIN_METHOD")) {
    define("PHPUnit_MAIN_METHOD", "GridTest::main");
    $dotdot = dirname(dirname(__FILE__));
    set_include_path($dotdot . '/models' . PATH_SEPARATOR . get_include_path());
    GridTest::main();
}



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
 * @subpackage Zend_Grid
 * @package    UnitTests
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: TestHelper.php 4528 2007-04-17 23:10:47Z darby $
 */
