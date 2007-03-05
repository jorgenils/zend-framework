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
 * Input feed data interface
 *
 * Classes implementing this interface can be passe to Zend_Feed::factory
 * as an input data source for the Zend_Feed construction
 *
 * @category   Zend
 * @package    Zend_Feed
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
interface Zend_Feed_Builder_Interface
{
    /**
     * Returns feed data
     * 
     * The returned data must have the following format:
     * <code>
     *  array( 
     *  'title'       => 'title of the feed', //required
     *  'link'        => 'canonical url to the feed', //required
     *  'lastUpdate'  => 'timestamp of the publication date', // required
     *  'charset'     => 'charset', // required
     *  'description' => 'short description of the feed', //optional
     *  'author'      => 'author/publisher of the feed', //optional
     *  'email'       => 'email of the author', //optional
     *  'copyright'   => 'copyright notice', //optional
     *  'image'       => 'url to image', //optional
     *  'generator'   => 'generator', // optional
     *  'entries'     => array(
     *                   array(
     *                    'title'        => 'title of the feed entry', //required
     *                    'link'         => 'url to a feed entry', //required
     *                    'description'  => 'short version of a feed entry', // only text, no html, required
     *                    'guid'         => 'id of the article, if not given link value will used', //optionnal
     *                    'content'      => 'long version', // can contain html, optionnal
     *                    'lastUpdate'   => 'timestamp of the publication date' // optionnal
     *                   ),
     *                   array(
     *                   //data for the second entry and so on
     *                   )
     *                 )
     * );
     * </code>
     * 
     * @return array
     */
    public function getFeedData();
}