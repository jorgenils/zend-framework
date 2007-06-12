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

require_once 'Zend/Db/TestUtil/Common.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

class Zend_Db_TestUtil_Db2 extends Zend_Db_TestUtil_Common
{

    public function setUp(Zend_Db_Adapter_Abstract $db)
    {
        $this->setAdapter($db);
        $this->createSequence('zfproducts_seq');
        parent::setUp($db);
    }

    public function getParams(array $constants = array())
    {
        $constants = array(
            'host'     => 'TESTS_ZEND_DB_ADAPTER_DB2_HOSTNAME',
            'username' => 'TESTS_ZEND_DB_ADAPTER_DB2_USERNAME',
            'password' => 'TESTS_ZEND_DB_ADAPTER_DB2_PASSWORD',
            'dbname'   => 'TESTS_ZEND_DB_ADAPTER_DB2_DATABASE',
            'port'     => 'TESTS_ZEND_DB_ADAPTER_DB2_PORT'
        );
        return parent::getParams($constants);
    }

    public function getSchema()
    {
        $server = db2_server_info($this->_db->getConnection());
        return $server->DB_NAME;
    }

    /**
     * For DB2, override the Products table to use an
     * explicit sequence-based column.
     */
    protected function _getColumnsProducts()
    {
        return array(
            'product_id'   => 'INT NOT NULL PRIMARY KEY',
            'product_name' => 'VARCHAR(100)'
        );
    }

    protected function _getDataProducts()
    {
        $data = parent::_getDataProducts();
        foreach ($data as &$row) {
            $row['product_id'] = new Zend_Db_Expr('NEXTVAL FOR '.$this->_db->quoteIdentifier('zfproducts_seq', true));
        }
        return $data;
    }

    public function getSqlType($type)
    {
        if ($type == 'IDENTITY') {
            return 'INT NOT NULL GENERATED BY DEFAULT AS IDENTITY (START WITH 1, INCREMENT BY 1) PRIMARY KEY';
        }
        if ($type == 'DATETIME') {
            return 'DATE';
        }
        return $type;
    }

    protected function _getSqlCreateTable($tableName)
    {
        $tableList = $this->_db->fetchCol('SELECT UPPER(T.TABLE_NAME) FROM SYSIBM.TABLES T '
            . $this->_db->quoteInto(' WHERE UPPER(T.TABLE_NAME) = UPPER(?)', $tableName)
        );
        if (in_array(strtoupper($tableName), $tableList)) {
            return null;
        }
        return 'CREATE TABLE ' . $this->_db->quoteIdentifier($tableName, true);
    }

    protected function _getSqlDropTable($tableName)
    {
        $tableList = $this->_db->fetchCol('SELECT UPPER(T.TABLE_NAME) FROM SYSIBM.TABLES T '
            . $this->_db->quoteInto(' WHERE UPPER(T.TABLE_NAME) = UPPER(?)', $tableName)
        );
        if (in_array(strtoupper($tableName), $tableList)) {
            return 'DROP TABLE ' . $this->_db->quoteIdentifier($tableName, true);
        }
        return null;
    }

    protected function _getSqlCreateSequence($sequenceName)
    {
        $seqList = $this->_db->fetchCol('SELECT UPPER(S.SEQNAME) FROM SYSIBM.SYSSEQUENCES S '
            . $this->_db->quoteInto(' WHERE UPPER(S.SEQNAME) = UPPER(?)', $sequenceName)
        );
        if (in_array(strtoupper($sequenceName), $seqList)) {
            return null;
        }
        return 'CREATE SEQUENCE ' . $this->_db->quoteIdentifier($sequenceName, true) . ' AS INT START WITH 1 INCREMENT BY 1 MINVALUE 1';
    }

    protected function _getSqlDropSequence($sequenceName)
    {
        $seqList = $this->_db->fetchCol('SELECT UPPER(S.SEQNAME) FROM SYSIBM.SYSSEQUENCES S '
            . $this->_db->quoteInto(' WHERE UPPER(S.SEQNAME) = UPPER(?)', $sequenceName)
        );
        if (in_array(strtoupper($sequenceName), $seqList)) {
            return 'DROP SEQUENCE ' . $this->_db->quoteIdentifier($sequenceName, true) . ' RESTRICT';
        }
        return null;
    }

    protected function _rawQuery($sql)
    {
        $conn = $this->_db->getConnection();
        $result = db2_exec($conn, $sql);
        if (!$result) {
            $e = db2_stmt_errormsg();
            require_once 'Zend/Db/Exception.php';
            throw new Zend_Db_Exception("SQL error for \"$sql\": $e");
        }
    }

}
