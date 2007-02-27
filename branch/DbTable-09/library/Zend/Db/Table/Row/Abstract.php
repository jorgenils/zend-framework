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
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Db_Table_Row_Abstract
{

    /**
     * The data for each column in the row (underscore_words => value).
     *
     * @var array
     */
    protected $_data = array();

    /**
     * Zend_Db_Table parent class or instance.
     *
     * @var Zend_Db_Table
     */
    protected $_table = null;

    /**
     * Name of the class of the Zend_Db_Table object.
     *
     * @var string
     */
    protected $_tableClass = null;

    /**
     * Primary row key(s).
     *
     * @var array
     */
    protected $_primary;

    /**
     * Constructor.
     *
     * Supported params for $config are:-
     * - table       = class name or object of type Zend_Db_Table_Abstract
     * - data        = values of columns in this row.
     *
     * @param  array $config Array of user-specified config options.
     * @throws Zend_Db_Table_Row_Exception
     */
    public function __construct(array $config = array())
    {
        if (isset($config['table']) && $config['table'] instanceof Zend_Db_Table_Abstract) {
            $this->_table = $config['table'];
            $this->_tableClass = get_class($this->_table);
        }

        if (isset($config['data'])) {
            $this->_data = $config['data'];
        }

        // Retrieve primary keys from table schema
        $info = $this->_getTable()->info();
        $this->_primary = (array) $info['primary'];
    }

    /**
     * Retrieve row field value
     *
     * @param  string $key The column key.
     * @return string      The mapped column value.
     * @throws Zend_Db_Table_Row_Exception
     */
    public function __get($key)
    {
        if (!array_key_exists($key, $this->_data)) {
            require_once 'Zend/Db/Table/Row/Exception.php';
            throw new Zend_Db_Table_Row_Exception("Specified column \"$key\" is not in the row");
        }

        if (isset($this->_data[$key]) && $this->_data[$key] !== null) {
            return $this->_data[$key];
        }

        return null;
    }

    /**
     * Set row field value
     *
     * @param  string $key   The column key.
     * @param  mixed  $value The value for the property.
     * @return void
     * @throws Zend_Db_Table_Row_Exception
     */
    public function __set($key, $value)
    {
        // @todo this should permit a primary key value to be set
        // not all table have an auto-generated primary key
        if (in_array($key, $this->_primary)) {
            require_once 'Zend/Db/Table/Row/Exception.php';
            throw new Zend_Db_Table_Row_Exception("Changing the primary key value(s) is not allowed");
        }

        if (!array_key_exists($key, $this->_data)) {
            require_once 'Zend/Db/Table/Row/Exception.php';
            throw new Zend_Db_Table_Row_Exception("Specified column \"$key\" is not in the row");
        }

        $this->_data[$key] = $value;
    }

    /**
     * Test existence of row field
     *
     * @param  string  $key   The column key.
     * @return boolean
     */
    public function __isset($key)
    {
        return array_key_exists($key, $this->_data);
    }

    /**
     * Store table, primary key and data in serialized object
     *
     * @return array
     */
    public function __sleep()
    {
        return array('_tableClass', '_primary', '_data');
    }

    /**
     * Saves the properties to the database.
     *
     * This performs an intelligent insert/update, and reloads the
     * properties with fresh data from the table on success.
     *
     * @return integer 0 on failure, 1 on success.
     */
    public function save()
    {
        // convenience var for the primary key name
        $keys = $this->_getPrimaryKey();
        $values = array_filter($keys);

        // check the primary key value for insert/update
        if (empty($values)) {

            // apply any built-in logic for row insertion
            $this->_insert();

            // attempt the insert.
            $result = $this->_getTable()->insert($this->_data);

            if (is_numeric($result)) {
                // insert worked, refresh with data from the table
                $this->_data[key($keys)] = $result;
                $this->_refresh();
            }
        } else {
            // has a primary key value, update only that key.
            $where = $this->_getWhereQuery();

            // apply any built-in logic for row update
            $this->_update();

            // return the result of the update attempt, no need to update the row object.
            $result = $this->_getTable()->update($this->_data, $where);

            if (is_int($result)) {
                // update worked, refresh with data from the table
                $this->_refresh();
            }
        }
        return $result;
    }

    /**
     * Deletes existing rows.
     *
     * @return int The number of rows deleted.
     */
    public function delete()
    {
        // has a primary key value, update only that key.
        $where = $this->_getWhereQuery();

        // apply any built-in logic for row deletion
        $this->_delete();

        $result = $this->_getTable()->delete($where);

        // reset all fields to null
        $this->_data = array_combine(array_keys($this->_data), array());

        return $result;
    }

    /**
     * Returns the column/value data as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_data;
    }

    /**
     * Sets all data in the row from an array.
     *
     * @param array $data
     */
    public function setFromArray($data)
    {
        foreach ($data as $key => $val) {
            if (array_key_exists($key, $this->_data)) {
                $this->_data[$key] = $val;
            }
        }
    }

    /**
     * Retrieves an instance of the parent table.
     *
     * @return Zend_Db_Table
     */
    protected function _getTable()
    {
        if ($this->_table == null) {
            if (empty($this->_tableClass)) {
                require_once 'Zend/Db/Table/Row/Exception.php';
                throw new Zend_Db_Table_Row_Exception('No table class name specified');
            }
            // @todo: the Table constructor requires a db adapter in its config argument
            $this->_table = new $this->_tableClass();
        }
        return $this->_table;
    }

    /**
     * Retrieves an associative array of primary keys.
     *
     * @return array
     */
    protected function _getPrimaryKey()
    {
        $keys = array_values($this->_primary);
        $vals = array_fill(0, count($keys), null);
        $primary = array_combine($keys, $vals);

        return array_intersect_key($this->_data, $primary);
    }

    /**
     * Constructs where statement for retrieving row(s).
     *
     * @return array
     */
    protected function _getWhereQuery()
    {
        $db = $this->_getTable()->getAdapter();
        $keys = $this->_getPrimaryKey();

        // retrieve recently updated row using primary keys
        foreach ($keys as $key => $val) {
            $where[] = $db->quoteInto($db->quoteIdentifier($key) . ' = ?', $val);
        }
        
        return $where;
    }

    /**
     * Refreshes properties from the database.
     *
     * @return void
     */
    protected function _refresh()
    {
        $where = $this->_getWhereQuery();

        $this->_data = $this->_getTable()->fetchRow($where)->toArray();
    }

    /**
     * Allows pre-insert logic to be applied to row.
     *
     * @return void
     */
    protected function _insert()
    {
    }

    /**
     * Allows pre-update logic to be applied to row.
     *
     * @return void
     */
    protected function _update()
    {
    }

    /**
     * Allows pre-delete logic to be applied to row.
     *
     * @return void
     */
    protected function _delete()
    {
    }

}
