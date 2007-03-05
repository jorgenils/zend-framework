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
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Zend_Feed_Element
 */
require_once 'Zend/Feed/Element.php';


/**
 * The Zend_Feed_Abstract class is an abstract class representing feeds.
 *
 * Zend_Feed_Abstract implements two core PHP 5 interfaces: ArrayAccess and
 * Iterator. In both cases the collection being treated as an array is
 * considered to be the entry collection, such that iterating over the
 * feed takes you through each of the feed.s entries.
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Feed_Abstract extends Zend_Feed_Element implements Iterator
{

    /**
     * Current index on the collection of feed entries for the
     * Iterator implementation.
     *
     * @var integer
     */
    protected $_entryIndex = 0;

    /**
     * Cache of feed entries.
     *
     * @var array
     */
    protected $_entries;

    /**
     * Feed constructor
     *
     * The Zend_Feed_Abstract constructor takes the URI of a feed or a
     * feed represented as a string and loads it as XML.
     *
     * @throws Zend_Feed_Exception If loading the feed failed.
     *
     * @param string $uri The full URI of the feed to load, or NULL if not retrieved via HTTP or as an array.
     * @param string $string The feed as a string, or NULL if retrieved via HTTP or as an array.
     * @param array $array The feed as an array or NULL if retrieved as a string or via HTTP.
     */
    public function __construct($uri = null, $string = null, $array = null)
    {
        if ($uri !== null) {
            // Retrieve the feed via HTTP
            $client = Zend_Feed::getHttpClient();
            $client->setUri($uri);
            $response = $client->request('GET');
            if ($response->getStatus() !== 200) {
                throw new Zend_Feed_Exception('Feed failed to load, got response code ' . $response->getStatus());
            }
            $this->_element = $response->getBody();
            $this->__wakeup();
        } elseif ($string !== null) {
            // Retrieve the feed from $string
            $this->_element = $string;
            $this->__wakeup();
        } else {
            // Generate the feed from the array
            $this->_checkFeedData($array);
            $this->_element = new DOMDocument('1.0', $array['charset']);
            $root = $this->_mapFeedHeaders($array);
            $this->_mapFeedEntries($root, $array);
            $this->_element = $root;
            $this->_buildEntryCache();
        }
    }


    /**
     * Load the feed as an XML DOMDocument object
     */
    public function __wakeup()
    {
        @ini_set('track_errors', 1);
        $doc = new DOMDocument();
        $success = @$doc->loadXML($this->_element);
        @ini_restore('track_errors');

        if (!$success) {
            throw new Zend_Feed_Exception("DOMDocument cannot parse XML: $php_errormsg");
        }

        $this->_element = $doc;
    }


    /**
     * Prepare for serialiation
     *
     * @return array
     */
    public function __sleep()
    {
        $this->_element = $this->saveXML();

        return array('_element');
    }


    /**
     * Cache the individual feed elements so they don't need to be
     * searched for on every operation.
     *
     * @internal
     */
    protected function _buildEntryCache()
    {
        $this->_entries = array();
        foreach ($this->_element->childNodes as $child) {
            if ($child->localName == $this->_entryElementName) {
                $this->_entries[] = $child;
            }
        }
    }


    /**
     * Get the number of entries in this feed object.
     *
     * @return integer Entry count.
     */
    public function count()
    {
        return count($this->_entries);
    }


    /**
     * Required by the Iterator interface.
     *
     * @internal
     */
    public function rewind()
    {
        $this->_entryIndex = 0;
    }


    /**
     * Required by the Iterator interface.
     *
     * @internal
     *
     * @return mixed The current row, or null if no rows.
     */
    public function current()
    {
        return new $this->_entryClassName(
            null,
            $this->_entries[$this->_entryIndex]);
    }


    /**
     * Required by the Iterator interface.
     *
     * @internal
     *
     * @return mixed The current row number (starts at 0), or NULL if no rows
     */
    public function key()
    {
        return $this->_entryIndex;
    }


    /**
     * Required by the Iterator interface.
     *
     * @internal
     *
     * @return mixed The next row, or null if no more rows.
     */
    public function next()
    {
        ++$this->_entryIndex;
    }


    /**
     * Required by the Iterator interface.
     *
     * @internal
     *
     * @return boolean Whether the iteration is valid
     */
    public function valid()
    {
        return (0 <= $this->_entryIndex && $this->_entryIndex < $this->count());
    }

    /**
     * Checks the format of the user's data and ensure we can generate a Zend_Feed_Abstract object
     *
     * @param array $array the data to check - see Zend_Feed_Interface for array structure
     * @throws Zend_Feed_Exception
     */
    private function _checkFeedData($array)
    {
        $mandatory = array('title', 'link', 'lastUpdate', 'charset');
        foreach ($mandatory as $key) {
            if (empty($array[$key])) {
                throw new Zend_Feed_Exception("The key $key can not be empty");
            }
        }
    }

    /**
     * Generate the header of the feed when working in write mode
     *
     * @param array $array the data to use - see Zend_Feed_Interface for array structure
     * @return DOMElement root node
     * @internal
     */
    abstract protected function _mapFeedHeaders($array);

    /**
     * Generate the entries of the feed when working in write mode
     *
     * @param array $array the data to use - see Zend_Feed_Interface for array structure
     * @param DOMElement $root the root node to use
     * @internal
     */
    abstract protected function _mapFeedEntries(DOMElement $root, $array);

    /**
     * Send feed to a http client with the correct header
     *
     * @throws Zend_Feed_Exception if headers have already been sent 
     * @return void
     */
    abstract public function send();
}
