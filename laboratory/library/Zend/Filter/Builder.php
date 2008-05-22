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
 * @package    Zend_Filter
 * @subpackage Zend_Filter_Builder
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Zend/Filter/Interface.php';

/**
 * Zend Filter Builder
 *
 * Filters an entire set of data in one pass using a composite structure of 
 * Zend_Filter_Interface instances. Provides a Builder API for constructing the 
 * filtration structure. Since Zend_Filter_Builder follows the Composite 
 * pattern, complex filter structures can be acheived in a relatively 
 * straight-forward manner.
 */
class Zend_Filter_Builder implements Zend_Filter_Interface
{
    /**
     * Specifies that the field name given is to be interpreted as a PCRE regex.  
     * All input data field names that match this pattern will be passed to the 
     * filter. If not set, the field name is a simple literal string match.
     */
    const PATTERN = 1;

    /**
     * Filter table. This is an array of arrays, just like a result set from a 
     * database query. There are three columns in each row: filter, fields, and 
     * flags. The filter column holds the instance of a filter, the fields 
     * column holds either a field name, an array of field names, or a PCRE 
     * regex of field names. The flags column holds any special flags set for 
     * this intersection of filter and field(s).
     *
     * @var array 2-dimensional array
     */
    protected $_filterTable;

    /**
     * Data set to filter
     *
     * @var array
     */
    public $dataSet;


    /**
     * Constructor
     *
     * @param array Optional array of data to filter
     * @param array Optional options
     * @returns void
     * @throws none
     */
    public function __construct(array $dataSet = null)
    {
        $this->dataSet      = $dataSet;
        $this->_filterTable = array();
    }

    /**
     * Clear
     *
     * Resets all the class's internal state.
     */
    public function clear()
    {
        $this->dataSet      = null;
        $this->_filterTable = array();
    }

    /**
     * Set Flags
     *
     * Provides a way to set field/filter flags outside the context of a call to 
     * add(). It requires the numeric array index of the filter "row" you want 
     * to modify. (add() returns the new row index it added.)
     *
     * @param int Filter row index
     * @param int Flags, just like the 3rd parm of add()
     * @returns void
     * @throws Zend_Filter_Exception
     */
    public function setFlags($id, $flags = 0)
    {
        if ((int)$id < 0) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('A filter index is required');
        }

        $this->_filterTable[(int)$id]['flags'] = $flags;
    }

    /**
     * Add Flags
     *
     * Just like setFlags, but appends (bitwise OR, actually) the given flags to 
     * the ones currently set, instead of overwriting.
     *
     * @param int Filter row index
     * @param int Flags, just like the 3rd parm of add()
     * @returns void
     * @throws Zend_Filter_Exception
     */
    public function addFlags($id, $flags = 0)
    {
        if ((int)$id < 0) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('A filter index is required');
        }

        $this->_filterTable[(int)$id]['flags'] |= $flags;
    }

    /**
     * Get Flags
     *
     * Allows interrogation of the current flags for a specific "row" in the 
     * current set of filters.
     *
     * @param int Filter row index
     * @returns int The flags, or null if invalid index
     * @throws Zend_Filter_Exception
     */
    public function getFlags($id)
    {
        $id = (int) $id;

        if ($id < 0) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('A filter index is required');
        }

        return array_key_exists($id, $this->_filterTable)? 
                       $this->_filterTable[$id]['flags'] :
                                                    null ;
    }

    /**
     * Add Filter
     *
     * @param Zend_Filter_Interface Filter object
     * @param string|array Field to filter. Can be a string or array. If an 
     * array, each element is a field name to apply the filter to. If a string, 
     * it can be single field name, or a PCRE regex to match against field names 
     * in the data set. Set the PATTERN flag to control this option.
     * @param int Optional flags. Bitwise OR'ed values of the class constants
     * @returns void
     * @throws Zend_Filter_Exception
     */
    public function add(Zend_Filter_Interface $filter, $fieldSpec, $flags = 0)
    {
        if (empty($fieldSpec)) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('A field specification is required');
        }

        $this->_filterTable[] = array(
            'filter'    => $filter,
            'fieldSpec' => $fieldSpec,
            'flags'     => $flags
        );

        // Return the index of the new row. Unlike with ZVB, the filter fluent 
        // facade doesn't need this, but it's better to keep the behaviour 
        // consistent.
        return count($this->_filterTable) - 1;
    }

    // Zend_Filter_Interface

    /**
     * Filter
     *
     * If a value is passed to the composite filter(), it should be an array 
     * representing the entire data set the filter structure should filter.  
     * This will override the dataset given to the constructor or the dataSet() 
     * method.
     *
     * @see Zend_Filter_Interface::filter()
     *
     * @param array Set of data to filter. May be null
     * @returns mixed The new, filtered data
     * @throws Zend_Filter_Exception
     */
    public function filter($dataSet)
    {
        if (null == $dataSet) {
            $dataSet = $this->dataSet;
        }
        if (null == $dataSet || !is_array($dataSet)) {
            require_once 'Zend/Filter/Exception.php';
            throw new Zend_Filter_Exception('A data set to filter is required, and must be an array');
        }

        foreach ($this->_filterTable as $row) {

            // If we have a normal, plain literal field name...
            if (is_string($row['fieldSpec']) && !($row['flags'] & self::PATTERN)) {

                // Filter the specific field
                $field = $row['fieldSpec'];

                // If the given field does not exist, we're not going to try to 
                // filter it.
                if (isset($dataSet[$field])) {
                    $dataSet[$field] = $row['filter']->filter($dataSet[$field]);
                }

            // If we have a field name pattern...
            } else if (is_string($row['fieldSpec']) && $row['flags'] & self::PATTERN) {

                // Filter each matching field
                foreach ($dataSet as $field => $value) {
                    if (isset($dataSet[$field]) &&
                        1 == preg_match($row['fieldSpec'], $field)) {
                        $dataSet[$field] = $row['filter']->filter($dataSet[$field]);
                    }
                }

            // If we have an array of fields to filter individually...
            } else if (is_array($row['fieldSpec'])) {

                // Filter each given field name
                foreach ($row['fieldSpec'] as $field) {

                    // If the given field does not exist, we're not going to try 
                    // to filter it.
                    if (isset($dataSet[$field])) {
                        $dataSet[$field] = $row['filter']->filter($dataSet[$field]);
                    }
                }
            }
        }

        return $dataSet;
    }
}
