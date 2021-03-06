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
 */

require_once 'Zend/Db/TestSetup.php';

require_once 'Zend/Db/Statement/Exception.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

abstract class Zend_Db_Statement_TestCommon extends Zend_Db_TestSetup
{

    public function testStatementConstruct()
    {
        $this->fail('This test should be overridden in all subclasses.');
        $select = $this->_db->select()
            ->from('zfproducts');
        $sql = $select->__toString();
        $stmt = new Zend_Db_Statement($this->_db, $sql);
        $this->assertType('Zend_Db_Statement_Interface', $stmt);
        $stmt->closeCursor();
    }

    public function testStatementConstructFromPrepare()
    {
        $select = $this->_db->select()
            ->from('zfproducts');
        $stmt = $this->_db->prepare($select->__toString());
        $this->assertType('Zend_Db_Statement_Interface', $stmt);
        $stmt->closeCursor();
    }

    public function testStatementConstructFromQuery()
    {
        $select = $this->_db->select()
            ->from('zfproducts');
        $stmt = $this->_db->query($select);
        $this->assertType('Zend_Db_Statement_Interface', $stmt);
        $stmt->closeCursor();
    }

    public function testStatementConstructFromSelect()
    {
        $stmt = $this->_db->select()
            ->from('zfproducts')
            ->query();
        $this->assertType('Zend_Db_Statement_Interface', $stmt);
        $stmt->closeCursor();
    }

    public function testStatementConstructExceptionBadSql()
    {
        $sql = "SELECT * FROM *";
        try {
            $stmt = $this->_db->query($sql);
            $this->fail('Expected to catch Zend_Db_Statement_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Statement_Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
        }
    }

    public function testStatementRowCount()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->prepare("DELETE FROM $products WHERE $product_id = 1");

        $n = $stmt->rowCount();
        $this->assertType('integer', $n);
        $this->assertEquals(0, $n, 'Expecting row count to be 0 before executing query');

        $stmt->execute();

        $n = $stmt->rowCount();
        $stmt->closeCursor();

        $this->assertType('integer', $n);
        $this->assertEquals(1, $n, 'Expected row count to be one after executing query');
    }

    public function testStatementColumnCountForSelect()
    {
        $select = $this->_db->select()
            ->from('zfproducts');

        $stmt = $this->_db->prepare($select->__toString());

        $n = $stmt->columnCount();
        $this->assertEquals(0, $n, 'Expecting column count to be 0 before executing query');

        $stmt->execute();

        $n = $stmt->columnCount();
        $stmt->closeCursor();

        $this->assertType('integer', $n);
        $this->assertEquals(2, $n);
    }

    public function testStatementColumnCountForDelete()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->prepare("DELETE FROM $products WHERE $product_id = 1");

        $n = $stmt->columnCount();
        $this->assertEquals(0, $n, 'Expecting column count to be 0 before executing query');

        $stmt->execute();

        $n = $stmt->columnCount();
        $this->assertEquals(0, $n, 'Expecting column count to be null after executing query');
    }

    public function testStatementExecuteWithParams()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');
        $product_name = $this->_db->quoteIdentifier('product_name');

        $stmt = $this->_db->prepare("INSERT INTO $products ($product_id, $product_name) VALUES (?, ?)");
        $stmt->execute(array(4, 'Solaris'));

        $select = $this->_db->select()
            ->from('zfproducts')
            ->where("$product_id = 4");
        $result = $this->_db->fetchAll($select);
        $stmt->closeCursor();

        $this->assertEquals(array(array('product_id'=>4, 'product_name'=>'Solaris')), $result);
    }

    public function testStatementErrorCodeKeyViolation()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');
        $product_name = $this->_db->quoteIdentifier('product_name');

        $stmt = $this->_db->prepare("INSERT INTO $products ($product_id, $product_name) VALUES (?, ?)");
        try {
            // INSERT a value that results in a key violation
            $retval = $stmt->execute(array(1, 'Solaris'));
            if ($retval === false) {
                throw new Zend_Db_Statement_Exception('dummy');
            }
            $this->fail('Expected to catch Zend_Db_Statement_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Statement_Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
        }
        $code = $stmt->errorCode();
        // @todo: what to assert here?
    }

    public function testStatementErrorInfoKeyViolation()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');
        $product_name = $this->_db->quoteIdentifier('product_name');

        $stmt = $this->_db->prepare("INSERT INTO $products ($product_id, $product_name) VALUES (?, ?)");
        try {
            // INSERT a value that results in a key violation
            $retval = $stmt->execute(array(1, 'Solaris'));
            if ($retval === false) {
                throw new Zend_Db_Statement_Exception('dummy');
            }
            $this->fail('Expected to catch Zend_Db_Statement_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Statement_Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
        }
        $code = $stmt->errorCode();
        $info = $stmt->errorInfo();
        $this->assertEquals($code, $info[0]);
        // @todo: what to assert here?
    }

    public function testStatementSetFetchModeNum()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $stmt->setFetchMode(Zend_Db::FETCH_NUM);
        $result = $stmt->fetchAll();

        $this->assertEquals(2, count($result));
        $this->assertEquals(2, count($result[0]));
        $this->assertEquals(2, $result[0][0]);
        $this->assertFalse(isset($result[0]['product_id']));
    }

    public function testStatementFetchAll()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetchAll();

        $this->assertEquals(2, count($result));
        $this->assertEquals(2, count($result[0]));
        $this->assertEquals(2, $result[0]['product_id']);
        $this->assertFalse(isset($result[0][0]));
    }

    public function testStatementFetchAllStyleNum()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetchAll(Zend_Db::FETCH_NUM);

        $this->assertEquals(2, count($result));
        $this->assertEquals(2, count($result[0]));
        $this->assertEquals(2, $result[0][0]);
        $this->assertEquals('Linux', $result[0][1]);
        $this->assertFalse(isset($result[0]['product_id']));
    }

    public function testStatementFetchAllStyleAssoc()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetchAll(Zend_Db::FETCH_ASSOC);

        $this->assertEquals(2, count($result));
        $this->assertEquals(2, count($result[0]));
        $this->assertEquals(2, $result[0]['product_id']);
        $this->assertFalse(isset($result[0][0]));
    }

    public function testStatementFetchAllStyleBoth()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetchAll(Zend_Db::FETCH_BOTH);

        $this->assertEquals(2, count($result));
        $this->assertEquals(4, count($result[0]));
        $this->assertEquals(2, $result[0][0]);
        $this->assertEquals('Linux', $result[0][1]);
        $this->assertEquals(2, $result[0]['product_id']);
        $this->assertEquals('Linux', $result[0]['product_name']);
    }

    public function testStatementFetchAllStyleObj()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetchAll(Zend_Db::FETCH_OBJ);

        $this->assertEquals(2, count($result));
        $this->assertType('stdClass', $result[0]);
        $this->assertEquals(2, $result[0]->product_id);
    }

    public function testStatementFetchAllStyleColumn()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetchAll(Zend_Db::FETCH_COLUMN);

        $this->assertEquals(2, count($result));
        $this->assertEquals(2, $result[0]);
        $this->assertEquals(3, $result[1]);
    }

    public function testStatementFetchAllStyleColumnWithArg()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetchAll(Zend_Db::FETCH_COLUMN, 1);

        $this->assertEquals(2, count($result));
        $this->assertType('string', $result[0]);
        $this->assertEquals('Linux', $result[0]);
        $this->assertEquals('OS X', $result[1]);
    }

    public function testStatementFetchAllStyleException()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        try {
            $result = $stmt->fetchAll(-99);
            $this->fail('Expected to catch Zend_Db_Statement_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Statement_Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
        }
    }

    public function testStatementFetchColumn()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");

        $result = $stmt->fetchColumn();
        $this->assertEquals(2, $result);
        $result = $stmt->fetchColumn();
        $this->assertEquals(3, $result);

        $stmt->closeCursor();
    }

    public function testStatementFetchColumnWithArg()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");

        $result = $stmt->fetchColumn(1);
        $this->assertEquals('Linux', $result);
        $result = $stmt->fetchColumn(1);
        $this->assertEquals('OS X', $result);

        $stmt->closeCursor();
    }

    public function testStatementFetchObject()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetchObject();
        $stmt->closeCursor();

        $this->assertType('stdClass', $result,
            'Expecting object of type stdClass, got '.get_class($result));
        $this->assertEquals('Linux', $result->product_name);
    }

    public function testStatementFetchStyleNum()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetch(Zend_Db::FETCH_NUM);
        $stmt->closeCursor();

        $this->assertType('array', $result);
        $this->assertEquals('Linux', $result[1]);
        $this->assertFalse(isset($result['product_name']));
    }

    public function testStatementFetchStyleAssoc()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetch(Zend_Db::FETCH_ASSOC);
        $stmt->closeCursor();

        $this->assertType('array', $result);
        $this->assertEquals('Linux', $result['product_name']);
        $this->assertFalse(isset($result[1]));
    }

    public function testStatementFetchStyleBoth()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetch(Zend_Db::FETCH_BOTH);
        $stmt->closeCursor();

        $this->assertType('array', $result);
        $this->assertEquals('Linux', $result[1]);
        $this->assertEquals('Linux', $result['product_name']);
    }

    public function testStatementFetchStyleObj()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        $result = $stmt->fetch(Zend_Db::FETCH_OBJ);
        $stmt->closeCursor();

        $this->assertType('stdClass', $result,
            'Expecting object of type stdClass, got '.get_class($result));
        $this->assertEquals('Linux', $result->product_name);
    }

    public function testStatementFetchStyleException()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");
        try {
            $result = $stmt->fetch(-99);
            $this->fail('Expected to catch Zend_Db_Statement_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Statement_Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
        }
        $stmt->closeCursor();
    }

    /* @todo
    public function testStatementBindParamByInteger()
    {
        $this->markTestIncomplete();

        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');
        $product_name = $this->_db->quoteIdentifier('product_name');

        $productIdValue   = 4;
        $productNameValue = 'Solaris';

        $stmt = $this->_db->prepare("INSERT INTO $products ($product_id, $product_name) VALUES (?, ?)");
        $this->assertTrue($stmt->bindParam(1, $productIdValue));
        $this->assertTrue($stmt->bindParam(2, $productNameValue));

        // no params as args to execute()
        try {
            $retval = $stmt->execute();
            if ($retval === false) {
                var_dump($stmt->errorInfo());
            }
        } catch (Zend_Exception $e) {
            echo "*** Caught exception: ".$e->getMessage()."\n";
        }

        $select = $this->_db->select()
            ->from('zfproducts')
            ->where("$product_id = 4");
        $result = $this->_db->fetchAll($select);
        $stmt->closeCursor();

        $this->assertEquals(array(array('product_id' => $productIdValue, 'product_name' => $productNameValue)), $result);
    }
     */

    /* @todo
    public function testStatementBindParamByName()
    {
        $this->markTestIncomplete();

        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');
        $product_name = $this->_db->quoteIdentifier('product_name');

        $productIdValue   = 4;
        $productNameValue = 'Solaris';

        $stmt = $this->_db->prepare("INSERT INTO $products ($product_id, $product_name) VALUES (:id, :name)");
        // test with colon prefix
        $this->assertTrue($stmt->bindParam(':id', $productIdValue));
        // test with no colon prefix
        $this->assertTrue($stmt->bindParam('name', $productNameValue));

        // no params as args to execute()
        $retval = $stmt->execute();
        if ($retval === false) {
            var_dump($stmt->errorInfo());
        }

        $select = $this->_db->select()
            ->from('zfproducts')
            ->where("$product_id = 4");
        $result = $this->_db->fetchAll($select);
        $stmt->closeCursor();

        $this->assertEquals(array(array('product_id' => $productIdValue, 'product_name' => $productNameValue)), $result);
    }
     */

    /* @todo
    public function testStatementBindParamException()
    {
        $this->markTestIncomplete();

        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');
        $product_name = $this->_db->quoteIdentifier('product_name');

        $id   = 4;
        $name = 'Solaris';

        $stmt = $this->_db->prepare("INSERT INTO $products ($product_id, $product_name) VALUES (4, 'Solaris')");
        // test invalid parameter binding
        try {
            $stmt->bindParam('mxyzptlk!', $id);
            $this->fail('Expected to catch Zend_Db_Statement_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Statement_Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
        }
    }
     */

    /* @todo
    public function testStatementBindValue()
    {
        $this->markTestIncomplete();
    }
     */

    /* @todo
    public function testStatementBindColumnByInteger()
    {
        $this->markTestIncomplete();

        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $prodIdValue = -99;
        $prodNameValue = 'AmigaOS';

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");

        $this->assertTrue($stmt->bindColumn(1, $prodIdValue),
            'Expected bindColumn(product_id) to return true');
        $this->assertTrue($stmt->bindColumn(2, $prodNameValue),
            'Expected bindColumn(product_name) to return true');

        $this->assertTrue($stmt->fetch(Zend_Db::FETCH_BOUND),
            'Expected fetch() call 1 to return true');
        $this->assertEquals(2, $prodIdValue);
        $this->assertEquals('Linux', $prodNameValue);

        $this->assertTrue($stmt->fetch(Zend_Db::FETCH_BOUND),
            'Expected fetch() call 2 to return true');
        $this->assertEquals(3, $prodIdValue);
        $this->assertEquals('OS X', $prodNameValue);

        $stmt->closeCursor();
    }
     */

    /* @todo
    public function testStatementBindColumnByName()
    {
        $this->markTestIncomplete();

        $products = $this->_db->quoteIdentifier('zfproducts');
        $product_id = $this->_db->quoteIdentifier('product_id');

        $prodIdValue = -99;
        $prodNameValue = 'AmigaOS';

        $stmt = $this->_db->query("SELECT * FROM $products WHERE $product_id > 1 ORDER BY $product_id ASC");

        $this->assertTrue($stmt->bindColumn('product_id', $prodIdValue),
            'Expected bindColumn(product_id) to return true');
        $this->assertTrue($stmt->bindColumn('product_name', $prodNameValue),
            'Expected bindColumn(product_name) to return true');

        $this->assertTrue($stmt->fetch(Zend_Db::FETCH_BOUND),
            'Expected fetch() call 1 to return true');
        $this->assertEquals(2, $prodIdValue);
        $this->assertEquals('Linux', $prodNameValue);

        $this->assertTrue($stmt->fetch(Zend_Db::FETCH_BOUND),
            'Expected fetch() call 2 to return true');
        $this->assertEquals(3, $prodIdValue);
        $this->assertEquals('OS X', $prodNameValue);

        $stmt->closeCursor();
    }
     */

    public function testStatementNextRowset()
    {
        $select = $this->_db->select()
            ->from('zfproducts');
        $stmt = $this->_db->prepare($select->__toString());
        try {
            $stmt->nextRowset();
            $this->fail('Expected to catch Zend_Db_Statement_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Statement_Exception', $e,
                'Expecting object of type Zend_Db_Statement_Exception, got '.get_class($e));
            $this->assertEquals('nextRowset() is not implemented', $e->getMessage());
        }
        $stmt->closeCursor();
    }

}
