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
 * @package    Zend_Data_Paginator
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @see Zend_Data_Paginator
 */
require_once 'Zend/Data/Paginator.php';

/**
 * @see PHPUnit_Framework_TestCase
 */
require_once 'PHPUnit/Framework/TestCase.php';

/**
 * @category   Zend
 * @package    Zend_Data_Paginator
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Data_PaginatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Paginator instance
     *
     * @var Zend_Data_Paginator
     */
    protected $_paginator = null; 
    
    protected $_testCollection = null;
    
    protected function setUp()
    {
        $this->_testCollection = range(1, 101);
        $this->_paginator = Zend_Data_Paginator::factory($this->_testCollection);
        $this->_paginator->setItemCountPerPage(10);
    }
    
    protected function tearDown()
    {
        $this->_paginator = null;
    }
    
    public function testFactoryReturnsArrayAdapter()
    {
        $paginator = Zend_Data_Paginator::factory($this->_testCollection);
        $this->assertType('Zend_Data_Paginator_Adapter_Array', $paginator->getAdapter());
    }

    public function testFactoryReturnsDbSelectAdapter()
    {
        require_once 'Zend/Db/Adapter/Pdo/Sqlite.php';
        $db = new Zend_Db_Adapter_Pdo_Sqlite(array(
            'dbname' => 'Paginator/_files/test.sqlite'
        ));
        $query   = $db->select()->from('test');
        $paginator = Zend_Data_Paginator::factory($query);
        $this->assertType('Zend_Data_Paginator_Adapter_DbSelect', $paginator->getAdapter());
    }

    public function testFactoryReturnsIteratorAdapter()
    {
        $paginator = Zend_Data_Paginator::factory(new ArrayIterator($this->_testCollection));
        $this->assertType('Zend_Data_Paginator_Adapter_Iterator', $paginator->getAdapter());
    }
    
    public function testGetSetItemCountPerPage()
    {
        $this->assertEquals(10, $this->_paginator->getItemCountPerPage());
        $this->_paginator->setItemCountPerPage(15);
        $this->assertEquals(15, $this->_paginator->getItemCountPerPage());
        $this->_paginator->setItemCountPerPage(10);
    }
    
    public function testGetCurrentItemCount()
    {
        $this->assertEquals(10, $this->_paginator->getCurrentItemCount());
        $this->_paginator->setCurrentPageNumber(11);
        $this->assertEquals(1, $this->_paginator->getCurrentItemCount());
        $this->_paginator->setCurrentPageNumber(1);
    }
    
    public function testGetCurrentItems()
    {
        $items = $this->_paginator->getCurrentItems();
        $this->assertType('Iterator', $items);
        
        $count = 0;
        
        foreach ($items as $item) {
        	$count++;
        }
        
        $this->assertEquals(10, $count);
    }
    
    public function testGetSetCurrentPageNumber()
    {
        $this->assertEquals(1, $this->_paginator->getCurrentPageNumber());
        $this->_paginator->setCurrentPageNumber(-1);
        $this->assertEquals(1, $this->_paginator->getCurrentPageNumber());
        $this->_paginator->setCurrentPageNumber(11);
        $this->assertEquals(11, $this->_paginator->getCurrentPageNumber());
        $this->_paginator->setCurrentPageNumber(111);
        $this->assertEquals(11, $this->_paginator->getCurrentPageNumber());
        $this->_paginator->setCurrentPageNumber(1);
        $this->assertEquals(1, $this->_paginator->getCurrentPageNumber());
    }
    
    public function testGetAbsoluteItemNumber()
    {
        $this->assertEquals(1, $this->_paginator->getAbsoluteItemNumber(1));
        $this->assertEquals(11, $this->_paginator->getAbsoluteItemNumber(1, 2));
        $this->assertEquals(24, $this->_paginator->getAbsoluteItemNumber(4, 3));
    }
    
    public function testGetItem()
    {
        $this->assertEquals(1, $this->_paginator->getItem(1));
        $this->assertEquals(11, $this->_paginator->getItem(1, 2));
        $this->assertEquals(24, $this->_paginator->getItem(4, 3));
    }
}