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

/**
 * Zend_Db
 */
require_once 'Zend/Db.php';
require_once 'Zend/Db/Expr.php';

/**
 * PHPUnit test case
 */
require_once 'PHPUnit/Framework/TestCase.php';
require_once 'PHPUnit/Util/Filter.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

/**
 * @package    Zend_Db_Adapter_Pdo_Common
 * @subpackage UnitTests
 */
abstract class Zend_Db_Adapter_Common extends PHPUnit_Framework_TestCase
{
    const TABLE_NAME = 'zf_test_table';
    const TABLE_NAME_2 = 'zf_test_table2';

    protected $_resultSetUppercase = false;
    protected $_schemaUppercase = false;
    protected $_textDataType = 'text';

    abstract public function getDriver();
    abstract public function getParams();
    abstract public function getCreateTableSQL();
    abstract public function getCreateTableSQL2();
    abstract public function testExceptionInvalidLoginCredentials();

    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * @param string The name of the identifier, to be transformed.
     * @return string The name of a column or table, transformed for the
     * current adapter.
     */
    public function getIdentifier($name)
    {
        if ($this->_schemaUppercase) {
            return strtoupper($name);
        } else {
            return $name;
        }
    }

    /**
     * @param string The name of the identifier, to be transformed.
     * @return string The name of a column or table, transformed for the
     * current adapter.
     */
    public function getResultSetKey($name)
    {
        if ($this->_resultSetUppercase) {
            return strtoupper($name);
        } else {
            return $name;
        }
    }

    /**
     * @return string SQL statement for dropping the test table.
     */
    protected function getDropTableSQL()
    {
        $sql = 'DROP TABLE ' . self::TABLE_NAME;
        return $sql;
    }

    /**
     * @return string SQL statement for dropping the test table.
     */
    protected function getDropTableSQL2()
    {
        $sql = 'DROP TABLE ' . self::TABLE_NAME_2;
        return $sql;
    }

    /**
     * Create the test table and populate it with some rows of data.
     * @return void
     */
    protected function createTestTable()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);

        $sql = $this->getCreateTableSQL();
        $this->_db->query($sql);

        $sql = 'INSERT INTO ' . $this->_db->quoteIdentifier($table) . "
            (title, subtitle, body, date_created)
            VALUES ('News Item 1', 'Sub title 1', 'This is body 1', '2006-05-01 11:11:11')";
        $this->_db->query($sql);

        $sql = 'INSERT INTO ' . $this->_db->quoteIdentifier($table) . "
            (title, subtitle, body, date_created)
            VALUES ('News Item 2', 'Sub title 2', 'This is body 2', '2006-05-02 12:12:12')";
        $this->_db->query($sql);
    }

    /**
     * Create a second test table that is used for joins.
     * @return void
     */
    protected function createTestTable2()
    {
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);

        $sql = $this->getCreateTableSQL2();
        $this->_db->query($sql);

        $sql = 'INSERT INTO ' . $this->_db->quoteIdentifier($table2) . "
            (news_id, user_id, comment_title, comment_body, date_posted)
            VALUES (1, 101, 'I agree', 'This is comment 1', '2006-05-01 13:13:13')";
        $this->_db->query($sql);

        $sql = 'INSERT INTO ' . $this->_db->quoteIdentifier($table2) . "
            (news_id, user_id, comment_title, comment_body, date_posted)
            VALUES (1, 102, 'I disagree', 'This is comment 2', '2006-05-01 14:14:14')";
        $this->_db->query($sql);

        $sql = 'INSERT INTO ' . $this->_db->quoteIdentifier($table2) . "
            (news_id, user_id, comment_title, comment_body, date_posted)
            VALUES (1, 101, 'I still agree', 'This is comment 3', '2006-05-01 15:15:15')";
        $this->_db->query($sql);
    }

    /**
     * Skip test if driver is disabled in TestConfiguration.php.
     * Instantiate driver.  Set up database metadata.
     */
    public function setUp()
    {
        // check for driver test disabled
        $driver = $this->getDriver();
        $enabledConst = 'TESTS_ZEND_DB_ADAPTER_' . strtoupper($driver) . '_ENABLED';
        if (!(defined($enabledConst) && constant($enabledConst) == true)) {
            $this->markTestSkipped("Tests for Zend_Db adapter $driver are disabled in TestConfiguration.php");
            return;
        }
        
        // open a new connection
        $this->_db = Zend_Db::factory($this->getDriver(), $this->getParams());

        $this->setUpMetadata();
    }

    /**
     * Drop existing test table if such exists.
     * This is also where one would drop other metadata objects, like sequences.
     * Then create clean test table.
     */
    protected function setUpMetadata()
    {
        // create a test table and populate it
        $this->tearDownMetadata();
        $this->createTestTable();
    }

    /**
     * Drop test table and close connection.
     */
    public function tearDown()
    {
        $this->tearDownMetadata();

        $connection = $this->_db->getConnection();
        $connection = null;
        $this->_db = null;
    }

    /**
     * Drop test table.
     */
    protected function tearDownMetadata()
    {
        $sql = $this->getDropTableSQL();
        $this->_db->query($sql);
        $sql = $this->getDropTableSQL2();
        $this->_db->query($sql);
    }

    /**
     * Test Adapter's delete() method.
     * Delete one row from test table, and verify it was deleted.
     * Then try to delete a row that doesn't exist, and verify it had no effect.
     *
     * @todo: test that require delimited identifiers.
     */
    public function testDelete()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $result = $this->_db->delete($table, 'id = 2');
        $this->assertEquals(1, $result, 'Expected rows affected to return 1');

        $select = $this->_db->select()
            ->from($table);
        $result = $this->_db->fetchAll($select);

        $this->assertEquals(1, count($result), 'Expected count of result to be 1');
        $this->assertEquals(1, $result[0][$id], 'Expected result[0][id] to be 1');

        $result = $this->_db->delete($table, 'id = 327');
        $this->assertEquals(0, $result, 'Expected rows affected to return 0');
    }

    /**
     * Test Adapter's describeTable() method.
     * Retrieve the adapter's description of the test table and examine it.
     */
    public function testDescribeTable()
    {
        $idKey = $this->getResultSetKey('id');
        $bodyKey = $this->getResultSetKey('body');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $desc = $this->_db->describeTable($table);

        $this->assertThat($desc, $this->arrayHasKey($bodyKey));

        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('SCHEMA_NAME'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('TABLE_NAME'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('COLUMN_NAME'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('COLUMN_POSITION'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('DATA_TYPE'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('DEFAULT'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('NULLABLE'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('LENGTH'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('SCALE'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('PRECISION'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('UNSIGNED'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('PRIMARY'));
        $this->assertThat($desc[$bodyKey], $this->arrayHasKey('PRIMARY_POSITION'));

        $this->assertEquals($table, $desc[$bodyKey]['TABLE_NAME']);
        $this->assertEquals($bodyKey, $desc[$bodyKey]['COLUMN_NAME']);
        $this->assertEquals(4, $desc[$bodyKey]['COLUMN_POSITION']);
        $this->assertEquals($this->_textDataType, $desc[$bodyKey]['DATA_TYPE']);
        $this->assertEquals('', $desc[$bodyKey]['DEFAULT']);
        $this->assertTrue($desc[$bodyKey]['NULLABLE']);
        $this->assertEquals(0, $desc[$bodyKey]['SCALE']);
        $this->assertEquals(0, $desc[$bodyKey]['PRECISION']);
        $this->assertEquals('', $desc[$bodyKey]['PRIMARY']);
        $this->assertEquals('', $desc[$bodyKey]['PRIMARY_POSITION']);

        $this->assertTrue($desc[$idKey]['PRIMARY']);
        $this->assertEquals(1, $desc[$idKey]['PRIMARY_POSITION']);
    }

    /**
     * Test the Adapter's fetchAll() method.
     */
    public function testAdapterFetchAll()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $result = $this->_db->fetchAll(
            'SELECT * FROM ' . $this->_db->quoteIdentifier($table) . ' WHERE date_created > ? ORDER BY id ASC',
            array('2006-01-01')
        );
        $this->assertEquals(2, count($result));
        $this->assertEquals('1', $result[0][$id]);
    }

    /**
     * Test the Adapter's fetchAssoc() method.
     */
    public function testAdapterFetchAssoc()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $result = $this->_db->fetchAssoc(
            'SELECT * FROM ' . $this->_db->quoteIdentifier($table) . ' WHERE date_created > ? ORDER BY id DESC',
            array('2006-01-01')
        );
        foreach ($result as $idKey => $row) {
            $this->assertEquals($idKey, $row[$id]);
        }
    }

    /**
     * Test the Adapter's fetchCol() method.
     */
    public function testAdapterFetchCol()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $result = $this->_db->fetchCol(
            'SELECT * FROM ' . $this->_db->quoteIdentifier($table) . ' WHERE date_created > ? ORDER BY id',
            array('2006-01-01')
        );
        $this->assertEquals(2, count($result)); // count rows
        $this->assertEquals(1, $result[0]);
        $this->assertEquals(2, $result[1]);
    }

    /**
     * Test the Adapter's fetchOne() method.
     */
    public function testAdapterFetchOne()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);

        $title = 'News Item 1';
        $result = $this->_db->fetchOne(
            'SELECT title FROM ' . $this->_db->quoteIdentifier($table) . ' WHERE date_created > ? ORDER BY id',
            array('2006-01-01')
        );
        $this->assertEquals($title, $result);
    }

    /**
     * Test the Adapter's fetchPairs() method.
     */
    public function testAdapterFetchPairs()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);

        $title = 'News Item 1';
        $result = $this->_db->fetchPairs(
            'SELECT id, title FROM ' . $this->_db->quoteIdentifier($table) . ' WHERE date_created > ? ORDER BY id',
            array('2006-01-01')
        );
        $this->assertEquals(2, count($result)); // count rows
        $this->assertEquals($title, $result[1]);
    }

    /**
     * Test the Adapter's fetchRow() method.
     */
    public function testAdapterFetchRow()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $result = $this->_db->fetchRow(
            'SELECT * FROM ' .  $this->_db->quoteIdentifier($table) . ' WHERE date_created > ? ORDER BY id',
            array('2006-01-01')
        );
        $this->assertEquals(5, count($result)); // count columns
        $this->assertEquals(1, $result[$id]);
    }

    /**
     * Test the Statement's fetchAll() method.
     */
    public function testStatementFetchAll()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $stmt = $this->_db->query(
            'SELECT * FROM ' . $this->_db->quoteIdentifier($table) . " WHERE date_created > '2006-01-01' ORDER BY id"
        );
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result));
        $this->assertEquals(5, count($result[0]));
        $this->assertEquals(1, $result[0][$id]);
    }

    /**
     * Test the Statement's fetchColumn() method.
     */
    public function testStatementFetchColumn()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);

        $stmt = $this->_db->query(
            'SELECT * FROM ' . $this->_db->quoteIdentifier($table) . " WHERE date_created > '2006-01-01' ORDER BY id"
        );
        $result = $stmt->fetchColumn();
        $this->assertEquals(1, $result);
        $result = $stmt->fetchColumn();
        $this->assertEquals(2, $result);
    }

    /**
     * Test the Statement's fetchObject() method.
     */
    public function testStatementFetchObject()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);

        $title = 'News Item 1';
        $titleCol = $this->getResultSetKey('title');
        $stmt = $this->_db->query(
            'SELECT * FROM ' . $this->_db->quoteIdentifier($table) . " WHERE date_created > '2006-01-01' ORDER BY id"
        );
        $result = $stmt->fetchObject();
        $this->assertThat($result, $this->isInstanceOf('stdClass'), 'Expecting object of type stdClass');
        $this->assertEquals($title, $result->$titleCol);
    }

    /**
     * Test the Adapter's insert() method.
     * This requires providing an associative array of column=>value pairs.
     *
     * @todo: test that require delimited identifiers.
     */
    public function testInsert()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);
        $row = array (
            'title'        => 'News Item 3',
            'subtitle'     => 'Sub title 3',
            'body'         => 'This is body 1',
            'date_created' => '2006-05-03 13:13:13'
        );
        $rows_affected = $this->_db->insert($table, $row);
        $last_insert_id = $this->_db->lastInsertId();
        $this->assertEquals(3, (string) $last_insert_id, 'Expected new id to be 3');
    }

    /**
     * Test the Adapter's limit() method.
     * Fetch 1 row.  Then fetch 1 row offset by 1 row.
     */
    public function testLimit()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $sql = $this->_db->limit('SELECT * FROM ' . $this->_db->quoteIdentifier($table), 1);

        $stmt = $this->_db->query($sql);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));
        $this->assertEquals(5, count($result[0]));
        $this->assertEquals(1, $result[0][$id]);

        $sql = $this->_db->limit('SELECT * FROM ' . $this->_db->quoteIdentifier($table), 1, 1);

        $stmt = $this->_db->query($sql);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));
        $this->assertEquals(5, count($result[0]));
        $this->assertEquals(2, $result[0][$id]);
    }

    /**
     * Test the Adapter's listTables() method.
     * Fetch the list of tables and verify that the test table exists in
     * the list.
     */
    public function testListTables()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);

        $tables = $this->_db->listTables();
        $this->assertContains($table, $tables);
    }

    /**
     *
    public function testTransactionCommit()
    {
    }
     */

    /**
     *
    public function testTransactionRollback()
    {
    }
     */

    /**
     * Test the Adapter's update() method.
     * Update a single row and verify that the change was made.
     * Attempt to update a row that does not exist, and verify
     * that no change was made.
     *
     * @todo: test that requires delimited identifiers.
     */
    public function testUpdate()
    {
        $idKey = $this->getResultSetKey('id');
        $titleKey = $this->getResultSetKey('title');
        $subtitleKey = $this->getResultSetKey('subtitle');
        $table = $this->getIdentifier(self::TABLE_NAME);
        $title = $this->getIdentifier('title');
        $subtitle = $this->getIdentifier('subtitle');

        $newTitle = 'New News Item 2';
        $newSubTitle = 'New Sub title 2';

        // Test that we can change the values in
        // an existing row.
        $result = $this->_db->update($table,
            array(
                $title        => $newTitle,
                $subtitle     => $newSubTitle
            ),
            'id = 2'
        );
        $this->assertEquals(1, $result);

        // Query the row to see if we have the new values.
        $select = $this->_db->select();
        $select->from($table);
        $select->where('id = 2');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();

        $this->assertEquals(2, $result[0][$idKey]);
        $this->assertEquals($newTitle, $result[0][$titleKey]);
        $this->assertEquals($newSubTitle, $result[0][$subtitleKey]);

        // Test that update affects no rows if the WHERE
        // clause matches none.
        $result = $this->_db->update($table,
            array(
                'title'        => $newTitle,
                'subtitle'     => $newSubTitle,
            ),
            'id = 327'
        );
        $this->assertEquals(0, $result);
    }

    public function testExceptionInvalidLimitArgument()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);

        $exceptionSeen = false;
        try {
            $sql = $this->_db->limit('SELECT * FROM ' . $this->_db->quoteIdentifier($table), 0);
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Db_Adapter_Exception'), 'Expecting object of type Zend_Db_Adapter_Exception');
            $exceptionSeen = true;
        }
        $this->assertTrue($exceptionSeen);

        $exceptionSeen = false;
        try {
            $sql = $this->_db->limit('SELECT * FROM ' . $this->_db->quoteIdentifier($table), 1, -1);
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Db_Adapter_Exception'), 'Expecting object of type Zend_Db_Adapter_Exception');
            $exceptionSeen = true;
        }
        $this->assertTrue($exceptionSeen);
    }

    /**
     * Test basic use of the Zend_Db_Select class.
     */
    public function testSelect()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $select = $this->_db->select();
        $this->assertThat($select, $this->isInstanceOf('Zend_Db_Select'), 'Expecting object of type Zend_Db_Select');

        $select->from($table);
        $stmt = $this->_db->query($select);
        $row = $stmt->fetch();
        $this->assertEquals(5, count($row)); // correct number of fields
        $this->assertEquals('1', $row[$id]); // correct data
    }

    /**
     * Test basic use of the Zend_Db_Select class.
     */
    public function testSelectQuery()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $select = $this->_db->select();
        $this->assertThat($select, $this->isInstanceOf('Zend_Db_Select'), 'Expecting object of type Zend_Db_Select');

        $select->from($table);
        $stmt = $select->query();
        $row = $stmt->fetch();
        $this->assertEquals(5, count($row)); // correct number of fields
        $this->assertEquals('1', $row[$id]); // correct data
    }

    /**
     * Test Zend_Db_Select specifying columns
     */
    public function testSelectColumns()
    {
        $titleKey = $this->getResultSetKey('title');
        $subtitleKey = $this->getResultSetKey('subtitle');
        $table = $this->getIdentifier(self::TABLE_NAME);
        $title = $this->getIdentifier('title');
        $subtitle = $this->getIdentifier('subtitle');

        $select = $this->_db->select()
            ->from($table, $title); // scalar
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result), 'Expected count of result set to be 2');
        $this->assertEquals(1, count($result[0]), 'Expected column count of result set to be 1');
        $this->assertThat($result[0], $this->arrayHasKey($titleKey));

        $select = $this->_db->select()
            ->from($table, array($title, $subtitle)); // array
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result), 'Expected count of result set to be 2');
        $this->assertEquals(2, count($result[0]), 'Expected column count of result set to be 2');
        $this->assertThat($result[0], $this->arrayHasKey($titleKey));
        $this->assertThat($result[0], $this->arrayHasKey($subtitleKey));
    }

    /**
     * Test adding the FOR UPDATE query modifier to a Zend_Db_Select object.
     */
    public function testSelectDistinctModifier()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $select = $this->_db->select()
            ->distinct()
            ->from($table, array())
            ->from('', 327);
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));
    }

    /**
     * Test adding the FOR UPDATE query modifier to a Zend_Db_Select object.
     *
    public function testSelectForUpdateModifier()
    {
    }
     */

    /**
     * Test adding a JOIN to a Zend_Db_Select object.
     */
    public function testSelectJoinClause()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);

        $this->createTestTable2();
        $select = $this->_db->select()
            ->from($table)
            ->join($table2, 'id = news_id');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(3, count($result));
        $this->assertEquals(10, count($result[0]));
    }

    /**
     * Test adding an INNER JOIN to a Zend_Db_Select object.
     * This should be exactly the same as the plain JOIN clause.
     */
    public function testSelectJoinClauseWithCorrelationName()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);

        $this->createTestTable2();
        $select = $this->_db->select()
            ->from( array($table => 'xyz1') )
            ->join( array($table2 => 'xyz2'), 'xyz1.id = xyz2.news_id')
            ->where('xyz1.id = 1');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(3, count($result));
        $this->assertEquals(10, count($result[0]));
    }

    /**
     * Test adding an INNER JOIN to a Zend_Db_Select object.
     * This should be exactly the same as the plain JOIN clause.
     */
    public function testSelectJoinInnerClause()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);

        $this->createTestTable2();
        $select = $this->_db->select()
            ->from($table)
            ->joinInner($table2, 'id = news_id');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(3, count($result));
        $this->assertEquals(10, count($result[0]));
    }

    /**
     * Test adding an outer join to a Zend_Db_Select object.
     */
    public function testSelectJoinLeftClause()
    {
        $id = $this->getResultSetKey('id');
        $newsId = $this->getResultSetKey('news_id');
        $table = $this->getIdentifier(self::TABLE_NAME);
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);

        $this->createTestTable2();
        $select = $this->_db->select()
            ->from($table)
            ->joinLeft($table2, 'id = news_id');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(4, count($result));
        $this->assertEquals(10, count($result[0]));
        $this->assertEquals(2, $result[3][$id]);
        $this->assertNull($result[3][$newsId]);
    }

    /**
     * Test adding an outer join to a Zend_Db_Select object.
     */
    public function testSelectJoinRightClause()
    {
        $id = $this->getResultSetKey('id');
        $newsId = $this->getResultSetKey('news_id');
        $table = $this->getIdentifier(self::TABLE_NAME);
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);

        $this->createTestTable2();
        $select = $this->_db->select()
            ->from($table2)
            ->joinRight($table, 'id = news_id');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(4, count($result));
        $this->assertEquals(10, count($result[0]));
        $this->assertEquals(2, $result[3][$id]);
        $this->assertNull($result[3][$newsId]);
    }

    /**
     * Test adding a cross join to a Zend_Db_Select object.
     */
    public function testSelectJoinCrossClause()
    {
        $id = $this->getResultSetKey('id');
        $newsId = $this->getResultSetKey('news_id');
        $table = $this->getIdentifier(self::TABLE_NAME);
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);

        $this->createTestTable2();
        $select = $this->_db->select()
            ->from($table)
            ->joinCross($table2);
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(6, count($result));
        $this->assertEquals(10, count($result[0]));
    }

    /**
     * Test adding a WHERE clause to a Zend_Db_Select object.
     * @todo: test where() with 2 args for quoteInto()
     */
    public function testSelectWhereClause()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $select = $this->_db->select()
            ->from($table)
            ->where('id = 2');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));
        $this->assertEquals(2, $result[0][$id]);

        // test adding more WHERE conditions,
        // which should be combined with AND by default.
        $select = $this->_db->select()
            ->from($table)
            ->where('id = 2')
            ->where('id = 1');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(0, count($result));
    }

    /**
     * Test adding an OR WHERE clause to a Zend_Db_Select object.
     * @todo: test orWhere() with 2 args for quoteInto()
     */
    public function testSelectOrWhereClause()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $select = $this->_db->select()
            ->from($table)
            ->orWhere('id = 1')
            ->orWhere('id = 2');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result));
        $this->assertEquals(1, $result[0][$id]);
        $this->assertEquals(2, $result[1][$id]);
    }

    /**
     * Test adding a GROUP BY clause to a Zend_Db_Select object.
     */
    public function testSelectGroupByClause()
    {
        $userIdKey = $this->getResultSetKey('user_id');
        $countKey = $this->getResultSetKey('thecount');
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);
        $userId = $this->getIdentifier('user_id');

        $this->createTestTable2();

        $select = $this->_db->select()
            ->from($table2, $userId)
            ->from('', new Zend_Db_Expr('count(*) as thecount'))
            ->group($userId)
            ->order($userId);
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result), 'Expected count of first result set to be 2');
        $this->assertEquals(101, $result[0][$userIdKey]);
        $this->assertEquals(2, $result[0][$countKey], 'Expected count(*) of first result set to be 2');
        $this->assertEquals(102, $result[1][$userIdKey]);
        $this->assertEquals(1, $result[1][$countKey]);

        $select = $this->_db->select()
            ->from($table2, array($userId))
            ->from('', new Zend_Db_Expr('count(*) as thecount'))
            ->group(array($userId, $userId))
            ->order($userId);
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result), 'Expected count of second result set to be 2');
        $this->assertEquals(101, $result[0][$userIdKey]);
        $this->assertEquals(2, $result[0][$countKey], 'Expected count(*) of second result set to be 2');
        $this->assertEquals(102, $result[1][$userIdKey]);
        $this->assertEquals(1, $result[1][$countKey]);
    }

    /**
     * Test adding a HAVING clause to a Zend_Db_Select object.
     * @todo: test having() with 2 args for quoteInto()
     */
    public function testSelectHavingClause()
    {
        // @todo: fix ZF-884
        if ($this->getDriver() == 'pdo_Sqlite') {
            $this->markTestIncomplete('Pending fix for ZF-884');
            return;
        }

        $userIdKey = $this->getResultSetKey('user_id');
        $countKey = $this->getResultSetKey('thecount');
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);
        $userId = $this->getIdentifier('user_id');

        $this->createTestTable2();

        $select = $this->_db->select()
            ->from($table2, array($userId))
            ->from('', 'count(*) as thecount')
            ->group($userId)
            ->having('count(*) > 1');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));
        $this->assertEquals(101, $result[0][$userIdKey]);
        $this->assertEquals(2, $result[0][$countKey]);

        $select = $this->_db->select()
            ->from($table2, array($userId))
            ->from('', 'count(*) as thecount')
            ->group($userId)
            ->having('count(*) > 1')
            ->having('count(*) = 1');
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(0, count($result));
    }

    /**
     * Test adding a HAVING clause to a Zend_Db_Select object.
     * @todo: test orHaving() with 2 args for quoteInto()
     */
    public function testSelectOrHavingClause()
    {
        // @todo: fix ZF-884
        if ($this->getDriver() == 'pdo_Sqlite') {
            $this->markTestIncomplete('Pending fix for ZF-884');
            return;
        }

        $userIdKey = $this->getResultSetKey('user_id');
        $countKey = $this->getResultSetKey('thecount');
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);
        $userId = $this->getIdentifier('user_id');

        $this->createTestTable2();

        $select = $this->_db->select()
            ->from($table2, array($userId))
            ->from('', 'count(*) as thecount')
            ->group($userId)
            ->orHaving('count(*) > 1')
            ->orHaving('count(*) = 1')
            ->order($userId);
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result));
        $this->assertEquals(101, $result[0][$userIdKey]);
        $this->assertEquals(2, $result[0][$countKey]);
        $this->assertEquals(102, $result[1][$userIdKey]);
        $this->assertEquals(1, $result[1][$countKey]);

        $select = $this->_db->select()
            ->from($table2, array($userId))
            ->from('', 'count(*) as thecount')
            ->group($userId)
            ->orHaving('count(*) > 1')
            ->orHaving('count(*) = 1')
            ->order($userId);
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result));
        $this->assertEquals(101, $result[0][$userIdKey]);
        $this->assertEquals(2, $result[0][$countKey]);
        $this->assertEquals(102, $result[1][$userIdKey]);
        $this->assertEquals(1, $result[1][$countKey]);
    }

    /**
     * Test adding an ORDER BY clause to a Zend_Db_Select object.
     */
    public function testSelectOrderByClause()
    {
        $idKey = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);
        $id = $this->getIdentifier('id');

        $select = $this->_db->select()
            ->from($table)
            ->order($id);
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, $result[0][$idKey]);

        $select = $this->_db->select()
            ->from($table)
            ->order(array($id, $id));
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result), 'Expected count of result set to be 2');
        $this->assertEquals(1, $result[0][$idKey]);

        $select = $this->_db->select()
            ->from($table)
            ->order("$id ASC");
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result), 'Expected count of result set to be 2');
        $this->assertEquals(1, $result[0][$idKey]);

        $select = $this->_db->select()
            ->from($table)
            ->order("$id DESC");
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result), 'Expected count of result set to be 2');
        $this->assertEquals(2, $result[0][$idKey]);
    }

    /**
     * Test adding a LIMIT clause to a Zend_Db_Select object.
     */
    public function testSelectLimitClause()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $select = $this->_db->select()
            ->from($table)
            ->limit(); // no limit
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(2, count($result));

        $select = $this->_db->select()
            ->from($table)
            ->limit(1);
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));
        $this->assertEquals(1, $result[0][$id]);

        $select = $this->_db->select()
            ->from($table)
            ->limit(1, 1);
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));
        $this->assertEquals(2, $result[0][$id]);
    }

    /**
     * Test the limitPage() method of a Zend_Db_Select object.
     */
    public function testSelectLimitPage()
    {
        $id = $this->getResultSetKey('id');
        $table = $this->getIdentifier(self::TABLE_NAME);

        $select = $this->_db->select()
            ->from($table)
            ->limitPage(1, 1); // first page, length 1
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));
        $this->assertEquals(1, $result[0][$id]);

        $select = $this->_db->select()
            ->from($table)
            ->limitPage(2, 1); // second page, length 1
        $stmt = $this->_db->query($select);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result));
        $this->assertEquals(2, $result[0][$id]);
    }

    /**
     * Test the getPart() and reset() methods of a Zend_Db_Select object.
     */
    public function testSelectGetPartAndReset()
    {
        $table = $this->getIdentifier(self::TABLE_NAME);

        $select = $this->_db->select()
            ->from($table)
            ->limit(1);
        $count = $select->getPart(Zend_Db_Select::LIMIT_COUNT);
        $this->assertEquals(1, $count);

        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $count = $select->getPart(Zend_Db_Select::LIMIT_COUNT);
        $this->assertNull($count);

        $select->reset(); // reset the whole object
        $from = $select->getPart(Zend_Db_Select::FROM);
        $this->assertTrue(empty($from));
    }

    protected function getInstanceOfDbTable()
    {
        Zend::loadClass('Zend_Db_Table_ZfTestTable');
        $table = $this->getIdentifier(self::TABLE_NAME);
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);
        $id = $this->getIdentifier('id');

        $dbTable = new Zend_Db_Table_ZfTestTable(
            array(
                'db'              => $this->_db,
                'name'            => $table,
                'primary'         => $id,
                'dependentTables' => array('Zend_Db_Table_ZfTestTable2')
            )
        );

        return array($dbTable, $table, $id);
    }

    protected function getInstanceOfDbTable2()
    {
        Zend::loadClass('Zend_Db_Table_ZfTestTable2');
        $table = $this->getIdentifier(self::TABLE_NAME);
        $table2 = $this->getIdentifier(self::TABLE_NAME_2);
        $id = $this->getIdentifier('id');
        $newsId = $this->getIdentifier('news_id');

        $dbTable = new Zend_Db_Table_ZfTestTable2(
            array(
                'db'              => $this->_db,
                'name'            => $table2,
                'referenceMap'    => array(
                    'News' => array(
                        'columns'    => array($newsId),
                        'refTable'   => 'Zend_Db_Table_ZfTestTable',
                        'refColumns' => array($id),
                        'onDelete'   => Zend_Db_Table_Abstract::CASCADE,
                        'onUpdate'   => Zend_Db_Table_Abstract::CASCADE
                    )
                )
            )
        );
        return array($dbTable, $table2);
    }

    public function testTable()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $title        = $this->getIdentifier('title');
        $subtitle     = $this->getIdentifier('subtitle');
        $body         = $this->getIdentifier('body');
        $date_created = $this->getIdentifier('date_created');

        $info = $dbTable->info();
        $this->assertThat($info, $this->arrayHasKey('name'));
        $this->assertThat($info, $this->arrayHasKey('cols'));
        $this->assertThat($info, $this->arrayHasKey('primary'));

        $this->assertEquals($table, $this->getIdentifier($info['name']));

        $this->assertContains($id, $info['cols'], "Expected column '$id' to be in list of columns");
        $this->assertContains($title, $info['cols'], "Expected column '$title' to be in list of columns");
        $this->assertContains($subtitle, $info['cols'], "Expected column '$subtitle' to be in list of columns");
        $this->assertContains($body, $info['cols'], "Expected column '$body' to be in list of columns");
        $this->assertContains($date_created, $info['cols'], "Expected column '$date_created' to be in list of columns");

        $this->assertContains($id, $info['primary'], "Expected column '$id' to be in list of primary key columns");
    }

    public function testTableSetAndGetAdapter()
    {
        Zend::loadClass('Zend_Db_Table_ZfTestTable');
        $table = $this->getIdentifier(self::TABLE_NAME);
        $id = $this->getIdentifier('id');

        Zend_Db_Table_ZfTestTable::setDefaultAdapter($this->_db);

        $dbTable = new Zend_Db_Table_ZfTestTable(
            array(
                'name' => $table,
                'primary' => $id
            )
        );

        $db = $dbTable->getAdapter();
        $this->assertThat($db, $this->isInstanceOf('Zend_Db_Adapter_Abstract'), 'Expecting object of type Zend_Db_Adapter_Abstract');
    }

    public function testTableExceptionSetInvalidAdapter()
    {
        Zend::loadClass('Zend_Db_Table_ZfTestTable');
        $table = $this->getIdentifier(self::TABLE_NAME);
        $id = $this->getIdentifier('id');

        try {
            Zend_Db_Table_ZfTestTable::setDefaultAdapter(new stdClass());
            $this->fail('Expected to catch PHPUnit_Framework_Error');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('PHPUnit_Framework_Error'), 'Expecting object of type PHPUnit_Framework_Error got '.get_class($e));
            $mesg = substr("Argument 1 passed to Zend_Db_Table_Abstract::setDefaultAdapter() must be an instance of Zend_Db_Adapter_Abstract, instance of stdClass given", 0, 100);
            $this->assertEquals($mesg, substr($e->getMessage(), 0, 100));
        }
    }

    public function testTableExceptionPrimaryKeyNotSpecified()
    {
        Zend::loadClass('Zend_Db_Table_ZfTestTable');
        $table = $this->getIdentifier(self::TABLE_NAME);

        try {
            $dbTable = new Zend_Db_Table_ZfTestTable(
                array(
                    'name' => $table,
                    'primary' => ''
                )
            );
            $this->fail('Expected to catch Zend_Db_Table_Exception');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Db_Table_Exception'), 'Expecting object of type Zend_Db_Table_Exception, got '.get_class($e));
            $this->assertContains("Primary key column(s)", $e->getMessage());
            $this->assertContains("are not columns in this table", $e->getMessage());
        }
    }

    public function testTableExceptionInvalidPrimaryKey()
    {
        Zend::loadClass('Zend_Db_Table_ZfTestTable');
        $table = $this->getIdentifier(self::TABLE_NAME);
        $invalidId = $this->getIdentifier('invalid');

        try {
            $dbTable = new Zend_Db_Table_ZfTestTable(
                array(
                    'name' => $table,
                    'primary' => $invalidId
                )
            );
            $this->fail('Expected to catch Zend_Db_Table_Exception');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Db_Table_Exception'), 'Expecting object of type Zend_Db_Table_Exception, got '.get_class($e));
            $this->assertContains("Primary key column(s)", $e->getMessage());
            $this->assertContains("are not columns in this table", $e->getMessage());
        }
    }

    public function testTableFind()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $row1 = $dbTable->find(1);
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');

        $rows = $dbTable->find(array(1, 2));
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
    }

    public function testTableInsert()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $row = array (
            'title'        => 'News Item 3',
            'subtitle'     => 'Sub title 3',
            'body'         => 'This is body 1',
            'date_created' => '2006-05-03 13:13:13'
        );
        $insertResult = $dbTable->insert($row);
        $last_insert_id = $this->_db->lastInsertId();

        $this->assertEquals($insertResult, (string) $last_insert_id);
        $this->assertEquals(3, (string) $last_insert_id);
    }

    public function testTableUpdate()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();
        $title = $this->getIdentifier('title');
        $subtitle = $this->getIdentifier('subtitle');

        $newTitle = 'New News Item 2';
        $newSubTitle = 'New Sub title 2';
        $data = array(
            $title    => $newTitle,
            $subtitle => $newSubTitle
        );
        $result = $dbTable->update($data, 'id = 2');
        $this->assertEquals(1, $result);

        // Query the row to see if we have the new values.
        $rows = $dbTable->find(2);
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $this->assertEquals(1, $rows->count(), "Expecting rowset count to be 1");
        $row = $rows->current();
        $this->assertThat($row, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');
        $this->assertEquals(2, $row->id, "Expecting row->id to be 2");
        $this->assertEquals($newTitle, $row->title, "Expecting row->title to be \"$newTitle\"");
        $this->assertEquals($newSubTitle, $row->subtitle, "Expecting row->subtitle to be \"$newSubTitle\"");
    }

    public function testTableDelete()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $rows = $dbTable->find(array(1, 2));
        $this->assertEquals(2, $rows->count());

        $dbTable->delete('id = 2');

        $rows = $dbTable->find(array(1, 2));
        $this->assertEquals(1, $rows->count());
    }

    public function testTableFetchNew()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $row1 = $dbTable->fetchNew();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');
    }

    public function testTableFetchAll()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();
        $rows = $dbTable->fetchAll();
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $this->assertEquals(2, $rows->count());
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');
    }

    public function testTableFetchAllWhere()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();
        $rows = $dbTable->fetchAll("$id = 2");
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $this->assertEquals(1, $rows->count());
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');
        $this->assertEquals(2, $row1->id);
    }

    public function testTableFetchAllOrder()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();
        $rows = $dbTable->fetchAll(null, "$id DESC");
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $this->assertEquals(2, $rows->count());
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');
        $this->assertEquals(2, $row1->id);
    }

    public function testTableFetchAllOrderExpr()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();
        $rows = $dbTable->fetchAll(null, new Zend_Db_Expr("$id + 1 DESC"));
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $this->assertEquals(2, $rows->count());
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');
        $this->assertEquals(2, $row1->id);
    }

    public function testTableFetchAllLimit()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();
        $rows = $dbTable->fetchAll(null, null, 2, 1);
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $this->assertEquals(1, $rows->count());
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');
        $this->assertEquals(2, $row1->id);
    }

    public function testTableExceptionNoAdapter()
    {
        Zend::loadClass('Zend_Db_Table_ZfTestTable');

        try {
            $dbTable = new Zend_Db_Table_ZfTestTable(array('db' => 327));
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Db_Table_Exception'), 'Expecting object of type Zend_Db_Table_Exception, got '.get_class($e));
            $this->assertEquals("No object of type Zend_Db_Adapter_Abstract has been specified", $e->getMessage());
        }

        Zend::register('registered_db', 327);
        try {
            $dbTable = new Zend_Db_Table_ZfTestTable(array('db' => 'registered_db'));
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Db_Table_Exception'), 'Expecting object of type Zend_Db_Table_Exception, got '.get_class($e));
            $this->assertEquals("No object of type Zend_Db_Adapter_Abstract has been specified", $e->getMessage());
        }

        try {
            Zend_Db_Table_ZfTestTable::setDefaultAdapter(327);
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('PHPUnit_Framework_Error'), 'Expecting object of type Zend_Db_Table_Exception, got '.get_class($e));
            $mesg = substr("Argument 1 passed to Zend_Db_Table_Abstract::setDefaultAdapter() must be an instance of Zend_Db_Adapter_Abstract, integer given", 0, 100);
            $this->assertEquals($mesg, substr($e->getMessage(), 0, 100));
        }

    }

    public function testTableRowsetIterator()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $rows = $dbTable->find(array(1, 2));
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');

        // see if we're at the beginning
        $this->assertEquals(0, $rows->key());

        // get first row and see if it's the right one
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');
        $this->assertEquals(1, $row1->id);

        // advance to next row
        $this->assertEquals(1, $rows->next());
        $this->assertEquals(1, $rows->key());
        $this->assertTrue($rows->valid());

        // get second row and see if it's the right one
        $row2 = $rows->current();
        $this->assertThat($row2, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');
        $this->assertEquals(2, $row2->id);

        // advance beyond last row
        $this->assertEquals(2, $rows->next());
        $this->assertEquals(2, $rows->key());
        $this->assertFalse($rows->valid());
        $this->assertFalse($rows->current());

        // rewind to beginning 
        $rows->rewind();
        $this->assertEquals(0, $rows->key());
        $this->assertTrue($rows->valid());

        // get row at beginning and compare it to 
        // the one we got earlier
        $row1Copy = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');
        $this->assertEquals(1, $row1->id);
        $this->assertSame($row1, $row1Copy);
    }

    public function testTableRowsetToArray()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $rows = $dbTable->find(array(1, 2));
        $this->assertEquals(2, $rows->count());

        // iterate through the rowset,
        // because that's the only way to
        // force it to instantiate the 
        // individual Rows
        foreach ($rows as $row)
        {
            $row->title = 'foo';
        }

        $a = $rows->toArray();

        $this->assertTrue(is_array($a));
        $this->assertEquals(count($a), $rows->count());
        $this->assertTrue(is_array($a[0]));
        $this->assertEquals(5, count($a[0]));
        $this->assertEquals('foo', $a[0]['title']);
    }

    public function testTableFindRow()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $rows = $dbTable->find(1);
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $this->assertTrue($rows->exists());
        $this->assertEquals(1, $rows->count());
    }

    public function testTableRowConstructor()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $row1 = new Zend_Db_Table_Row(
            array(
                'db'    => $this->_db,
                'table' => $dbTable
            )
        );

        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');

        try {
            $title = $row1->title;
            $this->fail('Expected to catch Zend_Db_Table_Row_Exception');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Db_Table_Row_Exception'), 'Expecting object of type Zend_Db_Table_Row_Exception, got '.get_class($e));
            $this->assertEquals("Specified column \"title\" is not in the row", $e->getMessage());
        }
    }

    public function testTableRowToArray()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $rows = $dbTable->find(1);
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');

        $a = $row1->toArray();

        $this->assertTrue(is_array($a));

        $this->assertThat($a, $this->arrayHasKey('id'));
        $this->assertThat($a, $this->arrayHasKey('title'));
        $this->assertThat($a, $this->arrayHasKey('subtitle'));
        $this->assertThat($a, $this->arrayHasKey('body'));
        $this->assertThat($a, $this->arrayHasKey('date_created'));
    }

    public function testTableRowMagicGet()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $rows = $dbTable->find(1);
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');

        try {
            $this->assertEquals(1, $row1->id);
            $this->assertEquals('News Item 1', $row1->title);
            $this->assertEquals('Sub title 1', $row1->subtitle);
            $this->assertEquals('This is body 1', $row1->body);
            $this->assertEquals('2006-05-01 11:11:11', $row1->date_created);
        } catch (Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
    }

    public function testTableRowMagicSet()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();
        $newTitle = 'New News Item 1';
        $newSubTitle = 'New Sub title 1';

        $rows = $dbTable->find(1);
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');

        try {
            $row1->title = $newTitle;
            $row1->subtitle = $newSubTitle;
            $this->assertEquals($newTitle, $row1->title);
            $this->assertEquals($newSubTitle, $row1->subtitle);
        } catch (Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
    }

    public function testTableRowSetFromArray()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $data = array(
            'title' => 'New News Item 1',
            'subtitle' => 'New Sub title 1'
        );

        $rows = $dbTable->find(1);
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');

        $row1->setFromArray($data);

        try {
            $row1->title = $data['title'];
            $row1->subtitle = $data['subtitle'];
            $this->assertEquals($data['title'], $row1->title);
            $this->assertEquals($data['subtitle'], $row1->subtitle);
        } catch (Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
    }

    public function testTableRowSaveInsert()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $data = array(
            'title'       => 'News Item 3',
            'subtitle'    => 'Sub title 3',
            'body'        => 'This is body 3',
            'date_created' => '2006-05-01 13:13:13'
        );

        $row3 = $dbTable->fetchNew();

        $row3->setFromArray($data);

        $row3->save();

        try {
            $this->assertEquals(3, $row3->id);
            $this->assertEquals($data['title'], $row3->title);
            $this->assertEquals($data['subtitle'], $row3->subtitle);
            $this->assertEquals($data['body'], $row3->body);
            $this->assertEquals($data['date_created'], $row3->date_created);
        } catch (Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
    }

    public function testTableRowSaveUpdate()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $data = array(
            'title'       => 'News Item 4',
            'subtitle'    => 'Sub title 4',
            'body'        => 'This is body 4',
            'date_created' => '2006-05-01 14:14:14'
        );

        $rows = $dbTable->find(1);
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');

        $row1->setFromArray($data);

        $row1->save();

        try {
            $this->assertEquals(1, $row1->id);
            $this->assertEquals($data['title'], $row1->title);
            $this->assertEquals($data['subtitle'], $row1->subtitle);
            $this->assertEquals($data['body'], $row1->body);
            $this->assertEquals($data['date_created'], $row1->date_created);
        } catch (Exception $e) {
            $this->fail("Caught exception of type \"".get_class($e)."\" where no exception was expected.  Exception message: \"".$e->getMessage()."\"\n");
        }
    }

    public function testTableRelationshipFindParentRow()
    {
        $this->createTestTable2();

        list ($dbTable2, $table) = $this->getInstanceOfDbTable2();
        $childRows = $dbTable2->fetchAll('news_id = 1');
        $this->assertThat($childRows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');

        $childRow1 = $childRows->current();
        $parentRow = $childRow1->findParentZend_Db_Table_ZfTestTable();
        $this->assertThat($parentRow, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');

        $this->assertEquals(1,                     $parentRow->id);
        $this->assertEquals('News Item 1',         $parentRow->title);
        $this->assertEquals('Sub title 1',         $parentRow->subtitle);
        $this->assertEquals('This is body 1',      $parentRow->body);
        $this->assertEquals('2006-05-01 11:11:11', $parentRow->date_created);
    }

    public function testTableRelationshipFindDependentRowset()
    {
        $this->createTestTable2();

        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();
        $parentRows = $dbTable->find(1);
        $this->assertThat($parentRows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $parentRow1 = $parentRows->current();

        $childRows = $parentRow1->findZend_Db_Table_ZfTestTable2();
        $this->assertThat($childRows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $this->assertEquals(3, $childRows->count());
        $childRow1 = $childRows->current();
        $this->assertThat($childRow1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');
        $this->assertEquals(1,                     $childRow1->news_id);
        $this->assertEquals(101,                   $childRow1->user_id);
        $this->assertEquals('I agree',             $childRow1->comment_title);
        $this->assertEquals('This is comment 1',   $childRow1->comment_body);
        $this->assertEquals('2006-05-01 13:13:13', $childRow1->date_posted);
    }

    public function testTableRowExceptionGetColumnNotInRow()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $rows = $dbTable->find(1);
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');

        $column = 'doesNotExist';

        try {
            $dummy = $row1->$column;
            $this->fail('Expected to catch Zend_Db_Table_Row_Exception');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Db_Table_Row_Exception'), 'Expecting object of type Zend_Db_Table_Row_Exception, got '.get_class($e));
            $this->assertEquals("Specified column \"$column\" is not in the row", $e->getMessage());
        }
    }

    public function testTableRowExceptionSetColumnNotInRow()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $rows = $dbTable->find(1);
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');

        $column = 'doesNotExist';

        try {
            $row1->$column = 'dummy value';
            $this->fail('Expected to catch Zend_Db_Table_Row_Exception');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Db_Table_Row_Exception'), 'Expecting object of type Zend_Db_Table_Row_Exception, got '.get_class($e));
            $this->assertEquals("Specified column \"$column\" is not in the row", $e->getMessage());
        }
    }

    public function testTableRowExceptionSetPrimaryKey()
    {
        list ($dbTable, $table, $id) = $this->getInstanceOfDbTable();

        $rows = $dbTable->find(1);
        $this->assertThat($rows, $this->isInstanceOf('Zend_Db_Table_Rowset'), 'Expecting object of type Zend_Db_Table_Rowset');
        $row1 = $rows->current();
        $this->assertThat($row1, $this->isInstanceOf('Zend_Db_Table_Row'), 'Expecting object of type Zend_Db_Table_Row');

        try {
            $row1->id = 'dummy value';
            $this->fail('Expected to catch Zend_Db_Table_Row_Exception');
        } catch (Exception $e) {
            $this->assertThat($e, $this->isInstanceOf('Zend_Db_Table_Row_Exception'), 'Expecting object of type Zend_Db_Table_Row_Exception, got '.get_class($e));
            $this->assertEquals("Changing the primary key value(s) is not allowed", $e->getMessage());
        }
    }

}
