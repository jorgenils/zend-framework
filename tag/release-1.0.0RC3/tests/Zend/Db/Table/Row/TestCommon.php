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
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/**
 * @see Zend_Db_Table_TestSetup
 */
require_once 'Zend/Db/Table/TestSetup.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);


/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage UnitTests
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Db_Table_Row_TestCommon extends Zend_Db_Table_TestSetup
{

    public function testTableFindRow()
    {
        $table = $this->_table['bugs'];

        $rowset = $table->find(1);
        $this->assertType('Zend_Db_Table_Rowset_Abstract', $rowset,
            'Expecting object of type Zend_Db_Table_Rowset_Abstract, got '.get_class($rowset));
        $this->assertTrue($rowset->exists());
        $this->assertEquals(1, count($rowset));
    }

    public function testTableRowConstructor()
    {
        $table = $this->_table['bugs'];

        $row1 = new Zend_Db_Table_Row(
            array(
                'db'    => $this->_db,
                'table' => $table
            )
        );

        $this->assertType('Zend_Db_Table_Row_Abstract', $row1,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1));

        try {
            $bug_description = $row1->bug_description;
            $this->fail('Expected to catch Zend_Db_Table_Row_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Table_Row_Exception', $e,
                'Expecting object of type Zend_Db_Table_Row_Exception, got '.get_class($e));
            $this->assertEquals("Specified column \"bug_description\" is not in the row", $e->getMessage());
        }
    }

    public function testTableRowToArray()
    {
        $table = $this->_table['bugs'];

        $rowset = $table->find(1);
        $this->assertType('Zend_Db_Table_Rowset_Abstract', $rowset,
            'Expecting object of type Zend_Db_Table_Rowset_Abstract, got '.get_class($rowset));
        $row1 = $rowset->current();
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1));

        $a = $row1->toArray();

        $this->assertTrue(is_array($a));
        $cols = array(
            'bug_id',
            'bug_description',
            'bug_status',
            'created_on',
            'updated_on',
            'reported_by',
            'assigned_to',
            'verified_by',
        );
        $this->assertEquals($cols, array_keys($a));
    }

    public function testTableRowMagicGet()
    {
        $table = $this->_table['bugs'];
        $bug_id = $this->_db->foldCase('bug_id');
        $bug_description = $this->_db->foldCase('bug_description');
        $bug_status = $this->_db->foldCase('bug_status');

        $rowset = $table->find(1);
        $this->assertType('Zend_Db_Table_Rowset_Abstract', $rowset,
            'Expecting object of type Zend_Db_Table_Rowset_Abstract, got '.get_class($rowset));
        $row1 = $rowset->current();
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1));

        try {
            $this->assertEquals(1, $row1->$bug_id);
            $this->assertEquals('System needs electricity to run', $row1->$bug_description);
            $this->assertEquals('NEW', $row1->$bug_status);
        } catch (Zend_Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }

        if (!isset($row1->$bug_id)) {
            $this->fail('Column "id" is set but isset() returns false');
        }
    }

    public function testTableRowMagicSet()
    {
        $table = $this->_table['bugs'];
        $bug_description = $this->_db->foldCase('bug_description');

        $rowset = $table->find(1);
        $this->assertType('Zend_Db_Table_Rowset_Abstract', $rowset,
            'Expecting object of type Zend_Db_Table_Rowset_Abstract, got '.get_class($rowset));
        $row1 = $rowset->current();
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1));

        try {
            $row1->$bug_description = 'foo';
            $this->assertEquals('foo', $row1->$bug_description);
        } catch (Zend_Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
    }

    public function testTableRowSetFromArray()
    {
        $table = $this->_table['bugs'];
        $bug_description = $this->_db->foldCase('bug_description');
        $bug_status = $this->_db->foldCase('bug_status');

        $data = array(
            $bug_description => 'New Description',
            $bug_status      => 'INVALID'
        );

        $rowset = $table->find(1);
        $this->assertType('Zend_Db_Table_Rowset_Abstract', $rowset,
            'Expecting object of type Zend_Db_Table_Rowset_Abstract, got '.get_class($rowset));
        $row1 = $rowset->current();
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1));

        $result = $row1->setFromArray($data);

        $this->assertSame($result, $row1);

        try {
            $this->assertEquals($data[$bug_description], $row1->$bug_description);
            $this->assertEquals($data[$bug_status], $row1->$bug_status);
        } catch (Zend_Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
    }

    public function testTableRowSaveInsert()
    {
        $table = $this->_table['bugs'];
        $data = array(
            'bug_description' => 'New Description',
            'bug_status'      => 'INVALID'
        );
        try {
            $row3 = $table->createRow($data);
            $row3->save();
            $this->assertEquals(5, $row3->bug_id);
            $this->assertEquals($data['bug_description'], $row3->bug_description);
            $this->assertEquals($data['bug_status'], $row3->bug_status);
        } catch (Zend_Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
    }

    public function testTableRowSaveInsertSequence()
    {
        $table = $this->_getTable('Zend_Db_Table_TableProducts',
            array(Zend_Db_Table_Abstract::SEQUENCE => 'zfproducts_seq'));
        $product_id   = $this->_db->foldCase('product_id');
        $product_name = $this->_db->foldCase('product_name');

        $data = array (
            $product_name => 'Solaris'
        );
        $row3 = $table->createRow($data);
        $row3->save();
        try {
            $this->assertEquals(4, $row3->$product_id);
        } catch (Zend_Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
    }

    public function testTableRowSaveUpdate()
    {
        $table = $this->_table['bugs'];
        $bug_id          = $this->_db->foldCase('bug_id');
        $bug_description = $this->_db->foldCase('bug_description');
        $bug_status      = $this->_db->foldCase('bug_status');

        $data = array(
            $bug_description => 'New Description',
            $bug_status      => 'INVALID'
        );

        $rowset = $table->find(1);
        $this->assertType('Zend_Db_Table_Rowset_Abstract', $rowset,
            'Expecting object of type Zend_Db_Table_Rowset_Abstract, got '.get_class($rowset));
        $row1 = $rowset->current();
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1));

        $row1->setFromArray($data);
        $row1->save();

        try {
            $this->assertEquals(1, $row1->$bug_id);
            $this->assertEquals($data[$bug_description], $row1->$bug_description);
            $this->assertEquals($data[$bug_status], $row1->$bug_status);
        } catch (Zend_Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
    }

    public function testTableRowSetTable()
    {
        $table = $this->_table['bugs'];
        $table2 = $this->_table['products'];

        $rowset = $table->find(1);
        $this->assertType('Zend_Db_Table_Rowset_Abstract', $rowset,
            'Expecting object of type Zend_Db_Table_Rowset_Abstract, got '.get_class($rowset));
        $row1 = $rowset->current();
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1));

        try {
            $row1->setTable($table2);
            $this->fail('Expected to catch Zend_Db_Table_Exception for incorrect parent table');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Table_Exception', $e,
                'Expecting object of type Zend_Db_Table_Exception got '.get_class($e));
            $this->assertEquals('The specified Table is of class Zend_Db_Table_TableProducts, expecting class to be instance of Zend_Db_Table_TableBugs', $e->getMessage());
        }
    }

    public function testTableRowExceptionGetColumnNotInRow()
    {
        $table = $this->_table['bugs'];

        $rowset = $table->find(1);
        $this->assertType('Zend_Db_Table_Rowset_Abstract', $rowset,
            'Expecting object of type Zend_Db_Table_Rowset_Abstract, got '.get_class($rowset));
        $row1 = $rowset->current();
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1));

        $column = 'doesNotExist';

        try {
            $dummy = $row1->$column;
            $this->fail('Expected to catch Zend_Db_Table_Row_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Table_Row_Exception', $e,
                'Expecting object of type Zend_Db_Table_Row_Exception, got '.get_class($e));
            $this->assertEquals("Specified column \"$column\" is not in the row", $e->getMessage());
        }
    }

    public function testTableRowExceptionSetColumnNotInRow()
    {
        $table = $this->_table['bugs'];

        $rowset = $table->find(1);
        $this->assertType('Zend_Db_Table_Rowset_Abstract', $rowset,
            'Expecting object of type Zend_Db_Table_Rowset_Abstract, got '.get_class($rowset));
        $row1 = $rowset->current();
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1));

        $column = 'doesNotExist';

        try {
            $row1->$column = 'dummy value';
            $this->fail('Expected to catch Zend_Db_Table_Row_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Table_Row_Exception', $e,
                'Expecting object of type Zend_Db_Table_Row_Exception, got '.get_class($e));
            $this->assertEquals("Specified column \"$column\" is not in the row", $e->getMessage());
        }
    }

    public function testTableRowSetPrimaryKey()
    {
        $table = $this->_table['bugs'];
        $bug_id = $this->_db->foldCase('bug_id');

        $rowset = $table->find(1);
        $this->assertType('Zend_Db_Table_Rowset_Abstract', $rowset,
            'Expecting object of type Zend_Db_Table_Rowset_Abstract, got '.get_class($rowset));
        $row1 = $rowset->current();
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1));

        try {
            $row1->$bug_id = 6;
            $row1->save();
            $this->assertEquals(6, $row1->$bug_id);
        } catch (Zend_Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
    }

    public function testTableRowSerialize()
    {
        $table = $this->_table['bugs'];

        $rowset = $table->find(1);
        $row1 = $rowset->current();

        $serRow1 = serialize($row1);

        $row1New = unserialize($serRow1);
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1New,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1New));
        $this->assertEquals($row1->toArray(), $row1New->toArray());
    }

    public function testTableRowSerializeExceptionNotConnected()
    {
        $table = $this->_table['bugs'];

        $rowset = $table->find(1);
        $row1 = $rowset->current();

        $serRow1 = serialize($row1);

        $row1New = unserialize($serRow1);
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1New,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1New));
        $bug_description = $this->_db->foldCase('bug_description');
        $row1New->$bug_description = 'New description';

        try {
            $row1New->save();
            $this->fail('Expected to catch Zend_Db_Table_Row_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Table_Row_Exception', $e,
                'Expecting object of type Zend_Db_Table_Row_Exception, got '.get_class($e));
            $this->assertEquals("Cannot save a Row unless it is connected", $e->getMessage());
        }
    }

    public function testTableRowSerializeReconnectedUpdate()
    {
        $table = $this->_table['bugs'];

        $rowset = $table->find(1);
        $row1 = $rowset->current();

        $serRow1 = serialize($row1);

        $row1New = unserialize($serRow1);
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1New,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1New));

        try {
            $connected = $row1New->setTable($table);
        } catch (Zend_Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
        $this->assertTrue($connected);

        $bug_description = $this->_db->foldCase('bug_description');
        $bug_status      = $this->_db->foldCase('bug_status');
        $data = array(
            $bug_description => 'New Description',
            $bug_status      => 'INVALID'
        );
        $row1New->setFromArray($data);

        try {
            $rowsAffected = $row1New->save();
        } catch (Zend_Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
        $this->assertEquals(1, $rowsAffected);
    }

    public function testTableRowSerializeReconnectedDelete()
    {
        $table = $this->_table['bugs'];

        $rowset = $table->find(1);
        $row1 = $rowset->current();

        $serRow1 = serialize($row1);

        $row1New = unserialize($serRow1);
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1New,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1New));

        try {
            $connected = $row1New->setTable($table);
        } catch (Zend_Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
        $this->assertTrue($connected);

        try {
            $rowsAffected = $row1New->delete();
        } catch (Zend_Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
        $this->assertEquals(1, $rowsAffected);
    }

    public function testTableRowSerializeExceptionWrongTable()
    {
        $table = $this->_table['bugs'];

        $rowset = $table->find(1);
        $row1 = $rowset->current();

        $serRow1 = serialize($row1);

        $row1New = unserialize($serRow1);
        $this->assertType('Zend_Db_Table_Row_Abstract', $row1New,
            'Expecting object of type Zend_Db_Table_Row_Abstract, got '.get_class($row1New));

        $table2 = $this->_table['products'];
        $connected = false;
        try {
            $connected = $row1New->setTable($table2);
            $this->fail('Expected to catch Zend_Db_Table_Row_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Table_Row_Exception', $e,
                'Expecting object of type Zend_Db_Table_Row_Exception, got '.get_class($e));
            $this->assertEquals('The specified Table is of class Zend_Db_Table_TableProducts, expecting class to be instance of Zend_Db_Table_TableBugs', $e->getMessage());
        }
        $this->assertFalse($connected);
    }

}
