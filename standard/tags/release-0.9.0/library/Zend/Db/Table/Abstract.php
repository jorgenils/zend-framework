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
 * Zend_Db_Adapter_Abstract
 */
require_once 'Zend/Db/Adapter/Abstract.php';

/**
 * Class for SQL table interface.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Db_Table_Abstract
{

    const NAME             = 'name';
    const PRIMARY          = 'primary';
    const ROW_CLASS        = 'rowClass';
    const ROWSET_CLASS     = 'rowsetClass';
    const REFERENCE_MAP    = 'referenceMap';
    const DEPENDENT_TABLES = 'dependentTables';

    const COLUMNS          = 'columns';
    const REF_TABLE_CLASS  = 'refTableClass';
    const REF_COLUMNS      = 'refColumns';
    const ON_DELETE        = 'onDelete';
    const ON_UPDATE        = 'onUpdate';

    const CASCADE          = 'cascade';
    const RESTRICT         = 'restrict';
    const SET_NULL         = 'setNull';

    /**
     * Default Zend_Db_Adapter_Abstract object.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    static protected $_defaultDb;

    /**
     * Zend_Db_Adapter_Abstract object.
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;

    /**
     * The table name derived from the class name (underscore format).
     *
     * @var array
     */
    protected $_name;

    /**
     * The table column names derived from Zend_Db_Adapter_Abstract::describeTable().
     *
     * The key is the underscore format, and the value is the camelized
     * format.
     *
     * @var array
     */
    protected $_cols;

    /**
     * The primary key column (underscore format).
     *
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Default classname for row
     *
     * @var string
     */
    protected $_rowClass = 'Zend_Db_Table_Row';

    /**
     * Default classname for rowset
     *
     * @var string
     */
    protected $_rowsetClass = 'Zend_Db_Table_Rowset';

    /**
     * Associative array map of declarative referential integrity rules.
     * This array has one entry per foreign key in the current table.
     * Each key is a mnemonic name for one reference rule.
     *
     * Each value is also an associative array, with the following keys:
     * - columns    = array of names of column(s) in the child table.
     * - refTable   = class name of the parent table.
     * - refColumns = array of names of column(s) in the parent table,
     *                in the same order as those in the 'columns' entry.
     * - onDelete   = "cascade" means that a delete in the parent table also
     *                causes a delete of referencing rows in the child table.
     * - onUpdate   = "cascade" means that an update of primary key values in
     *                the parent table also causes an update of referencing
     *                rows in the child table.
     *
     * @var array
     */ 
    protected $_referenceMap = array(); 
 
    /**
     * Simple array of class names of tables that are "children" of the current
     * table, in other words tables that contain a foreign key to this one.
     * Array elements are not table names; they are class names of classes that
     * extend Zend_Db_Table_Abstract.
     *
     * @var array
     */ 
    protected $_dependentTables = array(); 

    /**
     * Constructor.
     *
     * Supported params for $config are:
     * - db              = user-supplied instance of database connector,
     *                     or key name of registry instance.
     * - name            = table name.
     * - primary         = string or array of primary key(s).
     * - rowclass        = row class name.
     * - rowsetClass     = rowset class name.
     * - referenceMap    = array structure to declare relationship
     *                     to parent tables.
     * - dependentTables = array of child tables.
     *
     * @param  array $config Array of user-specified config options.
     * @throws Zend_Db_Table_Exception
     */
    public function __construct(array $config = array())
    {
        // set a custom Zend_Db_Adapter connection
        if (isset($config['db'])) {

            // convenience variable
            $db = $config['db'];

            // use an object from the registry?
            if (is_string($db)) {
                $db = Zend_Registry::get($db);
            }

            // save the connection
            $this->_db = $db;
        }

        // set default table name if supplied
        if (isset($config[self::NAME])) {
            $this->_name = $config[self::NAME];
        }

        // set primary key name if supplied
        if (isset($config[self::PRIMARY])) {
            $this->_primary = (array) $config[self::PRIMARY];
        }

        // set default row classname if supplied
        if (isset($config[self::ROW_CLASS])) {
            $this->setRowClass($config[self::ROW_CLASS]);
        }

        // set default rowset classname if supplied
        if (isset($config[self::ROWSET_CLASS])) {
            $this->setRowsetClass($config[self::ROWSET_CLASS]);
        }

        if (isset($config[self::REFERENCE_MAP])) {
            $this->setReferences($config[self::REFERENCE_MAP]);
        }

        if (isset($config[self::DEPENDENT_TABLES])) {
            $this->setDependentTables($config[self::DEPENDENT_TABLES]);
        }

        // continue with automated setup
        $this->_setup();
    }

    /**
     * @param string $classname
     * @return void
     */
    public function setRowClass($classname)
    {
        // @todo: confirm class is available or throw exception
        $this->_rowClass = $classname;
    }

    /**
     * @return string
     */
    public function getRowClass()
    {
        return $this->_rowClass;
    }

    /**
     * @param string $classname
     * @return void
     */
    public function setRowsetClass($classname)
    {
        // @todo: confirm class is available or throw exception
        $this->_rowsetClass = $classname;
    }

    /**
     * @return string
     */
    public function getRowsetClass()
    {
        return $this->_rowsetClass;
    }

    /**
     * @param array $referenceMap
     * @return void
     */
    public function setReferences(array $referenceMap)
    {
        $this->_referenceMap = $referenceMap;
    }

    /**
     * @param string $tableClassname
     * @param string $ruleKey OPTIONAL
     * @return array
     * @throws Zend_Db_Table_Exception
     */
    public function getReference($tableClassname, $ruleKey = null)
    {
        $thisClass = get_class($this);
        if ($ruleKey != null) {
            if (!isset($this->_referenceMap[$ruleKey])) {
                require_once "Zend/Db/Table/Exception.php";
                throw new Zend_Db_Table_Exception("No reference rule \"$ruleKey\" from table $thisClass to table $tableClassname");
            }
            if ($this->_referenceMap[$ruleKey][self::REF_TABLE_CLASS] != $tableClassname) {
                require_once "Zend/Db/Table/Exception.php";
                throw new Zend_Db_Table_Exception("Reference rule \"$ruleKey\" does not reference table $tableClassname");
            }
            return $this->_referenceMap[$ruleKey];
        }
        foreach ($this->_referenceMap as $reference) {
            if ($reference[self::REF_TABLE_CLASS] == $tableClassname) {
                return $reference;
            }
        }
        require_once "Zend/Db/Table/Exception.php";
        throw new Zend_Db_Table_Exception("No reference from table $thisClass to table $tableClassname");
    }

    /**
     * @param array $dependentTables
     * @return void
     */
    public function setDependentTables(array $dependentTables)
    {
        $this->_dependentTables = $dependentTables;
    }

    /**
     * @return array
     */
    public function getDependentTables()
    {
        return $this->_dependentTables;
    }

    /**
     * Sets the default Zend_Db_Adapter_Abstract for all Zend_Db_Table objects.
     *
     * @param  Zend_Db_Adapter_Abstract
     * @return void
     */
    static public final function setDefaultAdapter(Zend_Db_Adapter_Abstract $db)
    {
        Zend_Db_Table_Abstract::$_defaultDb = $db;
    }

    /**
     * Gets the default Zend_Db_Adapter_Abstract for all Zend_Db_Table objects.
     *
     * @return Zend_Db_Adapter_Abstract
     */
    static public final function getDefaultAdapter()
    {
        return self::$_defaultDb;
    }

    /**
     * Gets the Zend_Db_Adapter_Abstract for this particular Zend_Db_Table object.
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public final function getAdapter()
    {
        return $this->_db;
    }

    /**
     * Populate static properties for this table module.
     *
     * @return void
     * @throws Zend_Db_Table_Exception
     */
    protected function _setup()
    {
        // get the database adapter
        if (! $this->_db) {
            $this->_db = self::getDefaultAdapter();
        }

        if (! $this->_db instanceof Zend_Db_Adapter_Abstract) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception('No object of type Zend_Db_Adapter_Abstract has been specified');
        }

        // get the table name
        if (! $this->_name) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception('No table name has been specified');
        }
        
        // get the table columns
        if (! $this->_cols) {
            $desc = $this->_db->describeTable($this->_name);
            $this->_cols = array_keys($desc);
        }

        // primary key
        if ($this->_primary && array_intersect((array) $this->_primary, $this->_cols) !== (array) $this->_primary) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Primary key column(s) ("
                . implode(',', (array) $this->_primary)
                . ") are not columns in this table ("
                . implode(',', $this->_cols)
                . ")");
        }
    }

    /**
     * Returns table information.
     *
     * @return array
     */
    public function info()
    {
        // @todo add more info
        // like _referenceMap, etc.
        return array(
            'name'    => $this->_name,
            'cols'    => (array) $this->_cols,
            'primary' => (array) $this->_primary,
        );
    }

    /**
     * Inserts a new row.
     *
     * @param  array  $data  Column-value pairs.
     * @param  string $where An SQL WHERE clause.
     * @return integer       The last insert ID.
     */
    public function insert(array $data)
    {
        $this->_db->insert($this->_name, $data);

        // @todo handle tables that have no auto-generated key.

        // @todo handle tables that use a named sequence instead
        // of an implict auto-generated key.

        return $this->_db->lastInsertId();
    }

    /**
     * Updates existing rows.
     *
     * @param array  $data  Column-value pairs.
     * @param string $where An SQL WHERE clause.
     * @return int          The number of rows updated.
     */
    public function update(&$data, $where)
    {
        return $this->_db->update($this->_name, $data, $where);
    }

    /**
     * Called by a row object for the parent table's class during save() method.
     *
     * @param string $parentTableClassname
     * @param array $oldPrimaryKey
     * @param array $newPrimaryKey
     */ 
    public function _cascadeUpdate($parentTableClassname, $oldPrimaryKey, $newPrimaryKey) 
    { 
        $rowsAffected = 0;
        foreach ($this->_referenceMap as $rule => $map) {
            if ($map[self::REF_TABLE_CLASS] != $parentTableClassname) {
                continue;
            }
            if (!isset($map[self::ON_UPDATE])) {
                continue;
            }
            switch ($map[self::ON_UPDATE]) {
                case self::CASCADE:
                    for ($i = 0; $i < count($map[self::COLUMNS]); ++$i) {
                        $where[] = $this->_db->quoteInto(
                            $this->_db->quoteIdentifier($map[self::COLUMNS][$i]) . ' = ?', 
                            $oldPrimaryKey[$map[self::REF_COLUMNS][$i]]
                        );
                    }
                    $rowsAffected += $this->update($newPrimaryKey, $where); 
                    break;
                case self::NO_ACTION:
                case self::RESTRICT:
                case self::SET_NULL:
                case self::SET_DEFAULT:
                default:
                    // @todo
                    break;
            }
        }
        return $rowsAffected;
    }

    /**
     * Deletes existing rows.
     *
     * @param  string $where An SQL WHERE clause.
     * @return int           The number of rows deleted.
     */
    public function delete($where)
    {
        return $this->_db->delete($this->_name, $where);
    }

    /**
     * Called by parent table's class during delete() method.
     *
     * @param string $parentTableClassname
     * @param array $primaryKey
     */ 
    public function _cascadeDelete($parentTableClassname, $primaryKey) 
    { 
        $rowsAffected = 0;
        foreach ($this->_referenceMap as $rule => $map) {
            if ($map[self::REF_TABLE_CLASS] != $parentTableClassname) {
                continue;
            }
            if (!isset($map[self::ON_DELETE])) {
                continue;
            }
            switch ($map[self::ON_DELETE]) {
                case self::CASCADE:
                    for ($i = 0; $i < count($map[self::COLUMNS]); ++$i) {
                        $where[] = $this->_db->quoteInto(
                            $this->_db->quoteIdentifier($map[self::COLUMNS][$i]) . ' = ?', 
                            $primaryKey[$map[self::REF_COLUMNS][$i]]
                        );
                    }
                    $rowsAffected += $this->delete($where); 
                    break;
                case self::NO_ACTION:
                case self::RESTRICT:
                case self::SET_NULL:
                case self::SET_DEFAULT:
                default:
                    // @todo
                    break;
            }
        }
        return $rowsAffected;
    } 

    /**
     * Fetches rows by primary key.
     * The arguments specify the primary key values.
     * If the table has a multi-column primary key, you must
     * pass as many arguments as the count of column in the
     * primary key.
     *
     * To find multiple rows by primary key, the argument
     * should be an array.  If the table has a multi-column
     * primary key, all arguments must be arrays with the
     * same number of elements.
     *
     * The find() method always returns a Rowset object,
     * even if only one row was found.
     *
     * @param  mixed                         The value(s) of the primary key.
     * @return Zend_Db_Table_Rowset_Abstract Row(s) matching the criteria.
     * @throws Zend_Db_Table_Exception
     */
    public function find()
    {
        $args = func_get_args();
        $keyNames = array_values((array) $this->_primary);

        if (empty($args)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("No value(s) specified for the primary key");
        }

        if (count($args) != count($keyNames)) {
            require_once 'Zend/Db/Table/Exception.php';
            throw new Zend_Db_Table_Exception("Missing value(s) for the primary key");
        }

        $whereList = array();
        foreach ($args as $keyPosition => $keyValues) {
            // Coerce the values to an array.
            // Don't simply typecast to array, because the values
            // might be Zend_Db_Expr objects.
            if (!is_array($keyValues)) {
                $keyValues = array($keyValues);
            }
            for ($i = 0; $i < count($keyValues); ++$i) {
                $whereList[$i][$keyPosition] = $keyValues[$i];
            }
        }

        $whereClause = null;
        if (count($whereList)) {
            $whereOrTerms = array();
            foreach ($whereList as $keyValueSets) {
                $whereAndTerms = array();
                foreach ($keyValueSets as $keyPosition => $keyValue) {
                    $whereAndTerms[] = $this->_db->quoteInto(
                        $this->_db->quoteIdentifier($keyNames[$keyPosition]) . ' = ?',
                        $keyValue
                    );
                }
                $whereOrTerms[] = '(' . implode(' AND ', $whereAndTerms) . ')';
            }
            $whereClause = '(' . implode(' OR ', $whereOrTerms) . ')';
        }

        return $this->fetchAll($whereClause);
    }

    /**
     * Fetches all rows.
     *
     * Honors the Zend_Db_Adapter fetch mode.
     *
     * @param string|array $where            OPTIONAL An SQL WHERE clause.
     * @param string|array $order            OPTIONAL An SQL ORDER clause.
     * @param int          $count            OPTIONAL An SQL LIMIT count.
     * @param int          $offset           OPTIONAL An SQL LIMIT offset.
     * @return Zend_Db_Table_Rowset_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchAll($where = null, $order = null,
        $count = null, $offset = null)
    {
        $data  = array(
            'table'    => $this,
            'data'     => $this->_fetch('All', $where, $order, $count, $offset),
            'rowclass' => $this->_rowClass
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        return new $this->_rowsetClass($data);
    }

    /**
     * Fetches one row.
     *
     * Honors the Zend_Db_Adapter_Abstract fetch mode.
     *
     * @param string|array $where         OPTIONAL An SQL WHERE clause.
     * @param string|array $order         OPTIONAL An SQL ORDER clause.
     * @return Zend_Db_Table_Row_Abstract The row results per the Zend_Db_Adapter fetch mode.
     */
    public function fetchRow($where = null, $order = null)
    {
        $keys    = array_values((array) $this->_primary);
        $vals    = array_fill(0, count($keys), null);
        $primary = array_combine($keys, $vals);

        $row = $this->_fetch('Row', $where, $order, 1);

        $values = array_filter(array_intersect_key((array) $row, $primary));

        // @todo is this the right thing to do?
        // How can we tell that the query was unsuccessful?
        if (empty($values)) {
            return $this->fetchNew();
        }

        $data = array(
            'table'   => $this,
            'data'    => $row
        );

        Zend_Loader::loadClass($this->_rowClass);
        return new $this->_rowClass($data);
    }

    /**
     * Fetches a new blank row (not from the database).
     *
     * @param  array|null $defaults User-supplied defaults for a new row
     * @return Zend_Db_Table_Row_Abstract
     */
    public function fetchNew()
    {
        $keys = array_values($this->_cols);
        $vals = array_fill(0, count($keys), null);
        $row  = array_combine($keys, $vals);

        $config = array(
            'table'   => $this,
            'data'    => $row
        );

        Zend_Loader::loadClass($this->_rowClass);
        return new $this->_rowClass($config);
    }

    /**
     * Support method for fetching rows.
     *
     * @param string       $type   Whether to fetch 'all' or 'row'.
     * @param string|array $where  OPTIONAL An SQL WHERE clause.
     * @param string|array $order  OPTIONAL An SQL ORDER clause.
     * @param int          $count  OPTIONAL An SQL LIMIT count.
     * @param int          $offset OPTIONAL An SQL LIMIT offset.
     * @return mixed               The row results per the Zend_Db_Adapter fetch mode.
     */
    protected function _fetch($type, $where = null, $order = null,
        $count = null, $offset = null)
    {
        // selection tool
        $select = $this->_db->select();

        // the FROM clause
        $select->from($this->_name, $this->_cols);

        // the WHERE clause
        $where = (array) $where;
        foreach ($where as $key => $val) {
            // is $key an int?
            if (is_int($key)) {
                // $val is the full condition
                $select->where($val);
            } else {
                // $key is the condition with placeholder,
                // and $val is quoted into the condition
                $select->where($key, $val);
            }
        }

        // the ORDER clause
        if (!is_array($order)) {
            $order = array($order);
        }
        foreach ($order as $val) {
            $select->order($val);
        }

        // the LIMIT clause
        $select->limit($count, $offset);

        // return the results
        $method = "fetch$type";
        $mode = $this->_db->getFetchMode();
        $this->_db->setFetchMode(Zend_Db::FETCH_ASSOC);
        $data = $this->_db->$method($select);
        $this->_db->setFetchMode($mode);
        return $data;
    }

}
