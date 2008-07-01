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
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */

/**
 * Test helper
 */
require_once dirname(__FILE__) . '/../../TestHelper.php';

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Zend_Dojo_DataTest::main');
}

require_once 'Zend/Dojo/Data.php';

/**
 * @category   Zend
 * @package    Zend_Dojo
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Dojo_DataTest extends PHPUnit_Framework_TestCase
{
    public $dojoData;

    public static function main()
    {
        $suite  = new PHPUnit_Framework_TestSuite("Zend_Dojo_DataTest");
        $result = PHPUnit_TextUI_TestRunner::run($suite);
    }

    /**
     * Sets up the fixture, for example, open a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    public function setUp()
    {
        $this->dojoData = new Zend_Dojo_Data;
    }

    public function testIdentifierShouldBeNullByDefault()
    {
        $this->assertNull($this->dojoData->getIdentifier());
    }

    public function testIdentifierShouldBeMutable()
    {
        $this->testIdentifierShouldBeNullByDefault();
        $this->dojoData->setIdentifier('id');
        $this->assertEquals('id', $this->dojoData->getIdentifier());
    }

    public function testLabelShouldBeNullByDefault()
    {
        $this->assertNull($this->dojoData->getLabel());
    }

    public function testLabelShouldBeMutable()
    {
        $this->testLabelShouldBeNullByDefault();
        $this->dojoData->setLabel('title');
        $this->assertEquals('title', $this->dojoData->getLabel());
    }

    public function testAddItemShouldAcceptArray()
    {
        $this->markTestIncomplete();
    }

    public function testAddItemShouldAcceptStdObject()
    {
        $this->markTestIncomplete();
    }

    public function testAddItemShouldAcceptObjectImplementingToArray()
    {
        $this->markTestIncomplete();
    }

    public function testAddItemShouldAllowSpecifyingIdentifier()
    {
        $this->markTestIncomplete();
    }

    public function testAddItemShouldAutodiscoverIdentifierIfNoneProvidedAndObjectIdentifierSet()
    {
        $this->markTestIncomplete();
    }

    public function testSetItemShouldOverwriteExistingItem()
    {
        $this->markTestIncomplete();
    }

    public function testSetItemShouldAddItemIfNonexistent()
    {
        $this->markTestIncomplete();
    }

    public function testAddItemsShouldAcceptArray()
    {
        $this->markTestIncomplete();
    }

    public function testAddItemsShouldAcceptTraversableObject()
    {
        $this->markTestIncomplete();
    }

    public function testSetItemsShouldOverwriteAllCurrentItems()
    {
        $this->markTestIncomplete();
    }

    public function testRemoveItemShouldRemoveItemSpecifiedByIdentifier()
    {
        $this->markTestIncomplete();
    }

    public function testClearItemsShouldRemoveAllItems()
    {
        $this->markTestIncomplete();
    }

    public function testGetItemsShouldReturnArrayOfItems()
    {
        $this->markTestIncomplete();
    }

    public function testShouldSerializeToArray()
    {
        $this->markTestIncomplete();
    }

    public function testShouldSerializeToJson()
    {
        $this->markTestIncomplete();
    }

    public function testShouldSerializeToStringAsJson()
    {
        $this->markTestIncomplete();
    }

    public function testShouldImplementArrayAccess()
    {
        $this->markTestIncomplete();
    }

    public function testShouldImplementIterator()
    {
        $this->markTestIncomplete();
    }

    public function testShouldImplementCountable()
    {
        $this->markTestIncomplete();
    }
}

if (PHPUnit_MAIN_METHOD == 'Zend_Dojo_DataTest::main') {
    Zend_Dojo_DataTest::main();
}
