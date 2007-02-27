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
     */
    public function __construct(array $config = array())
    {
        if ($this->_table instanceof Zend_Db_Table) {
            $this->_table = $config['table'];
            $this->_tableClass = get_class($this->_table);
        } elseif (is_string($config['table'])) {
            $this->_tableClass = $config['table'];
            // _table is set in _getTable()
        }
        // @todo why does this take _primary as option?
        $this->_primary  = $config['primary'];
        $this->_data = $config['data'];
    }

    /**
     * Retrieve row field value
     *
     * @param  string $key The column key.
     * @return string      The mapped column value.
     */
    public function __get($key)
    {
        if ($this->_data{$key} !== null) {
            return $this->_data{$key};
        }
        return null;
    }

    /**
     * Set row field value
     *
     * @param  string $key   The column key.
     * @param  mixed  $value The value for the property.
     * @return void
     */
    public function __set($key, $value)
    {
        // @todo this should permit a primary key value to be set
        // not all table have an auto-generated primary key
        if (in_array($key, (array) $this->_primary)) {
            require_once 'Zend/Db/Table/Row/Exception';
            throw new Zend_Db_Table_Row_Exception("not allowed to change primary key value");
        } elseif (!array_key_exists($key, $this->_data)) {
            require_once 'Zend/Db/Table/Row/Exception';
            throw new Zend_Db_Table_Row_Exception("column '$key' not in row");
        } else {
            $this->_data[$key] = $value;
        }
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
        return array('_table', '_primary', '_data');
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
        $keys = array_values((array) $this->_primary);
        $vals = array_fill(0, count($keys), null);
        $primary = array_combine($keys, $vals);

        $values = array_filter(array_intersect_key($primary, $this->_data));

        // check the primary key value for insert/update
        if (empty($values)) {

            // apply any built-in logic for row insertion
            $this->_insert();

            // attempt the insert.
            $result = $this->_getTable()->insert($this->_data);

            if (is_numeric($result)) {
                // insert worked, refresh with data from the table
                $this->_data[key($primary)] = $result;
                $this->_refresh($primary);
            }
        } else {
            // has a primary key value, update only that key.
            $where = array();

            foreach (array_keys($primary) as $key) {
                $where[] = "$key = " . $this->_data[$key];
            }

            // apply any built-in logic for row update
            $this->_update();

            // return the result of the update attempt, no need to update the row object.
            $result = $this->_getTable()->update($this->_data, $where);

            if (is_int($result)) {
                // update worked, refresh with data from the table
                $this->_refresh($primary);
            }
        }
        return $result;
    }

    /**
     * Deletes existing rows.
     *
     * The WHERE clause must be in native (underscore) format.
     *
     * @param string $where An SQL WHERE clause.
     * @return int The number of rows deleted.
     */
    public function delete()
    {
        // convenience var for the primary key name
        $primary = $this->_primary;

        // has a primary key value, update only that key.
        $where = array();

        foreach ((array) $primary as $key) {
            $where[] = "$key = " . $this->_data[$key];
        }

        // apply any built-in logic for row deletion
        $this->_delete();

        $result = $this->_getTable()->delete($where);

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
     * Retrieves an instance of the parent table.
     *
     * @return Zend_Db_Table
     */
    protected function _getTable()
    {
        if ($this->_table == null) {
            $this->_table = new $this->_tableClass;
        }
        return $this->_table;
    }

    /**
     * Refreshes properties from the database.
     *
     * @param  array $primary Array of primary keys
     * @return void
     */
    protected function _refresh(array $primary)
    {
        $values = array_values(array_intersect_key($this->_data, $primary));
        $fresh = call_user_func_array(array($this->_getTable(), 'find'), $values);
        // we can do this because they're both Zend_Db_Table_Row objects
        // @todo handle find() returning Rowset, not Row
        $this->_data = $fresh->_data;
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
