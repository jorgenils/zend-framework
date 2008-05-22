<?php
/**
 * @package    Zend_Db
 * @subpackage UnitTests
 */

/**
 * Common class is DB independant
 */
require_once '_abstracts/CommonTest.php';


/**
 * @package    Zend_Db_Adapter_Pdo_MssqlTest
 * @subpackage UnitTests
 */
class Zend_Db_Adapter_Pdo_MssqlTest extends Zend_Db_Adapter_Pdo_Abstract_CommonTest
{
    protected $_config;
    
    function setUp()
    {
        $this->_config = ZFTestManager::getConfig('Zend_Db_Adapter_Pdo');
        
        if ( !isset($this->_config['mssql_hostname']) || 
             !isset($this->_config['mssql_username']) || 
             !isset($this->_config['mssql_password']) || 
             !isset($this->_config['mssql_database']) ) {
            $this->markTestSkipped('[Zend_Cache] mssql_hostname, mssql_username, mssql_password and mssql_database must be set to run these tests.');
            return;
        }
        
        parent::setUp();
    }
    
    function getCreatTableSQL()
    {
        return 'CREATE TABLE  '. self::TableName . '
            (id int IDENTITY, title varchar(100), subTitle varchar (100),
            body text, date_created datetime)';
    }

    function getDriver()
    {
        return 'pdo_Mssql';
    }

    function getParams()
    {
        $params = array ('host'     => $this->_config['mssql_hostname'],
            'username' => $this->_config['mssql_username'],
            'password' => $this->_config['mssql_password'],
            'dbname'   => $this->_config['mssql_database']);

        return $params;
    }


    public function testQuote()
    {
        // test double quotes are fine
        $value = $this->_db->quote('St John"s Wort');
        $this->assertEquals("'St John\"s Wort'", $value);

        // test that single quotes are escaped with another single quote
        $value = $this->_db->quote('St John\'s Wort');
        $this->assertEquals("'St John''s Wort'", $value);

        // quote an array
        $value = $this->_db->quote(array("it's", 'all', 'right!'));
        $this->assertEquals("'it''s', 'all', 'right!'", $value);

        // test numeric
        $value = $this->_db->quote(1);
        $this->assertEquals("'1'", $value);

        $value = $this->_db->quote(array('1'));
        $this->assertEquals("'1'", $value);
    }

    public function testQuoteInto()
    {
        // test double quotes are fine
        $value = $this->_db->quoteInto('id=?', 'St John"s Wort');
        $this->assertEquals("id='St John\"s Wort'", $value);

        // test that single quotes are escaped with another single quote
        $value = $this->_db->quoteInto('id = ?', 'St John\'s Wort');
        $this->assertEquals("id = 'St John''s Wort'", $value);
    }

    public function testQuoteIdentifier()
    {
        $value = $this->_db->quoteIdentifier('table_name');
        $this->assertEquals("[table_name]", $value);

        $value = $this->_db->quoteIdentifier('table_[]_name');
        $this->assertEquals("[table_[]]_name]", $value);
    }

}
