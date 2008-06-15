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
 * @package    Zend_Ical
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */


/**
 * Zend_Ical_Source_Exception
 */
require_once 'Zend/Ical/Source/Exception.php';


/**
 * @category   Zend
 * @package    Zend_Ical
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Ical_Source_Abstract
{
    /**
     * URI of the source
     *
     * @var string
     */
    protected $_uri;


    /**
     * Set the URI of the source
     */
    public function __construct($uri)
    {
        $this->_uri = $uri;
    }

    /**
     * Get raw data from the source
     *
     * @throws Zend_Ical_Source_Exception
     * @return string
     */
    abstract public function getRawData();

    /**
     * Put raw data into the source
     *
     * @param string $data
     * @throws Zend_Ical_Source_Exception
     */
    abstract public function putRawData($data);
}
