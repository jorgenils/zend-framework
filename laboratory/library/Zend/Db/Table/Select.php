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
 * @subpackage Select
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Select.php 5308 2007-06-14 17:18:45Z bkarwin $
 */


/**
 * @see Zend_Db_Select
 */
require_once 'Zend/Db/Select.php';


/**
 * @see Zend_Db_Table_Abstract
 */
require_once 'Zend/Db/Table/Abstract.php';


/**
 * Class for SQL SELECT query manipulation for the Zend_Db_Table component.
 *
 * @category   Zend
 * @package    Zend_Db
 * @subpackage Table
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Db_Table_Select extends Zend_Db_Select
{
    /**
     * Table schema for parent Zend_Db_Table.
     *
     * @var array
     */
    protected $_info;

    /**
     * Class constructor
     *
     * @param Zend_Db_Table_Abstract $adapter
     */
    public function __construct(Zend_Db_Table_Abstract $table)
    {
        $this->setTable($table);
        $this->_parts   = $this->_partsInit;
    }

    /**
     * Sets the primary table name and retrieves the table schema
     *
     * @param Zend_Db_Table_Abstract $adapter
     */
    public function setTable(Zend_Db_Table_Abstract $table)
    {
        $this->_adapter = $table->getAdapter();
        $this->_info    = $table->info();
        return $this;
    }

    /**
     * Tests query to determine if expressions or aliases columns exist.
     *
     * @return boolean
     */
    public function isReadOnly()
    {
        $readOnly = false;
        $fields   = $this->getPart(Zend_Db_Table_Select::COLUMNS);
        $cols     = $this->_info[Zend_Db_Table_Abstract::COLS];
        
        if (!count($fields)) {
            return $readOnly;
        }
        
        foreach ($fields as $columnEntry) {
            list($table, $column, $alias) = $columnEntry;

            if ($alias !== null) {
                $column = $alias;
            }
            
            switch (true) {
                case ($column == '*'):
                    break;

                case ($column instanceof Zend_Db_Expr):
                case (!in_array($column, $cols)):
                    $readOnly = true;
                    break 2;
            }
        }
        
        return $readOnly;
    }

    /**
     * Performs a validation on the select query before passing back to the parent class.
     * Ensures that only columns from the Zend_Db_Table are returned in the result.
     *
     * @return boolean
     */
    public function __toString()
    {
        $fields  = $this->getPart(Zend_Db_Table_Select::COLUMNS);
        $primary = $this->_info[Zend_Db_Table_Abstract::NAME];
        
        // If no fields are specified we assume all fields from primary table
        if (!count($fields)) {
            $this->from($primary, '*');
            $fields = $this->getPart(Zend_Db_Table_Select::COLUMNS);
        }
        
        $from    = $this->getPart(Zend_Db_Table_Select::FROM);
        
        foreach ($fields as $columnEntry) {
            list($table, $column, $alias) = $columnEntry;
            
            // Check each column to ensure it only references the primary table
            if ($column) {
                if (!isset($from[$table]) || $from[$table]['tableName'] != $primary) {
                    require_once 'Zend/Db/Table/Exception.php';
                    throw new Zend_Db_Table_Exception("Select query cannot join with another table");
                }
            }
        }

        return parent::__toString();
    }

    /**
     * Disables the query SELECT FOR UPDATE.
     *
     * @return Zend_Db_Table_Select_Exception.
     */
    public function forUpdate()
    {
        require_once 'Zend/Db/Table/Select/Exception.php';
        throw new Zend_Db_Table_Select_Exception("SELECT FOR UPDATE is not supported");
    }
}
