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

require_once 'Zend/Db/Adapter/Pdo/TestCommon.php';

PHPUnit_Util_Filter::addFileToFilter(__FILE__);

class Zend_Db_Adapter_Pdo_OciTest extends Zend_Db_Adapter_Pdo_TestCommon
{

    public function testAdapterInsert()
    {
        $row = array (
            'product_id'   => new Zend_Db_Expr($this->_db->quoteIdentifier('zfproducts_seq').'.NEXTVAL'),
            'product_name' => 'Solaris',
        );
        $rowsAffected = $this->_db->insert('zfproducts', $row);
        $this->assertEquals(1, $rowsAffected);
        $lastInsertId = $this->_db->lastInsertId('zfproducts', null); // implies 'products_seq'
        $lastSequenceId = $this->_db->lastSequenceId('zfproducts_seq');
        $this->assertEquals('4', (string) $lastInsertId, 'Expected new id to be 4');
        $this->assertEquals('4', (string) $lastSequenceId, 'Expected new id to be 4');
    }

    public function testAdapterExceptionInvalidLoginCredentials()
    {
        $params = $this->_util->getParams();
        $params['password'] = 'xxxxxxxx'; // invalid password

        try {
            $db = new Zend_Db_Adapter_Pdo_Oci($params);
            $db->getConnection(); // force connection
            $this->fail('Expected to catch Zend_Db_Adapter_Exception');
        } catch (Zend_Exception $e) {
            $this->assertType('Zend_Db_Adapter_Exception', $e,
                'Expecting object of type Zend_Db_Adapter_Exception, got '.get_class($e));
        }
    }

    /**
     * Test the Adapter's limit() method.
     * Fetch 1 row.  Then fetch 1 row offset by 1 row.
     */
    public function testAdapterLimit()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');

        $sql = $this->_db->limit("SELECT * FROM $products", 1);

        $stmt = $this->_db->query($sql);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result),
            'Expecting row count to be 1');
        $this->assertEquals(3, count($result[0]),
            'Expecting column count to be 3');
        $this->assertEquals(1, $result[0]['product_id'],
            'Expecting to get product_id 1');
    }

    public function testAdapterLimitOffset()
    {
        $products = $this->_db->quoteIdentifier('zfproducts');

        $sql = $this->_db->limit("SELECT * FROM $products", 1, 1);

        $stmt = $this->_db->query($sql);
        $result = $stmt->fetchAll();
        $this->assertEquals(1, count($result),
            'Expecting row count to be 1');
        $this->assertEquals(3, count($result[0]),
            'Expecting column count to be 3');
        $this->assertEquals(2, $result[0]['product_id'],
            'Expecting to get product_id 2');
    }

    public function getDriver()
    {
        return 'Pdo_Oci';
    }

}
