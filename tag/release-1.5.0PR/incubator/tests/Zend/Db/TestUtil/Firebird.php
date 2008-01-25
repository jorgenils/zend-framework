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
 * @version    $Id: Firebird.php 6847 2007-11-18 05:24:21Z peptolab $
 */


/**
 * @see Zend_Db_TestUtil_Common
 */
require_once 'Zend/Db/TestUtil/Common.php';


PHPUnit_Util_Filter::addFileToFilter(__FILE__);


/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_TestUtil_Firebird extends Zend_Db_TestUtil_Common
{
    public function setUp(Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
        $this->createSequence('zfbugs_seq');
        $this->_rawQuery('SET GENERATOR "zfbugs_seq" TO 4');
        $this->createSequence('zfproducts_seq');
        $this->_rawQuery('SET GENERATOR "zfproducts_seq" TO 3');
        parent::setUp($db);
    }

    protected function _getDataProducts()
    {
        return array(
            array('product_id' => 1, 'product_name' => 'Windows'),
            array('product_id' => 2, 'product_name' => 'Linux'),
            array('product_id' => 3, 'product_name' => 'OS X'),
        );
    }	
	
    protected function _getColumnsBugs()
    {
        return array(
            'bug_id'          => 'IDENTITY',
            'bug_description' => 'VARCHAR(100)',
            'bug_status'      => 'VARCHAR(20)',
            'created_on'      => 'TIMESTAMP',
            'updated_on'      => 'TIMESTAMP',
            'reported_by'     => 'VARCHAR(100)',
            'assigned_to'     => 'VARCHAR(100)',
            'verified_by'     => 'VARCHAR(100)'
        );
    }	
	
    protected function _getDataBugs()
    {
        return array(
            array(
				'bug_id'		  => 1,
                'bug_description' => 'System needs electricity to run',
                'bug_status'      => 'NEW',
                'created_on'      => '2007-04-01',
                'updated_on'      => '2007-04-01',
                'reported_by'     => 'goofy',
                'assigned_to'     => 'mmouse',
                'verified_by'     => 'dduck'
            ),
            array(
				'bug_id'		  => 2,			
                'bug_description' => 'Implement Do What I Mean function',
                'bug_status'      => 'VERIFIED',
                'created_on'      => '2007-04-02',
                'updated_on'      => '2007-04-02',
                'reported_by'     => 'goofy',
                'assigned_to'     => 'mmouse',
                'verified_by'     => 'dduck'
            ),
            array(
				'bug_id'		  => 3,			
                'bug_description' => 'Where are my keys?',
                'bug_status'      => 'FIXED',
                'created_on'      => '2007-04-03',
                'updated_on'      => '2007-04-03',
                'reported_by'     => 'dduck',
                'assigned_to'     => 'mmouse',
                'verified_by'     => 'dduck'
            ),
            array(
				'bug_id'		  => 4,			
                'bug_description' => 'Bug no product',
                'bug_status'      => 'INCOMPLETE',
                'created_on'      => '2007-04-04',
                'updated_on'      => '2007-04-04',
                'reported_by'     => 'mmouse',
                'assigned_to'     => 'goofy',
                'verified_by'     => 'dduck'
            )
        );
    }	
	
    protected function _getColumnsDocuments()
    {
        return array(
            'doc_id'       => 'INTEGER NOT NULL',
            'doc_clob'     => 'BLOB',
            'doc_blob'     => 'BLOB',
            'PRIMARY KEY'  => 'doc_id'
            );
    }
	
    public function getParams(array $constants = array())
    {
        $constants = array(
            'host'     => 'TESTS_ZEND_DB_ADAPTER_FIREBIRD_HOSTNAME',
            'username' => 'TESTS_ZEND_DB_ADAPTER_FIREBIRD_USERNAME',
            'password' => 'TESTS_ZEND_DB_ADAPTER_FIREBIRD_PASSWORD',
            'dbname'   => 'TESTS_ZEND_DB_ADAPTER_FIREBIRD_DATABASE'
        );
        return parent::getParams($constants);
    }

    public function getSqlType($type)
    {
        if ($type == 'IDENTITY') {
            return 'INTEGER NOT NULL PRIMARY KEY';
        }
        return $type;
    }
	
    protected function _getSqlCreateSequence($sequenceName)
    {
        return "CREATE GENERATOR \"$sequenceName\"";
    }

    protected function _getSqlDropSequence($sequenceName)
    {
        return "DROP GENERATOR \"$sequenceName\"";
    }	

    protected function _rawQuery($sql)
    {
        $conn = $this->_db->getConnection();		
        try {
		  ibase_query($conn, $sql);
		  ibase_commit($conn);
		} catch (Exception $e) {
            $e = ibase_errmsg();
            require_once 'Zend/Db/Exception.php';
			if (!stripos(' '.$sql, 'drop'))
				throw new Zend_Db_Exception("SQL parse error for \"$sql\": ".$e);
        }
    }
    
}
