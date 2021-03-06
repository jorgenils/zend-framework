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
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Zend_Service_Simpy_Watchlist
 */
require_once 'Zend/Service/Simpy/Watchlist.php';

/**
 * @category   Zend
 * @package    Zend_Service
 * @subpackage Simpy
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Service_Simpy_WatchlistSet implements IteratorAggregate
{
    /**
     * List of watchlists
     *
     * @var Zend_Service_Simpy_Watchlist
     */
    protected $_watchlists;
    
    /**
     * Constructor to initialize the object with data
     *
     * @param DOMDocument $doc Parsed response from a GetWatchlists operation
     */
    public function __construct(DOMDocument $doc)
    {
        $xpath = new DOMXPath($doc);
        $list = $xpath->query('//watchlists/watchlist');
        $this->_watchlists = array();
        
        for ($x = 0; $x < $list->length; $x++) {
            $this->_watchlists[$x] = new Zend_Service_Simpy_Watchlist($list->item($x));
        }
    }
    
    /**
     * Returns an iterator for the watchlist set
     *
     * @return IteratorIterator
     */
    public function getIterator()
    {
        $array = new ArrayObject($this->_watchlists);
        return $array->getIterator();
    }
    
    /**
     * Returns the number of watchlists in the set
     * 
     * @return int
     */
    public function getLength()
    {
        return count($this->_watchlists);
    }
}
