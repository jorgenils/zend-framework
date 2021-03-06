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
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */

/**
 * Gdata CodeSearch
 *
 * @link http://code.google.com/apis/gdata/codesearch.html
 *
 * @category   Zend
 * @package    Zend_Gdata
 * @copyright  Copyright (c) 2006 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Gdata_CodeSearch extends Zend_Gdata
{
    const CODESEARCH_FEED_URI = 'http://www.google.com/codesearch/feeds/search';

    /**
     * Retreive feed object
     *
     * @return Zend_Feed
     */
    public function getCodeSearchFeed($uri = null)
    {
        if ($uri == null) {
            $uri = self::CODESEARCH_FEED_URI;
        }
        $uri .= $this->getQueryString();
        return parent::getFeed($uri);
    }

    /**
     * There are no POST operations for CodeSearch.
     *
     * @param string $xml
     * @param string $uri 
     * @throws Zend_Gdata_Exception
     */
    public function post($xml, $uri = null)
    {
        throw Zend::exception('Zend_Gdata_Exception', 'There are no post operations for CodeSearch.');
    }

    /**
     * @param string $var
     * @param string $value
     */
    protected function __set($var, $value)
    {
        switch ($var) {
            case 'updatedMin':
            case 'updatedMax':
                throw Zend::exception('Zend_Gdata_Exception', "Parameter '$var' is not currently supported in CodeSearch.");
                break;
        }
        parent::__set($var, $value);
    }

}
