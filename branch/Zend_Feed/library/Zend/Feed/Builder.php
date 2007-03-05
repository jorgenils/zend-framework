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

require_once 'Zend/Feed/Builder/Interface.php';
require_once 'Zend/Feed/Exception.php';

/**
 * A simple implementation of Zend_Feed_Builder_Interface.
 *
 * Users are encouraged to make their own classes to implement Zend_Feed_Builder_Interface
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

class Zend_Feed_Builder implements Zend_Feed_Builder_Interface
{
    /**
     * The data of the feed
     *
     * @var $_data array
     */
    private $_data;

    /**
     * Constructor
     *
     * @param $data array
     * @throws Zend_Feed_Exception
     */
    public function __construct(array $data)
    {
        $this->_data = $data;
        $this->_validate();
    }

    /**
     * Returns feed data
     *
     * @return array
     */
    public function getFeedData()
    {
        return $this->_data;
    }

    /**
     * Validate the content of the data array
     *
     * @throws Zend_Feed_Exception
     */
    private function _validate()
    {
        /* general properties */
        $mandatories = array('title', 'link', 'lastUpdate', 'charset');
        foreach ($mandatories as $mandatory) {
            if (empty($this->_data[$mandatory])) {
                throw new Zend_Feed_Exception("you have to set $mandatory key to a non empty value");
            }
        }
        if (!isset($this->_data['entries'])) {
            throw new Zend_Feed_Exception("you have to set entries key");
        }

        /* entry properties */
        $mandatories = array('title', 'link', 'description');
        foreach ($this->_data['entries'] as $idx => $entry) {
            foreach ($mandatories as $mandatory) {
                if (empty($entry[$mandatory])) {
                    throw new Zend_Feed_Exception("you have to set $mandatory key (entry $idx) to a non empty value");
                }
            }
        }
    }
}