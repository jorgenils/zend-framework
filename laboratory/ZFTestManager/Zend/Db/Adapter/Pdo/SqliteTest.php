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
 * @package    Zend_Db_Adapter_Pdo_MysqlTest
 * @subpackage UnitTests
 */
class Zend_Db_Adapter_Pdo_SqliteTest extends Zend_Db_Adapter_Pdo_Abstract_CommonTest
{

    protected $_config;
    
    function setUp()
    {
        $this->_config = ZFTestManager::getConfig('Zend_Db_Adapter_Pdo');
        
        if ( !isset($this->_config['sqlite_username']) || 
             !isset($this->_config['sqlite_password']) || 
             !isset($this->_config['sqlite_database']) ) {
            $this->markTestSkipped('[Zend_Cache] sqlite_username, sqlite_password and sqlite_database must be set to run these tests.');
            return;
        }
        
        parent::setUp();
    }
    
    function getCreatTableSQL()
    {
        return 'CREATE TABLE  '. self::TableName . '
        (id INTEGER PRIMARY KEY, subTitle TEXT, title TEXT, body TEXT, date_created TEXT)';
    }

    function getDriver()
    {
        return 'pdo_Sqlite';
    }

    function getParams()
    {
        $params = array ('username' => $this->_config['sqlite_username'],
            'password' => $this->_config['sqlite_password'],
            'dbname'   => $this->_config['sqlite_database']);

        return $params;
    }


    public function testQuote()
    {
        // test double quotes are fine
        $value = $this->_db->quote('St John"s Wort');
        $this->assertEquals("'St John\"s Wort'", $value);

        // test that single quotes are escaped with another single quote
        $value = $this->_db->quote("St John's Wort");
        $this->assertEquals("'St John''s Wort'", $value);

        // quote an array
        $value = $this->_db->quote(array("it's", 'all', 'right!'));
        $this->assertEquals("'it''s', 'all', 'right!'", $value);

        // test numeric
        $value = $this->_db->quote('1');
        $this->assertEquals("'1'", $value);

        $value = $this->_db->quote(1);
        $this->assertEquals("'1'", $value);

        $value = $this->_db->quote(array(1,'2',3));
        $this->assertEquals("'1', '2', '3'", $value);
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
        $this->assertEquals("'table_name'", $value);
        $value = $this->_db->quoteIdentifier('table_`_name');
        $this->assertEquals("'table_`_name'", $value);
    }

}