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
 * @package    Zend_Dojo
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: $
 */

/**
 * dojo.data support for Zend Framework
 * 
 * @uses       ArrayAccess
 * @uses       Iterator
 * @uses       Countable
 * @package    Zend_Dojo
 * @copyright  Copyright (c) 2005-2008 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Dojo_Data implements ArrayAccess,Iterator,Countable 
{
    /**
     * Identifier field of item
     * @var string|int
     */
    protected $_identifier;

    /**
     * Label field of item
     * @var string
     */
    protected $_label;

    /**
     * Constructor
     * 
     * @param  array|Traversable|null $items 
     * @param  string|null $identifier 
     * @param  string|null $label 
     * @return void
     */
    public function __construct($items = null, $identifier = null, $label = null) 
    { 
        if (null !== $items) { 
            $this->setItems($items); 
        } 
        if (null !== $identifier) { 
            $this->setIdentifier($identifier); 
        } 
        if (null !== $label) { 
            $this->setLabel($label); 
        } 
    } 
 
    /**
     * Set the items to collect
     *
     * @param array|Traversable $items
     * @return Zend_Dojo_Data
     */
    public function setItems($items)
    {
    }

    /**
     * Set an individual item, optionally by identifier (overwrites)
     *
     * @param  array|object $item
     * @param  string|null $identifier
     * @return Zend_Dojo_Data
     */
    public function setItem($item, $identifier = null)
    {
    }

    /**
     * Add an individual item, optionally by identifier
     *
     * @param  array|object $item
     * @param  string|null $identifier
     * @return Zend_Dojo_Data
     */
    public function addItem($item, $identifier = null)
    {
    }

    /**
     * Add multiple items at once
     *
     * @param  array|Traversable $items
     * @return Zend_Dojo_Data
     */
    public function addItems($items)
    {
    }

    /**
     * Get all items as an array
     *
     * Serializes items to arrays.
     *
     * @return array
     */
    public function getItems()
    {
    }

    /**
     * Retrieve an item by identifier
     *
     * Item retrieved will be flattened to an array.
     *
     * @param  string $identifier
     * @return array
     */
    public function getItem($identifier)
    {
    }

    /**
     * Remove item by identifier
     *
     * @param  string $identifier
     * @return Zend_Dojo_Data
     */
    public function removeItem($identifier)
    {
    }

    /**
     * Remove all items at once
     *
     * @return Zend_Dojo_Data
     */
    public function clearItems()
    {
    }

 
    /**
     * Set identifier for item lookups
     *
     * @param  string|int|null $identifier
     * @return Zend_Dojo_Data
     */
    public function setIdentifier($identifier)
    {
        if (null === $identifier) {
            $this->_identifier = null;
        } elseif (is_string($identifier)) {
            $this->_identifier = $identifier;
        } elseif (is_numeric($identifier)) {
            $this->_identifier = (int) $identifier;
        } else {
            require_once 'Zend/Dojo/Exception.php';
            throw new Zend_Dojo_Exception('Invalid identifier; please use a string or integer');
        }

        return $this;
    }

    /**
     * Retrieve current item identifier
     *
     * @return string|int|null
     */
    public function getIdentifier()
    {
        return $this->_identifier;
    }

 
    /**
     * Set label to use for displaying item associations
     *
     * @param  string|null $label
     * @return Zend_Dojo_Data
     */
    public function setLabel($label)
    {
        if (null === $label) {
            $this->_label = null;
        } else {
            $this->_label = (string) $label;
        }
        return $this;
    }

    /**
     * Retrieve item association label
     *
     * @return string|null
     */
    public function getLabel()
    {
        return $this->_label;
    }

    /**
     * Seralize entire data structure, including identifier and label, to array
     * 
     * @return array
     */
    public function toArray()
    {
    }
 
    /**
     * Serialize to JSON (dojo.data format)
     *
     * @return string
     */
    public function toJson()
    {
    }

    /**
     * Serialize to string (proxy to {@link toJson()})
     *
     * @return string
     */
    public function __toString()
    {
    }

    /**
     * ArrayAccess: does offset exist?
     *
     * @param  string $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
    }

    /**
     * ArrayAccess: retrieve by offset
     *
     * @param  string $offset
     * @return array
     */
    public function offsetGet($offset)
    {
    }

    /**
     * ArrayAccess: set value by offset
     *
     * @param  string $offset
     * @param  array|object|null $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * ArrayAccess: unset value by offset
     *
     * @param  string $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
    }

    /**
     * Iterator: get current value
     *
     * @return array
     */
    public function current()
    {
    }

    /**
     * Iterator: get current key
     *
     * @return string|int
     */
    public function key()
    {
    }

    /**
     * Iterator: get next item
     *
     * @return void
     */
    public function next()
    {
    }

    /**
     * Iterator: rewind to first value in collection
     *
     * @return void
     */
    public function rewind()
    {
    }

    /**
     * Iterator: is item valid?
     *
     * @return bool
     */
    public function valid()
    {
    }

    /**
     * Countable: how many items are present
     *
     * @return int
     */
    public function count()
    {
    }
} 
