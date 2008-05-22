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
 * @subpackage Zend_Filter
 * @copyright  2007 Bryce Lohr (blohr@gearheadsoftware.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

require_once 'Zend/Filter/Interface.php';

/**
 * Zend Filter Array
 *
 * Filter decorator that runs the given Filter over the array value passed to 
 * filter(). It basically converts an existing filter into one that can filter 
 * an array; optionally recursively.
 */
class Zend_Filter_Array implements Zend_Filter_Interface
{
    /**
     * Filter we're decorating
     *
     * @var Zend_Filter_Interface
     */
    protected $_filter;

    /**
     * Flag that determines whether this decorator should recurse into nested 
     * arrays found in the input data
     *
     * @var bool
     */
    public $recursive;


    /**
     * Constructor
     *
     * The recursive flag tells the filter to recurse into any nested arrays 
     * found in the data input to filter(). Be sure this flag is set to false 
     * (default) if the filter being decorated is an instance of 
     * Zend_Filter_Builder. ZFB operates on arrays, so the recursive flag would 
     * break that behaviour.
     *
     * @param Zend_Filter_Interface Validator
     * @param bool Recursive flag; false by default
     * @returns void
     * @throws none
     */
    public function __construct(Zend_Filter_Interface $filter, $recursive = false)
    {
        $this->_filter   = $filter;
        $this->recursive = (bool) $recursive;
    }

    /**
     * Filter
     *
     * @see Zend_Filter_Interface::filter()
     *
     * @param array Array of values to filter
     * @returns array The input array, each element processed through the filter
     * @throws Zend_Filter_Exception
     */
    public function filter($value)
    {
        if (!is_array($value)) {
            throw new Zend_Filter_Exception('Value to filter must be an array');
        }

        foreach ($value as $key => $elem) {
            
            if ($this->recursive && is_array($elem)) {
                $value[$key] = $this->filter($elem);
            } else {
                $value[$key] = $this->_filter->filter($elem);
            }
        }

        return $value;
    }
}
