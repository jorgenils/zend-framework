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
 * @package    Zend_Paginator
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Paginator_Adapter_Array
 */
require_once 'Zend/Paginator/Adapter/Array.php';

/**
 * @see PHPUnit_Framework_TestCase
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @category   Zend
 * @package    Zend_Paginator
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Paginator_Adapter_ArrayTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Zend_Paginator_Adapter_Array
     */
    private $_adapter;
    
    /**
     * Prepares the environment before running a test.
     */
    protected function setUp ()
    {
        parent::setUp();
        $this->_adapter = new Zend_Paginator_Adapter_Array(range(1, 101));
    }
    /**
     * Cleans up the environment after running a test.
     */
    protected function tearDown ()
    {
        $this->_adapter = null;
        parent::tearDown();
    }
    
    public function testGetItemsOffsetZero()
    {
        $expected = range(1, 10);
        $actual = $this->_adapter->getItems(0, 10);
        $this->assertEquals($expected, $actual);
    }
    
    public function testGetItemsOffsetTen()
    {
        $expected = range(11, 20);
        $actual = $this->_adapter->getItems(10, 10);
        $this->assertEquals($expected, $actual);
    }
    
    public function testCount()
    {
        $this->assertEquals(101, $this->_adapter->count());
    }
}