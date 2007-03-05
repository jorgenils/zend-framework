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
     * This is set to a copy of $_data when the data is fetched from
     * a database, specified as a new tuple in the constructor, or
     * when dirty data is posted to the database with save().
     * @var array
     */ 
    protected $_cleanData = array(); 
 
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
            $this->_cleanData = $this->_data;
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

            // execute cascading updates against dependent tables
            $depTables = $this->_getTable()->getDependentTables();
            if (!empty($depTables)) {
                $db = $this->_getTable()->getAdapter();
                $pkNew = $this->_getPrimaryKey();
                $pkOld = $this->_getPrimaryKey(false);
                $thisClass = get_class($this);
                foreach ($depTables as $tableClass) {
                    Zend::loadClass($tableClass);
                    $t = new $tableClass(array('db' => $db));
                    $t->_cascadeUpdate($thisClass, $pkOld, $pkNew);
                }
            }

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

        // execute cascading deletes against dependent tables
        $depTables = $this->_getTable()->getDependentTables();
        if (!empty($depTables)) {
            $db = $this->_getTable()->getAdapter();
            $pk = $this->_getPrimaryKey();
            $thisClass = get_class($this);
            foreach ($depTables as $tableClass) {
                Zend::loadClass($tableClass);
                $t = new $tableClass(array('db' => $db));
                $t->_cascadeDelete($thisClass, $pk);
            }
        }

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
     * @param bool $dirty
     * @return array
     */
    protected function _getPrimaryKey($dirty = true)
    {
        $primary = array_flip($this->_primary);
        if ($dirty) {
            return array_intersect_key($this->_data, $primary);
        } else {
            return array_intersect_key($this->_cleanData, $primary);
        }
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
        $this->_cleanData = $this->_data;
    }

    /**
     * Allows pre-insert logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _insert()
    {
    }

    /**
     * Allows pre-update logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _update()
    {
    }

    /**
     * Allows pre-delete logic to be applied to row.
     * Subclasses may override this method.
     *
     * @return void
     */
    protected function _delete()
    {
    }

    /**
     * @return Zend_Db_Table_Rowset - Query $dependentTableClass
     *   for the rows matching the primary key in the current row.
     * @throws Zend_Db_Table_Exception
     */ 
    public function findDependentRowset($dependentTableClass, $ruleKey = null) 
    { 
        $db = $this->_getTable()->getAdapter();
        Zend::loadClass($dependentTableClass);
        $depTable = new $dependentTableClass(array('db' => $db));
        $map = $depTable->getReference($this->_tableClass, $ruleKey);
        for ($i = 0; $i < count($map['columns']); ++$i) {
            $cond = $db->quoteIdentifier($map['columns'][$i]) . ' = ?';
            $where[$cond] = $this->_data[$map['refColumns'][$i]];
        }
        return $depTable->fetchAll($where);
    } 
 
    /**
     * @return Zend_Db_Table_Row - Query $parentTableClass for the
     *   single row matching the foreign key in the current row.
     */ 
    public function findParentRow($parentTableClass, $ruleKey = null) 
    { 
        $db = $this->_getTable()->getAdapter();
        Zend::loadClass($parentTableClass);
        $parentTable = new $parentTableClass(array('db' => $db));
        $map = $this->_getTable()->getReference($parentTableClass, $ruleKey);
        for ($i = 0; $i < count($map['columns']); ++$i) {
            $cond = $db->quoteIdentifier($map['refColumns'][$i]) . ' = ?';
            $where[$cond] = $this->_data[$map['columns'][$i]];
        }
        return $parentTable->fetchRow($where);
    } 
 
    /**
     * @return Zend_Db_Table_Rowset - Query $matchingTableClass
     *   and $intersectionTableClass for the rows matching the primary
     *   key in the current row.
     */ 
    public function findManyToManyRowset( 
        $matchTableClass, $intersectionTableClass, 
        $primaryRefRule = null, $matchRefRule = null) 
    { 
        $db = $this->_getTable()->getAdapter();
        Zend::loadClass($intersectionTableClass);
        $interTable = new $intersectionTableClass(array('db' => $db));
        Zend::loadClass($matchTableClass);
        $matchTable = new $matchTableClass(array('db' => $db));
        $interInfo = $interTable->info();
        $inter = $interInfo['name'];
        $matchInfo = $matchTable->info();
        $match = $matchInfo['name'];

        $matchMap = $interTable->getReference($matchTableClass, $matchRefRule);
        for ($i = 0; $i < count($matchMap['columns']); ++$i) {
            $interCol = $db->quoteIdentifier($inter) . '.' . $db->quoteIdentifier($matchMap['columns'][$i]);
            $matchCol = $db->quoteIdentifier($match) . '.' . $db->quoteIdentifier($matchMap['refColumns'][$i]);
            $joinCond[] = "$interCol = $matchCol";
        }
        $joinCond = implode(' AND ', $joinCond);

        $select = $db->select()
            ->from($inter, array())
            ->join($match, $joinCond, '*');

        $primaryMap = $interTable->getReference($matchTableClass, $primaryRefRule);
        for ($i = 0; $i < count($primaryMap['columns']); ++$i) {
            $interCol = $db->quoteIdentifier($inter) . '.' . $db->quoteIdentifier($matchMap['columns'][$i]);
            $value = $this->_data[$primaryMap['refColumns'][$i]];
            $select->where("$interCol = ?", $value);
        }
        $stmt = $select->query(Zend_Db::FETCH_ASSOC);

        $config = array(
            'table' => $matchTable,
            'data'  => $stmt->fetchAll()
        );
        $rowsetClass = $matchTable->getRowsetClass();
        $rowset = new $rowsetClass($config);
        return $rowset;
    } 

    /**
     * Turn magic function calls into non-magic function calls
     * to the above methods.
     *
     * @return Zend_Db_Table_Row_Abstract|Zend_Db_Table_Rowset_Abstract
     */ 
    protected function __call($method, $args) 
    { 
        /**
         * Recognize methods for Belongs-To cases: 
         * find<Class>() 
         * find<Class>By<Rule>() 
         *
         * Recognize methods for Has-Many cases: 
         * findParent<Class>() 
         * findParent<Class>By<Rule>() 
         *
         * Recognize methods for Many-to-Many cases: 
         * find<Class1>Via<Class2>() 
         * find<Class1>Via<Class2>By<Rule>() 
         * find<Class1>Via<Class2>By<Rule1>And<Rule2>() 
         */
        if (preg_match('/^find(Parent)?(\w+)(?:Via(\w+))?(?:By(\w+)(?:And(\w+))?)?/', $method, $matches)) { 
            $isParent = $matches[1];
            $class    = $matches[2];
            $viaClass = isset($matches[3]) ? $matches[3] : null;
            $ruleKey1 = isset($matches[4]) ? $matches[4] : null;
            $ruleKey2 = isset($matches[5]) ? $matches[5] : null;

            if (!empty($isParent)) {
                if (!empty($ruleKey2) || !empty($viaClass)) {
                    require_once 'Zend/Db/Table/Row/Exception.php';
                    throw new Zend_Db_Table_Row_Exception("Invalid syntax in method '$method()': do not specify Via classname or second rule");
                }
                return $this->findParentRow($class, $ruleKey1); 
            }
            if (!empty($viaClass)) {
                return $this->findManyToManyRowset($class, $viaClass, $ruleKey1, $ruleKey2); 
            }
            if (!empty($ruleKey2)) {
                require_once 'Zend/Db/Table/Row/Exception.php';
                throw new Zend_Db_Table_Row_Exception("Invalid syntax in method '$method()': do not specify second rule");
            }
            return $this->findDependentRowset($class, $ruleKey1); 
        } 

        require_once 'Zend/Db/Table/Row/Exception.php';
        throw new Zend_Db_Table_Row_Exception("Unrecognized method '$method()'");
    } 

}
